<?php

use App\Http\Controllers\Admin\AlternativeController as AdminAlternativeController;
use App\Http\Controllers\Admin\CalculationController as AdminCalculationController;
use App\Http\Controllers\Admin\CriterionController;
use App\Http\Controllers\Admin\QuestionnaireController;
use App\Http\Controllers\Admin\SubCriterionController;
use App\Http\Controllers\ComparisonController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\User\AlternativeController as UserAlternativeController;
use App\Http\Controllers\User\WeightingController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/hasil/{calculation}', [ResultController::class, 'show'])->name('results.show');
    Route::get('/hasil/{calculation}/ekspor', [ExportController::class, 'export'])->name('results.export');

    Route::get('/bandingkan', [ComparisonController::class, 'index'])->name('comparison.index');
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('criteria', CriterionController::class)->except(['show']);
    Route::get('criteria/{criterion}/sub-criteria', [SubCriterionController::class, 'index'])->name('sub-criteria.index');
    Route::get('criteria/{criterion}/sub-criteria/create', [SubCriterionController::class, 'create'])->name('sub-criteria.create');
    Route::post('criteria/{criterion}/sub-criteria', [SubCriterionController::class, 'store'])->name('sub-criteria.store');
    Route::get('sub-criteria/{subCriterion}/edit', [SubCriterionController::class, 'edit'])->name('sub-criteria.edit');
    Route::patch('sub-criteria/{subCriterion}', [SubCriterionController::class, 'update'])->name('sub-criteria.update');
    Route::delete('sub-criteria/{subCriterion}', [SubCriterionController::class, 'destroy'])->name('sub-criteria.destroy');

    Route::resource('alternatives', AdminAlternativeController::class)->except(['show']);

    Route::get('questionnaire', [QuestionnaireController::class, 'index'])->name('questionnaire.index');
    Route::get('questionnaire/edit', [QuestionnaireController::class, 'edit'])->name('questionnaire.edit');
    Route::put('questionnaire', [QuestionnaireController::class, 'update'])->name('questionnaire.update');

    Route::get('calculations', [AdminCalculationController::class, 'index'])->name('calculations.index');
});

Route::middleware(['auth', 'verified'])->prefix('user')->name('user.')->group(function () {
    Route::get('alternatives', [UserAlternativeController::class, 'index'])->name('alternatives.index');
    Route::get('weighting', [WeightingController::class, 'index'])->name('weighting.index');
    Route::post('weighting/calculate', [WeightingController::class, 'calculate'])->name('weighting.calculate');
});

require __DIR__.'/auth.php';
