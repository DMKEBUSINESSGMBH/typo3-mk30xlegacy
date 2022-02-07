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

$GLOBALS['SiteConfiguration']['site']['columns']['mk30xlegacy_enabled'] = [
    'label' => 'Enable Mk 30x Legacy Redirects for this site',
    'description' => 'Enables the legacy redirect middleware.',
    'onChange' => 'reload',
    'config' => [
        'type' => 'check',
        'renderType' => 'checkboxToggle',
        'default' => 1,
        'items' => [
            [
                0 => '',
                1 => '',
            ],
        ],
    ],
];

$GLOBALS['SiteConfiguration']['site']['columns']['mk30xlegacy_responseMatchPattern'] = [
    'label' => 'TYPO3 Response Match Pattern',
    'description' => 'Regex to match with current request http response code to perform legacy redirect.',
    'config' => [
        'type' => 'input',
        'eval' => 'trim',
        'valuePicker' => [
            'items' => [
                ['300-599', '[345]\d\d'],
                ['400-599', '[45]\d\d'],
            ],
        ],
        'placeholder' => '[45]\d\d',
    ],
    'displayCond' => 'FIELD:mk30xlegacy_enabled:=:1',
];

$GLOBALS['SiteConfiguration']['site']['columns']['mk30xlegacy_redirectDomain'] = [
    'label' => 'Legacy Redirect Domain',
    'description' => 'Domain to performe the legacy redirect to.',
    'config' => [
        'type' => 'input',
        'eval' => 'trim,required',
        'placeholder' => 'legacy.domain.tld',
    ],
    'displayCond' => 'FIELD:mk30xlegacy_enabled:=:1',
];

$GLOBALS['SiteConfiguration']['site']['columns']['mk30xlegacy_redirectDomainAvailabilityMatchPattern'] = [
    'label' => 'TYPO3 Redirect Domain Availability Match Pattern',
    'description' => 'Regex to match with http response code from legacy check. On match a redirect to legacy domain will be performed.',
    'config' => [
        'type' => 'input',
        'eval' => 'trim',
        'valuePicker' => [
            'items' => [
                ['100-299', '[12]\d\d'],
                ['200-299', '[2]\d\d'],
                ['200', '200'],
            ],
        ],
        'placeholder' => '[12]\d\d',
    ],
    'displayCond' => 'FIELD:mk30xlegacy_enabled:=:1',
];

$GLOBALS['SiteConfiguration']['site']['columns']['mk30xlegacy_redirectResponseStatusCode'] = [
    'label' => 'Redirect Response HTTP-Status-Code',
    'description' => 'The HTTP-Status Code used for redirects to legacy domain.',
    'config' => [
        'type' => 'input',
        'eval' => 'trim',
        'valuePicker' => [
            'items' => [
                ['301', '301'],
                ['302', '302'],
                ['307', '307'],
                ['308', '308'],
            ],
        ],
        'placeholder' => '307',
    ],
    'displayCond' => 'FIELD:mk30xlegacy_enabled:=:1',
];

$GLOBALS['SiteConfiguration']['site']['types']['0']['showitem'] .= implode(
    ',',
    [
        ',--div--;30x Legacy Redirects',
        'mk30xlegacy_enabled',
        'mk30xlegacy_responseMatchPattern',
        'mk30xlegacy_redirectDomain',
        'mk30xlegacy_redirectDomainAvailabilityMatchPattern',
        'mk30xlegacy_redirectResponseStatusCode',
    ]
);

/*
 * Language specific redirectDomain configuration
 */
$GLOBALS['SiteConfiguration']['site_language']['columns']['mk30xlegacy_redirectDomain'] = [
    'label' => 'Legacy Redirect Domain',
    'description' => 'Domain to performe the legacy redirect to.',
    'config' => [
        'type' => 'input',
        'eval' => 'trim',
    ],
];

$GLOBALS['SiteConfiguration']['site_language']['types']['1']['showitem'] = str_replace(
    'flag',
    'flag, mk30xlegacy_redirectDomain, ',
    $GLOBALS['SiteConfiguration']['site_language']['types']['1']['showitem']
);
