<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGaa extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gaa', function (Blueprint $table) {
            $table->id();
            $table->string('item_of_expenditure', 500);
            $table->string('object_type');
            $table->string('fund_cluster');
            $table->decimal('budget_allocation', 12, 2)->nullable()->default(0.00);
            $table->string('remarks')->nullable();
            $table->integer('division_id')->nullable();
            $table->integer('parent_id')->nullable();
            $table->string('approved_budget_id');
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
        Schema::dropIfExists('gaa');
    }
}
