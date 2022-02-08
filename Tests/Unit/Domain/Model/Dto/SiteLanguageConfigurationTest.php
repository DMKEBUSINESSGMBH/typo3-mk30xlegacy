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

namespace DMK\Mk30xLegacy\Tests\Domain\Model\Dto;

use DMK\Mk30xLegacy\Domain\Model\Dto\ExtensionConfiguration;
use DMK\Mk30xLegacy\Domain\Model\Dto\SiteLanguageConfiguration;
use DMK\Mk30xLegacy\Tests\BaseUnitTestCase;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

/**
 * ExtensionConfiguration model test.
 *
 * @author Michael Wagner
 */
class SiteLanguageConfigurationTest extends BaseUnitTestCase
{
    /**
     * @test
     */
    public function getAttribute(): void
    {
        $site = $this->prophesize(SiteLanguage::class);
        $site->toArray()->willReturn([
            'mk30xlegacy_conf_path_1' => 'conf_value_1',
            'unknown_conf_path_2' => 'conf_value_2',
        ]);

        $siteConfiguration = new SiteLanguageConfiguration($site->reveal());

        $this->assertSame('conf_value_1', $siteConfiguration->getAttribute('conf_path_1'));
        $this->assertSame(null, $siteConfiguration->getAttribute('mk30xlegacy_conf_path_1'));
        $this->assertSame(null, $siteConfiguration->getAttribute('conf_path_2'));
        $this->assertSame(null, $siteConfiguration->getAttribute('unknown_conf_path_2'));
    }
}
