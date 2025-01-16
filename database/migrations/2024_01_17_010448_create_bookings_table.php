<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sauna_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->text('comment')->nullable();
            $table->timestamps();
            
            // Индексы для быстрого поиска
            $table->index(['sauna_id', 'start_datetime']);
            $table->index(['client_id', 'start_datetime']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bookings');
    }
};
