<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ClosureConfigsController;
use App\Http\Controllers\FacultiesController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\SubmissionsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use \Illuminate\Http\Request;
use App\Http\Controllers\GroupController;


Route::get('/', function () {
    return view('welcome');
});


Route::any('/user/{action}', function (Request $request) {
    $action = $request->action;
    $class  = new UserController($request);
    return $class->$action();
});

Route::any('/group/{action}', function (Request $request) {
    $action = $request->action;
    $class  = new GroupController($request);
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
//email
//Route::any('/sendMail/{action}', function (Request $request) {
//    pd("999999");
//    $action = $request->action;
//    $class  = new FacultiesController($request);
//    return $class->$action();
//});
Route::get('send-mail', function () {

    $details = [
        'title' => 'Mail from fpt-school.com',
        'body' => 'Testing mail'
    ];

    \Mail::to('duongnguyen0902@gmail.com')->send(new \App\Mail\sendingMail($details));

    dd("Email is Sent.");
});

Route::any('/submissions/{action}', function (Request $request) {
    $action = $request->action;
    $class  = new SubmissionsController($request);
    return $class->$action();
});
