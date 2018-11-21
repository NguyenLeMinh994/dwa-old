<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePriceInputTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('price_input', function (Blueprint $table) {
            $table->increments('id');

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
        Schema::dropIfExists('price_input');
    }
}
