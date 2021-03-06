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
use Prophecy\Argument;
use TYPO3\CMS\Core\Site\Entity\Site;

/**
 * ConfigurationManager test.
 *
 * @author Michael Wagner
 */
class ConfigurationManagerWithSiteTest extends BaseUnitTestCase
{
    private ?ConfigurationManager $manager = null;
    /**
     * @var ExtensionConfiguration|\Prophecy\Prophecy\ObjectProphecy|null
     */
    private $extensionConfiguration = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extensionConfiguration = $this->prophesize(ExtensionConfiguration::class);
        $this->extensionConfiguration->getAttribute(Argument::any())->shouldNotBeCalled();
        $this->addInstance(ExtensionConfiguration::class, $this->extensionConfiguration->reveal());

        $site = $this->prophesize(Site::class);
        $site->getConfiguration()->willReturn([
            'mk30xlegacy_enabled' => '1',
            'mk30xlegacy_responseMatchPattern' => '4\d\d',
            'mk30xlegacy_suffixRemovalSuffixes' => 'html',
            'mk30xlegacy_redirectDomain' => 'foo.bar.baz',
            'mk30xlegacy_redirectDomainAvailabilityMatchPattern' => '[123]\d\d',
            'mk30xlegacy_redirectResponseStatusCode' => '302',
        ]);

        $this->manager = new ConfigurationManager();
        $this->manager->addSiteConfiguration($site->reveal());
    }

    /**
     * @test
     */
    public function isEnabledReturnsTrue()
    {
        $this->assertTrue($this->manager->isEnabled());
    }

    /**
     * @test
     */
    public function getResponseMatchPattern()
    {
        $this->assertSame('4\d\d', $this->manager->getResponseMatchPattern());
    }

    /**
     * @test
     */
    public function getSuffixRemovalSuffixes()
    {
        $this->assertSame('html', $this->manager->getSuffixRemovalSuffixes());
    }

    /**
     * @test
     */
    public function getRedirectDomain()
    {
        $this->assertSame('foo.bar.baz', $this->manager->getRedirectDomain());
    }

    /**
     * @test
     */
    public function getRedirectDomainAvailabilityMatchPattern()
    {
        $this->assertSame('[123]\d\d', $this->manager->getRedirectDomainAvailabilityMatchPattern());
    }

    /**
     * @test
     */
    public function getRedirectResponseStatusCode()
    {
        $this->assertSame(302, $this->manager->getRedirectResponseStatusCode());
    }
}
