<?php

namespace Valera\Loader;

use Guzzle\Http\ClientInterface;
use Guzzle\Http\Message\Response;
use Valera\Loader\Result as LoaderResult;
use Valera\Resource;

class Guzzle implements LoaderInterface
{
    protected $httpClient;

    public function __construct(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function load(Resource $resource, LoaderResult $result)
    {
        $response = $this->sendRequest($resource);
        $this->processResponse($response, $result);
    }

    protected function sendRequest(Resource $resource)
    {
        return $this->httpClient->createRequest(
            $resource->getMethod(),
            $resource->getUrl(),
            $this->getHeaders($resource),
            $resource->getData()
        )->send();
    }

    protected function processResponse(Response $response, LoaderResult $result)
    {
        if ($response->isError()) {
            $message = $response->getStatusCode();
            $result->fail($message);
        } else {
            $contentType = $response->getHeader('Content-Type');
            if ($contentType) {
                $contentType = (string) $contentType;
            }

            $body = $response->getBody(true);
            $result->setContent($body, $contentType);
        }
    }

    /**
     * Returns request headers. The Referer header is first taken from headers, and if not found
     * then referrer resource property is used
     *
     * @param \Valera\Resource $resource
     *
     * @return array
     */
    protected function getHeaders(Resource $resource)
    {
        return array_merge(array_filter(array(
            'referer' => $resource->getReferrer(),
        )), $resource->getHeaders());
    }
}
