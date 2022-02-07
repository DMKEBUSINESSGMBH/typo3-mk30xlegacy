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

namespace DMK\Mk30xLegacy\Domain\Model\Dto;

use TYPO3\CMS\Core\Site\Entity\Site;

/**
 * Site Configuration  model.
 *
 * @author Michael Wagner
 */
class SiteConfiguration implements ConfigurationInterface
{
    /**
     * @var array<string, string>
     */
    private array $data;

    public function __construct(Site $site)
    {
        $this->data = array_filter(
            $site->getConfiguration(),
            fn (string $key) => 0 === strpos($key, 'mk30xlegacy_', 0),
            ARRAY_FILTER_USE_KEY
        );
    }

    public function getAttribute(string $path): ?string
    {
        return $this->data['mk30xlegacy_'.$path] ?? null;
    }
}
