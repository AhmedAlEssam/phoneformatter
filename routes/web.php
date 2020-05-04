<?php

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});
Route::post('/','\App\Http\Controllers\phoneController@index')->name('phoneController');
