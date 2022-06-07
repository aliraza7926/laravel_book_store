<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthorController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
/*
Route::get('/authors/{author}', [AuthorController::class,'show'])->name('sh');
Route::get('/authors', [AuthorController::class,'index']);

Route::post('/authors',[AuthorController::class,'store']);
Route::patch('/authors/{author}',[AuthorController::class,'update']);
Route::delete('/authors/{author}',)
*/
Route::apiResource('authors', AuthorController::class);
