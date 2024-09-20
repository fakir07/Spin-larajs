<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WinnerController;


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




Route::get('/', [WinnerController::class, 'index'])->name('index');
Route::post('/winners', [WinnerController::class, 'store'])->name('winners.store');
Route::get('/getuser', [WinnerController::class, 'getuser'])->name('getuser');
Route::post('/import', [WinnerController::class, 'import'])->name('import');
