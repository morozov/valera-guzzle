<?php

namespace Valera\Loader;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Event\CompleteEvent;
use GuzzleHttp\Event\ErrorEvent;
use Valera\Queueable;
use Valera\Resource;
use Valera\Worker\Converter;

class Guzzle implements LoaderInterface, Converter
{
    /**
     * @var \GuzzleHttp\ClientInterface
     */
    protected $httpClient;

    /**
     * Constructor
     *
     * @param \GuzzleHttp\ClientInterface $httpClient
     */
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

    /**
     * Process tasks
     *
     * @param \Traversable|\GuzzleHttp\Message\RequestInterface[] $tasks
     * @param callable $resolver
     */
    public function process(\Traversable $tasks, callable $resolver)
    {
        $this->httpClient->sendAll($tasks, array(
            'complete' => function (CompleteEvent $event) use ($resolver) {
                $resolver($event->getRequest(), function (Result $result) use ($event) {
                    $this->onComplete($event, $result);
                });
            },
            'error' => function (ErrorEvent $event) use ($resolver) {
                $resolver($event->getRequest(), function (Result $result) use ($event) {
                    $this->onError($event, $result);
                });
            },
        ));
    }

    /**
     * Convert queued item to Guzzle Request
     *
     * @param \Valera\Queueable|\Valera\Resource $item
     * @return \GuzzleHttp\Message\RequestInterface
     */
    public function convert(Queueable $item)
    {
        return $this->httpClient->createRequest(
            $item->getMethod(),
            $item->getUrl(),
            array(
                'headers' => $this->getHeaders($item),
                'body' => $item->getPayload(),
            )
        );
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
            'Referer' => $resource->getReferrer(),
        )), $resource->getHeaders());
    }

    /**
     * "Complete" event handler
     *
     * @param \GuzzleHttp\Event\CompleteEvent $event
     * @param \Valera\Loader\Result $result
     */
    protected function onComplete(CompleteEvent $event, Result $result)
    {
        $response = $event->getResponse();
        $contentType = $response->getHeader('Content-Type');
        if ($contentType) {
            $contentType = (string) $contentType;
        }
        $body = (string) $response->getBody();
        $result->setContent($body, $contentType);
    }

    /**
     * "Error" event handler
     *
     * @param \GuzzleHttp\Event\ErrorEvent $event
     * @param \Valera\Loader\Result $result
     */
    protected function onError(ErrorEvent $event, Result $result)
    {
        $exception = $event->getException();
        $message = $exception->getMessage();
        $result->fail($message);
    }
}
