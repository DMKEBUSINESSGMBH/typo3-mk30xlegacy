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

use DMK\Mk30xLegacy\System\Http\RequestFactory;
use DMK\Mk30xLegacy\System\Routing\UriResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Michael Wagner
 */
class MatcherRegistry implements MatcherInterface
{
    /**
     * @var array<int, array<int, MatcherInterface>>
     */
    private array $matcher = [];

    private RequestFactory $requestFactory;

    public function __construct(
        RequestFactory $requestFactory
    ) {
        $this->requestFactory = $requestFactory;
    }

    public function addMatcher(MatcherInterface $matcher, int $priority = 0): void
    {
        if (!array_key_exists($priority, $this->matcher)) {
            $this->matcher[$priority] = [];
        }

        $this->matcher[$priority][] = $matcher;
    }

    public function isMatchableResponse(
        ResponseInterface $response,
        ServerRequestInterface $request
    ): bool {
        if ($this->requestFactory->isAvailabilityRequest($request)) {
            return false;
        }

        foreach ($this->getFlattenMatchers() as $matcher) {
            if ($matcher->isMatchableResponse($response, $request)) {
                return true;
            }
        }

        return false;
    }

    public function matchRequest(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): UriResult {
        foreach ($this->getFlattenMatchers() as $matcher) {
            if (!$matcher->isMatchableResponse($response, $request)) {
                continue;
            }
            $result = $matcher->matchRequest($request, $response);

            // return first available result
            if ($result->isAvailable()) {
                return $result;
            }
        }

        return new UriResult();
    }

    /**
     * @return \Generator<MatcherInterface>
     */
    private function getFlattenMatchers(): \Generator
    {
        ksort($this->matcher);

        foreach (array_values($this->matcher) as $objects) {
            yield from $objects;
        }
    }
}
