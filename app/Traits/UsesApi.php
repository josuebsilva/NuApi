<?php

namespace App\Traits;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

trait UsesApi
{
    /**
     * Send request to any service
     * @param $method
     * @param $requestUrl
     * @param array $formParams
     * @param array $headers
     * @return object
     */
    public function makeRequest($method, $requestUrl, $params = [], $headers = [])
    {
        $client = new Client([
            'base_uri'  =>  $this->baseUri,
        ]);
        if (isset($this->secret)) {
            $headers['Authorization'] = $this->secret;
        }

        $contentType = isset($headers['Content-Type']) ? $headers['Content-Type'] : null;

        $options = ['headers' => $headers];

        if ($contentType === 'multipart/form-data')
            unset($options['headers']['Content-Type']);

        if ($method == 'GET') $requestUrl = $requestUrl . '?' . http_build_query($params);
        else {
            if ($contentType === 'application/x-www-form-urlencoded') {
                $options['form_params'] = $params;
            } else if ($contentType === 'multipart/form-data') {
                $options['multipart'] = $params;
            } else {
                $options['json'] = $params;
            }
        }

        $response = $client->request($method, $requestUrl, $options);
        $content = $response->getBody()->getContents();

        if ($this->isJson($content)) return json_decode($content);
        return $content;
    }

    public function makeConcurrentRequests($method, $requestUrl, $params = [], $headers = [])
    {
        $client = new Client([
            'base_uri'  =>  $this->baseUri,
        ]);
        $requests = [];
        $contents = [];
        if (isset($this->secret)) {
            $headers['Authorization'] = $this->secret;
        }
        $chunks = array_chunk($params, 1000);

        foreach ($chunks as $chunk) {

            if ($method == 'GET') $requestUrl = $requestUrl . '?' . http_build_query($chunk);
            else {
                if (isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/x-www-form-urlencoded') {
                    $options['form_params'] = $chunk;
                } else {
                    $options['json'] = $chunk;
                    $headers['Content-type'] = 'application/json';
                    $headers['Content-Length'] = strlen(json_encode($chunk));
                }
            }

            $requests[] =  new \GuzzleHttp\Psr7\Request($method, $requestUrl, $headers, json_encode($chunk));
        }

        $pool = new \GuzzleHttp\Pool($client, $requests, [
            'concurrency' => 10,
            'fulfilled' => function (\GuzzleHttp\Psr7\Response $response, $index) use (&$contents) {
                $contents[$index] = json_decode($response->getBody()->getContents());
            },
            'rejected' => function (\GuzzleHttp\Exception\RequestException $reason, $index) {
                var_dump($reason->getResponse()->getBody()->getContents());
            },
        ]);
        $promise = $pool->promise();
        $promise->wait();

        return $contents;
    }



    function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
