<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LibraryProxyController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

// Publik (tanpa token)
Route::get('/authors', [LibraryProxyController::class, 'authors']);
Route::get('/authors/{id}', [LibraryProxyController::class, 'author']);
Route::get('/books', [LibraryProxyController::class, 'books']);

Route::get('/sample_1', function (Request $request) {
    echo "sample";
})->middleware('end_point');
Route::get('/sample_2', function (Request $request) {
    echo "sample";
})->middleware('end_point');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // MEMBER hanya melihat pinjaman miliknya (filter di LibraryProxyController)
    Route::get('/lendings', [LibraryProxyController::class, 'lendings'])
        ->middleware('role:ADMIN,LIBRARIAN,MEMBER');

    // LIBRARIAN
    Route::middleware('role:LIBRARIAN')->group(function () {
        Route::post('/books', [LibraryProxyController::class, 'books']);
        Route::post('/authors', [LibraryProxyController::class, 'authors']);
        Route::get('/members', [LibraryProxyController::class, 'members']);
        Route::post('/members', [LibraryProxyController::class, 'members']);
        Route::post('/lendings', [LibraryProxyController::class, 'lendings']);
    });

    // ADMIN
    Route::middleware('role:ADMIN')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::post('/users/{id}/reset-password', [UserController::class, 'resetPassword']);
        Route::put('/users/{id}/status', [UserController::class, 'updateStatus']);
    });
});
