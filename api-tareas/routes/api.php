<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });



Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {
    
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    //Route::post('/logout', [AuthController::class, 'logout'])->withoutMiddleware('RedirectIfAuthenticated');;
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);    
});

Route::post('/test',[AuthController::class, 'test']);

Route::group(['middleware' => 'api', 'prefix' => 'tasks'], function(){
    Route::get('/',[TaskController::class, 'getAllTaskFromUser']);
    Route::get('/{id}', [TaskController::class, 'showTaskDetails']);
    Route::post('/',[TaskController::class, 'insertTask']);
    Route::put('/{id}',[TaskController::class, 'updateTask']);
    Route::delete('/{id}',[TaskController::class, 'destroyTask']);
});
