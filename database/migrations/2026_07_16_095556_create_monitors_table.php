<?php

use App\Enums\MonitorCheckCriterionType;
use App\Enums\MonitorStatus;
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
        Schema::create('monitors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('url', 2048);
            $table->boolean('enabled')->default(true);
            $table->unsignedTinyInteger('interval_minutes')->default(5);
            $table->unsignedTinyInteger('timeout_seconds')->default(10);
            $table->json('check_criteria')->default(json_encode([
                ['type' => MonitorCheckCriterionType::HttpStatus->value, 'expected' => 200],
            ]));
            $table->string('current_status')->default(MonitorStatus::Unknown->value);
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('last_success_at')->nullable();
            $table->timestamp('last_failure_at')->nullable();
            $table->timestamp('last_alerted_at')->nullable();
            $table->timestamp('next_check_at')->nullable();
            $table->timestamps();

            $table->index(['enabled', 'next_check_at']);
            $table->index('current_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitors');
    }
};
