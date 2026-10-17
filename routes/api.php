<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LibraryProxyController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Semua pengguna yang sudah login dapat membaca daftar buku.
    Route::get('/books', [LibraryProxyController::class, 'books']);

    // Hanya admin dan pustakawan yang dapat mengelola data anggota.
    Route::get('/members', [LibraryProxyController::class, 'members'])
        ->middleware('role:ADMIN,LIBRARIAN');

    Route::post('/members', [LibraryProxyController::class, 'members'])
        ->middleware('role:ADMIN,LIBRARIAN');

    // Peminjaman hanya dapat dibuat oleh admin atau pustakawan.
    Route::get('/lendings', [LibraryProxyController::class, 'lendings'])
        ->middleware('role:ADMIN,LIBRARIAN');

    Route::post('/lendings', [LibraryProxyController::class, 'lendings'])
        ->middleware('role:ADMIN,LIBRARIAN');
});
