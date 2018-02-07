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

use itnovum\openITCOCKPIT\Core\DowntimeServiceConditions;
use itnovum\openITCOCKPIT\Core\Views\Downtime;

class DowntimeService extends NagiosModuleAppModel {
    public $useTable = 'downtimehistory';
    public $primaryKey = 'downtimehistory_id';
    public $tablePrefix = 'nagios_';
    public $recursive = 2;
    public $belongsTo = [
        'Objects' => [
            'className'  => 'NagiosModule.Objects',
            'foreignKey' => 'object_id',
        ],
    ];

    //See http://nagios.sourceforge.net/docs/ndoutils/NDOUtils_DB_Model.pdf and search for "downtimehistory Table"
    public $downtime_type = '1,2';

    public function getQuery(DowntimeServiceConditions $Conditions, $paginatorConditions) {
        $query = [
            'recursive' => -1,
            'fields'    => [
                'DowntimeService.author_name',
                'DowntimeService.comment_data',
                'DowntimeService.entry_time',
                'DowntimeService.scheduled_start_time',
                'DowntimeService.scheduled_end_time',
                'DowntimeService.duration',
                'DowntimeService.was_started',
                'DowntimeService.internal_downtime_id',
                'DowntimeService.downtimehistory_id',
                'DowntimeService.was_cancelled',
                'Host.id',
                'Host.uuid',
                'Host.name',
                'Service.id',
                'Service.uuid',
                'Service.name',
                'Service.servicetemplate_id',
                'Servicetemplate.id',
                'Servicetemplate.name',
                'HostsToContainers.container_id',
            ],
            'joins'     => [
                [
                    'table'      => 'nagios_objects',
                    'type'       => 'INNER',
                    'alias'      => 'Objects',
                    'conditions' => 'Objects.object_id = DowntimeService.object_id AND DowntimeService.downtime_type = 1' //Downtime.downtime_type = 1 Service downtime
                ],
                [
                    'table'      => 'services',
                    'type'       => 'INNER',
                    'alias'      => 'Service',
                    'conditions' => 'Service.uuid = Objects.name2',
                ],
                [
                    'table'      => 'servicetemplates',
                    'type'       => 'INNER',
                    'alias'      => 'Servicetemplate',
                    'conditions' => 'Servicetemplate.id = Service.servicetemplate_id',
                ],
                [
                    'table'      => 'hosts',
                    'type'       => 'INNER',
                    'alias'      => 'Host',
                    'conditions' => 'Host.id = Service.host_id',
                ],
                [
                    'table'      => 'hosts_to_containers',
                    'alias'      => 'HostsToContainers',
                    'type'       => 'LEFT',
                    'conditions' => [
                        'HostsToContainers.host_id = Host.id',
                    ],
                ],
            ],
            'group'      => 'DowntimeService.downtimehistory_id',
            'order'      => $Conditions->getOrder(),
            'limit'      => $Conditions->getLimit(),
            'conditions' => [
                'DowntimeService.scheduled_start_time >' => date('Y-m-d H:i:s', $Conditions->getFrom()),
                'DowntimeService.scheduled_start_time <' => date('Y-m-d H:i:s', $Conditions->getTo())
            ]
        ];

        if ($Conditions->hasContainerIds()) {
            $query['conditions']['HostsToContainers.container_id'] = $Conditions->getContainerIds();
        }

        if ($Conditions->hideExpired()) {
            $query['conditions']['DowntimeService.scheduled_end_time >'] = date('Y-m-d H:i:s', time());
        }


        //Merge ListFilter conditions
        $query['conditions'] = Hash::merge($paginatorConditions, $query['conditions']);
        return $query;
    }

    public function getServiceDowntimesByHostAndDowntime($hostId, Downtime $Downtime) {
        $query = [
            'conditions' => [
                'DowntimeService.downtime_type'        => 1,
                'Service.host_id'               => $hostId,
                'DowntimeService.scheduled_start_time' => date('Y-m-d H:i:s',$Downtime->getScheduledStartTime()),
                'DowntimeService.scheduled_end_time'   => date('Y-m-d H:i:s', $Downtime->getScheduledEndTime())
            ],
            'fields'     => [
                'DowntimeService.internal_downtime_id',
            ],
            'joins'      => [
                [
                    'table'      => 'services',
                    'type'       => 'INNER',
                    'alias'      => 'Service',
                    'conditions' => 'Service.uuid = Objects.name2',
                ]
            ]
        ];
        return $this->find('all', $query);
    }


}
