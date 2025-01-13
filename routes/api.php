<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

Route::post('/register',[UserController::class,'register']);
Route::post('/login',[UserController::class,'login']);
Route::middleware('auth:api')->group(function(){
    Route::get('/userprofile',[UserController::class,'userProfile']);
});
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');
