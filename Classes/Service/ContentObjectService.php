<?php
namespace B13\Sessionpassword\Service;

/*
 * This file is part of TYPO3 CMS-based extension "sessionpassword" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Sessionpassword\Helper\SessionHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Helper object to NOT render a cObj if the data contains the
 * tx_sessionpassword, which is not in the session.
 */
class ContentObjectService implements \TYPO3\CMS\Frontend\ContentObject\ContentObjectPostInitHookInterface
{
    /**
     * called at the end of cObj->start().
     * @param ContentObjectRenderer $parentObject
     */
    public function postProcessContentObjectInitialization(ContentObjectRenderer &$parentObject)
    {
        // check if the DB record has a tx_sessionpassword
        if ($parentObject->getCurrentTable() === 'tt_content' && !empty($parentObject->data['tx_sessionpassword'])) {
            // make the content element non-cacheable, as it is based on the session password

            $sessionHelper = GeneralUtility::makeInstance(SessionHelper::class);
            if ($sessionHelper->isInSession($this->hashifyPassword($parentObject->data['tx_sessionpassword']))) {
                // unlocked => show the content element
            } else {
                // is locked
            }
        }
    }

    /**
     * helper function to not work with the passwords
     * directly.
     *
     * @param string $string
     *
     * @return string
     */
    protected function hashifyPassword($string)
    {
        return GeneralUtility::hmac($string);
    }
}
