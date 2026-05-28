<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin/login');

require __DIR__ . '/auth.php';
