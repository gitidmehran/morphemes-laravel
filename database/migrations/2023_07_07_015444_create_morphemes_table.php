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
        Schema::create('morphemes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('root_word_id');
            $table->string('root_word');
            $table->bigInteger('word_id');
            $table->string('word');
            $table->string('word_reference')->nullable();
            $table->string('morpheme_form')->nullable();
            $table->string('morpheme_translation')->nullable();
            $table->string('sheet_reference')->nullable();
            $table->json('matching_words')->nullable();
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
        Schema::dropIfExists('morphemes');
    }
};
