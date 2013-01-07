<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

/**
 * Configure the Plugin to call the right combination of Controller and Action according to
 * the user input (default settings, FlexForm, URL etc.)
 */
Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,		// The extension name (in UpperCamelCase) or the extension key (in lower_underscore)
	'Password',			// A unique name of the plugin in UpperCamelCase
	array(			// An array holding the controller-action-combinations that are accessible 
					// The first controller and its first action will be the default
		'Password' => 'unlock'
	),
	array(			// An array of non-cachable controller-action-combinations (they must already be enabled)
		'Password' => 'unlock'
	)
);


// hook to see if the content object is allowed to render anything at all,
// based on the data inside the cObj
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass']['sessionpassword'] = array(
	'sessionpassword',	// key for the array
	'Tx_Sessionpassword_Service_ContentObjectService'	// class to instantiate
);



// hook in to add additional usergroups
// by registering the base authentication service
t3lib_extMgm::addService($_EXTKEY,  'auth' /* sv type */,  'tx_sessionpassword_fegroups' /* sv key */,
	array(
		'title' => 'Session password groups',
		'description' => 'Adds frontend usergroups by checking the session data for stored passwords by tx_sessionpassword.',
		'subtype' => 'getGroupsFE',
		'available' => TRUE,
		'priority' => 20,
		'quality' => 20,

		'os' => '',
		'exec' => '',

		'classFile' => t3lib_extMgm::extPath($_EXTKEY) . 'Classes/Service/FrontendUsergroupService.php',
		'className' => 'Tx_Sessionpassword_Service_FrontendUsergroupService',
	)
);


#$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['initFEuser']['tx_sessionpassword'] = 'Tx_Sessionpassword_Service_FrontendUsergroupService->checkForSessionPassword'