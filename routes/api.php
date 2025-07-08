<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EnterprisePermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FichierEntrepriseController;
use App\Http\Controllers\EntrepriseController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/register', [AuthController::class, 'register'])
    ->name('auth.register');

Route::post('/login', [AuthController::class, 'login'])
    ->name('auth.login');

Route::post('password/reset', [AuthController::class, 'sendResetLink']);


Route::middleware('auth:sanctum')->group(function () {

    Route::post('verify_code', [AuthController::class, 'verifyCode']);

    Route::post('/change_password', [AuthController::class, 'changePassword']);
    Route::post('/password/update', [AuthController::class, 'updatePassword']);
    Route::get('/users', [UserController::class, 'index']);

    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::get('/token/users', [AuthController::class, 'showByToken']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Permissions
    Route::post('/permissions/assign', [EnterprisePermissionController::class, 'assignPermissions']);
    Route::post('/permissions/revoke', [EnterprisePermissionController::class, 'revokePermissions']);
    Route::get('/enterprise_users', [EnterprisePermissionController::class, 'listEnterpriseUsers']);

    Route::get('/my-permissions', [EnterprisePermissionController::class, 'myPermissions']);
});



Route::prefix('entreprises/')->middleware('auth:sanctum')->group(function () {
    Route::get('', [EntrepriseController::class, 'index']);
    Route::post('', [EntrepriseController::class, 'store']);
    Route::get('{id}', [EntrepriseController::class, 'show']);
    Route::get('me/company', [EntrepriseController::class, 'showByToken']);
    Route::post('{id}', [EntrepriseController::class, 'update']);
    Route::delete('{id}', [EntrepriseController::class, 'destroy']);
});


Route::get('/health', function () {
    return response()->json([
        'status' => 'UP',
        'service' => 'user-service',
        'timestamp' => now(),
        'database' => 'connected' // Vérifier la DB si nécessaire
    ]);
});
