<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 b:dreizehn, Germany <typo3@b13.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Helper object to access a certain password from the session
 * only worries if a certain password (hash) in the session, and how it
 * is stored in there
 *
 * @package Tx_Sessionpassword
 * @subpackage Helper
 */
class Tx_Sessionpassword_Helper_SessionHelper {

	// namespace within the fe_user session object
	protected $namespace = 'tx_sessionpassword';

	/** 
	 * stores a certain value in the 
	 * current frontend session
	 */
	public function storeInSession($key, $value = TRUE) {
		$frontendUserObject = $this->getUserObject();
		$allSessionData = $this->getAllSessionData();

		// store the value as key in an array
		$allSessionData[$key] = $value;

		// save the data in the session
		$frontendUserObject->setKey('ses', $this->namespace, $allSessionData);
	}

	/**
	 * checks if a certain value is stored
	 * in the current frontend session
	 * 
	 * @param string $value the value to check for
	 * @return boolean
	 */
	public function isInSession($value) {
		$allSessionData = $this->getAllSessionData();
		if (isset($allSessionData[$value])) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * returns all unlocked session passwords
	 * 
	 * @return array
	 */
	public function getAllSessionData() {
		$frontendUserObject = $this->getUserObject();
		$allSessionData = $frontendUserObject->getKey('ses', $this->namespace);
		if (is_array($allSessionData)) {
			return $allSessionData;
		} else {
			return array();
		}
	}

	/**
	 * removes all stored passwords from the 
	 * current frontend session
	 */
	public function clearSessionData() {

		$allSessionData = array();

		// save an empty array in the session and override everything
		$frontendUserObject = $this->getUserObject();
		$frontendUserObject->setKey('ses', $this->namespace, $allSessionData);
		$frontendUserObject->storeSessionData();
	}

	/** 
	 * wrapper function to fetch the tsfe_feuserauth object
	 * @return tslib_feuserauth
	 */
	protected function getUserObject() {
		return $GLOBALS['TSFE']->fe_user;
	}
}