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
 * The application logic for the Password Form
 * allows to enter a password that is stored in the session
 *
 *
 * @package Tx_Sessionpassword
 * @subpackage Domain\Model
 * @entity
 */
class Tx_Sessionpassword_Controller_PasswordController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * Initializes the current action
	 *
	 * @return void
	 */
	public function initializeAction() {
	}

	
	/**
	 * Displays a form to enter a certain password and save the valid password
	 * in the session
	 * 
	 *	case 1: no entered password & needed password is in session => don't show anything
	 *	case 2: no entered password & needed password is not in session => show the form
	 *	case 3: wrong entered password => show the form plus a message
	 *	case 4: valid entered password => store in session and check for a redirect
	 *
	 * @param string $password the entered password
	 * @return	void	taken care by the view
	 */
	public function unlockAction($password = NULL) {
		$sessionHelper = $this->objectManager->get('Tx_Sessionpassword_Helper_SessionHelper');
		$neededPassword = $this->hashifyPassword($this->settings['password']);
		$enteredPassword = $password;

			// case 1 and 2: no entered password 
		if ($enteredPassword === NULL) {
			// case 1: needed password is in session => don't show anything as everything is done already
			if ($sessionHelper->isInSession($neededPassword)) {
				return '';
			}
			// case 2: needed password is not in session
			// => show the form without any message
		} else {
			$enteredPassword = $this->hashifyPassword($enteredPassword);

				// case 3: wrong entered password => show the form plus a message
			if ($enteredPassword !== $neededPassword) {
				$this->view->assign('wrongPasswordEntered', TRUE);
			} else {
				// case 4: valid entered password => store in session and check for a redirect
				// check if we need to add usergroups
				if ($this->settings['sessionUsergroups']) {
					$sessionHelper->storeInSession($enteredPassword, array('usergroups' => $this->settings['sessionUsergroups']));
				} else {
					$sessionHelper->storeInSession($enteredPassword);
				}
				if ($this->settings['redirectPage']) {
					$this->redirect(NULL, NULL, NULL, array(), $this->settings['redirectPage']);
				}
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
