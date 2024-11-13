<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\WorkTimeController;

// Grupa dla routingu API
Route::middleware('api')->group(function () {

    // Endpoint do tworzenia nowego pracownika
    Route::post('/employees', [EmployeeController::class, 'create']);

    // Endpoint do rejestracji czasu pracy dla pracownika
    Route::post('/worktimes', [WorkTimeController::class, 'register']);

    // Endpoint do podsumowania czasu pracy dla pracownika
    Route::get('/worktimes/summary', [WorkTimeController::class, 'summary']);

});
