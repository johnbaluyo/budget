<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGaaProjectMonthlyBudgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gaa_project_monthly_budgets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gaa_project_id');
            $table->integer('month'); // 1-12
            $table->integer('year');
            $table->decimal('budget_amount', 12, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('gaa_project_id')->references('id')->on('gaa_project')->onDelete('cascade');
            $table->unique(['gaa_project_id', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gaa_project_monthly_budgets');
    }
}
