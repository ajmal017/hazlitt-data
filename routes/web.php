<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('dashboard');
});

Route::group(['namespace' => 'Main'], function() {
    Route::get('countries/index', 'CountryController@index')
    ->name('country.index');
    Route::get('commodities/index', 'CommodityController@index')
    ->name('commodity.index');

    Route::get('research/chart-generator', 'ResearchController@chart_generator')
    ->name('research.chart_generator');
});

Route::fallback('DynamicRouter@handle')
    ->name('default')
    ->middleware('minify');
