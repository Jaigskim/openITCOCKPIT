<?php
// Copyright (C) <2018>  <it-novum GmbH>
//
// This file is dual licensed
//
// 1.
//  This program is free software: you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation, version 3 of the License.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
// 2.
//  If you purchased an openITCOCKPIT Enterprise Edition you can use this file
//  under the terms of the openITCOCKPIT Enterprise Edition license agreement.
//  License agreement and license key will be shipped with the order
//  confirmation.

namespace itnovum\openITCOCKPIT\Core\Permissions;


use itnovum\openITCOCKPIT\Core\HostSharingPermissions;

class HostContainersPermissions {

    /**
     * @var int
     */
    private $primaryContainerId;

    /**
     * @var array
     */
    private $sharedContainerIds = [];

    /**
     * @var array
     */
    private $MY_WRITABLE_CONTAINERS = [];

    /**
     * @var bool
     */
    private $hasRootPrivileges = false;

    /**
     * HostContainers constructor.
     * @param int $primaryContainerId
     * @param array|int $sharedContainerIds
     * @param array|int $MY_WRITABLE_CONTAINERS
     * @param bool $hasRootPrivileges
     */
    public function __construct($primaryContainerId, $sharedContainerIds, $MY_WRITABLE_CONTAINERS = [], $hasRootPrivileges = false) {
        $this->primaryContainerId = (int)$primaryContainerId;
        $this->sharedContainerIds = $this->castToIntArray($sharedContainerIds);
        $this->MY_WRITABLE_CONTAINERS = $this->castToIntArray($MY_WRITABLE_CONTAINERS);
        $this->hasRootPrivileges = $hasRootPrivileges;
    }

    /**
     * @return bool
     */
    public function isPrimaryContainerChangeable() {
        if ($this->primaryContainerId === ROOT_CONTAINER) {
            return false;
        }

        if ($this->hasRootPrivileges === true && $this->primaryContainerId !== ROOT_CONTAINER) {
            return true;
        }

        if ($this->hasRootPrivileges === false && isset($this->MY_WRITABLE_CONTAINERS[$this->primaryContainerId])) {
            return true;
        }

        return false;
    }

    /**
     * @param array|int $MY_RIGHTS
     * @param int $hostTypeId
     * @return bool
     */
    public function allowSharing($MY_RIGHTS, $hostTypeId) {
        $MY_RIGHTS = $this->castToIntArray($MY_RIGHTS);
        $hostTypeId = (int)$hostTypeId;

        if ($hostTypeId !== GENERIC_HOST) {
            return false;
        }

        $HostSharingPermissions = new HostSharingPermissions(
            $this->primaryContainerId,
            $this->hasRootPrivileges,
            $this->sharedContainerIds,
            $MY_RIGHTS
        );

        return $HostSharingPermissions->allowSharing();
    }


    /**
     * @param array|int $arr
     * @return array
     */
    private function castToIntArray($arr) {
        if (!is_array($arr)) {
            $arr = [$arr];
        }

        $intArr = [];
        foreach ($arr as $item) {
            $item = (int)$item;
            $intArr[$item] = $item;
        }
        return $intArr;
    }

}
