<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chat_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('chat_with_id')->constrained('users')->onDelete('cascade');
            $table->string('auto_delete', 20)->default('never'); // never, 5min, seen, 1day, 7day, immediate
            $table->string('pin_hash')->nullable(); // bcrypt hashed PIN
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();
            $table->unique(['user_id', 'chat_with_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_settings');
    }
};
