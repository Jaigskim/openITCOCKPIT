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


use App\Model\Table\MacrosTable;

/**
 * Class Macro
 * @package itnovum\openITCOCKPIT\InitialDatabase
 * @property MacrosTable $Table
 */
class Macro extends Importer {

    /**
     * @return bool
     */
    public function import() {
        if ($this->isTableEmpty()) {
            $data = $this->getData();
            foreach ($data as $record) {
                $entity = $this->Table->newEmptyEntity();
                $entity->setAccess('id', true);
                $entity = $this->Table->patchEntity($entity, $record, [
                    //'validate' => false,
                ]);
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
                'name'        => '$USER1$',
                'value'       => '/opt/openitc/nagios/libexec',
                'description' => 'Path to monitoring plugins',
                'password'    => '0',
                'created'     => '2015-01-05 15:17:23',
                'modified'    => '2015-01-05 15:17:23'
            ]
        ];

        return $data;
    }
}
