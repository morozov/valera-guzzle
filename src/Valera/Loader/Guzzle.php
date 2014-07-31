<?php

namespace Valera\Loader;

use GuzzleHttp\Exception\RequestException;
use Valera\Loader\Guzzle\GuzzleAbstract;
use Valera\Loader\Result as LoaderResult;
use Valera\Resource;

class Guzzle extends GuzzleAbstract implements LoaderInterface
{
    public function load(Resource $resource, LoaderResult $result)
    {
        try {
            $response = $this->sendRequest($resource);
            $this->processResponse($response, $result);
        } catch (RequestException $e) {
            $this->processFailure($e, $result);
        }
    }

    protected function sendRequest(Resource $resource)
    {
        $request = $this->createRequest($resource);
        return $this->httpClient->send($request);
    }
}
