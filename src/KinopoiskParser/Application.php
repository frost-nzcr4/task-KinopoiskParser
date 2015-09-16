<?php
/**
 * @version 0.1.0
 */

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
        while ($div = $div->next()) {
            if (!$div->hasClass('dub')) {
                break;
            }
            $actor = $div->find('.actorInfo');
            $key = $actor->find('.photo a')->attr('href');
            $actors[$key] = [];

            $actors[$key]['photo'] = $actor->find('.photo img')->attr('title');
            $actors[$key]['name'] = $actor->find('.name > a')->text();
            if ($role = $actor->find('.info > .role')) {
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
                $value = trim($tr->find('td:nth-child(2) > :first-child')->text());
            } else if (in_array($key, ['сборы в мире'], true)) {
                $value = trim($tr->find('td:nth-child(2) > div > :first-child')->text());
            } else {
                $value = trim($tr->find('td:nth-child(2)')->text());
            }

            if (in_array($key, ['режиссер', 'сценарий', 'продюсер', 'оператор', 'композитор', 'художник', 'жанр'], true)) {
                $value = explode(',', $value);
                $value = array_map(trim, $value);
                $last_value = array_pop($value);
                if ('...' !== $last_value) {
                    $value[] = $last_value;
                }
            }
            $info[$key] = $value;
        }
        return $info;
    }
}
