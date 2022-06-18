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
*/


/*
|--------------------------------------------------------------------------
| LANDING ROUTES
|--------------------------------------------------------------------------
*/


Route::get('/terms', function () {
    return view('landing/terms');
});



/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
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
    return view('admin/drill/add-drill');
});

// ADD BUSINESS 
Route::get('/admin/business/add', function () {
    return view('admin/business/add-business');
});

// ADD STOCK VALUE 
Route::get('/admin/business/add-stock-value', function () {
    return view('admin/business/add-new-stock-value');
});

// ADD STOCK VALUE 
Route::get('/admin/business/add-stock-training-data', function () {
    return view('admin/business/add-stock-train-data');
});

// SUGGEST DRILL
Route::get('/admin/suggestion/suggest-drill', function () {
    return view('admin/suggestion/suggest-drill');
});

// SUGGEST BUSINESS
Route::get('/admin/suggestion/suggest-business', function () {
    return view('admin/suggestion/suggest-business');
});

// VIEW ORDERS
Route::get('/admin/orders/view-orders', function () {
    return view('admin/order/view-orders');
});

// VIEW USERS
Route::get('/admin/users/view-users', function () {
    return view('admin/user/view-users');
});

// VIEW USERS
Route::get('/admin/users/notify-users', function () {
    return view('admin/user/notify-users');
});


/********************************* */

// VIEW USERS
Route::get('/user/email/sub-or-unsub', function () {
    return view('user/email/suborunsubcribe');
});
