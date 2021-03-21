<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksTable extends Migration
{
    public function up()
    {
        Schema::create('book_types', function(Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->tinyInteger('sort_order')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('book_type_id')->constrained('book_types');
            $table->string('cover_image_url')->nullable(); // remote image
            $table->string('cover_image_filename')->nullable(); // local image
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('book_authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books');
            $table->foreignId('author_id')->constrained('authors');
            $table->unique(['book_id', 'author_id'], 'book_id_author_id_unique');
            $table->string('role')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('series', function(Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('series_authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('series_id')->constrained('series');
            $table->foreignId('author_id')->constrained('authors');
            $table->unique(['series_id', 'author_id'], 'series_id_author_id_unique');
            $table->string('role')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('book_series', function(Blueprint $table) {
            $table->id();
            $table->double('index');
            $table->foreignId('book_id')->constrained('books');
            $table->foreignId('series_id')->constrained('series');
            $table->unique('book_id', 'book_id_unique');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('user_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('book_id')->constrained('books');
            $table->unique(['user_id', 'book_id'], 'user_id_book_id_unique');
            $table->string('reading_medium')->nullable();
            $table->text('private_notes')->nullable();
            $table->text('public_notes')->nullable();
            $table->double('rating')->nullable();
            $table->enum('status', ['TO_READ', 'CURRENTLY_READING', 'READ']);
            $table->date('started_reading')->nullable();
            $table->date('completed_reading')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_books');
        Schema::dropIfExists('series_authors');
        Schema::dropIfExists('book_series');
        Schema::dropIfExists('series');
        Schema::dropIfExists('book_authors');
        Schema::dropIfExists('authors');
        Schema::dropIfExists('books');
        Schema::dropIfExists('book_types');
    }
}
