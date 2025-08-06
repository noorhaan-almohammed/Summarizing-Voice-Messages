<?php

use App\Http\Controllers\AudioTranscriptionController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TextSummarizationController;


Route::post('/login',[AuthController::class,'login']);
Route::post('/register',[AuthController::class,'register']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout',[AuthController::class,'logout']);
    Route::get('/me', function () {
        return response()->json([
            'user' => Auth::user()
        ]);
    });
});

Route::post('/transcribe', [AudioTranscriptionController::class, 'transcribe']);

Route::post('/summarize-text', [TextSummarizationController::class, 'summarize']);
