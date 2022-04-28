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

namespace DMK\Mk30xLegacy\Tests\System\Routing;

use DMK\Mk30xLegacy\System\Routing\UriResult;
use DMK\Mk30xLegacy\Tests\BaseUnitTestCase;
use Psr\Http\Message\UriInterface;

/**
 * UriResult test.
 *
 * @author Michael Wagner
 */
class UriResultTest extends BaseUnitTestCase
{
    /**
     * @test
     */
    public function setUriAndGetUri()
    {
        $uri = $this->prophesize(UriInterface::class)->reveal();
        $result = new UriResult();
        $result->setUri($uri);
        $this->assertSame($uri, $result->getUri());
    }

    /**
     * @test
     */
    public function setAvailableAndIsAvailable()
    {
        $result = new UriResult();
        // initially, available is null and should be false
        $this->assertFalse($result->isAvailable());
        // test false
        $result->setAvailable(false);
        $this->assertFalse($result->isAvailable());
        // test true
        $result->setAvailable(true);
        $this->asserttrue($result->isAvailable());
    }
}
