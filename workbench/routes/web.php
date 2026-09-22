<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laratribe\AdvancedFilters\Support\FilteredQuery;
use Workbench\App\Models\Product;

// Serve the package's Alpine controller as an ES module for the demo pages.
Route::get('/assets/advanced-filters.js', function () {
    $path = __DIR__.'/../../resources/js/advanced-filters.js';

    return response(file_get_contents($path))
        ->header('Content-Type', 'application/javascript');
});

Route::get('/assets/advanced-filters.css', function () {
    $path = __DIR__.'/../../resources/css/advanced-filters.css';

    return response(file_get_contents($path))
        ->header('Content-Type', 'text/css');
});

// Blade + Alpine demo.
Route::get('/', function (Request $request) {
    $query = FilteredQuery::for(Product::class)->fromRequest($request);

    return view('workbench::index', [
        'products' => $query->paginate(15),
        'filterFields' => $query->fieldDefinitions(),
        'activeFilters' => $query->activeFilters(),
    ]);
})->name('products.index');

// Livewire demo.
Route::get('/livewire', function () {
    return view('workbench::livewire');
})->name('products.livewire');
