<?php

use Illuminate\Support\Facades\Route;

Route::post('/upload', 'UploadController@handle');
Route::get('/config', 'ConfigController@index');
Route::get('/progress/{file}', 'ImportController@progress');
Route::get('/preview/{file}', 'ImportController@preview');
Route::post('/import/{file}', 'ImportController@import');
Route::delete('/{file}', 'UploadController@delete');