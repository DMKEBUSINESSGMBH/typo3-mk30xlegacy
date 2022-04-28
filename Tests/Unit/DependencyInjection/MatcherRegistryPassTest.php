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

namespace DMK\Mk30xLegacy\Tests\DependencyInjection;

use DMK\Mk30xLegacy\DependencyInjection\MatcherRegistryPass;
use DMK\Mk30xLegacy\Middleware\RedirectMiddleware;
use DMK\Mk30xLegacy\System\Routing\Matcher\MatcherRegistry;
use DMK\Mk30xLegacy\Tests\BaseUnitTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * RedirectMiddleware test.
 *
 * @author Michael Wagner
 */
class MatcherRegistryPassTest extends BaseUnitTestCase
{
    private ?MatcherRegistryPass $pass = null;
    /**
     * @var ObjectProphecy|ContainerBuilder|null
     */
    private $containerBuilder = null;
    /**
     * @var ObjectProphecy|Definition|null
     */
    private $definition = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pass = new MatcherRegistryPass();
        $this->definition = $this->prophesize(Definition::class);
        $this->containerBuilder = $this->prophesize(ContainerBuilder::class);
        $this->containerBuilder->findDefinition(MatcherRegistry::class)->willReturn($this->definition->reveal());
    }

    /**
     * @test
     */
    public function process()
    {
        $taggedServices = [
            'LegacyUriMatcher' => [['priority' => 200]],
            'SuffixRemovalMatcher' => [['priority' => 100]],
        ];

        $this->containerBuilder->has(MatcherRegistry::class)->willReturn(true);
        $this->containerBuilder->findDefinition(MatcherRegistry::class)->willReturn($this->definition->reveal());
        $this->containerBuilder->findTaggedServiceIds('mk30xlegacy.routing.matcher')->willReturn($taggedServices);

        $this->definition->addMethodCall(
            'addMatcher',
            [
                new Reference('LegacyUriMatcher'),
                200,
            ]
        )->shouldBeCalledOnce();
        $this->definition->addMethodCall(
            'addMatcher',
            [
                new Reference('SuffixRemovalMatcher'),
                100,
            ]
        )->shouldBeCalledOnce();

        $this->pass->process($this->containerBuilder->reveal());
    }

    /**
     * @test
     */
    public function processWithoutRegistry()
    {
        $this->containerBuilder->has(MatcherRegistry::class)->willReturn(false);
        $this->containerBuilder->findDefinition()->shouldNotBeCalled();
        $this->pass->process($this->containerBuilder->reveal());
    }
}
