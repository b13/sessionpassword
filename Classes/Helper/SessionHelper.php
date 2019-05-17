<?php
namespace B13\Sessionpassword\Helper;

/*
 * This file is part of TYPO3 CMS-based extension "sessionpassword" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

/**
 * Helper object to access a certain password from the session
 * only worries if a certain password (hash) in the session, and how it
 * is stored in there.
 */
class SessionHelper
{
    // namespace within the fe_user session object
    protected $namespace = 'tx_sessionpassword';

    /**
     * stores a certain value in the
     * current frontend session.
     */
    public function storeInSession($key, $value = true)
    {
        $frontendUserObject = $this->getUserObject();
        $allSessionData = $this->getAllSessionData();

        // store the value as key in an array
        $allSessionData[$key] = $value;

        // save the data in the session
        $frontendUserObject->setKey('ses', $this->namespace, $allSessionData);
        $GLOBALS['TSFE']->storeSessionData();
    }

    /**
     * checks if a certain value is stored
     * in the current frontend session.
     *
     * @param string $value the value to check for
     *
     * @return bool
     */
    public function isInSession($value)
    {
        $allSessionData = $this->getAllSessionData();
        if (isset($allSessionData[$value])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * returns all unlocked session passwords.
     *
     * @return array
     */
    public function getAllSessionData()
    {
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
     * current frontend session.
     */
    public function clearSessionData()
    {
        $allSessionData = array();

        // save an empty array in the session and override everything
        $frontendUserObject = $this->getUserObject();
        $frontendUserObject->setKey('ses', $this->namespace, $allSessionData);
        $frontendUserObject->storeSessionData();
    }

    /**
     * wrapper function to fetch the FrontendUserAuthentication object.
     *
     * @return \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication
     */
    protected function getUserObject()
    {
        if (\TYPO3\CMS\Core\Utility\GeneralUtility::_GET('eID')) {
            return \TYPO3\CMS\Frontend\Utility\EidUtility::initFeUser();
        } else {
            return $GLOBALS['TSFE']->fe_user;
        }
    }
}
