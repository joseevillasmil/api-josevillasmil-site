<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () {
    return date('H:i'); 
});

//$app->get('/post/check/{empresa}', 'CheckController@postCheck');
$app->post('/post/check/{empresa}', 'CheckController@postCheck');

