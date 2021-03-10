<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ClosureConfigsController;
use App\Http\Controllers\FacultiesController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use \Illuminate\Http\Request;
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


Route::any('/user/{action}', function (Request $request) {
    $action = $request->action;
    $class  = new UserController($request);
    return $class->$action();
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

Route::any('/closure-configs/{action}', function (Request $request) {
    $action = $request->action;
    $class  = new ClosureConfigsController($request);
    return $class->$action();
});

Route::any('/faculty/{action}', function (Request $request) {
    $action = $request->action;
    $class  = new FacultiesController($request);
    return $class->$action();
});
