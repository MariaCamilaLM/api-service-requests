<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('engineer_id')->nullable()->constrained()->onDelete('set null');
            $table->string('status')->default('opened');
            $table->string('title');
            $table->string('priority');
            $table->text('issue_description');
            $table->text('equipment_number');
            $table->text('serial_number');
            $table->text('brand');
            $table->boolean('is_under_warranty');
            $table->boolean('accept_conditions');
            $table->text('solution_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
