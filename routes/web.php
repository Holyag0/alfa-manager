<?php
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Cadastros\UserController;
use Illuminate\Routing\Route as RoutingRoute;

Route::middleware([HandleInertiaRequests::class])->group(function () {
    Route::get('login', [LoginController::class, 'homeLogin'])->name('login');
    Route::post('login-authenticate', [LoginController::class, 'authenticate'])->name('auth');
});

Route::middleware(['auth',HandleInertiaRequests::class])->group(function() {

    Route::get('/', function (){ return Inertia::render('welcome');})->name('home');
    Route::get('logout', [LoginController::class, 'logout'])->middleware('auth');

    Route::group(['prefix'=>'cadastros'],function() {
        Route::resource('users', UserController::class);
    });
   


});