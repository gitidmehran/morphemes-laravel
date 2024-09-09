<?php

use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\MorphemeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// GET SERVER VERSION
Route::get('version-info', [HomeController::class, 'index']);

// MORPHEME ROUTES
Route::post('get-morphemes', [MorphemeController::class, 'index']);
Route::get('get-root-words', [MorphemeController::class, 'getRootWords']);
Route::post('words-by-groups', [MorphemeController::class, 'getWordByGroups']);
Route::post('words-by-groups-only', [MorphemeController::class, 'wordsByGroupsOnly']);
Route::post('words-by-wordid', [MorphemeController::class, 'getWordsByWordIdOnly']);
Route::post('find-by-wordid', [MorphemeController::class, 'getWordByWordIdOnly']);
