<?php

namespace Valera\Loader\Guzzle;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\ResponseInterface;
use Valera\Loader\Result as LoaderResult;
use Valera\Resource;

abstract class GuzzleAbstract
{
    /**
     * @var \GuzzleHttp\ClientInterface
     */
    protected $httpClient;

    public function __construct(ClientInterface $httpClient)
    {
        $this->setHttpClient($httpClient);
    }

    /**
     * Returns underlying HTTP client implementation
     *
     * @return \GuzzleHttp\ClientInterface
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * Sets underlying HTTP client implementation
     *
     * @param \GuzzleHttp\ClientInterface $httpClient
     */
    public function setHttpClient(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    protected function createRequest(Resource $resource)
    {
        return $this->httpClient->createRequest(
            $resource->getMethod(),
            $resource->getUrl(),
            array(
                'headers' => $this->getHeaders($resource),
                'body' => $resource->getPayload(),
            )
        );
    }

    protected function processResponse(ResponseInterface $response, LoaderResult $result)
    {
        $contentType = $response->getHeader('Content-Type');
        if ($contentType) {
            $contentType = (string) $contentType;
        }

        $body = (string) $response->getBody();
        $result->setContent($body, $contentType);
    }

    protected function processFailure(RequestException $e, LoaderResult $result)
    {
        $message = $e->getMessage();
        $result->fail($message);
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