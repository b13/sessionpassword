<?php
declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
    'tx-sessionpassword' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:sessionpassword/Resources/Public/Icons/Extension.svg',
    ],
];
