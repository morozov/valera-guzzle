<?php

namespace Valera\Loader\Guzzle;

use GuzzleHttp\Event\AbstractTransferEvent;
use GuzzleHttp\Event\CompleteEvent;
use GuzzleHttp\Event\ErrorEvent;

class Multi extends GuzzleAbstract
{
    public function loadAll(\Iterator $iterator)
    {
        $this->httpClient->sendAll($iterator, array(
            'complete' => function (CompleteEvent $event) {
                $result = $this->getResult($event);
                $response = $event->getResponse();
                $this->processResponse($response, $result);
            },
            'error' => function (ErrorEvent $event) {
                $result = $this->getResult($event);
                $e = $event->getException();
                $this->processFailure($e, $result);
            }
        ));
    }

    protected function getResult(AbstractTransferEvent $event)
    {
        $request = $event->getRequest();
        return;
    }
}
