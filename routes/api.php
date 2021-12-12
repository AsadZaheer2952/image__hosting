<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ImageController;

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


Route::post('/register' , [UserController::class , 'register']);
Route::post('/login' , [UserController::class , 'login']);
Route::post('verify-code' , [UserController::class , 'verifyCode'])->middleware('auth:api');
Route::get('resend-code' , [UserController::class , 'resendCode'])->middleware('auth:api');
Route::post('forgot-password' , [UserController::class , 'forgotPassword']);
Route::post('reset-password',[UserController::class , 'resetPassword']);
Route::middleware(['auth:api','email.verified'])->group(function (){
    Route::put('update-profile' , [UserController::class , 'update']);
    Route::put('update-password' , [UserController::class , 'updatePassword']);
    Route::resource('images' , ImageController::class);
    Route::put('image/update-status/{id}' ,[ImageController::class , 'updateStatus']);
    Route::post('image/search' , [ImageController::class , 'search']);

});
