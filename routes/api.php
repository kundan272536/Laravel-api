<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProductController;
Route::post('/register',[UserController::class,'register']);
Route::post('/login',[UserController::class,'login']);

Route::get('/login', function () {
    return response()->json(['message' => 'Unauthorized'], 401);
})->name('login');
Route::middleware('auth:api')->group(function(){
    Route::get('/userprofile',[UserController::class,'userProfile']);
    Route::get('/logout',[UserController::class,'userLogout']);
    Route::get('/products',[ProductController::class,'index']);
    Route::post('/products',[ProductController::class,'store']);
    Route::put('/products',[ProductController::class,'update']);
    Route::delete('/products',[ProductController::class,'destroy']);

});

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');
