<?php

namespace KinopoiskParser;

class Downloader
{
    const MOVIE_URL = 'http://www.kinopoisk.ru/film/%s/';
    const CAST_URL = 'http://www.kinopoisk.ru/film/%s/cast/';

    public function __construct(array $values = array())
    {
    }

    /**
     * Prepare context for stream.
     *
     * @return resource.
     */
    public function prepareContext()
    {
        $headers = [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Encoding: gzip, deflate',
            'Accept-Language: ru-RU,ru;q=0.9',
            'Accept-Charset: utf-8',
            'Connection: keep-alive',
            'Host: www.kinopoisk.ru',
            'user_country=ru',
            'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:40.0) Gecko/20100101 Firefox/40.0'
        ];

        $options = [
            'http' => [
                'method' => 'GET',
                //'protocol_version' => 1.1,
                //'proxy' => 'http://proxy:8080',
                //'timeout' => 4.0,
                'header' => implode("\r\n", $headers),
                //'request_fulluri' => true
            ]
        ];

        $context = stream_context_create($options);

        return $context;
    }

    /**
     * Fix data.
     *
     * @param string $data
     * @return string
     */
    public function fixData($data)
    {
        $data = mb_convert_encoding($data, 'UTF-8', 'cp1251');
        $data = str_replace('charset=windows-1251', 'charset=UTF-8', $data);
        $tidy = new \tidy();
        $config = [
            //'clean' => true,
            'indent' => true,
            //'input-xhtml' => true,
            'new-empty-tags' => 'spacer',
            'new-inline-tags' => 'spacer',
            'output-html' => true,
        ];
        $data = $tidy->repairString($data, $config, 'utf8');
        $data = str_replace('<spacer ', '<br ', $data);

        // TODO: Remove this block after tests.
        if (0) {
            $fh = fopen(__DIR__ . '/../../tidy.html', 'wb');
            fwrite($fh, $data);
            fclose($fh);
        }

        return $data;
    }

    /**
     * Download movie data.
     *
     * @param integer|string $id
     * @return NULL|string
     */
    public function downloadMovie($id)
    {
        $context = $this->prepareContext();
        $fileHandle = fopen(sprintf(self::MOVIE_URL, $id), 'rb', false, $context);
        //$fileHandle = fopen(sprintf(__DIR__ . '/../../71065.html.gz', $id), 'rb', false, $context);
        if (!$fileHandle) {
            return null;
        }
        stream_set_timeout($fileHandle, 1);
        $fileContents = stream_get_contents($fileHandle);
        fclose($fileHandle);
        $file_contents_decoded = gzdecode($fileContents);
        $data = $this->fixData($file_contents_decoded);

        return $data;
    }

    /**
     * Download cast data.
     *
     * @param integer|string $id
     * @return NULL|string
     */
    public function downloadCast($id)
    {
        $context = $this->prepareContext();
        $fileHandle = fopen(sprintf(self::CAST_URL, $id), 'rb', false, $context);
        //$fileHandle = fopen(sprintf(__DIR__ . '/../../71065_cast.html.gz', $id), 'rb', false, $context);
        if (!$fileHandle) {
            return null;
        }
        stream_set_timeout($fileHandle, 1);
        $fileContents = stream_get_contents($fileHandle);
        fclose($fileHandle);
        $file_contents_decoded = gzdecode($fileContents);
        $data = $this->fixData($file_contents_decoded);

        return $data;
    }
}
