<?php
require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();

$app['debug'] = true;

$app->get('/', function () use ($app) {
    return 'Hello world <a href="/movie/71065">movie</a>';
});

$app->get('/hello/{name}', function ($name) use ($app) {
    return 'Hello '.$app->escape($name);
});

$app->get('/movie/{id}', function ($id) {
    $parser = new \KinopoiskParser\Application();
    $data = $parser->parse($id);
    //$message = $request->get('message');
    //mail('feedback@yoursite.com', '[YourSite] Feedback', $message);

    //return new Response('Thank you for your feedback!', 201);
    //return $app->escape($data);
    return $data;
});

$app->run();
