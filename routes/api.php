<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MusicController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::controller(MusicController::class)->group(function (){
    Route::get('/buscar-artista/{nombre}', 'buscarArtista')->name('api.buscar-artista');
    Route::get('/detalle-artista/{id}', 'detalleArtista')->name('api.detalle-artista');
    Route::get('/top-albums', 'obtenerTopAlbums')->name('api.top-albums');
    Route::get('/detalle-album/{id}', 'detalleAlbum')->name('api.detalle-album');
});