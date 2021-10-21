<?php

use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\OAuthController;

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

Route::get('/', [PostController::class, 'index'])
    ->name('root');

Route::resource('posts', PostController::class)
    ->only(['create', 'store', 'update', 'edit', 'destroy'])
    ->middleware('auth');

Route::resource('posts', PostController::class)
    ->only(['index', 'show']);

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';

Route::prefix('auth')->middleware('guest')->group(function () {
    Route::get('{provider}', [OAuthController::class, 'redirectToProvider'])
        ->where('provider', 'github|google')
        ->name('redirectToProvider');
    Route::get('{provider}/callback', [OAuthController::class, 'oauthCallback'])
        ->where('provider', 'github|google')
        ->name('oauthCallback');
});