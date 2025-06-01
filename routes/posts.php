<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

Route::get('posts', [PostController::class, 'index']);
Route::get('posts/{id}', [PostController::class, 'show']);
Route::post('posts', [PostController::class, 'store'])->middleware('auth:api');
Route::put('posts/{id}', [PostController::class, 'update'])->middleware('auth:api');
Route::delete('posts/{id}', [PostController::class, 'destroy'])->middleware('auth:api');

Route::get('posts/{id}/comments', [PostController::class, 'comments']);
Route::post('posts/{id}/comments', [PostController::class, 'addComment'])->middleware('auth:api');
Route::put('posts/{id}/comments/{commentId}', [PostController::class, 'updateComment'])->middleware('auth:api');
Route::delete('posts/{id}/comments/{commentId}', [PostController::class, 'deleteComment'])->middleware('auth:api');

Route::post('posts/{id}/like', [PostController::class, 'like'])->middleware('auth:api');
Route::post('posts/{id}/unlike', [PostController::class, 'removelike'])->middleware('auth:api');