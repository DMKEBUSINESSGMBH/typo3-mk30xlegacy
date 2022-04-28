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

namespace DMK\Mk30xLegacy\Tests\Middleware;

use DMK\Mk30xLegacy\Domain\Manager\ConfigurationManager;
use DMK\Mk30xLegacy\Middleware\RedirectMiddleware;
use DMK\Mk30xLegacy\System\Routing\Matcher\MatcherRegistry;
use DMK\Mk30xLegacy\System\Routing\UriResult;
use DMK\Mk30xLegacy\Tests\BaseUnitTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

/**
 * RedirectMiddleware test.
 *
 * @author Michael Wagner
 */
class RedirectMiddlewareTest extends BaseUnitTestCase
{
    private ?RedirectMiddleware $middleware = null;
    /**
     * @var MatcherRegistry|ObjectProphecy|null
     */
    private ?ObjectProphecy $matcher = null;
    /**
     * @var ObjectProphecy|ConfigurationManager|null
     */
    private ?ObjectProphecy $configuration = null;
    /**
     * @var ObjectProphecy|LoggerInterface|null
     */
    private ?ObjectProphecy $logger = null;
    /**
     * @var ObjectProphecy|ServerRequestInterface|null
     */
    private ?ObjectProphecy $request = null;
    /**
     * @var ObjectProphecy|ResponseInterface|null
     */
    private ?ObjectProphecy $response = null;
    /**
     * @var ObjectProphecy|RequestHandlerInterface|null
     */
    private ?ObjectProphecy $handler = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->matcher = $this->prophesize(MatcherRegistry::class);
        $this->configuration = $this->prophesize(ConfigurationManager::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->middleware = new RedirectMiddleware(
            $this->matcher->reveal(),
            $this->configuration->reveal()
        );
        $this->middleware->setLogger($this->logger->reveal());

        $this->request = $this->prophesize(ServerRequestInterface::class);
        $this->response = $this->prophesize(ResponseInterface::class);
        $this->handler = $this->prophesize(RequestHandlerInterface::class);
        $this->handler->handle($this->request->reveal())->willReturn($this->response->reveal())->shouldBeCalledOnce();

        $site = $this->prophesize(Site::class)->reveal();
        $this->request->getAttribute('site')->willReturn($site)->shouldBeCalledOnce();
        $this->configuration->addSiteConfiguration($site)->shouldBeCalledOnce();

        $siteLanguage = $this->prophesize(SiteLanguage::class)->reveal();
        $this->request->getAttribute('language')->willReturn($siteLanguage)->shouldBeCalledOnce();
        $this->configuration->addSiteLanguageConfiguration($siteLanguage)->shouldBeCalledOnce();
    }

    /**
     * @test
     */
    public function processWithDisabledConfig()
    {
        $this->configuration->isEnabled()->willReturn(false)->shouldBeCalledOnce();
        $this->matcher->matchRequest()->shouldNotBeCalled();
        $this->assertSame(
            $this->response->reveal(),
            $this->middleware->process($this->request->reveal(), $this->handler->reveal())
        );
    }

    /**
     * @test
     */
    public function processWithUnmatchedResponse()
    {
        $this->configuration->isEnabled()->willReturn(true)->shouldBeCalledOnce();
        $this->matcher->isMatchableResponse($this->response->reveal())->willReturn(false)->shouldBeCalledOnce();
        $this->matcher->matchRequest()->shouldNotBeCalled();
        $this->assertSame(
            $this->response->reveal(),
            $this->middleware->process($this->request->reveal(), $this->handler->reveal())
        );
    }

    /**
     * @test
     */
    public function processWithMatchedButUnavailableResponse()
    {
        $result = new UriResult();
        $result->setAvailable(false);

        $this->request->getUri()->willReturn(new Uri('/bar.baz'))->shouldBeCalledOnce();
        $this->response->getStatusCode()->willReturn(815)->shouldBeCalledOnce();

        $this->configuration->isEnabled()->willReturn(true)->shouldBeCalledOnce();
        $this->matcher->isMatchableResponse($this->response->reveal())->willReturn(true)->shouldBeCalledOnce();
        $this->matcher->matchRequest($this->request->reveal(), $this->response->reveal())->willReturn($result)->shouldBeCalledOnce();
        $this->configuration->getRedirectResponseStatusCode()->shouldNotBeCalled();
        $this->logger->debug(
            'No available legacy found for matchable legacy redirect response.',
            [
                'request_uri' => '/bar.baz',
                'response_status' => 815,
                'legacy_redirect_available' => false,
            ]
        )->shouldBeCalledOnce();

        $this->assertSame(
            $this->response->reveal(),
            $this->middleware->process($this->request->reveal(), $this->handler->reveal())
        );
    }

    /**
     * @test
     */
    public function processWithMatchedAndAvailableResponse()
    {
        $result = new UriResult();
        $result->setAvailable(true);
        $result->setUri(new Uri('/foo.bar'));

        $this->request->getUri()->willReturn(new Uri('/bar.baz'))->shouldBeCalledOnce();
        $this->response->getStatusCode()->willReturn(815)->shouldBeCalledOnce();

        $this->configuration->isEnabled()->willReturn(true)->shouldBeCalledOnce();
        $this->matcher->isMatchableResponse($this->response->reveal())->willReturn(true)->shouldBeCalledOnce();
        $this->matcher->matchRequest($this->request->reveal(), $this->response->reveal())->willReturn($result)->shouldBeCalledOnce();
        $this->configuration->getRedirectResponseStatusCode()->willReturn(204)->shouldBeCalledOnce();
        $this->logger->info(
            'Available and matchable legacy redirect response found.',
            [
                'request_uri' => '/bar.baz',
                'response_status' => 815,
                'legacy_redirect_uri' => '/foo.bar',
                'legacy_redirect_available' => true,
            ]
        )->shouldBeCalledOnce();

        $response = $this->middleware->process($this->request->reveal(), $this->handler->reveal());
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(['/foo.bar'], $response->getHeader('location'));
        $this->assertEquals(['DMK.Mk30xLegacy.Middleware.Redirect'], $response->getHeader('X-Redirect-By'));
        $this->assertEquals(204, $response->getStatusCode());
    }
}
