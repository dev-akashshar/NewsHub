<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class NewsController extends Controller
{
    // ── Index ─────────────────────────────────────────────────
    public function index(Request $request)
    {
        $lang     = (string)($request->get('lang') ?? 'english');
        $category = (string)($request->get('category') ?? 'general');
        $search   = trim((string)($request->get('q') ?? ''));

        // Livewire Component `news-feed` now handles all data fetching and advanced query filtering logic natively inside the DOM.
        return view('news.index', compact('lang', 'category', 'search'));
    }



    // ── Show Internal Article ──────────────────────────────────
    public function show(string $id)
    {
        $found = Cache::get("article_{$id}");
        
        if (!$found) {
            return redirect()->route('news.index')->with('error', 'Article expired or not found.');
        }

        // Generate extensive fake paragraphs for realistic long-form reading based on title
        $paragraphs = [
            $found['content'] ?? "This is a developing story that has captured international attention. Authorities and experts are closely monitoring the situation as more crucial details continue to emerge from the verified sources on the ground.",
            "In recent press briefings, top officials and industry leaders highlighted the significant impact of these ongoing events on both local and global scales. The timeline of occurrences has raised multifaceted questions among political analysts who study these unprecedented trends in modern infrastructure.",
            "While some key observers emphasize the overwhelmingly positive aspects of this rapid development, seasoned critics warn of potential long-term socio-economic consequences that could disrupt the established ecosystem if left unchecked by regulatory frameworks.",
            "Looking ahead, primary stakeholders are expected to convene a special legislative session to address the immediate logistical challenges. We will continue to bring you exhaustive 24/7 live coverage and authoritative embedded reports as this situation continually unfolds."
        ];

        return view('news.show', ['article' => $found, 'paragraphs' => $paragraphs]);
    }


}
