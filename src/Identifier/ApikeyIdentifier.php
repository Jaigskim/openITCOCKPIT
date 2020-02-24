<?php
// Copyright (C) <2015>  <it-novum GmbH>
//
// This file is dual licensed
//
// 1.
//	This program is free software: you can redistribute it and/or modify
//	it under the terms of the GNU General Public License as published by
//	the Free Software Foundation, version 3 of the License.
//
//	This program is distributed in the hope that it will be useful,
//	but WITHOUT ANY WARRANTY; without even the implied warranty of
//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//	GNU General Public License for more details.
//
//	You should have received a copy of the GNU General Public License
//	along with this program.  If not, see <http://www.gnu.org/licenses/>.
//

// 2.
//	If you purchased an openITCOCKPIT Enterprise Edition you can use this file
//	under the terms of the openITCOCKPIT Enterprise Edition license agreement.
//	License agreement and license key will be shipped with the order
//	confirmation.

namespace App\Identifier;

use App\Model\Table\UsersTable;
use Authentication\Identifier\AbstractIdentifier;
use Authentication\Identifier\IdentifierInterface;
use Cake\ORM\TableRegistry;

class ApikeyIdentifier extends AbstractIdentifier implements IdentifierInterface {

    /**
     * Identifies an user or service by the passed credentials
     *
     * @param array $credentials Authentication credentials
     * @return array|\ArrayAccess|\Cake\Datasource\EntityInterface|null
     */
    public function identify(array $credentials) {
        if (isset($credentials['apikey']) && $credentials['apikey'] !== null) {
            $identity = $this->_findIdentity($credentials['apikey']);

            return $identity;
        }

        return null;
    }

    /**
     * Find a user record using the apikey/identifier provided.
     *
     * @param string $apikey
     * @return array|\Cake\Datasource\EntityInterface|null
     */
    protected function _findIdentity(string $apikey) {
        /** @var UsersTable $UsersTable */
        $UsersTable = TableRegistry::getTableLocator()->get('Users');

        if (isset($apikey) && strlen($apikey) > 1) {
            return $UsersTable->getUserByApikeyForLogin($apikey);
        }

        return null;
    }
}
