<?php

namespace Valera\Tests;

use GuzzleHttp\Exception\RequestException;
use Valera\Loader\Guzzle as Loader;
use Valera\Tests\Value\Helper;

/**
 * @covers \Valera\Loader\Guzzle
 */
class LoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testSuccessResponse()
    {
        /** @var \GuzzleHttp\Message\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject $response */
        $response = $this->getMock('GuzzleHttp\\Message\\ResponseInterface');
        $response->expects($this->once())
            ->method('getHeader')
            ->will($this->returnValue('X-PHPUnit'));
        $response->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue('Hello, world!'));

        $loader = $this->getLoader();
        $loader->expects($this->any())
            ->method('sendRequest')
            ->will($this->returnValue($response));

        /** @var \Valera\Loader\Result|\PHPUnit_Framework_MockObject_MockObject $result */
        $result = $this->getMockBuilder('Valera\\Loader\\Result')
            ->disableOriginalConstructor()
            ->getMock();
        $result->expects($this->once())
            ->method('setContent')
            ->with('Hello, world!', 'X-PHPUnit');

        $this->load($loader, $result);
    }

    public function testFailedResponse()
    {
        /** @var \GuzzleHttp\Message\RequestInterface $request */
        $request = $this->getMock('GuzzleHttp\\Message\\RequestInterface');
        $e = new RequestException('Oops...', $request);

        $loader = $this->getLoader();
        $loader->expects($this->any())
            ->method('sendRequest')
            ->will($this->throwException($e));

        /** @var \Valera\Loader\Result|\PHPUnit_Framework_MockObject_MockObject $result */
        $result = $this->getMockBuilder('Valera\\Loader\\Result')
            ->disableOriginalConstructor()
            ->getMock();
        $result->expects($this->once())
            ->method('fail')
            ->with('Oops...');

        $this->load($loader, $result);
    }

    /**
     * @return \Valera\Loader\Guzzle|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getLoader()
    {
        return $this->getMockBuilder('Valera\\Loader\\Guzzle')
            ->disableOriginalConstructor()
            ->setMethods(array('sendRequest'))
            ->getMock();
    }

    private function load(Loader $loader, $result)
    {
        $resource = Helper::getResource();
        $loader->load($resource, $result);
    }
}
