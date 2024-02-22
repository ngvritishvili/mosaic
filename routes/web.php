<?php

use App\Http\Controllers\MosaicController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [MosaicController::class, 'index']);

Route::post('/create-mosaic', [MosaicController::class, 'createMosaic']);



/*
 * Starting my own alg
 */

Route::get('/cut-image', [MosaicController::class, 'show']);
Route::post('/cut-image', [MosaicController::class, 'importMainImg'])->name('cut-image');

Route::get('/import_images', [MosaicController::class, 'importImg'])->name('importImg');
Route::post('/import_images', [MosaicController::class, 'importBatchPhotos'])->name('importBatchPhotos');

Route::get('/test',[MosaicController::class, 'testReq'])->name('testReq');
