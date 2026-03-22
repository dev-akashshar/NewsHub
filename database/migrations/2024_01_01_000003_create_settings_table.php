<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Default settings
        DB::table('settings')->insert([
            ['key' => 'logo_click_count', 'value' => '7', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'app_name',         'value' => 'NewsHub',  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'news_api_key',     'value' => '',         'created_at' => now(), 'updated_at' => now()],
            ['key' => 'session_timeout',  'value' => '30',       'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
