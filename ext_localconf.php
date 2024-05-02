<?php

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

// hook to see if the content object is allowed to render anything at all,
// based on the data inside the cObj
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass']['sessionpassword'] = [
    'sessionpassword',    // key for the array
    \B13\Sessionpassword\Service\ContentObjectService::class,    // class to instantiate
];

