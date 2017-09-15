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

Route::any('/', function () {

	return view('welcome');

});


Auth::routes();

Route::get('/home/{timestamp?}', 'HomeController@index')->where(['timestamp' => '[0-9]+']);
Route::get('/home/open/{id?}', 'HomeController@open')->where(['id' => '[0-9]+']);
Route::post('/home/send', 'HomeController@send');

Route::get('/telegram_dialogs', 'TelegramDialogsController@index');
Route::get('/telegram_dialogs/open/{id}', 'TelegramDialogsController@open')->where(['id' => '[0-9]+']);
Route::get('/telegram_dialogs/all/{id}', 'TelegramDialogsController@all')->where(['id' => '[0-9]+']);
Route::post('/telegram_dialogs/send', 'TelegramDialogsController@send');
Route::post('/telegram_dialogs/close', 'TelegramDialogsController@close');
Route::post('/telegram_dialogs/delete', 'TelegramDialogsController@delete');
Route::get('/telegram_dialogs/server/{id}/{timestamp?}', 'TelegramDialogsController@server')
	->where(['id' => '[0-9]+','timestamp' => '[0-9]+']);
Route::any('/telegram', 'WebhookController@telegram_webhook');
Route::any('/callback/xE4sA/', 'WebhookController@vkbot_webhook');



//Clear Cache facade value:
Route::get('/clear-cache', function() {
    $exitCode = Artisan::call('cache:clear');
    return '<h1>Cache facade value cleared</h1>';
});

//Reoptimized class loader:
Route::get('/optimize', function() {
    $exitCode = Artisan::call('optimize');
    return '<h1>Reoptimized class loader</h1>';
});

//Clear Route cache:
Route::get('/route-cache', function() {
    $exitCode = Artisan::call('route:cache');
    return '<h1>Route cache cleared</h1>';
});

//Clear View cache:
Route::get('/view-clear', function() {
    $exitCode = Artisan::call('view:clear');
    return '<h1>View cache cleared</h1>';
});

//Clear Config cache:
Route::get('/config-cache', function() {
    $exitCode = Artisan::call('config:cache');
    return '<h1>Clear Config cleared</h1>';
});

Route::get('/dbtest', function() {
  if (DB::connection()->getDatabaseName())  {
    dd('goodd!');
  } else {
    return 'error db';
  }
  });
