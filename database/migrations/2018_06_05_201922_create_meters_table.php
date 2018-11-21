<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMetersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meters', function (Blueprint $table) {
            $table->string('RateCardId')->nullable();
            $table->string('MeterId')->nullable();
            $table->string('MeterName')->nullable();
            $table->string('MeterCategory')->nullable();
            $table->string('MeterSubCategory')->nullable();
            $table->string('Unit')->nullable();
            $table->string('MeterRates')->nullable();
            $table->string('EffectiveDate')->nullable();
            $table->string('MeterTags')->nullable();
            $table->string('MeterRegion')->nullable();
            $table->string('IncludedQuantity')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meters');
    }
}
