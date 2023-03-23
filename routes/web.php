<?php

use App\Http\Controllers\PersonalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::group(['prefix' => 'api'], function () {
    Route::post('/reg', [PersonalController::class, 'reg'])->name('reg');

    Route::post('/auth', [PersonalController::class, 'auth'])->name('auth');

    Route::get('/katalog', [PersonalController::class, 'katalog'])->name('katalog');
    
});
