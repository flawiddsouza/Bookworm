<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('user_book_note_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_book_id');
            $table->text('notes');
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('user_book_id')->references('id')->on('user_books')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_book_note_versions');
    }
};
