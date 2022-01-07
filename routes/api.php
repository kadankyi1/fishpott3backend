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

// THESE ROUTES MUST BE DELETED --------------- START
//Route::post('/v1/admin/ai-training-test',[App\Http\Controllers\version1\AdministratorController::class, 'trainingAi']);
//Route::post('/v1/admin/ai-testing-test',[App\Http\Controllers\version1\AdministratorController::class, 'testingAi']);
//Route::post('/v1/admin/ai-data-fetch',[App\Http\Controllers\version1\AdministratorController::class, 'getData']);
// THESE ROUTES MUST BE DELETED --------------- END


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

// GET SUGGESTION
Route::middleware('auth:api')->post('/v1/user/get-my-suggestion', [App\Http\Controllers\version1\UserController::class, 'getMySuggestion']);

// SAVE DRILL
Route::middleware('auth:api')->post('/v1/user/save-drill-answer', [App\Http\Controllers\version1\UserController::class, 'saveDrillAnswerAndReturnWhatOthersSaid']);

// GET FINAL PRICE
Route::middleware('auth:api')->post('/v1/user/get-final-price', [App\Http\Controllers\version1\UserController::class, 'getFinalPriceSummary']);

// UPDATE BUY STOCK ORDER PAYMENT STATUS
Route::middleware('auth:api')->post('/v1/user/update-order-payment-status', [App\Http\Controllers\version1\UserController::class, 'updateBuyOrderPaymentInfo']);

// SEND WITHDRAWAL REQUEST
Route::middleware('auth:api')->post('/v1/user/send-withdrawal-request', [App\Http\Controllers\version1\UserController::class, 'sendWithdrawalRequest']);

// GET MY TRANSACTIONS
Route::middleware('auth:api')->post('/v1/user/get-my-transactions', [App\Http\Controllers\version1\UserController::class, 'getMyTransactions']);

// GET MY INVESTMENTS
Route::middleware('auth:api')->post('/v1/user/get-my-investments', [App\Http\Controllers\version1\UserController::class, 'getMyInvestments']);

// FIND A BUSINESS
Route::middleware('auth:api')->post('/v1/user/find-business', [App\Http\Controllers\version1\UserController::class, 'findBusiness']);

// UPDATE AND GET RECENT USER INFO
Route::middleware('auth:api')->post('/v1/user/get-user-info', [App\Http\Controllers\version1\UserController::class, 'updateAndGetRecentUserInfo']);

// SEND STOCK TRANSFER 
Route::middleware('auth:api')->post('/v1/user/send-stock-transfer', [App\Http\Controllers\version1\UserController::class, 'sendStockTransfer']);

// SELL BACK STOCK 
Route::middleware('auth:api')->post('/v1/user/sellback-stock', [App\Http\Controllers\version1\UserController::class, 'sellBackStocks']);

// DEFAULT FALLBACK ROUTE
Route::fallback(function(){
    return response()->json([
        'message' => 'Page Not Found. If error persists, contact info@fishpott.com'], 404);
});
/*
|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|
| |-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-| ADMINISTRATOR SECTION ADMINISTRATOR SECTION ADMINISTRATOR SECTION ADMINISTRATOR SECTION |-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|
| |-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-| ADMINISTRATOR SECTION ADMINISTRATOR SECTION ADMINISTRATOR SECTION ADMINISTRATOR SECTION |-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|
| |-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-| ADMINISTRATOR SECTION ADMINISTRATOR SECTION ADMINISTRATOR SECTION ADMINISTRATOR SECTION |-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|
|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|-|
*/

// ADD FIRST ADMINISTRATOR
Route::post('/v1/admin/register-first-admin',[App\Http\Controllers\version1\AdministratorController::class, 'registerFirstAdmin']);

// LOGIN AS ADMINISTRATOR
Route::post('/v1/admin/login',[App\Http\Controllers\version1\AdministratorController::class, 'loginAsAdministrator']);

// LOG OUT AS ADMINISTRATOR
Route::get('/v1/admin/logout',[App\Http\Controllers\version1\AdministratorController::class, 'logoutAsAdministrator']);

// ADD DRILL
Route::middleware('auth:administrator-api')->post('/v1/admin/add-drill', [App\Http\Controllers\version1\AdministratorController::class, 'addDrill']);

// ADD BUSINESS
Route::middleware('auth:administrator-api')->post('/v1/admin/add-business', [App\Http\Controllers\version1\AdministratorController::class, 'addBusiness']);

// ADD BUSINESS STOCK VALUE
Route::middleware('auth:administrator-api')->post('/v1/admin/add-new-stock-value', [App\Http\Controllers\version1\AdministratorController::class, 'addNewShareValue']);

// ADD STOCK TRAIN DATA
Route::middleware('auth:administrator-api')->post('/v1/admin/add-stock-train-data', [App\Http\Controllers\version1\AdministratorController::class, 'addTrainDataStockValuesAndOutput']);

// GET DASHBOARD DATA
Route::middleware('auth:administrator-api')->post('/v1/admin/get-dashboard-data', [App\Http\Controllers\version1\AdministratorController::class, 'getDashboardData']);

// GET MODEL LIST
Route::middleware('auth:administrator-api')->post('/v1/admin/search-model', [App\Http\Controllers\version1\AdministratorController::class, 'searchModel']);

// GET ORDERS LIST
Route::middleware('auth:administrator-api')->post('/v1/admin/search-orders', [App\Http\Controllers\version1\AdministratorController::class, 'searchOrders']);

// GET USERS LIST
Route::middleware('auth:administrator-api')->post('/v1/admin/search-users', [App\Http\Controllers\version1\AdministratorController::class, 'searchUsers']);

// GET ORDERS LIST
Route::middleware('auth:administrator-api')->post('/v1/admin/update-order', [App\Http\Controllers\version1\AdministratorController::class, 'updateOrderProcessedOrFlaggedStatus']);

// GET ORDERS LIST
Route::middleware('auth:administrator-api')->post('/v1/admin/update-user', [App\Http\Controllers\version1\AdministratorController::class, 'updateUserFlaggedStatus']);

// GET BUSINESS LIST
Route::middleware('auth:administrator-api')->post('/v1/admin/add-suggestion', [App\Http\Controllers\version1\AdministratorController::class, 'addSuggestion']);
