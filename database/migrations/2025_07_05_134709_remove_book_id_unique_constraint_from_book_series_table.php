<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveBookIdUniqueConstraintFromBookSeriesTable extends Migration
{
    public function up()
    {
        Schema::table('book_series', function (Blueprint $table) {
            $table->dropUnique('book_id_unique');
            $table->unique(['book_id', 'series_id', 'index'], 'book_id_series_id_unique');
        });
    }

    public function down()
    {
        Schema::table('book_series', function (Blueprint $table) {
            $table->dropUnique('book_id_series_id_unique');
            $table->unique('book_id', 'book_id_unique');
        });
    }
}
