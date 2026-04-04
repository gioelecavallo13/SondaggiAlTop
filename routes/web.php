<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PublicSurveyController;
use App\Http\Controllers\ResponseController;
use App\Http\Controllers\SurveyController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/chi-siamo', [HomeController::class, 'about'])->name('about');

Route::get('/contatti', [ContactController::class, 'show'])->name('contacts.index');
Route::post('/contatti', [ContactController::class, 'submit'])->name('contacts.submit');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/sondaggi', [PublicSurveyController::class, 'index'])->name('surveys.public.index');
Route::get('/sondaggi/ricerca', [PublicSurveyController::class, 'search'])->name('surveys.public.search');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [SurveyController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/sondaggi/nuovo', [SurveyController::class, 'createForm'])->name('surveys.create');
    Route::post('/dashboard/sondaggi/nuovo', [SurveyController::class, 'store'])->name('surveys.store');
    Route::get('/dashboard/sondaggi/{sondaggio}/modifica', [SurveyController::class, 'editForm'])->name('surveys.edit');
    Route::post('/dashboard/sondaggi/{sondaggio}/modifica', [SurveyController::class, 'update'])->name('surveys.update');
    Route::post('/dashboard/sondaggi/{sondaggio}/elimina', [SurveyController::class, 'destroy'])->name('surveys.destroy');
    Route::get('/dashboard/sondaggi/{sondaggio}/statistiche', [SurveyController::class, 'stats'])->name('surveys.stats');
    Route::get('/sondaggi/{sondaggio}', [SurveyController::class, 'show'])->name('surveys.show');
    Route::post('/sondaggi/{sondaggio}/compila', [ResponseController::class, 'submit'])->name('surveys.submit');
});
