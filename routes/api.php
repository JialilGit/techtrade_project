<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PasswordResetController;

use App\Http\Controllers\Api\CustomerController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Public Routes
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/send-reset-password-email', [PasswordResetController::class, 'send_reset_password_email']);
Route::post('/reset-password/{token}', [PasswordResetController::class, 'reset']);


//Protected Routes
Route::middleware(['auth:sanctum'])->group(function(){
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/loggeduser', [UserController::class, 'logged_user']);
    Route::post('/changepassword', [UserController::class, 'change_password']);


    Route::post('add_cart/{id}', [CustomerController::class, 'addToCart']);
    Route::get('get_cart', [CustomerController::class, 'getCart']);
    Route::delete('remove_cart/{id}', [CustomerController::class, 'removeCart']);
    Route::post('cash_order', [CustomerController::class, 'cashOrder']);
    Route::post('stripe/{totalprice}', [CustomerController::class, 'stripe']);
    Route::post('stripe_post/{totalprice}', [CustomerController::class, 'stripePost']);
    Route::get('/show_order', [CustomerController::class, 'showOrder']);
    Route::delete('/cancel_order/{id}', [CustomerController::class, 'cancelOrder']);


    Route::post('/add_comment', [CustomerController::class, 'addComment']);
    Route::delete('/delete_comment/{id}', [CustomerController::class, 'deleteComment']);
    Route::post('/add_reply', [CustomerController::class, 'addReply']);
    Route::delete('/delete_reply/{id}', [CustomerController::class, 'deleteReply']);


    Route::get('/view_details', [CustomerController::class, 'viewDetails']);
    Route::put('/update_details', [CustomerController::class, 'updateDetails']);


});


//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    //return $request->user();
//});

//ForApi that doest need Auth
Route::apiResource('products', CustomerController::class);
Route::get('/show_comments', [CustomerController::class, 'show_comment']);
Route::post('/product_search', [CustomerController::class, 'productSearch']);
Route::post('/add_contact', [CustomerController::class, 'addContact']);

