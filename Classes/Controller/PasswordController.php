<?php

declare(strict_types=1);

namespace B13\Sessionpassword\Controller;

/*
 * This file is part of TYPO3 CMS-based extension "sessionpassword" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Sessionpassword\Helper\PasswordHasher;
use B13\Sessionpassword\Helper\SessionHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * The application logic for the Password Form
 * allows to enter a password that is stored in the session.
 */
class PasswordController extends ActionController
{
    /**
     * Displays a form to enter a certain password and save the valid password
     * in the session.
     *    case 1: no entered password & needed password is in session => don't show anything
     *    case 2: no entered password & needed password is not in session => show the form
     *    case 3: wrong entered password => show the form plus a message
     *    case 4: valid entered password => store in session and check for a redirect
     *
     * @param string $password the entered password
     * @param string $referer URL to redirect to. Takes pre
     */
    public function unlockAction($password = null, $referer = '')
    {
        $sessionHelper = GeneralUtility::makeInstance(SessionHelper::class);
        $passwordHelper = GeneralUtility::makeInstance(PasswordHasher::class);
        $neededPassword = $this->settings['password'];
        $enteredPassword = $password;

        $this->view->assign('data', $this->configurationManager->getContentObject()->data);
        $this->view->assign('referer', $referer);

        // case 1 and 2: no entered password
        if ($enteredPassword === null) {
            // case 1: needed password is in session => don't show anything as everything is done already
            if ($sessionHelper->isInSession($neededPassword)) {
                return '';
            }
            // case 2: needed password is not in session
            // => show the form without any message
        } elseif (!$passwordHelper->checkPassword($enteredPassword, $neededPassword)) {
            // case 3: wrong entered password => show the form plus a message
            $this->view->assign('wrongPasswordEntered', true);
        } else {
            // case 4: valid entered password => store in session and check for a redirect

            // check if we need to add usergroups
            if ($this->settings['sessionUsergroups']) {
                $sessionHelper->storeInSession($enteredPassword, ['usergroups' => $this->settings['sessionUsergroups']]);
            } else {
                $sessionHelper->storeInSession($enteredPassword);
            }
            // make sure the groups get initialized again, (done via the FrontendUsergroupService)
            // so if the redirect page is a protected page, you can
            // @todo: maybe we need to do the storeInSession in an earlier phase.
            $GLOBALS['TSFE']->initUserGroups();

            if (!empty($referer)) {
                $this->redirectToUri($referer);
            }

            if ($this->settings['redirectPage'] && $this->configurationManager->getContentObject()) {
                $url = $this->configurationManager->getContentObject()->typoLink_URL([
                    'parameter' => $this->settings['redirectPage'],
                    'linkAccessRestrictedPages' => 1
                ]);
                $this->redirectToUri($url);
            }
        }
    }
}
