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

namespace DMK\Mk30xLegacy\Tests\Domain\Manager;

use DMK\Mk30xLegacy\Domain\Manager\ConfigurationManager;
use DMK\Mk30xLegacy\Domain\Model\Dto\ExtensionConfiguration;
use DMK\Mk30xLegacy\Tests\BaseUnitTestCase;

/**
 * ConfigurationManager test.
 *
 * @author Michael Wagner
 */
class ConfigurationManagerTest extends BaseUnitTestCase
{
    private ?ConfigurationManager $manager = null;

    /**
     * @var ExtensionConfiguration|\Prophecy\Prophecy\ObjectProphecy|null
     */
    private ?\Prophecy\Prophecy\ObjectProphecy $extensionConfiguration = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extensionConfiguration = $this->prophesize(ExtensionConfiguration::class);
        $this->addInstance(ExtensionConfiguration::class, $this->extensionConfiguration->reveal());

        $this->manager = new ConfigurationManager();
    }

    /**
     * @test
     */
    public function isEnabledReturnsTrue(): void
    {
        $this->extensionConfiguration->getAttribute('enabled')->willReturn('1')->shouldBeCalledOnce();
        $this->assertTrue($this->manager->isEnabled());
    }

    /**
     * @test
     */
    public function isEnabledReturnsFalse(): void
    {
        $this->extensionConfiguration->getAttribute('enabled')->willReturn('0')->shouldBeCalledOnce();
        $this->assertfalse($this->manager->isEnabled());
    }

    /**
     * @test
     */
    public function isEnabledReturnsFalseByDefault(): void
    {
        $this->extensionConfiguration->getAttribute('enabled')->willReturn(null)->shouldBeCalledOnce();
        $this->assertfalse($this->manager->isEnabled());
    }

    /**
     * @test
     */
    public function getResponseMatchPattern(): void
    {
        $this->extensionConfiguration->getAttribute('responseMatchPattern')->willReturn('[45]\d\d')->shouldBeCalledOnce();
        $this->assertSame('[45]\d\d', $this->manager->getResponseMatchPattern());
    }

    /**
     * @test
     */
    public function getResponseMatchPatternReturnsDefault(): void
    {
        $this->extensionConfiguration->getAttribute('responseMatchPattern')->willReturn(null)->shouldBeCalledOnce();
        $this->assertSame('[345]\d\d', $this->manager->getResponseMatchPattern());
    }

    /**
     * @test
     */
    public function getSuffixRemovalSuffixes(): void
    {
        $this->extensionConfiguration->getAttribute('suffixRemovalSuffixes')->willReturn('html')->shouldBeCalledOnce();
        $this->assertSame('html', $this->manager->getSuffixRemovalSuffixes());
    }

    /**
     * @test
     */
    public function getSuffixRemovalSuffixesReturnsDefault(): void
    {
        $this->extensionConfiguration->getAttribute('suffixRemovalSuffixes')->willReturn(null)->shouldBeCalledOnce();
        $this->assertSame('html,htm', $this->manager->getSuffixRemovalSuffixes());
    }

    /**
     * @test
     */
    public function getRedirectDomain(): void
    {
        $this->extensionConfiguration->getAttribute('redirectDomain')->willReturn('foo.bar')->shouldBeCalledOnce();
        $this->assertSame('foo.bar', $this->manager->getRedirectDomain());
    }

    /**
     * @test
     */
    public function getRedirectDomainReturnsDefault(): void
    {
        $this->extensionConfiguration->getAttribute('redirectDomain')->willReturn(null)->shouldBeCalledOnce();
        $this->assertSame('', $this->manager->getRedirectDomain());
    }

    /**
     * @test
     */
    public function getRedirectDomainAvailabilityMatchPattern(): void
    {
        $this->extensionConfiguration->getAttribute('redirectDomainAvailabilityMatchPattern')->willReturn('[12]\d\d')->shouldBeCalledOnce();
        $this->assertSame('[12]\d\d', $this->manager->getRedirectDomainAvailabilityMatchPattern());
    }

    /**
     * @test
     */
    public function getRedirectDomainAvailabilityMatchPatternReturnsDefault(): void
    {
        $this->extensionConfiguration->getAttribute('redirectDomainAvailabilityMatchPattern')->willReturn(null)->shouldBeCalledOnce();
        $this->assertSame('2\d\d', $this->manager->getRedirectDomainAvailabilityMatchPattern());
    }

    /**
     * @test
     */
    public function getRedirectResponseStatusCode(): void
    {
        $this->extensionConfiguration->getAttribute('redirectResponseStatusCode')->willReturn(308)->shouldBeCalledOnce();
        $this->assertSame(308, $this->manager->getRedirectResponseStatusCode());
    }

    /**
     * @test
     */
    public function getRedirectResponseStatusCodeReturnsDefault(): void
    {
        $this->extensionConfiguration->getAttribute('redirectResponseStatusCode')->willReturn(null)->shouldBeCalledOnce();
        $this->assertSame(307, $this->manager->getRedirectResponseStatusCode());
    }
}
