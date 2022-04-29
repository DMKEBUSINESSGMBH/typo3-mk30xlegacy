<?php

declare(strict_types=1);

/*
 * Copyright notice
 *
 * (c) DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This file is part of the "mk30xlegacy" Extension for TYPO3 CMS.
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GNU Lesser General Public License can be found at
 * www.gnu.org/licenses/lgpl.html
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

namespace DMK\Mk30xLegacy\Tests\System\Http;

use DMK\Mk30xLegacy\System\Http\RequestFactory;
use DMK\Mk30xLegacy\Tests\BaseUnitTestCase;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\RequestFactory as Typo3RequestFactory;

/**
 * RequestFactory test.
 *
 * @author Michael Wagner
 */
class RequestFactoryTest extends BaseUnitTestCase
{
    private ?RequestFactory $factory = null;
    private $subFactory = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subFactory = $this->prophesize(Typo3RequestFactory::class);
        $this->factory = new RequestFactory($this->subFactory->reveal());
    }

    /**
     * @test
     */
    public function createRequest()
    {
        $request = $this->prophesize(RequestInterface::class)->reveal();
        $this->subFactory->createRequest('HEAD', 'https://foo.bar/baz')->willReturn($request)->shouldBeCalledOnce();
        $this->assertSame($request, $this->factory->createRequest('HEAD', 'https://foo.bar/baz'));
    }

    /**
     * @test
     */
    public function requestAvailability()
    {
        $response = $this->prophesize(ResponseInterface::class)->reveal();
        $this->subFactory->request(
            'https://foo.bar/baz',
            'HEAD',
            [
                'headers' => [
                    'X-Mk30xLegacy-Request' => '1',
                ],
            ]
        )->willReturn($response)->shouldBeCalledOnce();
        $this->assertSame($response, $this->factory->requestAvailability(new Uri('https://foo.bar/baz')));
    }

    /**
     * @test
     */
    public function isAvailabilityRequestReturnsTrue()
    {
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->hasHeader('x-mk30xlegacy-request')->willReturn(true)->shouldBeCalledOnce();
        $this->assertTrue($this->factory->isAvailabilityRequest($request->reveal()));
    }

    /**
     * @test
     */
    public function isAvailabilityRequestReturnsFalse()
    {
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->hasHeader('x-mk30xlegacy-request')->willReturn(false)->shouldBeCalledOnce();
        $this->assertFalse($this->factory->isAvailabilityRequest($request->reveal()));
    }
}
