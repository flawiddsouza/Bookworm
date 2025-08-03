<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('user_books', function (Blueprint $table) {
            $table->unsignedSmallInteger('notes_type')
                ->default(config(('constants.notes_type.plain_text')))
                ->after('notes');
        });
    }

    public function down()
    {
        Schema::table('user_books', function (Blueprint $table) {
            $table->dropColumn('notes_type');
        });
    }
};
