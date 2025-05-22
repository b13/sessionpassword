<?php

declare(strict_types=1);

$EM_CONF[$_EXTKEY] = [
    'title' => 'Protect content by simple passwords, stored in the session.',
    'description' => 'Adds an additional field to any content element, so the content element is only shown if the password was entered in this session. An additional plugin allows the user to enter the password.',
    'category' => 'fe',
    'author' => 'b13 GmbH',
    'author_email' => 'typo3@b13.com',
    'state' => 'stable',
    'version' => '3.0.0',
    'constraints' => [
        'depends' => [
            'core' => '12.4.0-13.99.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
