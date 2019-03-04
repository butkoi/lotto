<?php

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

Route::get('/mega45', 'Mega645Controller@index');
Route::post('/mega45', 'Mega645Controller@store');
Route::get('/mega', 'Mega645Controller@index655');
Route::post('/mega', 'Mega645Controller@store655');
Route::get('/test', 'Mega645Controller@test');

