<?php

declare(strict_types=1);

namespace B13\Sessionpassword\Helper;

/*
 * This file is part of TYPO3 CMS-based extension "sessionpassword" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * Helper object to access a certain password from the session
 * only worries if a certain password (hash) in the session, and how it
 * is stored in there. Hashing however is taken care outside of the session.
 */
class SessionHelper
{
    // namespace within the fe_user session object
    protected $namespace = 'tx_sessionpassword';

    /**
     * @var FrontendUserAuthentication
     */
    protected $userObj;

    /**
     * @var PasswordHasher
     */
    protected $passwordHasher;

    public function __construct(FrontendUserAuthentication $user = null)
    {
        $this->userObj = $user ?? $this->getUserObject();
        /** @var PasswordHasher $passwordHelper */
        $this->passwordHasher = GeneralUtility::makeInstance(PasswordHasher::class);
    }

    /**
     * stores a certain value in the
     * current frontend session.
     */
    public function storeInSession($key, $value = true)
    {
        $allSessionData = $this->getAllSessionData();

        $key = $this->passwordHasher->hashPassword($key);
        // store the value as key in an array
        $allSessionData[$key] = $value;

        // save the data in the session
        $this->userObj->setKey('ses', $this->namespace, $allSessionData);
        $this->userObj->storeSessionData();
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
        $value = $this->passwordHasher->ensurePasswordIsHashed($value);
        $allSessionData = $this->getAllSessionData();
        if (isset($allSessionData[$value])) {
            return true;
        }
        return false;
    }

    /**
     * returns all unlocked session passwords.
     *
     * @return array
     */
    public function getAllSessionData()
    {
        $allSessionData = $this->userObj->getKey('ses', $this->namespace);
        if (is_array($allSessionData)) {
            return $allSessionData;
        }
        return [];
    }

    /**
     * removes all stored passwords from the
     * current frontend session.
     */
    public function clearSessionData()
    {
        // save an empty array in the session and override everything
        $this->userObj->setKey('ses', $this->namespace, []);
        $this->userObj->storeSessionData();
    }

    /**
     * wrapper function to fetch the FrontendUserAuthentication object.
     *
     * @return FrontendUserAuthentication
     */
    protected function getUserObject()
    {
        return $GLOBALS['TSFE']->fe_user;
    }
}
