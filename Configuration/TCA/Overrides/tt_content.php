<?php

declare(strict_types=1);

defined('TYPO3') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'sessionpassword',
    'Password',
    'Session Password Form',
    'tx-sessionpassword',
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['sessionpassword_password'] = 'layout,select_key,pages,recursive';
// activate flexforms
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['sessionpassword_password'] = 'pi_flexform';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'sessionpassword_password',
    'FILE:EXT:sessionpassword/Configuration/FlexForms/unlock_password.xml'
);
