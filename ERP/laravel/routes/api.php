<?php

use App\Http\Controllers\Sales\AutofetchController;
use App\Http\Controllers\System\AmcController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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

// Protected routes
Route::group(['middleware' => ['auth']], function() {
    Route::get('/user', [UserController::class, 'authenticatedUser']);
});

Route::group([
    'middleware' => ['client:use-autofetch'],
    'name' => 'api.autofetch'
], function() {
    Route::post('/autofetch/store', [AutofetchController::class, 'store'])->name('store');
    Route::match(['put', 'patch'], '/autofetch/application/{applicationId}', [AutofetchController::class, 'completed'])->where('applicationId', '[a-zA-Z0-9]+')->name('completed');
});

Route::post('/amc/sync', [AmcController::class, 'fetchFromUpstream'])->name('api.amc.sync');