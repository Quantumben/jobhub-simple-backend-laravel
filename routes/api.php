<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::get('/jobs', [JobController::class, 'index']);

Route::get('/jobs/{job}', [JobController::class, 'show']);

Route::get('/companies', [CompanyController::class, 'index']);

Route::get('/companies/{company}', [CompanyController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/my-jobs', [JobController::class, 'myJobs']);
    Route::get('/my-jobs/{id}', [JobController::class, 'myJobID']);
    Route::patch('/jobs/{job}', [JobController::class, 'update'])->can('update', 'job');
    Route::delete('/jobs/{job}', [JobController::class, 'destroy'])->can('delete', 'job');
    Route::post('/jobs', [JobController::class, 'store']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/password', [ProfileController::class, 'updatePassword']);
    Route::get('/my-companies', [CompanyController::class, 'myCompanies']);
    Route::post('/companies', [CompanyController::class, 'store']
    );
});
