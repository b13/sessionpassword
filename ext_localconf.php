<?php

declare(strict_types=1);

defined('TYPO3') or die();

/*
 * Configure the Plugin to call the right combination of Controller and Action according to
 * the user input (default settings, FlexForm, URL etc.)
 */
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Sessionpassword',
    'Password',
    [\B13\Sessionpassword\Controller\PasswordController::class => 'unlock'],
    [\B13\Sessionpassword\Controller\PasswordController::class => 'unlock']
);

// should be changed, when v12 support is dropped
// \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
