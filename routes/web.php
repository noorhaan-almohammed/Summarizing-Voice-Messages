<?php

use App\Http\Controllers\AudioTranscriptionController;
use App\Http\Controllers\TextSummarizationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::post('/transcribe', [AudioTranscriptionController::class, 'transcribe']);

Route::post('/summarize-text', [TextSummarizationController::class, 'summarize']);
