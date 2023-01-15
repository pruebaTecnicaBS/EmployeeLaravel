<?php

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



Route::post('register', 'UsuarioApiController@register');
Route::post('login', 'UsuarioApiController@authenticate');

//Route::group(['middleware' => ['jwt.verify']], function () {
    Route::post('sesionCiudadano', 'ApiCitasController@sesionCiudadano');
    Route::post('consulta-tramite', 'ApiCitasController@ObtenerDatosCita');
    Route::post('validaTieneCita', 'ApiCitasController@validaTieneCita');
    Route::post('obtenerVentanillas', 'ApiCitasController@obtenerVentanillas');
//});
