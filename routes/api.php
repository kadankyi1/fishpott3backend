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
| //* * * * * php /Applications/XAMPP/xamppfiles/htdocs/fishpott/artisan schedule:run 1>> /dev/null 2>&1
*/

// PERSONAL ACCOUNT REGISTRATION ROUTE
Route::post('/v1/user/register-personal',[App\Http\Controllers\version1\UserController::class, 'registerPersonalAccount']);

// BUSINESS ACCOUNT REGISTRATION ROUTE
Route::post('/v1/user/register-business',[App\Http\Controllers\version1\UserController::class, 'registerBusinessAccount']);

// LOGIN
Route::post('/v1/user/login',[App\Http\Controllers\version1\UserController::class, 'login']);

// SEND PASSWORD RESET
Route::post('/v1/user/send-password-reset-code',[App\Http\Controllers\version1\UserController::class, 'sendPasswordResetCode']);

// SEND PASSWORD RESET
Route::post('/v1/user/change-password-with-reset-code',[App\Http\Controllers\version1\UserController::class, 'changePasswordWithResetCode']);

// UPLOAD PROFILE PICTURE
Route::middleware('auth:api')->post('/v1/user/upload-pott-pic', [App\Http\Controllers\version1\UserController::class, 'uploadProfilePicture']);

// ADD SUGGESTO
Route::middleware('auth:api')->post('/v1/user/add-drill', [App\Http\Controllers\version1\UserController::class, 'addDrill']);

// GET SUGGESTO
Route::middleware('auth:api')->get('/v1/user/get-drill', [App\Http\Controllers\version1\UserController::class, 'getDrill']);

/*
|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|
| |-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-| ADMINISTRATOR SECTION ADMINISTRATOR SECTION ADMINISTRATOR SECTION ADMINISTRATOR SECTION |-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|
| |-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-| ADMINISTRATOR SECTION ADMINISTRATOR SECTION ADMINISTRATOR SECTION ADMINISTRATOR SECTION |-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|
| |-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-| ADMINISTRATOR SECTION ADMINISTRATOR SECTION ADMINISTRATOR SECTION ADMINISTRATOR SECTION |-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|
|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|
*/


// ADD NEW ADMINISTRATOR
Route::middleware('auth:administrator-api')->get('/v1/user/get-drill', [App\Http\Controllers\version1\AdministratorController::class, 'getDrill']);
