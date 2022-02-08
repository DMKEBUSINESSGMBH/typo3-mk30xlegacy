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
use DMK\Mk30xLegacy\Tests\BaseUnitTestCase;
use Throwable;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration as Typo3ExtConf;

/**
 * ExtensionConfiguration model test.
 *
 * @author Michael Wagner
 */
class ExtensionConfigurationTest extends BaseUnitTestCase
{
    private $extensionConfiguration = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extensionConfiguration = $this->prophesize(Typo3ExtConf::class);
        $this->addInstance(Typo3ExtConf::class, $this->extensionConfiguration->reveal());
    }

    /**
     * @test
     */
    public function getAttributeReturnsValue(): void
    {
        $config = new ExtensionConfiguration();
        $this->extensionConfiguration
            ->get('mk30xlegacy', 'config_path')
            ->willReturn('config_value')
            ->shouldBeCalledOnce();
        $this->assertSame('config_value', $config->getAttribute('config_path'));
    }

    /**
     * @test
     */
    public function getAttributeReturnsNull(): void
    {
        $config = new ExtensionConfiguration();
        $this->extensionConfiguration
            ->get('mk30xlegacy', 'config_path')
            ->willThrow($this->prophesize(Throwable::class)->reveal())
            ->shouldBeCalledOnce();
        $this->assertSame(null, $config->getAttribute('config_path'));
    }
}
