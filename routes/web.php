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
        return \App\Models\Post::all();
    });

    $router->get('/posts/{post}', function ($post) {
        $post = \App\Models\Post::where('slug', $post)->firstOrFail();

        return $post;
    });

    $router->post('/playlists', function (\Illuminate\Http\Request  $request) {
        $data = $request->json()->all();
        $rules = ['tracks' => 'present|array', 'videos' => 'present|array'];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return $validator->messages();
        }

        $slug = \Illuminate\Support\Str::random(22);

        file_put_contents(__DIR__ . '/../public/playlists/' . $slug . '.json', json_encode($data));

        return [
            'slug' => $slug
        ];
    });

    $router->get('/playlists/{slug}', function ($slug) {
        if (file_exists(__DIR__ . '/../public/playlists/' . $slug . '.json')) {
            header('Content-Type: application/json');

            return file_get_contents(__DIR__ . '/../public/playlists/' . $slug . '.json');
        }
    });
});
