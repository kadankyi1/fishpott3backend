<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\version1\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is 2aL,W4c7r9(2qf#y where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// PERSONAL ACCOUNT REGISTRATION ROUTE
Route::post('/v1/user/register-personal',[App\Http\Controllers\version1\UserController::class, 'registerPersonalAccount']);


// BUSINESS ACCOUNT REGISTRATION ROUTE
Route::post('/v1/user/register-business',[App\Http\Controllers\version1\UserController::class, 'registerBusinessAccount']);

// LOGIN
Route::post('/v1/user/login',[App\Http\Controllers\version1\UserController::class, 'login']);

// UPLOAD PROFILE PICTURE
Route::post('/v1/user/upload-pott-pic',[App\Http\Controllers\version1\UserController::class, 'uploadProfilePicture']);
