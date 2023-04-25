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

namespace DMK\Mk30xLegacy\Tests\System\Routing\Matcher;

use DMK\Mk30xLegacy\Domain\Manager\ConfigurationManager;
use DMK\Mk30xLegacy\System\Event\UriMatchPreAvailabilityCheckEvent;
use DMK\Mk30xLegacy\System\Http\RequestFactory;
use DMK\Mk30xLegacy\System\Routing\Matcher\LegacyUriMatcher;
use DMK\Mk30xLegacy\Tests\BaseUnitTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\Uri;

/**
 * LegacyUriMatcher test.
 *
 * @author Michael Wagner
 */
class LegacyUriMatcherTest extends BaseUnitTestCase
{
    private ?LegacyUriMatcher $matcher = null;
    /**
     * @var ConfigurationManager|ObjectProphecy|null
     */
    private ?ObjectProphecy $configuration = null;
    /**
     * @var ObjectProphecy|RequestFactory|null
     */
    private ?ObjectProphecy $requestFactory = null;
    /**
     * @var ObjectProphecy|EventDispatcherInterface|null
     */
    private ?ObjectProphecy $eventDispatcher = null;
    /**
     * @var ObjectProphecy|ServerRequestInterface|null
     */
    private ?ObjectProphecy $request = null;
    /**
     * @var ObjectProphecy|ResponseInterface|null
     */
    private ?ObjectProphecy $response = null;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->configuration = $this->prophesize(ConfigurationManager::class);
        $this->requestFactory = $this->prophesize(RequestFactory::class);
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->matcher = new LegacyUriMatcher(
            $this->configuration->reveal(),
            $this->requestFactory->reveal(),
            $this->eventDispatcher->reveal()
        );

        $this->request = $this->prophesize(ServerRequestInterface::class);
        $this->request->getUri()->willReturn(new Uri('https://relaunch.dev/foo.html?bar=baz'));
        $this->response = $this->prophesize(ResponseInterface::class);
        $this->eventDispatcher->dispatch(Argument::type(UriMatchPreAvailabilityCheckEvent::class))->willReturnArgument();
    }

    /**
     * @test
     */
    public function isMatchableResponseForMissingDomainConfig()
    {
        $this->configuration->getRedirectDomain()->willReturn('')->shouldBeCalledOnce();
        $this->assertFalse(
            $this->matcher->isMatchableResponse($this->response->reveal(), $this->request->reveal())
        );
    }

    /**
     * @test
     */
    public function isMatchableResponseForWrongStatusCode()
    {
        $this->configuration->getRedirectDomain()->willReturn('foo.bar')->shouldBeCalledOnce();
        $this->configuration->getResponseMatchPattern()->willReturn('404')->shouldBeCalledOnce();
        $this->response->getStatusCode()->willReturn(200)->shouldBeCalledOnce();
        $this->assertFalse(
            $this->matcher->isMatchableResponse($this->response->reveal(), $this->request->reveal())
        );
    }

    /**
     * @test
     */
    public function isMatchableResponseForCorrectStatusCode()
    {
        $this->configuration->getRedirectDomain()->willReturn('foo.bar')->shouldBeCalledOnce();
        $this->configuration->getResponseMatchPattern()->willReturn('404')->shouldBeCalledOnce();
        $this->response->getStatusCode()->willReturn(404)->shouldBeCalledOnce();
        $this->assertTrue(
            $this->matcher->isMatchableResponse($this->response->reveal(), $this->request->reveal())
        );
    }

    /**
     * @test
     */
    public function matchRequestForSameDomain()
    {
        $this->configuration->getRedirectDomain()->willReturn('relaunch.dev');
        $this->requestFactory->requestAvailability()->shouldNotBeCalled();

        $result = $this->matcher->matchRequest($this->request->reveal(), $this->response->reveal());

        $this->assertFalse($result->isAvailable());
    }

    /**
     * @test
     */
    public function matchRequestForUnavailable()
    {
        $this->configuration->getRedirectDomain()->willReturn('old.dev');
        $this->requestFactory->requestAvailability(new Uri('https://old.dev/foo.html?bar=baz'))
            ->willThrow($this->prophesize(\Throwable::class)->reveal())->shouldBeCalledOnce();
        $this->configuration->getRedirectDomainAvailabilityMatchPattern()->shouldNotBeCalled();

        $result = $this->matcher->matchRequest($this->request->reveal(), $this->response->reveal());

        $this->assertFalse($result->isAvailable());
        $this->assertSame('https://old.dev/foo.html?bar=baz', (string) $result->getUri());
    }

    /**
     * @test
     */
    public function matchRequestForAvailableButUnmatchedStatusPattern()
    {
        $this->configuration->getRedirectDomain()->willReturn('old.dev');
        $subResponse = $this->prophesize(ResponseInterface::class);
        $subResponse->getStatusCode()->willReturn(404)->shouldBeCalledOnce();
        $this->requestFactory->requestAvailability(new Uri('https://old.dev/foo.html?bar=baz'))
            ->willReturn($subResponse->reveal())->shouldBeCalledOnce();
        $this->configuration->getRedirectDomainAvailabilityMatchPattern()->willReturn('200')->shouldBeCalledOnce();

        $result = $this->matcher->matchRequest($this->request->reveal(), $this->response->reveal());

        $this->assertFalse($result->isAvailable());
        $this->assertSame('https://old.dev/foo.html?bar=baz', (string) $result->getUri());
    }

    /**
     * @test
     */
    public function matchRequestForAvailableWithMatchedStatusPattern()
    {
        $this->configuration->getRedirectDomain()->willReturn('old.dev');
        $subResponse = $this->prophesize(ResponseInterface::class);
        $subResponse->getStatusCode()->willReturn(200)->shouldBeCalledOnce();
        $this->requestFactory->requestAvailability(new Uri('https://old.dev/foo.html?bar=baz'))
            ->willReturn($subResponse->reveal())->shouldBeCalledOnce();
        $this->configuration->getRedirectDomainAvailabilityMatchPattern()->willReturn('200')->shouldBeCalledOnce();

        $result = $this->matcher->matchRequest($this->request->reveal(), $this->response->reveal());

        $this->assertTrue($result->isAvailable());
        $this->assertSame('https://old.dev/foo.html?bar=baz', (string) $result->getUri());
    }
}
