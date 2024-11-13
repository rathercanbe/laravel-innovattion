<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_times', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID jako klucz główny
            $table->uuid('employee_id'); // UUID pracownika jako klucz obcy
            $table->dateTime('start_time'); // Data i godzina rozpoczęcia
            $table->dateTime('end_time');   // Data i godzina zakończenia
            $table->date('start_day');      // Dzień rozpoczęcia pracy
            $table->timestamps();           // Automatyczne pola created_at i updated_at

            // Dodanie relacji w stosunku tabeli employees
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('work_times');
    }
}
