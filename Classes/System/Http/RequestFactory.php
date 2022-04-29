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

namespace DMK\Mk30xLegacy\System\Http;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Http\RequestFactory as Typo3RequestFactory;

/**
 * @author Michael Wagner
 *
 * Returns PSR-7 Request objects.
 */
class RequestFactory implements RequestFactoryInterface
{
    private Typo3RequestFactory $requestFactory;

    public function __construct(
        Typo3RequestFactory $requestFactory
    ) {
        $this->requestFactory = $requestFactory;
    }

    public function createRequest(string $method, $uri): RequestInterface
    {
        return $this->requestFactory->createRequest($method, $uri);
    }

    public function requestAvailability(
        UriInterface $uri
    ): ResponseInterface {
        return $this->requestFactory->request(
            (string) $uri,
            'HEAD',
            [
                'headers' => [
                    'X-Mk30xLegacy-Request' => '1',
                ],
            ]
        );
    }

    public function isAvailabilityRequest(ServerRequestInterface $request): bool
    {
        return $request->hasHeader('x-mk30xlegacy-request');
    }
}
