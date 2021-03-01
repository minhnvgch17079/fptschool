<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ClosureConfigsController;
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
    Route::get('/{action}', function ($action) {
        $classController = new UserController();
        return $classController->$action();
    });
});

Route::group(['prefix'=>'/closure-configs'], function() {
    Route::post('/{action}', function ($action) {
        $classController = new ClosureConfigsController();
        return $classController->$action();
    });
});

Route::group(['prefix'=>'/admin'], function() {
    Route::get('/index', [AdminController::class, 'index']);

});

Route::group(['prefix'=>'/staff'], function() {
    Route::get('/{action}', function ($action) {
        $classController = new StaffController();
        return $classController->$action();
    });
});
