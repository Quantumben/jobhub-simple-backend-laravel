<?php

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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title', 150);

            $table->string('company', 150);

            $table->string('location');

            $table->string('job_type');

            $table->string('work_mode');

            $table
                ->decimal('salary_min', 12, 2)
                ->nullable();

            $table
                ->decimal('salary_max', 12, 2)
                ->nullable();

            $table->text('description');

            $table->text('requirements');

            $table
                ->string('application_url')
                ->nullable();

            $table
                ->string('image')
                ->nullable();

            $table
                ->string('status')
                ->default('active');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
