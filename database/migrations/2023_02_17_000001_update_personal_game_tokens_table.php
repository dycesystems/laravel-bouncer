<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePersonalGameTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('personal_game_tokens', function (Blueprint $table) {
            $table->enum('type', ['auth', 'session', 'session_day', 'never'])->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('personal_game_tokens', function (Blueprint $table) {
            $table->enum('type', ['auth', 'session', 'session_day'])->change();
        });
    }
}
