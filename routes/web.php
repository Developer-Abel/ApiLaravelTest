<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FacturaController;

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

Route::get('/', function () {
    return view('welcome');
});
Route::get('getfacturaAll', [FacturaController::class, 'getfacturaAll'])->name('getfacturaAll');
Route::post('addPedido', [FacturaController::class, 'addPedido'])->name('addPedido');
Route::post('addFactura', [FacturaController::class, 'addFactura'])->name('addFactura');
Route::post('findfactura', [FacturaController::class, 'findfactura'])->name('findfactura');