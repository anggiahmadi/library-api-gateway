<?php

namespace Tests\Feature;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LibraryProxyIntegrationTest extends TestCase
{
    public function test_books_request_is_forwarded_and_returns_the_book_service_response(): void
    {
        config(['services.book.url' => 'http://book-service.test']);

        Http::fake([
            'http://book-service.test/api/books' => Http::response([
                'message' => 'Books retrieved successfully.',
                'data' => [
                    ['id' => 17, 'title' => 'The Test Book'],
                ],
            ], 200),
        ]);

        $this->getJson('/api/books')
            ->assertOk()
            ->assertJsonPath('message', 'Books retrieved successfully.')
            ->assertJsonPath('data.0.id', 17)
            ->assertJsonPath('data.0.title', 'The Test Book');

        Http::assertSent(function (ClientRequest $request) {
            return $request->method() === 'GET'
                && $request->url() === 'http://book-service.test/api/books'
                && str_contains($request->header('Accept')[0], 'application/json');
        });
    }

    public function test_book_service_error_status_and_message_are_forwarded_unchanged(): void
    {
        config(['services.book.url' => 'http://book-service.test']);

        Http::fake([
            'http://book-service.test/api/books/999' => Http::response([
                'message' => 'Book not found.',
            ], 404),
        ]);

        $this->getJson('/api/books/999')
            ->assertNotFound()
            ->assertJsonPath('message', 'Book not found.');
    }

    public function test_unavailable_book_service_returns_a_gateway_error_message(): void
    {
        config(['services.book.url' => 'http://book-service.test']);

        Http::fake(function () {
            throw new ConnectionException('Book service is unavailable.');
        });

        $this->getJson('/api/books')
            ->assertServiceUnavailable()
            ->assertJsonPath('message', 'Layanan tujuan tidak dapat dihubungi.');
    }
}
