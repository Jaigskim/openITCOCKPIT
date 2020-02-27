<?php
// Copyright (C) <2015>  <it-novum GmbH>
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

namespace itnovum\openITCOCKPIT\InitialDatabase;

use App\Model\Table\UsergroupsTable;

/**
 * Class Usergroup
 * @package itnovum\openITCOCKPIT\InitialDatabase
 * @property UsergroupsTable $Table
 */
class Usergroup extends Importer {

    /**
     * @return bool
     */
    public function import() {
        if ($this->isTableEmpty()) {
            $data = $this->getData();
            foreach ($data as $record) {
                $entity = $this->Table->newEmptyEntity();
                $entity = $this->patchEntityAndKeepAllIds($entity, $record);
                $this->Table->save($entity);
            }
        }

        return true;
    }

    /**
     * @return array
     */
    public function getData() {
        $data = [
            (int)0 => [
                'id'          => '1',
                'name'        => 'Administrator',
                'description' => '',
                'created'     => '2015-08-19 14:57:42',
                'modified'    => '2015-08-19 14:57:42'
            ],
            (int)1 => [
                'id'          => '2',
                'name'        => 'Viewer',
                'description' => '',
                'created'     => '2015-08-19 15:00:36',
                'modified'    => '2015-08-19 15:00:36'
            ]
        ];

        return $data;
    }
}
