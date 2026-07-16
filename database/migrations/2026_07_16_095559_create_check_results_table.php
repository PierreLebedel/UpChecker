<?php

use App\Enums\CheckStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('check_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('monitor_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default(CheckStatus::Up->value);
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->longText('error_message')->nullable();
            $table->string('checked_url', 2048);
            $table->timestamp('checked_at');
            $table->longText('response_excerpt')->nullable();
            $table->timestamps();

            $table->index(['monitor_id', 'checked_at']);
            $table->index('checked_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('check_results');
    }
};
