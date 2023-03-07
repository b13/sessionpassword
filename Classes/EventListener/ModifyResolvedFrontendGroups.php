<?php

declare(strict_types=1);

namespace B13\Sessionpassword\EventListener;

/*
 * This file is part of TYPO3 CMS-based extension "sessionpassword" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Sessionpassword\Helper\SessionHelper;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Authentication\ModifyResolvedFrontendGroupsEvent;

class ModifyResolvedFrontendGroups
{
    protected string $usergroupTable;
    protected LoggerInterface $logger;

    public function __invoke(ModifyResolvedFrontendGroupsEvent $event): void
    {

        $allGroups = $event->getGroups();
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        $this->usergroupTable = $event->getUser()->usergroup_table;

        $groups = [];
        if (GeneralUtility::_GP('logintype') === 'logout') {
            GeneralUtility::makeInstance(SessionHelper::class, $event->getUser())->clearSessionData();
        } else {
            $groups = $this->findValidSessionUsergroups($event->getUser());
            if (!empty($groups)) {
                $this->logger->debug('Get usergroups with id: ' . implode(',', $groups));
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                    ->getQueryBuilderForTable($this->usergroupTable);
                if (!empty($this->authInfo['showHiddenRecords'])) {
                    $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
                }

                $res = $queryBuilder->select('*')
                    ->from($this->usergroupTable)
                    ->where(
                        $queryBuilder->expr()->in(
                            'uid',
                            $queryBuilder->createNamedParameter($groups, Connection::PARAM_INT_ARRAY)
                        )
                    )
                    ->execute();
                while ($row = $res->fetch()) {
                    $allGroups[$row['uid']] = $row;
                }
            }
        }


        $event->setGroups($allGroups);
    }

    protected function findValidSessionUsergroups(FrontendUserAuthentication $frontendUserAuthentication)
    {
        $groups = [];
        $sessionHelper = GeneralUtility::makeInstance(SessionHelper::class, $frontendUserAuthentication);
        $allSessionData = $sessionHelper->getAllSessionData();
        foreach ($allSessionData as $hashedPassword => $data) {
            if (isset($data['usergroups'])) {
                $this->getSubGroups($data['usergroups'], '', $groups);
            }
        }

        return array_unique($groups);
    }

    /**
     * Fetches subgroups of groups. Function is called recursively for each subgroup.
     * Function was previously copied from
     * \TYPO3\CMS\Core\Authentication\BackendUserAuthentication->fetchGroups and has been slightly modified.
     *
     * @param string $grList Commalist of fe_groups uid numbers
     * @param string $idList List of already processed fe_groups-uids so the function will not fall into an eternal recursion.
     * @param array $groups
     * @return array
     */
    public function getSubGroups($grList, $idList, &$groups)
    {
        // Fetching records of the groups in $grList (which are not blocked by lockedToDomain either):
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('fe_groups');
        if (!empty($this->authInfo['showHiddenRecords'])) {
            $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
        }

        $res = $queryBuilder
            ->select('uid', 'subgroup')
            ->from($this->usergroupTable)
            ->where(
                $queryBuilder->expr()->in(
                    'uid',
                    $queryBuilder->createNamedParameter(
                        GeneralUtility::intExplode(',', $grList, true),
                        Connection::PARAM_INT_ARRAY
                    )
                )
            )
            ->execute();

        // Internal group record storage
        $groupRows = [];
        // The groups array is filled
        while ($row = $res->fetch()) {
            if (!in_array($row['uid'], $groups)) {
                $groups[] = $row['uid'];
            }
            $groupRows[$row['uid']] = $row;
        }
        // Traversing records in the correct order
        $include_staticArr = GeneralUtility::intExplode(',', $grList);
        // traversing list
        foreach ($include_staticArr as $uid) {
            // Get row:
            $row = $groupRows[$uid];
            // Must be an array and $uid should not be in the idList, because then it is somewhere previously in the grouplist
            if (is_array($row) && !GeneralUtility::inList($idList, $uid)) {
                // Include sub groups
                if (array_key_exists('subgroup', $row)) {
                    // Make integer list
                    $theList = implode(',', GeneralUtility::intExplode(',', $row['subgroup']));
                    // Call recursively, pass along list of already processed groups so they are not processed again.
                    $this->getSubGroups($theList, $idList . ',' . $uid, $groups);
                }
            }
        }
    }
}
