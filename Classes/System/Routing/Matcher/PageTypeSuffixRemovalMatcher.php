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

use DMK\Mk30xLegacy\System\Routing\UriResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Routing\Aspect\SiteAccessorTrait;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Routing\SiteRouteResult;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @author Michael Wagner
 */
class PageTypeSuffixRemovalMatcher extends AbstractMatcher implements MatcherInterface
{
    use SiteAccessorTrait;

    public function isMatchableResponse(
        ResponseInterface $response,
        ServerRequestInterface $request
    ): bool {
        if ($request->getUri() === $this->getRequestUriWithoutSuffix($request->getUri())) {
            return false;
        }

        return parent::isMatchableResponse($response, $request);
    }

    protected function createResultForRequest(ServerRequestInterface $request, ResponseInterface $response): UriResult
    {
        $result = new UriResult();
        $result->setUri($this->getRequestUriWithoutSuffix($request->getUri()));

        if ($this->checkSiteAvailability($request->withUri($result->getUri()))) {
            $result->setAvailable(true);
        }

        return $result;
    }

    private function getRequestUriWithoutSuffix(UriInterface $uri): UriInterface
    {
        $suffixes = GeneralUtility::trimExplode(
            ',',
            $this->getConfiguration()->getSuffixRemovalSuffixes(),
            true
        );
        if (empty($suffixes)) {
            return $uri;
        }

        $pattern = '(.*)\.('.implode('|', $suffixes).')';
        $matches = [];
        if (1 !== preg_match('#^'.$pattern.'$#', $uri->getPath(), $matches)) {
            return $uri;
        }

        return $uri->withPath($matches[1]);
    }

    protected function checkSiteAvailability(
        ServerRequestInterface $request
    ): bool {
        $routeResult = $this->getSiteMatcher()->matchRequest($request);
        if (!$routeResult instanceof SiteRouteResult) {
            return false;
        }

        $site = $routeResult->getSite();
        // no site matched? skip internal availability check
        if (!$site instanceof Site) {
            return false;
        }

        $request = $request->withAttribute('site', $site);
        $request = $request->withAttribute('language', $routeResult->getLanguage());
        $request = $request->withAttribute('routing', $routeResult);

        try {
            $pageArguments = $site->getRouter()->matchRequest(
                $request,
                $routeResult
            );
        } catch (\Throwable $error) {
            return false;
        }

        if (
            $pageArguments instanceof PageArguments
            && $pageArguments->getPageId()
            && !$pageArguments->areDirty()
        ) {
            return true;
        }

        return false;
    }
}
