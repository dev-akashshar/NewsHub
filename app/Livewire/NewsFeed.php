<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use jcobhams\NewsApi\NewsApi;
use Livewire\Attributes\Url;

class NewsFeed extends Component
{
    #[Url] public $lang = 'english';
    #[Url] public $category = 'general';
    #[Url] public $search = '';
    #[Url] public $country = '';
    #[Url] public $source = '';
    #[Url] public $sortBy = '';
    #[Url] public $from = '';

    public function render()
    {
        $news = $this->getNews();
        return view('livewire.news-feed', compact('news'));
    }

    private function getNews(): array
    {
        $lang = $this->lang;
        $category = $this->category;
        $search = $this->search;
        $country = $this->country;
        $source = $this->source;
        $sortBy = $this->sortBy;
        $from = $this->from;

        $cacheKey = "news_api_livewire_{$lang}_{$category}_" . md5($search.$country.$source.$sortBy.$from);

        return Cache::remember($cacheKey, 60, function () use ($lang, $category, $search, $country, $source, $sortBy, $from) {
            $fetched = $this->fetchFromNewsAPI($lang, $category, $search, $country, $source, $sortBy, $from);
            return $fetched ?? []; 
        });
    }

    private function fetchFromNewsAPI(string $lang, string $category, string $search, string $country, string $source, string $sortBy, string $from): array
    {
        try {
            $apiKey = env('NEWSAPI_KEY', '10a5f38b677c4b62be0b11a38f044f1b');
            
            $newsapi = new NewsApi($apiKey);
            
            // Aggressively bypass SSL Verify failures inherent to cURL 60/77 on Local Windows PHP environments
            // jcobhams/newsapi does not declare the $client property, meaning we can overwrite it dynamically
            $newsapi->client = new \GuzzleHttp\Client(['verify' => false, 'timeout' => 30]);

            $language = $lang === 'hindi' ? 'hi' : 'en';

            $countryMap = ['india' => 'in', 'in' => 'in', 'us' => 'us', 'usa' => 'us', 'gb' => 'gb', 'uk' => 'gb'];
            $c = strtolower(trim($country));
            $mappedCountry = $countryMap[$c] ?? ($c ?: null);

            $s = strtolower(trim($source)) ?: null;
            $qQuery = strtolower(trim($search)) ?: null;
            $cat = $category !== 'general' ? $category : null;

            if ($s) {
                // getTopHeadlines($q, $sources, $country, $category, $page_size, $page)
                $response = $newsapi->getTopHeadlines($qQuery, $s, null, null, 100, 1);
            } elseif ($mappedCountry) {
                $response = $newsapi->getTopHeadlines($qQuery, null, $mappedCountry, $cat, 100, 1);
            } else {
                $q = $qQuery;
                if (empty($q)) {
                    $q = $category === 'general' ? 'latest news' : $category;
                    if ($lang === 'hindi') $q .= ' india hindi';
                }

                $sb = !empty($sortBy) ? $sortBy : 'publishedAt';
                
                if (!empty($from)) {
                    $frm = $from === 'today' ? now()->format('Y-m-d') : 
                          ($from === 'week' ? now()->subDays(7)->format('Y-m-d') : 
                          ($from === 'month' ? now()->subDays(30)->format('Y-m-d') : $from));
                } else {
                    $frm = now()->subDays(3)->format('Y-m-d');
                }

                // The SDK getEverything method throws exceptions for unsupported languages like 'hi'.
                $safeLang = $language === 'hi' ? null : $language;

                // getEverything($q, $sources, $domains, $exclude_domains, $from, $to, $language, $sort_by, $page_size, $page)
                $response = $newsapi->getEverything($q, null, null, null, $frm, null, $safeLang, $sb, 100, 1);
            }

            if (empty($response->articles)) return [];

            $result = [];
            foreach ($response->articles as $item) {
                $articleUrl = $item->url ?? '#';
                $title = $item->title ?? '';

                if (empty($title) || str_contains($title, '[Removed]')) continue;

                $id = md5($articleUrl . $title);
                $image = $item->urlToImage ?? null;
                
                $article = [
                    'id'          => $id,
                    'title'       => trim($title),
                    'description' => $item->description ?? '',
                    'content'     => $item->content ?? '',
                    'url'         => $articleUrl,
                    'image'       => $image,
                    'publishedAt' => $item->publishedAt ?? now()->toRfc2822String(),
                    'source'      => ['name' => $item->source->name ?? 'NewsAPI'],
                    'author'      => $item->author ?? null,
                ];
                
                Cache::put("article_{$id}", $article, 86400);
                $result[] = $article;
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('NewsAPI SDK error: ' . $e->getMessage());
            return [];
        }
    }
}
