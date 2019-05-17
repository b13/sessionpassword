<?php
defined('TYPO3_MODE') or die();

// add the additional field to tt_content
$GLOBALS['TCA']['tt_content']['columns'] += [
    'tx_sessionpassword' => [
        'label' => 'LLL:EXT:sessionpassword/Resources/Private/Language/db.xlf:tt_content.tx_sessionpassword',
        'exclude' => true,
        'config' => [
            'type' => 'input',
            'size' => 20,
            'max' => 255,
            'eval' => 'trim',
        ],
    ],
];


\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin('sessionpassword', 'Password', 'Session Password Form');

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['sessionpassword_password'] = 'layout,select_key,pages,recursive';

// activate flexforms
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['sessionpassword_password'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'sessionpassword_password',
    'FILE:EXT:sessionpassword/Configuration/FlexForms/unlock.xml'
);
