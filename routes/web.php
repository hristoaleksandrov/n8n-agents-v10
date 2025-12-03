<?php

use App\Livewire\TaskCreate;
use App\Livewire\TaskList;
use App\Livewire\TaskShow;
use Illuminate\Support\Facades\Route;

Route::get('/', TaskList::class)->name('tasks.index');
Route::get('/tasks/create', TaskCreate::class)->name('tasks.create');
Route::get('/tasks/{task}', TaskShow::class)->name('tasks.show');
