<?php

use App\Models\Project;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('endpoints', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 12)->unique();
            $table->foreignIdFor(Project::class)->constrained()->cascadeOnDelete();
            $table->string('url');
            $table->string('expected_status_code', 10)->nullable();
            $table->unsignedInteger('timeout')->nullable();
            $table->boolean('follow_redirects');
            $table->unsignedInteger('checkup_delay');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('endpoints');
    }
};
