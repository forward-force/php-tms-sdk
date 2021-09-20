<?php

namespace ForwardForce\TMS\Tests;

use ForwardForce\TMS\HttpClient;
use PHPUnit\Framework\TestCase;

class HttpClientTest extends TestCase
{
    public function testBaseURL()
    {
        $fakeToken = '123456';
        $fixture = new HttpClient($fakeToken);

        try {
            $reflector = new \ReflectionProperty($fixture, 'client');
            $reflector->setAccessible(true);
            $host = $reflector->getValue($fixture)->getConfig()['base_uri']->getHost();
            $this->assertSame('api.rightsignature.com', $host);

            $authenticatedFakeToken = $reflector->getValue($fixture)->getConfig()['headers']['Authorization:Basic '];
            $this->assertSame($fakeToken, base64_decode($authenticatedFakeToken));
        } catch (\ReflectionException $e) {
            $this->assertTrue(false);
        }
    }

    public function testBuildQuery()
    {
        try {
            $fixture = new HttpClient('123456');

            $reflector = new \ReflectionMethod($fixture, 'buildQuery');
            $reflector->setAccessible(true);

            $query = $reflector->invoke($fixture, '/test');
            $this->assertSame($fixture::API_VERSION . '/test', $query);

            $fixture = new HttpClient('123456');
            $fixture->addQueryParameter('foo', 'bar');
            $query = $reflector->invoke($fixture, '/test');
            $this->assertSame($fixture::API_VERSION . '/test/?foo=bar', $query);
        } catch (\ReflectionException $e) {
            $this->assertTrue(false);
        }
    }

}
