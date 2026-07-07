<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return view('dashboard.index');
});

Route::get('/products', function () {
    return view('products.index');
});

Route::get('/products/create', function () {
    return view('products.form');
});

Route::get('/products/{id}/edit', function ($id) {
    return view('products.form', ['id' => $id]);
});

Route::get('/expenses', function () {
    return view('expenses.index');
});

Route::get('/expenses/create', function () {
    return view('expenses.form');
});

Route::get('/expenses/{id}/edit', function ($id) {
    return view('expenses.form', ['id' => $id]);
});

Route::get('/sales', function () {
    return view('sales.index');
});

Route::get('/sales/create', function () {
    return view('sales.form');
});

Route::get('/sales/{id}/edit', function ($id) {
    return view('sales.form', ['id' => $id]);
});

Route::get('/reports', function () {
    return view('reports.index');
});
