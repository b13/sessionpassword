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
 * Helper object to NOT render a cObj if the data contains the
 * tx_sessionpassword, which is not in the session
 *
 * @package Tx_Sessionpassword
 * @subpackage Service
 */
class Tx_Sessionpassword_Service_ContentObjectService implements tslib_content_PostInitHook {
	
	/**
	 * called at the end of cObj->start()
	 */
	public function postProcessContentObjectInitialization(tslib_cObj &$parentObject) {
			// check if the DB record has a tx_sessionpassword
		if ($parentObject->table == 'tt_content' && !empty($parentObject->data['tx_sessionpassword'])) {
			// make the content element non-cacheable, as it is based on the session password

			$sessionHelper = t3lib_div::makeInstance('Tx_Sessionpassword_Helper_SessionHelper');
			if ($sessionHelper->isInSession($this->hashifyPassword($parentObject->data['tx_sessionpassword']))) {
				// unlocked => show the content element
			} else {
				// is locked
			}
		}
	}


	/**
	 * helper function to not work with the passwords
	 * directly
	 * 
	 * @param string $string
	 * @return string
	 */
	protected function hashifyPassword($string) {
		return t3lib_div::hmac($string);
	}
}