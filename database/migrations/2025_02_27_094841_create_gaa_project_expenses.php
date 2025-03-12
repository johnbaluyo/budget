<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGaaProjectExpenses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gaa_project_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('gaa_project_id');
            $table->string('type'); //IN or OUT
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->string('remarks')->nullable();
            $table->integer('realign_from')->nullable(); //gaa_project_id
            $table->integer('realign_to')->nullable(); //gaa_project_id
            $table->integer('division_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gaa_project_expenses');
    }
}
