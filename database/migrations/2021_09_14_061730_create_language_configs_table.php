<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLanguageConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('language_configs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('language_id')->unsigned();
            $table->string('name', 100);
            $table->string('script', 30)->nullable();
            $table->string('native', 30)->nullable();
            $table->string('regional', 30)->nullable();
            $table->timestamps();
        });

        $now = date('Y-m-d H:i:s');

        \App\Models\LanguageConfig::insert([
            'language_id' => 1,
            'name' => 'English',
            'script' => 'Latn',
            'native' => 'English',
            'regional' => 'en_GB',
            'created_at' => $now,
            'updated_at' => $now
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('language_configs');
    }
}
