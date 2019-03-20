<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModelSettingsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('model_settings')) {
            Schema::create('model_settings', function (Blueprint $table) {
                $table->increments('id');
                $table->bigInteger('model_id');
                $table->string('model_type');
                $table->json('settings');
                $table->timestamps();

                $table->unique(['model_id', 'model_type']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('model_settings');
    }
}
