<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\EnterprisePermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FichierEntrepriseController;
use App\Http\Controllers\EntrepriseController;
use App\Http\Controllers\OperatorController;

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


Route::post('/register', [AuthController::class, 'register'])
    ->name('auth.register');

Route::post('/login', [AuthController::class, 'login'])
    ->name('auth.login');

Route::post('password/reset', [AuthController::class, 'sendResetLink']);

Route::post('verify_code', [AuthController::class, 'verifyCode']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/change_password', [AuthController::class, 'changePassword']);
    Route::post('/password/update', [AuthController::class, 'updatePassword']);
    Route::get('/users', [UserController::class, 'index']);

    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::get('/token/users', [AuthController::class, 'showByToken']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::put('/update/status/{id}', [UserController::class, 'updateStatus']);


    // Permissions
    Route::post('/permissions/assign', [EnterprisePermissionController::class, 'assignPermissions']);
    Route::post('/permissions/revoke', [EnterprisePermissionController::class, 'revokePermissions']);
    Route::get('/enterprise_users', [EnterprisePermissionController::class, 'listEnterpriseUsers']);

    Route::get('/my-permissions', [EnterprisePermissionController::class, 'myPermissions']);
});



Route::prefix('entreprises/')->group(function () {
    Route::get('', [EntrepriseController::class, 'index'])->middleware('auth:sanctum');
    Route::post('', [EntrepriseController::class, 'store'])->middleware('auth:sanctum');
    Route::get('{id}', [EntrepriseController::class, 'show']);
    Route::get('me/company', [EntrepriseController::class, 'showByToken'])->middleware('auth:sanctum');
    Route::post('{id}', [EntrepriseController::class, 'update'])->middleware('auth:sanctum');
    Route::post('update/status/{id}', [EntrepriseController::class, 'updateStatus'])->middleware('auth:sanctum');
    Route::delete('{id}', [EntrepriseController::class, 'destroy'])->middleware('auth:sanctum');
});


Route::prefix('countries/')->group(function () {
    Route::get('', [CountryController::class, 'index']);
    Route::post('', [CountryController::class, 'store'])->middleware('auth:sanctum');
    Route::get('{id}', [CountryController::class, 'show']);
    Route::get('code/{code}', [CountryController::class, 'showCountryByCode']);
    Route::post('{id}', [CountryController::class, 'update']);
    Route::delete('{id}', [CountryController::class, 'destroy']);
});


Route::prefix('operators/')->group(function () {
    Route::get('', [OperatorController::class, 'index']);
    Route::post('', [OperatorController::class, 'store'])->middleware('auth:sanctum');
    Route::get('{id}', [OperatorController::class, 'show']);
    Route::get('code/{code}', [OperatorController::class, 'showCountryByCode']);
    Route::post('{id}', [OperatorController::class, 'update']);
    Route::delete('{id}', [OperatorController::class, 'destroy']);
});

// Route::apiResource('countries', CountryController::class)->middleware('auth:sanctum');

// Route::middleware('auth:sanctum')->apiResource('operators', OperatorController::class);

Route::get('/health', function () {
    return response()->json([
        'status' => 'UP',
        'service' => 'user-service',
        'timestamp' => now(),
        'database' => 'connected' // Vérifier la DB si nécessaire
    ]);
});
