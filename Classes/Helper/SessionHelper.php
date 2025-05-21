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

use Psr\Http\Message\ServerRequestInterface;
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
    protected string $namespace = 'tx_sessionpassword';
    protected FrontendUserAuthentication $userObj;
    protected PasswordHasher $passwordHasher;

    public function __construct(FrontendUserAuthentication $user = null)
    {
        $this->userObj = $user ?? $this->getUserObject();
        $this->passwordHasher = GeneralUtility::makeInstance(PasswordHasher::class);
    }

    /**
     * stores a certain value in the current frontend session.
     */
    public function storeInSession($key, $value = true): void
    {
        $allSessionData = $this->getAllSessionData();

        // store the value as key in an array
        $allSessionData[$key] = $value;

        // save the data in the session
        $this->userObj->setKey('ses', $this->namespace, $allSessionData);
        $this->userObj->storeSessionData();
    }

    /**
     * checks if a certain value is stored in the current frontend session.
     */
    public function isInSession(string $value): bool
    {
        $allSessionData = $this->getAllSessionData();
        return isset($allSessionData[$value]);
    }

    /**
     * returns all unlocked session passwords.
     */
    public function getAllSessionData(): array
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
    public function clearSessionData(): void
    {
        // save an empty array in the session and override everything
        $this->userObj->setKey('ses', $this->namespace, []);
        $this->userObj->storeSessionData();
    }

    /**
     * wrapper function to fetch the FrontendUserAuthentication object.
     */
    protected function getUserObject(): FrontendUserAuthentication
    {
        $user = null;
        if (($GLOBALS['TYPO3_REQUEST'] ?? null) instanceof ServerRequestInterface) {
            $user = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.user');
        }
        if ($user === null) {
            return $GLOBALS['TSFE']->fe_user;
        }
        return $user;
    }
}
