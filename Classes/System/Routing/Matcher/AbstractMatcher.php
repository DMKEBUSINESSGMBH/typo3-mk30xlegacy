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

namespace DMK\Mk30xLegacy\System\Routing\Matcher;

use DMK\Mk30xLegacy\Domain\Manager\ConfigurationManager;
use DMK\Mk30xLegacy\System\Event\UriMatchPreAvailabilityCheckEvent;
use DMK\Mk30xLegacy\System\Routing\UriResult;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Throwable;
use TYPO3\CMS\Core\Http\RequestFactory;

/**
 * @author Michael Wagner
 */
abstract class AbstractMatcher implements MatcherInterface
{
    private ConfigurationManager $configuration;
    private RequestFactory $requestFactory;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        ConfigurationManager $configuration,
        RequestFactory $requestFactory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->configuration = $configuration;
        $this->requestFactory = $requestFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getConfiguration(): ConfigurationManager
    {
        return $this->configuration;
    }

    public function isMatchableResponse(
        ResponseInterface $response,
        ServerRequestInterface $request
    ): bool {
        $pattern = $this->getConfiguration()->getResponseMatchPattern();

        return 1 === preg_match(
            '#^'.$pattern.'$#',
            (string) $response->getStatusCode()
        );
    }

    public function matchRequest(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): UriResult {
        $result = $this->createResultForRequest($request, $response);

        if (null === $result) {
            $result = new UriResult();
            $result->setAvailable(false);

            return $result;
        }

        $this->eventDispatcher->dispatch(
            new UriMatchPreAvailabilityCheckEvent($result)
        );

        // we need to check availability if not performed already
        if (!$result->hasAvailability()) {
            $result->setAvailable($this->checkLegacyAvailability($result->getUri()));
        }

        return $result;
    }

    /**
     * Abstract method to match the request and create the uri result.
     * The uri result has a uri to be set or availability set to false!
     */
    abstract protected function createResultForRequest(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ?UriResult;

    protected function checkLegacyAvailability(
        UriInterface $uri
    ): bool {
        try {
            $response = $this->requestFactory->request((string) $uri, 'HEAD');
        } catch (Throwable $error) {
            return false;
        }
        $pattern = $this->getConfiguration()->getRedirectDomainAvailabilityMatchPattern();

        return 1 === preg_match(
            '#^'.$pattern.'$#',
            (string) $response->getStatusCode()
        );
    }
}
