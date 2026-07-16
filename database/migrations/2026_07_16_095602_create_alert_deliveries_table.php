<?php

use App\Enums\AlertChannel;
use App\Enums\AlertDeliveryStatus;
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
        Schema::create('alert_deliveries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('monitor_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('check_result_id')->constrained()->cascadeOnDelete();
            $table->string('channel')->default(AlertChannel::Mail->value);
            $table->string('recipient');
            $table->string('status')->default(AlertDeliveryStatus::Pending->value);
            $table->timestamp('sent_at')->nullable();
            $table->longText('error_message')->nullable();
            $table->timestamps();

            $table->index(['monitor_id', 'created_at']);
            $table->index('check_result_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alert_deliveries');
    }
};
