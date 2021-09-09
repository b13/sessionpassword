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

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PasswordHasher
{
    /**
     * @var PasswordHashInterface
     */
    protected $hasher;

    /**
     * @var bool
     */
    protected $useHashedPasswordsInDatabase;

    public function __construct()
    {
        // needs to be "BE" as data stored inside "tt_content" is marked as "BE" (see DataHandler)
        $this->hasher = GeneralUtility::makeInstance(PasswordHashFactory::class)->getDefaultHashInstance('BE');
        $this->useHashedPasswordsInDatabase = (bool)GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('sessionpassword', 'useHashedPasswords');
    }

    public function ensurePasswordIsHashed(string $plainOrHashedPassword): string
    {
        if ($this->useHashedPasswordsInDatabase) {
            return $plainOrHashedPassword;
        }
        return $this->hashPassword($plainOrHashedPassword);
    }

    public function hashPassword(string $enteredPassword): string
    {
        return $this->hasher->getHashedPassword($enteredPassword);
    }

    public function checkPassword(string $enteredPassword, string $neededPassword): bool
    {
        $neededPassword = $this->ensurePasswordIsHashed($neededPassword);
        return $this->hasher->checkPassword($enteredPassword, $neededPassword);
    }
}
