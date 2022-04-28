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
use DMK\Mk30xLegacy\System\Event\LegacyUriMatchPreAvailabilityCheckEvent;
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
class LegacyUriMatcher implements MatcherInterface
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

    public function isMatchableResponse(ResponseInterface $response): bool
    {
        $legacyDomain = $this->configuration->getRedirectDomain();

        if (empty($legacyDomain)) {
            return false;
        }

        $pattern = $this->configuration->getResponseMatchPattern();

        return 1 === preg_match(
            '#^'.$pattern.'$#',
            (string) $response->getStatusCode()
        );
    }

    public function matchRequest(ServerRequestInterface $request, ResponseInterface $response): UriResult
    {
        $result = new UriResult();
        $result->setUri($request->getUri());

        $legacyDomain = $this->configuration->getRedirectDomain();

        // legacy domain has to be different from current domain!
        if ($legacyDomain === $result->getUri()->getHost()) {
            return $result;
        }

        $result->setUri($result->getUri()->withHost($legacyDomain));

        $this->eventDispatcher->dispatch(
            new LegacyUriMatchPreAvailabilityCheckEvent($result)
        );

        if (!$result->hasAvailability()) {
            $result->setAvailable($this->checkLegacyAvailability($result->getUri()));
        }

        return $result;
    }

    private function checkLegacyAvailability(
        UriInterface $uri
    ): bool {
        try {
            $response = $this->requestFactory->request((string) $uri, 'HEAD');
        } catch (Throwable $error) {
            return false;
        }
        $pattern = $this->configuration->getRedirectDomainAvailabilityMatchPattern();

        return 1 === preg_match(
            '#^'.$pattern.'$#',
            (string) $response->getStatusCode()
        );
    }
}
