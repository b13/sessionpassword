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
use TYPO3\CMS\Core\Authentication\AbstractAuthenticationService;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Helper object to check if certain usergroups should be added based on
 * the filled forms.
 */
class FrontendUsergroupService extends AbstractAuthenticationService
{
    /**
     * all valid session usergroups and their subgroups.
     *
     * @var array
     */
    protected $sessionUsergroups = [];

    protected function findValidSessionUsergroups()
    {
        $groups = [];
        $sessionHelper = GeneralUtility::makeInstance(SessionHelper::class, $this->pObj);
        $allSessionData = $sessionHelper->getAllSessionData();
        foreach ($allSessionData as $hashedPassword => $data) {
            if (isset($data['usergroups'])) {
                $this->getSubGroups($data['usergroups'], '', $groups);
            }
        }

        return array_unique($groups);
    }

    /**
     * Find usergroup records in the session.
     *
     * @param array $user Data of user.
     * @param array $knownGroups Group data array of already known groups. This is handy if you want select other related groups. Keys in this array are unique IDs of those groups.
     * @return array Groups array, keys = uid which must be unique
     */
    public function getGroups($user, $knownGroups)
    {
        $groups = [];

        if (GeneralUtility::_GP('logintype') === 'logout') {
            GeneralUtility::makeInstance(SessionHelper::class, $this->pObj)->clearSessionData();
        } else {
            $groups = $this->findValidSessionUsergroups();
            if (!empty($groups)) {
                $this->logger->debug('Get usergroups with id: ' . implode(',', $groups));
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                    ->getQueryBuilderForTable($this->db_groups['table']);
                if (!empty($this->authInfo['showHiddenRecords'])) {
                    $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
                }

                $res = $queryBuilder->select('*')
                    ->from($this->db_groups['table'])
                    ->where(
                        $queryBuilder->expr()->in(
                            'uid',
                            $queryBuilder->createNamedParameter($groups, Connection::PARAM_INT_ARRAY)
                        ),
                        $queryBuilder->expr()->orX(
                            $queryBuilder->expr()->eq(
                                'lockToDomain',
                                $queryBuilder->createNamedParameter('', \PDO::PARAM_STR)
                            ),
                            $queryBuilder->expr()->isNull('lockToDomain'),
                            $queryBuilder->expr()->eq(
                                'lockToDomain',
                                $queryBuilder->createNamedParameter($this->authInfo['HTTP_HOST'], \PDO::PARAM_STR)
                            )
                        )
                    )
                    ->execute();
                while ($row = $res->fetch()) {
                    $groups[$row['uid']] = $row;
                }
            }
        }

        return $groups;
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
            ->from($this->db_groups['table'])
            ->where(
                $queryBuilder->expr()->in(
                    'uid',
                    $queryBuilder->createNamedParameter(
                        GeneralUtility::intExplode(',', $grList, true),
                        Connection::PARAM_INT_ARRAY
                    )
                ),
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq(
                        'lockToDomain',
                        $queryBuilder->createNamedParameter('', \PDO::PARAM_STR)
                    ),
                    $queryBuilder->expr()->isNull('lockToDomain'),
                    $queryBuilder->expr()->eq(
                        'lockToDomain',
                        $queryBuilder->createNamedParameter($this->authInfo['HTTP_HOST'], \PDO::PARAM_STR)
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
                if (trim($row['subgroup'])) {
                    // Make integer list
                    $theList = implode(',', GeneralUtility::intExplode(',', $row['subgroup']));
                    // Call recursively, pass along list of already processed groups so they are not processed again.
                    $this->getSubGroups($theList, $idList . ',' . $uid, $groups);
                }
            }
        }
    }
}
