<?php

use App\Livewire\EstimateGenerator;
use Illuminate\Support\Facades\Route;

Route::get('/estimate', EstimateGenerator::class);
