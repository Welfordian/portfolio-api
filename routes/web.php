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
    $router->get('/bookmarks', function (\Illuminate\Http\Request $request) {
        $bookmarks = json_decode(file_get_contents(__DIR__ . '/../public/bookmarks.json'), true);

        return ['bookmarks' => $bookmarks];
    });

    $router->post('/bookmarks', function (\Illuminate\Http\Request $request) {
        if (str_replace('Bearer: ', '', $request->header('Authorization')) !== 'eu9JU801P9#iPliIev#&g1ej7!$oB2K') return ['success' => false, 'message' => 'Unauthorized'];

        $data = $request->json()->all();
        $id = $data['id'];

        if ($data['parentId'] !== "50") return;

        $bookmarks = json_decode(file_get_contents(__DIR__ . '/../public/bookmarks.json'), true);

        $bookmarks[$id] = $data;

        file_put_contents(__DIR__ . '/../public/bookmarks.json', json_encode($bookmarks, JSON_PRETTY_PRINT));

        return ['success' => true];
    });

    $router->delete('/bookmarks', function (\Illuminate\Http\Request $request) {
        if (str_replace('Bearer: ', '', $request->header('Authorization')) !== 'eu9JU801P9#iPliIev#&g1ej7!$oB2K') return ['success' => false, 'message' => 'Unauthorized'];

        $id = $request->json()->get('id');

        $bookmarks = json_decode(file_get_contents(__DIR__ . '/../public/bookmarks.json'), true);

        if (array_key_exists($id, $bookmarks)) {
            unset($bookmarks[$id]);
        }

        file_put_contents(__DIR__ . '/../public/bookmarks.json', json_encode($bookmarks, JSON_PRETTY_PRINT));

        return ['success' => true];
    });

    $router->put('/bookmarks', function (\Illuminate\Http\Request $request) {
        if (str_replace('Bearer: ', '', $request->header('Authorization')) !== 'eu9JU801P9#iPliIev#&g1ej7!$oB2K') return ['success' => false, 'message' => 'Unauthorized'];
        
        $data = $request->json()->all();
        $id = $request->json()->get('id');

        $bookmarks = json_decode(file_get_contents(__DIR__ . '/../public/bookmarks.json'), true);

        if (array_key_exists($id, $bookmarks)) {
            $bookmarks[$id]['title'] = $data['title'];
            $bookmarks[$id]['url'] = $data['url'];
        }

        file_put_contents(__DIR__ . '/../public/bookmarks.json', json_encode($bookmarks, JSON_PRETTY_PRINT));

        return ['success' => true];
    });

    $router->group(['prefix' => 'internal'], function () use ($router) {
        $router->post('build', function (\Illuminate\Http\Request $request) {
            if (! $request->has('secret') || $request->get('secret') !== env('INTERNAL_SECRET')) abort(401);

            $client = new \GuzzleHttp\Client();

            $client->post('https://api.digitalocean.com/v2/apps/' . env('DIGITALOCEAN_APP_ID') . '/deployments', [
                'json' => [
                    'force_build' => true
                ],

                'headers' => [
                    'Authorization' => 'Bearer ' . env('DIGITALOCEAN_KEY')
                ]
            ]);
        });
    });

    $router->post('/playlists', function (\Illuminate\Http\Request $request) {
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

    $router->get('/ping', function () {
        return ['pong' => true];
    });
});
