<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('morpheme_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('root_word_id');
            $table->bigInteger('morpheme_no')->nullable();
            $table->integer('weight');
            $table->jsonb('sheet_words');
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
        Schema::dropIfExists('morpheme_details');
    }
};
