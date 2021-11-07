<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|

Route::get('/', function () {
    return view('welcome');
});
*/

// ADMINER DATABASE MANAGEMENT TOOL
Route::any('adminer', '\Aranyasen\LaravelAdminer\AdminerController@index');

// LOGIN PAGE
Route::get('/admin/login', function () {
    return view('admin/login');
});

// DASHBOARD PAGE
Route::get('/admin/dashboard', function () {
    return view('admin/dashboard');
});

// DRILLS ADD PAGE
Route::get('/admin/drill/add', function () {
    return view('admin/drill/add');
});

// BUSINESS ADD PAGE
Route::get('/admin/business/add', function () {
    return view('admin/business/add');
});

// SUGGEST BUSINESS
Route::get('/admin/suggestion/business', function () {
    return view('admin/suggestion/suggest-business');
});
