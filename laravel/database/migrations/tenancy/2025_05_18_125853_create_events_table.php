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
        // (title, description, venue, date, price, max attendees)
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->uuid('serial_number');
            $table->string('cover_image')->nullable();
            $table->string('title');
            $table->longText('description');
            $table->string('venue');
            $table->date('date');
            $table->decimal('price', 10, 2);
            $table->integer('max_attendees');
            $table->boolean('status')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};