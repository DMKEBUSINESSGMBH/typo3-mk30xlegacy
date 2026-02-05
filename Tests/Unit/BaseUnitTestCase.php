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

namespace DMK\Mk30xLegacy\Tests;

use Pimple\Container as PimpleContainer;
use Pimple\Psr11\Container as PimplePsr11Container;
use Prophecy\PhpUnit\ProphecyTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * BaseUnitTestCase class.
 *
 * @author Michael Wagner
 *
 * @SuppressWarnings(PHPMD.Superglobals)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class BaseUnitTestCase extends UnitTestCase
{
    use ProphecyTrait;

    protected ?PimpleContainer $container = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpContainer();

        //        // inject content type repository and manager
        //        $this->addInstance(
        //            Repository::class,
        //            new ContentTypeRepository()
        //        );
        //        $this->addInstance(
        //            Manager::class,
        //            fn (PimpleContainer $container) => new Manager(
        //                $container->offsetGet(Repository::class)
        //            )
        //        );
    }

    /**
     * Create a simple container and inject it for testing as typo3 di container.
     */
    protected function setUpContainer(): void
    {
        $this->container = new PimpleContainer();
        GeneralUtility::setContainer(new PimplePsr11Container($this->container));
    }

    /**
     * Add an instance to the testing typo3 di container.
     *
     * @param class-string    $className
     * @param object|\Closure $instanceOrFactory
     */
    protected function addInstance(string $className, $instanceOrFactory): void
    {
        if (!$instanceOrFactory instanceof \Closure) {
            $instanceOrFactory = fn () => $instanceOrFactory;
        }

        $this->container[$className] = $instanceOrFactory;
    }
}
