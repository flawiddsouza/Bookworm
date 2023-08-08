<?php

use Illuminate\Database\Migrations\Migration;

class AddAbandonedToUserBooksStatusEnum extends Migration
{
    public function up()
    {
        DB::transaction(function () {
            DB::statement('ALTER TABLE user_books DROP CONSTRAINT user_books_status_check;');
            DB::statement("ALTER TABLE user_books ADD CONSTRAINT user_books_status_check CHECK (
                status::text = ANY (ARRAY['TO_READ'::character varying, 'CURRENTLY_READING'::character varying, 'READ'::character varying, 'ABANDONED'::character varying]::text[])
            )");
        });
    }

    public function down()
    {
        DB::statement('ALTER TABLE user_books DROP CONSTRAINT user_books_status_check;');
            DB::statement("ALTER TABLE user_books ADD CONSTRAINT user_books_status_check CHECK (
                status::text = ANY (ARRAY['TO_READ'::character varying, 'CURRENTLY_READING'::character varying, 'READ'::character varying]::text[])
            )");
    }
}
