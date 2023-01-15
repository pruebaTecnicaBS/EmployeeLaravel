<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

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





Route::post('createEmployee', [ApiController::class, 'createEmployee']);
Route::post('addHoursEmployee', [ApiController::class, 'addHoursEmployee']);
Route::post('listEmployeesByJobTitle', [ApiController::class, 'listEmployeesByJobTitle']);
Route::post('getHoursEmployee', [ApiController::class, 'getHoursEmployee']);
Route::post('getPayEmployee', [ApiController::class, 'getPayEmployee']);
