<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MergeNotesColumnsInUserBooksTable extends Migration
{
    public function up()
    {
        Schema::table('user_books', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('book_id');
        });

        $userBooks = DB::table('user_books')->get();

        foreach ($userBooks as $userBook) {
            $privateNotes = $userBook->private_notes ?? '';
            $publicNotes = $userBook->public_notes ?? '';

            $notes = '';

            if (!empty($privateNotes) && !empty($publicNotes)) {
                $notes = "Private Notes:\n{$privateNotes}\n\nPublic Notes:\n{$publicNotes}";
            } elseif (!empty($privateNotes)) {
                $notes = $privateNotes;
            } elseif (!empty($publicNotes)) {
                $notes = $publicNotes;
            }

            if (!empty($notes)) {
                DB::table('user_books')
                    ->where('id', $userBook->id)
                    ->update(['notes' => $notes]);
            }
        }

        Schema::table('user_books', function (Blueprint $table) {
            $table->dropColumn(['private_notes', 'public_notes']);
        });
    }

    public function down()
    {
        Schema::table('user_books', function (Blueprint $table) {
            $table->text('private_notes')->nullable();
            $table->text('public_notes')->nullable();
        });

        Schema::table('user_books', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
}
