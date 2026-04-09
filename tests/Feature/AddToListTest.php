<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Book;
use App\Models\Author;
use App\Models\BookType;
use App\Models\UserBook;
use App\Models\BookAuthor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddToListTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(): User
    {
        return User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    // Use DB::table() directly to avoid Book/BookType boot methods requiring Auth::id()
    private function createBookType(int $userId): BookType
    {
        $id = \DB::table('book_types')->insertGetId([
            'name' => 'Novel',
            'sort_order' => 1,
            'created_by' => $userId,
            'updated_by' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return BookType::find($id);
    }

    private function createBook(int $userId, int $bookTypeId, string $name = 'Test Book'): Book
    {
        $id = \DB::table('books')->insertGetId([
            'name' => $name,
            'book_type_id' => $bookTypeId,
            'created_by' => $userId,
            'updated_by' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return Book::find($id);
    }

    /** @test */
    public function search_books_returns_user_status()
    {
        $user = $this->createUser();
        $bookType = $this->createBookType($user->id);
        $book = $this->createBook($user->id, $bookType->id, 'Dune');

        UserBook::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'TO_READ',
        ]);

        $response = $this->actingAs($user)->getJson('/json/search/books?q=Dune');

        $response->assertStatus(200);
        $response->assertJsonFragment(['user_status' => 'TO_READ']);
    }

    /** @test */
    public function search_books_returns_null_user_status_for_unadded_books()
    {
        $user = $this->createUser();
        $bookType = $this->createBookType($user->id);
        $this->createBook($user->id, $bookType->id, 'Dune');

        $response = $this->actingAs($user)->getJson('/json/search/books?q=Dune');

        $response->assertStatus(200);
        $response->assertJsonFragment(['user_status' => null]);
    }

    /** @test */
    public function manage_books_index_returns_user_status()
    {
        $user = $this->createUser();
        $bookType = $this->createBookType($user->id);
        $book = $this->createBook($user->id, $bookType->id, 'Dune');

        UserBook::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'READ',
        ]);

        $response = $this->actingAs($user)->getJson('/json/manage-books');

        $response->assertStatus(200);
        $data = $response->json('paginator.data');
        $found = collect($data)->firstWhere('name', 'Dune');
        $this->assertEquals('READ', $found['user_status']);
    }

    /** @test */
    public function add_to_list_creates_user_book()
    {
        $user = $this->createUser();
        $bookType = $this->createBookType($user->id);
        $book = $this->createBook($user->id, $bookType->id);

        $response = $this->actingAs($user)->postJson("/json/books/{$book->id}/add-to-list", [
            'status' => 'TO_READ',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('user_books', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'TO_READ',
        ]);
    }

    /** @test */
    public function add_to_list_requires_valid_status()
    {
        $user = $this->createUser();
        $bookType = $this->createBookType($user->id);
        $book = $this->createBook($user->id, $bookType->id);

        $response = $this->actingAs($user)->postJson("/json/books/{$book->id}/add-to-list", [
            'status' => 'INVALID_STATUS',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function create_book_returns_new_book_id()
    {
        $user = $this->createUser();
        $bookType = $this->createBookType($user->id);
        $authorId = \DB::table('authors')->insertGetId([
            'name' => 'Test Author',
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->postJson('/json/manage-books', [
            'name' => 'New Book',
            'book_type_id' => $bookType->id,
            'authors' => [['author_id' => $authorId, 'role' => null]],
            'series' => [],
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['id']);
        $this->assertDatabaseHas('books', ['name' => 'New Book']);
    }

    /** @test */
    public function create_book_auto_creates_author_by_name()
    {
        $user = $this->createUser();
        $bookType = $this->createBookType($user->id);

        $response = $this->actingAs($user)->postJson('/json/manage-books', [
            'name' => 'New Book',
            'book_type_id' => $bookType->id,
            'authors' => [['author_id' => null, 'author_name' => 'Brand New Author', 'role' => null]],
            'series' => [],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('authors', ['name' => 'Brand New Author']);
        $this->assertDatabaseHas('book_authors', [
            'book_id' => $response->json('id'),
        ]);
    }
}
