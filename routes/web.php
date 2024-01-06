<?php

use App\Http\Controllers\BehaviorController;
use App\Http\Controllers\EndpointController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Livewire\ProjectsIndex;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::resource('project/{project:slug}/endpoint/{endpoint:slug}/behavior', BehaviorController::class)
        ->except(['index', 'show'])
        ->parameters([
            'behavior' => 'behavior:slug',
        ]);

    Route::resource('project/{project:slug}/endpoint', EndpointController::class)
        ->except(['index', 'destroy'])
        ->parameters([
            'endpoint' => 'endpoint:slug',
        ]);

    Route::get('project', ProjectsIndex::class)->name('project.index');

    Route::resource('project', ProjectController::class)
        ->except(['index', 'destroy'])
        ->parameters(['project' => 'project:slug']);

    Route::get('/livewire', \App\Livewire\FullPageComponent::class)->name('livewire');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
