<?php

declare(strict_types=1);

defined('TYPO3') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'sessionpassword',
    'Password',
    'Session Password Form',
    'tx-sessionpassword',
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', '--div--;Configuration,pi_flexform,', 'sessionpassword_password', 'after:subheader');

if ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() < 14) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue('*', 'FILE:EXT:sessionpassword/Configuration/FlexForms/unlock_password.xml', 'sessionpassword_password');
} else {
    $GLOBALS['TCA']['tt_content']['types']['sessionpassword_password']['columnsOverrides']['pi_flexform']['config']['ds'] = 'FILE:EXT:sessionpassword/Configuration/FlexForms/unlock_password.xml';
}
