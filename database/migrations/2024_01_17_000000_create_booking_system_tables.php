<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Companies
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('data'); // JSON или структура
            $table->string('address');
            $table->timestamps();
        });

        // Saunas
        Schema::create('saunas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('address');
            $table->timestamps();
        });

        // Schedules
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sauna_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->json('slots'); // например, [{"time": "10:00", "status": "available"}]
            $table->timestamps();
        });

        // Clients
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->unique();
            $table->timestamps();
        });

        // Bookings
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
        Schema::dropIfExists('clients');
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('saunas');
        Schema::dropIfExists('companies');
    }
};
