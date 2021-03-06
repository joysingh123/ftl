<?php

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

Route::get('/','Auth\LoginController@showLoginForm');
Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('exportcontactdata/{id}',"ExportController@export")->middleware('auth');
Route::get('exportdomaincontactdata/{id}',"ExportController@exportDomain")->middleware('auth');
Route::get('exportemaildata/{id}',"ExportController@exportEmail")->middleware('auth');
Route::get('exportcontactdnf/{id}',"ExportController@exportDomainNotFound")->middleware('auth');
Route::get('exportcontactcnf/{id}',"ExportController@exportCompanyNotFound")->middleware('auth');
Route::get('reprocesssheet/{id}',"ImportDataController@reprocessSheet")->middleware('auth');
Route::get('markascomplete/{id}',"ImportDataController@markComplete")->middleware('auth');
Route::get('unmarkascomplete/{id}',"ImportDataController@unmarkComplete")->middleware('auth');
Route::get('importcontactdata',"ImportDataController@importContactView")->middleware('auth');
Route::post('importcontactdata',"ImportDataController@importContactData")->name('contactimport')->middleware('auth');
Route::get('importdomaincontactdata',"ImportDataController@importDomainContactView")->middleware('auth');
Route::post('importdomaincontactdata',"ImportDataController@importDomainContactData")->name('contactdomainimport')->middleware('auth');

Route::get('emailverificationimportdata',"ImportDataController@importEmailView")->middleware('auth');
Route::post('emailverificationimportdata',"ImportDataController@importEmailData")->name('emailverificationimport')->middleware('auth');

Route::get('importcompaniesdata',"ImportDataController@importCompanyView")->middleware('auth');
Route::post('importcompaniesdata',"ImportDataController@importCompanyData")->name('companyimport')->middleware('auth');

Route::get('getloginuserinfo',"LoginUserInfoController@loginuserinfo")->middleware('auth');