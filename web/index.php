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

$app->get('/movie/{id}', function ($id) use ($app) {
    $parser = new \KinopoiskParser\Application();
    $data = $parser->parse($id);

    return $app['twig']->render('movie.twig', ['data' => $data, 'id' => $id]);
});

$app->post('/movie/{id}/pdf', function (Request $request, $id) use ($app) {
    $pdf = new \KinopoiskParser\Pdf($request->get('html_data'));

    return $pdf->getPdf()->stream('www.kinopoisk.ru_film_' . $id . '.pdf');
});

$app->run();
