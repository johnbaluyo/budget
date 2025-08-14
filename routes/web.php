<?php

use Illuminate\Support\Facades\Auth;
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

Route::redirect('/', 'gaa');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/users', 'UserController@index');
Route::post('/users/store', 'UserController@store');
Route::post('/users/update', 'UserController@update');
Route::post('/users/delete', 'UserController@delete');

Route::get('/positions', 'PositionController@index');
Route::post('/positions/store', 'PositionController@store');
Route::post('/positions/update', 'PositionController@update');
Route::post('/positions/delete', 'PositionController@delete');

Route::get('/divisions', 'DivisionController@index');
Route::post('/divisions/store', 'DivisionController@store');
Route::post('/divisions/update', 'DivisionController@update');
Route::post('/divisions/delete', 'DivisionController@delete');

Route::get('/fundclusters', 'FundClusterController@index');
Route::post('/fundclusters/store', 'FundClusterController@store');
Route::post('/fundclusters/update', 'FundClusterController@update');
Route::post('/fundclusters/delete', 'FundClusterController@delete');

Route::get('/approvedbudget', 'ApprovedBudgetController@index');
Route::post('/approvedbudget/store', 'ApprovedBudgetController@store');
Route::post('/approvedbudget/update', 'ApprovedBudgetController@update');
Route::post('/approvedbudget/delete', 'ApprovedBudgetController@delete');

Route::get('/gaa/{year}', 'GAAController@index');
Route::post('/gaa/delete', 'GAAController@delete');
Route::post('/gaa/store', 'GAAController@store');
Route::post('/gaa/edit', 'GAAController@edit');
Route::post('/gaa/getGaaprojects', 'GAAController@getGaaprojects');
Route::post('/gaa/saveProjectAllocation', 'GAAController@saveProjectAllocation');
Route::post('/gaa/saveGAABudget', 'GAAController@saveGAABudget');
Route::get('/project/{id}', 'GAAController@project')->name('project');
Route::post('/project/saveItemToProject', 'GAAController@saveItemToProject');
Route::post('/project/getExpenseId', 'GAAController@getExpenseId');
Route::post('/project/loadTracking', 'GAAController@loadTracking');
Route::post('/project/updateTracking', 'GAAController@updateTracking');
Route::post('/project/getGaaFromProject', 'GAAController@getGaaFromProject');
Route::post('/project/moveToOtherProject', 'GAAController@moveToOtherProject');

Route::get('/projects/{year}', 'ProjectController@index');
