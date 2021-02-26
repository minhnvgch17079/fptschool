<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\UserController;
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

Route::get('/', function () {
    return view('welcome');
});

// todo: User
Route::group(['prefix'=>'/user'], function() {
    Route::post('/{action}', function ($action) {
        $classController = new UserController();
        return $classController->$action();
    });
});

//admin
Route::group(['prefix'=>'/admin'], function() {
    Route::post('/{action}', function ($action) {
        $classController = new AdminController();
        return $classController->$action();
    });
});

Route::group(['prefix'=>'/staff'], function() {
    Route::get('/{action}', function ($action) {
        $classController = new StaffController();
        return $classController->$action();
    });

});
