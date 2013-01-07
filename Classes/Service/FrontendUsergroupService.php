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
 * Helper object to check if certain usergroups should be added based on 
 * the filled forms
 *
 * @package Tx_Sessionpassword
 * @subpackage Service
 */
class Tx_Sessionpassword_Service_FrontendUsergroupService extends Tx_Sv_Authbase {

	/**
	 * all valid session usergroups and their subgroups
	 * @var array
	 */
	protected $sessionUsergroups = array();


	protected function findValidSessionUsergroups() {
		$groups = array();
		$sessionHelper = t3lib_div::makeInstance('Tx_Sessionpassword_Helper_SessionHelper');
		$allSessionData = $sessionHelper->getAllSessionData();
		foreach ($allSessionData as $encryptedPassword => $data) {
			if (isset($data['usergroups'])) {
				$this->getSubGroups($data['usergroups'], '', $groups);
			}
		}
		$this->sessionUsergroups = array_unique($groups);
		return $this->sessionUsergroups;
	}

	/**
	 * Find usergroup records in the session
	 *
	 * @param	array		Data of user.
	 * @param	array		Group data array of already known groups. This is handy if you want select other related groups. Keys in this array are unique IDs of those groups.
	 * @return	mixed		Groups array, keys = uid which must be unique
	 */
	public function getGroups($user, $knownGroups) {

		$sessionHelper = t3lib_div::makeInstance('Tx_Sessionpassword_Helper_SessionHelper');
		$additionalGroups = array();
		
		if (t3lib_div::_GP('logintype') == 'logout') {
			$sessionHelper->clearSessionData();
		} else {
			$groups = $this->findValidSessionUsergroups();
			if (count($groups))	{

				$lockToDomain_SQL = ' AND (lockToDomain=\'\' OR lockToDomain IS NULL OR lockToDomain=\''.$this->authInfo['HTTP_HOST'].'\')';
				if (!$this->authInfo['showHiddenRecords']) {
					$hiddenP = 'AND hidden=0 ';
				}
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'*',
					$this->db_groups['table'],
					'deleted=0 ' . $hiddenP . ' AND uid IN (' . implode(',', $groups) . ')' . $lockToDomain_SQL
				);
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
					$additionalGroups[$row['uid']] = $row;
				}
				if ($res) {
					$GLOBALS['TYPO3_DB']->sql_free_result($res);
				}
			}
		}
		return $additionalGroups;
	}

	/**
	 * Fetches subgroups of groups. Function is called recursively for each subgroup.
	 * Function was previously copied from t3lib_userAuthGroup->fetchGroups and has been slightly modified.
	 *
	 * @param	string		Commalist of fe_groups uid numbers
	 * @param	string		List of already processed fe_groups-uids so the function will not fall into a eternal recursion.
	 * @return	array
	 * @access private
	 */
	public function getSubGroups($grList, $idList='', &$groups)	{

			// Fetching records of the groups in $grList (which are not blocked by lockedToDomain either):
		$lockToDomain_SQL = ' AND (lockToDomain=\'\' OR lockToDomain IS NULL OR lockToDomain=\''.$this->authInfo['HTTP_HOST'].'\')';
		if (!$this->authInfo['showHiddenRecords'])	$hiddenP = 'AND hidden=0 ';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,subgroup', 'fe_groups', 'deleted=0 '.$hiddenP.' AND uid IN ('.$grList.')'.$lockToDomain_SQL);

		$groupRows = array();	// Internal group record storage

			// The groups array is filled
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			if(!in_array($row['uid'], $groups))	{ $groups[] = $row['uid']; }
			$groupRows[$row['uid']] = $row;
		}

			// Traversing records in the correct order
		$include_staticArr = t3lib_div::intExplode(',', $grList);
		foreach($include_staticArr as $uid)	{	// traversing list

				// Get row:
			$row=$groupRows[$uid];
			if (is_array($row) && !t3lib_div::inList($idList,$uid))	{	// Must be an array and $uid should not be in the idList, because then it is somewhere previously in the grouplist

					// Include sub groups
				if (trim($row['subgroup']))	{
					$theList = implode(',',t3lib_div::intExplode(',',$row['subgroup']));	// Make integer list
					$this->getSubGroups($theList, $idList.','.$uid, $groups);		// Call recursively, pass along list of already processed groups so they are not recursed again.
				}
			}
		}
	}
}