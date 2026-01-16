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
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * The application logic for the Password Form
 * allows to enter a password that is stored in the session.
 */
class PasswordController extends ActionController
{
    public function __construct(private readonly PasswordHasher $passwordHasher) {}

    /**
     * Displays a form to enter a certain password and save the valid password
     * in the session.
     *    case 1: no entered password & needed password is in session => don't show anything
     *    case 2: no entered password & needed password is not in session => show the form
     *    case 3: wrong entered password => show the form plus a message
     *    case 4: valid entered password => store in session and check for a redirect
     *
     * @param ?string $password the entered password
     * @param ?string $referer URL to redirect to. Takes pre
     */
    public function unlockAction(?string $password = null, ?string $referer = null): ResponseInterface
    {

        $neededPassword = $this->settings['password'] ?? null;
        if ($neededPassword === null) {
            throw new \InvalidArgumentException('password setting is required.', 1747826469);
        }
        $enteredPassword = $password;

        /** @var FrontendUserAuthentication $frontendUserAuthentication */
        $frontendUserAuthentication = $this->request->getAttribute('frontend.user');
        $sessionHelper = GeneralUtility::makeInstance(SessionHelper::class, $frontendUserAuthentication);

        $this->view->assign('referer', $referer);

        // case 1 and 2: no entered password
        if ($enteredPassword === null) {
            // case 1: needed password is in session => don't show anything as everything is done already
            if ($sessionHelper->isInSession($neededPassword)) {
                $this->view->assign('loggedIn', true);
            }
            // case 2: needed password is not in session
            // => show the form without any message
        } elseif (!$this->passwordHasher->checkPassword($enteredPassword, $neededPassword)) {
            // case 3: wrong entered password => show the form plus a message
            $this->view->assign('wrongPasswordEntered', true);
        } else {
            // case 4: valid entered password => store in session and check for a redirect

            // check if we need to add usergroups
            if ($this->settings['sessionUsergroups'] ?? null) {
                $sessionHelper->storeInSession($neededPassword, ['usergroups' => $this->settings['sessionUsergroups']]);
            } else {
                $sessionHelper->storeInSession($neededPassword);
            }
            // make sure the groups get initialized again, (done via the FrontendUsergroupService)
            // so if the redirect page is a protected page, you can
            // @todo: maybe we need to do the storeInSession in an earlier phase.
            $frontendUserAuthentication->fetchGroupData($this->request);
            if ($referer !== null && $referer !== '') {
                return new RedirectResponse($referer);
            }
            $contentObjectRenderer = $this->request->getAttribute('currentContentObject');
            if (!empty($this->settings['redirectPage'] ?? null)) {
                $url = $contentObjectRenderer->typoLink_URL([
                    'parameter' => $this->settings['redirectPage'],
                    'linkAccessRestrictedPages' => 1,
                ]);
                return new RedirectResponse($url);
            }

            $url = $contentObjectRenderer->typoLink_URL([
                'parameter' => $this->getCurrentPageId(),
                'linkAccessRestrictedPages' => 1,
            ]);
            return new RedirectResponse($url);
        }
        return new HtmlResponse($this->view->render());
    }

    protected function getCurrentPageId(): int
    {
        // use frontend.page.information attribute when v12 is dropped
        /** @var TypoScriptFrontendController $frontendController */
        $frontendController = $this->request->getAttribute('frontend.controller');
        return $frontendController->id;
    }
}
