<?php

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


use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;
//all
Route::controller(App\Http\Controllers\AuthController::class)->group(function () {
    Route::prefix('panel')->group(function () {
        Route::get('/user', 'currentUser');

        Route::get('/user/logout/{user}', 'logout');

        Route::post('/admin/login', 'login');
        Route::post('/admin/register', 'register');
        Route::post('/check/admin/token', 'updateLastActivity');

        Route::post('/user/login', 'login');
        Route::post('/user/register', 'register');
        Route::post('/check/user/token', 'updateLastActivity');
    });


    Route::post('/user/login', 'login');
    Route::post('/user/register', 'register');
    Route::post('/check/user/token', 'updateLastActivity');
    Route::get('/user/logout/{user}', 'logout');
    Route::post('/user/logout', 'logout');

    Route::post('/otp/mobile', 'getOtp');
    Route::post('/mobile/login', 'loginMobile');
});
Route::controller(App\Http\Controllers\UserController::class)->group(function () {

    Route::post('/update/user', 'updateProfile');
    Route::get('/user', 'indexUsers');
    Route::get('/admins', 'indexAdmins');
    Route::get('/user/{user}', 'show');
    Route::post('/user', 'store');
    Route::post('/user/{user}', 'update');
    Route::get('/delete/user/{user}', 'destroy');


    Route::get('/get/otp/{mobile}', 'getOtpNoRedis');//getOtp
    Route::post('/verify/otp', 'verifyOtp');
    Route::get('/login/otp/{user}', 'loginOtp');

    Route::get('/otp1', 'otp1');
    Route::get('/otp2', 'otp2');


    Route::prefix('panel')->group(function () {

        Route::post('/update/user', 'updateProfile');
        Route::get('/user', 'indexUsers');
        Route::get('/user/{user}', 'show');
        Route::post('/user', 'store');
        Route::post('/user/{user}', 'update');
        Route::post('/delete/user', 'destroy');

        Route::post('/update/admin', 'updateProfile');
        Route::get('/admin', 'indexAdmins');
        Route::get('/admin/{admin}', 'show');
        Route::post('/admin', 'store');
        Route::post('/admin/{admin}', 'update');
        Route::get('/delete/admin/{admin}', 'destroy');
    });
});
Route::controller(App\Http\Controllers\SlideController::class)->group(function () {

    Route::get('/slide', 'indexSite');
    Route::prefix('panel')->group(function () {
        Route::get('/slide', 'index');
        Route::get('/slide/{slide}', 'show');
        Route::post('/slide', 'store');
        Route::post('/slide/{slide}', 'update');
        Route::post('/delete/slide', 'destroy');
        Route::get('/active/slide/{slide}', 'activeToggle');
    });
});
Route::controller(App\Http\Controllers\ImageController::class)->group(function () {

    Route::prefix('panel')->group(function () {
//        Route::get('/icon', 'index');
//        Route::get('/icon/{icon}', 'show');
//        Route::post('/icon', 'store');
        Route::post('/icon', 'makeIconPack');
        Route::post('/upload/editor/image', 'uploadEditorImage');
//        Route::post('/panel/upload/editor/image',[\App\Http\Controllers\ImageController::class, 'uploadEditorImage']);

    });
});
Route::controller(App\Http\Controllers\ProductController::class)->group(function () {

    Route::get('/product', 'indexSite');
    Route::get('/product/{product}', 'show');
    Route::get('/latest/product', 'latestSite');
    Route::get('/stock/product', 'stockSite');
    Route::get('/sizes/product/{id}/{color}', 'getSizes');
    Route::get('/product/by/category/{id}', 'byCat');


    Route::prefix('panel')->group(function () {

        Route::get('/product', 'index');
        Route::get('/product/{product}', 'show');
        Route::post('/product', 'store');
        Route::post('/product/{product}', 'update');
        Route::post('/sort/product/{product}', 'sort');
        Route::get('/delete/product/{product}', 'destroy');

        Route::get('/active/product/{product}', 'activeToggle');
        Route::get('/latest/product', 'latest');

        Route::post('/images/reorder/product/{product}', 'updateOrder');
        Route::get('/product/by/category/{id}', 'byCatPanel');

    });
});
Route::controller(App\Http\Controllers\ProductCategoryController::class)->group(function () {

    Route::get('/category/product', 'indexSite');
    Route::get('/category/product/{productCategory}', 'show');

    Route::prefix('panel')->group(function () {
        Route::get('/category/product', 'index');
        Route::get('/category/product/{productCategory}', 'show');
        Route::post('/category/product', 'store');
        Route::post('/category/product/{productCategory}', 'update');
        Route::post('/delete/category/product', 'destroy');
        Route::get('/active/category/product/{productCategory}', 'activeToggle');
    });
});
Route::get('/category/product', [\App\Http\Controllers\ProductCategoryController::class,'indexSite']);
Route::get('/search', [\App\Http\Controllers\SearchController::class,'search']);
Route::get('/fix', [\App\Http\Controllers\ProductController::class,'fix']);

