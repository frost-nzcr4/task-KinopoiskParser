<?php

namespace KinopoiskParser;

class Application
{
    const VERSION = '0.1.0';

    public function __construct(array $values = array())
    {
    }

    /**
     * Parse whole data.
     *
     * @param int|string $id
     */
    public function parse($id)
    {
        $downloader = new Downloader();

        $movie_data = $downloader->downloadMovie($id);
        $qp_movie = \QueryPath::with($movie_data);

        $cast_data = $downloader->downloadCast($id);
        $qp_cast = \QueryPath::with($cast_data);

        $header = trim($qp_movie->find('#headerFilm > h1')->text());
        $info = $this->parseInfo($qp_movie);
        $actors = $this->parseActors($qp_cast);

        return [
            'header' => $header,
            'info' => $info,
            'actors' => $actors
        ];
    }

    /**
     * Parse actors.
     *
     * @param \QueryPath\DOMQuery $qp
     * @return array
     */
    public function parseActors(\QueryPath\DOMQuery $qp) {
        $actors = [];
        $div = $qp->find('a[name=actor]')->next();
        while ($start = $div->next()) {
            if (!$start->hasClass('dub')) {
                break;
            }
            $div = $start;
            if (!$div) {
                continue;
            }
            $key = $div->find('.photo a')->attr('href');
            $actors[$key] = [];

            $actors[$key]['photo'] = $div->find('.photo img')->attr('title');
            $actors[$key]['name'] = $div->find('.name > a')->text();
            if ($role = $div->find('.info > .role')) {
                $actors[$key]['role'] = $role->text();
            }
        }

        return $actors;
    }

    /**
     * Parse movie info.
     *
     * @param \QueryPath\DOMQuery $qp
     * @return array
     */
    public function parseInfo(\QueryPath\DOMQuery $qp) {
        $info = [];
        foreach($qp->find('#infoTable tr') as $tr) {
            $key = trim($tr->find('td:nth-child(1)')->text());
            $value = null;
            if (in_array($key, ['жанр', 'бюджет', 'сборы в США', 'сборы в России'], true)) {
                $value = $tr->find('td:nth-child(2) > :first-child')->text();
            } else if (in_array($key, ['сборы в мире'], true)) {
                $value = $tr->find('td:nth-child(2) > div > :first-child')->text();
            } else {
                $value = $tr->find('td:nth-child(2)')->text();
            }
            $info[$key] = trim($value);
        }
        return $info;
    }
}
