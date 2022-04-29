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

namespace DMK\Mk30xLegacy\Middleware;

use DMK\Mk30xLegacy\Domain\Manager\ConfigurationManager;
use DMK\Mk30xLegacy\System\Routing\Matcher\MatcherRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

/**
 * Redirect middleware.
 *
 * @author Michael Wagner
 */
class RedirectMiddleware implements MiddlewareInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private MatcherRegistry $matcher;
    private ConfigurationManager $configuration;

    public function __construct(
        MatcherRegistry $matcher,
        ConfigurationManager $configuration
    ) {
        $this->matcher = $matcher;
        $this->configuration = $configuration;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $site = $request->getAttribute('site');
        if ($site instanceof Site) {
            $this->configuration->addSiteConfiguration($site);
        }
        $language = $request->getAttribute('language');
        if ($language instanceof SiteLanguage) {
            $this->configuration->addSiteLanguageConfiguration($language);
        }

        if (!$this->configuration->isEnabled()) {
            return $response;
        }

        if (!$this->matcher->isMatchableResponse($response, $request)) {
            return $response;
        }

        $result = $this->matcher->matchRequest($request, $response);
        if ($result->isAvailable()) {
            if (null !== $this->logger) {
                $this->logger->info(
                    'Available and matchable legacy redirect response found.',
                    [
                        'request_uri' => (string) $request->getUri(),
                        'response_status' => $response->getStatusCode(),
                        'legacy_redirect_uri' => (string) $result->getUri(),
                        'legacy_redirect_available' => true,
                    ]
                );
            }

            return new RedirectResponse(
                $result->getUri(),
                $this->configuration->getRedirectResponseStatusCode(),
                ['X-Redirect-By' => 'DMK.Mk30xLegacy.Middleware.Redirect']
            );
        }

        if (null !== $this->logger) {
            $this->logger->debug(
                'No available legacy found for matchable legacy redirect response.',
                [
                    'request_uri' => (string) $request->getUri(),
                    'response_status' => $response->getStatusCode(),
                    'legacy_redirect_available' => false,
                ]
            );
        }

        return $response;
    }
}
