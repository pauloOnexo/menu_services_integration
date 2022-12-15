<?php

use App\Http\Controllers\API\AdminCatalogosApiController;
use App\Http\Controllers\API\ArticleController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\MenuApiController;
use App\Http\Controllers\API\SubcategorieController;
use App\Http\Controllers\AUTH\LoginController;
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

Route::resource('articles', ArticleController::class);
Route::resource('subcategories', SubcategorieController::class);
Route::resource('categories', CategoryController::class);
Route::resource('menu', MenuApiController::class);
Route::resource('addMenu',AdminCatalogosApiController::class);

Route::post('checkUser',[LoginController::class, 'validar_datos_cliente']);
Route::post('checkToken',[LoginController::class, 'checkToken']);
