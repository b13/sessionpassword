<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

// add the additional field to tt_content
t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['columns'] += array(
	'tx_sessionpassword' => array(
		'label' => 'LLL:EXT:sessionpassword/Resources/Private/Language/db.xml:tt_content.tx_sessionpassword',
		'exclude'     => 1,
		'config' => array(
			'type' => 'input',
			'size' => 20,
			'max'  => 255,
			'eval' => 'trim'
		)
	)
);

// add the field to all tt_content CTypes
t3lib_extMgm::addToAllTCAtypes('tt_content', 'tx_sessionpassword', '', 'after:sys_language_uid');


$extensionName = t3lib_div::underscoredToUpperCamelCase($_EXTKEY);
t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Session Password');


/**
 * Registers a Plugin to be listed in the Backend.
 * You also have to configure the Dispatcher in ext_localconf.php.
 */
Tx_Extbase_Utility_Extension::registerPlugin(
	$_EXTKEY,			// The extension name (in UpperCamelCase) or the extension key (in lower_underscore)
	'Password',				// A unique name of the plugin in UpperCamelCase
	'Session Password Form'	// A title shown in the backend dropdown field
);

$pluginSignature = strtolower($extensionName) . '_password';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages,recursive';
// activate flexforms
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
t3lib_extMgm::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/unlock.xml');

