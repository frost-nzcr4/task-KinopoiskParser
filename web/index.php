<?php
require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();

$app['debug'] = true;

$app->register(new Silex\Provider\TwigServiceProvider(), [
    'twig.path' => __DIR__ . '/../src/KinopoiskParser/templates',
]);

$app->get('/', function () use ($app) {
    return $app['twig']->render('homepage.twig');
});

$app->get('/hello/{name}', function ($name) use ($app) {
    return 'Hello '.$app->escape($name);
});

$app->get('/movie/{id}', function ($id) use ($app) {
    $parser = new \KinopoiskParser\Application();
    $data = $parser->parse($id);
    //$message = $request->get('message');
    //mail('feedback@yoursite.com', '[YourSite] Feedback', $message);

    //return new Response('Thank you for your feedback!', 201);
    //return $app->escape($data);
    return $app['twig']->render('movie.twig', ['data' => $data]);
});

$app->run();
