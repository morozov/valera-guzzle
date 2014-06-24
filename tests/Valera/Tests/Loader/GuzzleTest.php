<?php

namespace Valera\Tests;

use Valera\Loader;

/**
 * @covers \Valera\Loader\Guzzle
 */
class LoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testSuccessResponse()
    {
        $response = $this->getResponseMock(200);
        $result = $this->getResultMock('setContent');
        $this->callProcessResponse($response, $result);
    }

    public function testFailedResponse()
    {
        $response = $this->getResponseMock(404);
        $result = $this->getResultMock('fail');
        $this->callProcessResponse($response, $result);
    }

    private function getResponseMock($statusCode)
    {
        $response = $this->getMockBuilder('GuzzleHttp\\Message\\Response')
            ->disableOriginalConstructor()
            ->setMethods(array('getStatusCode'))
            ->getMock();
        $response->expects($this->any())
            ->method('getStatusCode')
            ->will($this->returnValue($statusCode));

        return $response;
    }

    private function getResultMock($expectedMethod)
    {
        $result = $this->getMockBuilder('Valera\\Loader\\Result')
            ->disableOriginalConstructor()
            ->getMock();
        $result->expects($this->once())
            ->method($expectedMethod);

        return $result;
    }

    private function callProcessResponse($response, $result)
    {
        $loader = $this->getMockBuilder('Valera\\Loader\\Guzzle')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();
        $re = new \ReflectionMethod($loader, 'processResponse');
        $re->setAccessible(true);
        $re->invoke($loader, $response, $result);
    }
}
