<?php

use App\Models\Endpoint;
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
        Schema::create('checkups', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Endpoint::class)->constrained()->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->unsignedDecimal('microtime', 10, 7);
            $table->string('url', 255)->nullable();
            $table->string('status_code', 10)->nullable();
            $table->string('exception_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkups');
    }
};
