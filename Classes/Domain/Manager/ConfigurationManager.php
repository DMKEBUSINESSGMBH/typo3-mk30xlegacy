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

namespace DMK\Mk30xLegacy\Domain\Manager;

use DMK\Mk30xLegacy\Domain\Model\Dto\ConfigurationInterface;
use DMK\Mk30xLegacy\Domain\Model\Dto\ExtensionConfiguration;
use DMK\Mk30xLegacy\Domain\Model\Dto\SiteConfiguration;
use DMK\Mk30xLegacy\Domain\Model\Dto\SiteLanguageConfiguration;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Configuration manager.
 *
 * @author Michael Wagner
 */
class ConfigurationManager
{
    /**
     * @var array<int, ConfigurationInterface>
     */
    private array $configurations = [];

    public function __construct()
    {
        $this->configurations[] = GeneralUtility::makeInstance(
            ExtensionConfiguration::class
        );
    }

    public function addSiteConfiguration(Site $site): void
    {
        // we must check site config first, so put site config in front of ext config.
        array_unshift(
            $this->configurations,
            GeneralUtility::makeInstance(
                SiteConfiguration::class,
                $site
            )
        );
    }

    public function addSiteLanguageConfiguration(SiteLanguage $language): void
    {
        // we must check site language config first, so put site config in front of ext config.
        array_unshift(
            $this->configurations,
            GeneralUtility::makeInstance(
                SiteLanguageConfiguration::class,
                $language
            )
        );
    }

    private function getAttribute(string $path): ?string
    {
        foreach ($this->configurations as $configuration) {
            $attribute = $configuration->getAttribute($path);

            if (empty($attribute)) {
                continue;
            }

            return $attribute;
        }

        return null;
    }

    public function isEnabled(): bool
    {
        return 0 !== (int) ($this->getAttribute('enabled') ?? 0);
    }

    public function getResponseMatchPattern(): string
    {
        return $this->getAttribute('responseMatchPattern') ?? '[345]\d\d';
    }

    public function getRedirectDomain(): string
    {
        return $this->getAttribute('redirectDomain') ?? '';
    }

    public function getRedirectDomainAvailabilityMatchPattern(): string
    {
        return $this->getAttribute('redirectDomainAvailabilityMatchPattern') ?? '2\d\d';
    }

    public function getRedirectResponseStatusCode(): int
    {
        return (int) ($this->getAttribute('redirectResponseStatusCode') ?? 307);
    }
}
