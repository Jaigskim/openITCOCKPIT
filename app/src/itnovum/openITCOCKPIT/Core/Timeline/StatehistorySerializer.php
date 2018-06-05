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

namespace itnovum\openITCOCKPIT\Core\Timeline;


use itnovum\openITCOCKPIT\Core\Views\Statehistory;
use itnovum\openITCOCKPIT\Core\Views\UserTime;

class StatehistorySerializer {

    /**
     * @var array
     */
    private $records;

    /**
     * @var UserTime
     */
    private $UserTime;

    /**
     * @var int
     */
    private $end;

    /**
     * @var int
     */
    private $groupId;

    /**
     * @var string
     */
    private $type;

    /**
     * StatehistorySerializer constructor.
     * @param array $statehistoryRecords
     * @param UserTime $UserTime
     * @param int $end
     * @param string $type
     */
    public function __construct($statehistoryRecords = [], UserTime $UserTime, $end = 0, $type = 'host') {
        $this->records = $statehistoryRecords;
        $this->UserTime = $UserTime;
        $this->end = $end;
        if($this->end === 0){
            $this->end = time();
        }

        $TimelineGroups = new Groups();
        $this->groupId = $TimelineGroups->getStatehistoryHostId();
        if($type === 'service'){
            $this->groupId = $TimelineGroups->getStatehistoryServiceId();
        }

        $this->type = $type;

    }

    public function serialize() {
        $records = [];
        $size = sizeof($this->records);

        for ($i = 0; $i < $size; $i++) {
            $end = $this->end;
            if (isset($this->records[$i + 1])) {
                $end = $this->records[$i + 1]->getStateTime();
            }

            $records[] = [
                'start' => $this->UserTime->customFormat('%Y-%m-%d %H:%M:%S', $this->records[$i]->getStateTime()),
                'end'   => $this->UserTime->customFormat('%Y-%m-%d %H:%M:%S', $end),
                'type' => 'background',
                'className' => $this->getStateClass($this->records[$i]),
                'group' => $this->groupId
            ];
        }

        return $records;
    }

    public function getStateClass(Statehistory $Statehistory){

        if($this->type === 'host'){

            if($Statehistory->isHardstate()){
                switch($Statehistory->getState()){
                    case 0:
                        return 'bg-color-greenDark';
                        break;

                    case 1:
                        return 'bg-color-red';
                        break;

                    case 2:
                        return 'bg-color-blueDark';
                        break;
                }
            }

            switch($Statehistory->getState()){
                case 0:
                    return 'bg-color-greenLight';
                    break;

                case 1:
                    return 'bg-color-redLight';
                    break;

                case 2:
                    return 'bg-color-blueLight';
                    break;
            }

        }

        if($this->type === 'service'){

            if($Statehistory->isHardstate()){
                switch($Statehistory->getState()){
                    case 0:
                        return 'bg-color-greenDark';
                        break;

                    case 1:
                        return 'bg-color-orangeDark';
                        break;

                    case 2:
                        return 'bg-color-red';
                        break;

                    case 3:
                        return 'bg-color-blueDark';
                        break;
                }
            }

            switch($Statehistory->getState()){
                case 0:
                    return 'bg-color-greenLight';
                    break;

                case 1:
                    return 'bg-color-orange';
                    break;

                case 2:
                    return 'bg-color-redLight';
                    break;

                case 3:
                    return 'bg-color-blueLight';
                    break;
            }

        }

        return 'bg-color-blue';

    }

}
