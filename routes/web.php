<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->group(['prefix' => '/v1'], function () use ($router) {
    $router->get('/posts', function () {
        header('Access-Control-Allow-Origin: *');

        return \App\Models\Post::all();
    });

    $router->get('/posts/{post}', function ($post) {
        header('Access-Control-Allow-Origin: *');
        
        $post = \App\Models\Post::where('slug', $post)->firstOrFail();

        return $post;
    });
});
