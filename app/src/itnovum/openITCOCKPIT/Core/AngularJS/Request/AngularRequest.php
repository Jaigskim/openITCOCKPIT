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

namespace itnovum\openITCOCKPIT\Core\AngularJS\Request;

use itnovum\openITCOCKPIT\Core\ValueObjects\HostStates;
use itnovum\openITCOCKPIT\Core\ValueObjects\ServiceStates;
use itnovum\openITCOCKPIT\Core\ValueObjects\StateTypes;
use NotImplementedException;

class AngularRequest {

    /**
     * @var \CakeRequest
     */
    private $Request;


    /**
     * @var array
     */
    protected $filters = [
        'action' => [
            'like' => [
                'Model.field'
            ]
        ]
    ];

    /**
     * @var string
     */
    protected $HostStateField = 'Hoststatus.current_state';

    /**
     * @var string
     */
    protected $HostStateTypeField = 'Hoststatus.state_type';

    /**
     * @var string
     */
    protected $ServiceStateField = 'Servicestatus.current_state';

    /**
     * @var string
     */
    protected $ServiceStateTypeField = 'Servicestatus.state_type';

    /**
     * AngularRequest constructor.
     * @param \CakeRequest $Request
     */
    public function __construct(\CakeRequest $Request) {
        $this->Request = $Request;
    }

    /**
     * @return ServiceStates
     */
    public function getServiceStates(){
        $field = $this->ServiceStateField;
        $ServiceStates = new ServiceStates();

        if($this->queryHasField($field)){
            $requestValues = $this->getQueryFieldValue($field);
            foreach($ServiceStates->getAvailableStateIds() as $stateName => $stateId){
                if(in_array($stateName, $requestValues, true)){
                    $ServiceStates->setState($stateId, true);
                }
            }
        }

        return $ServiceStates;
    }

    /**
     * @return StateTypes
     */
    public function getServiceStateTypes(){
        $field = $this->ServiceStateTypeField;
        $ServiceStateTypes = new StateTypes();

        if($this->queryHasField($field)){
            $requestValues = $this->getQueryFieldValue($field);
            if($requestValues !== '') {
                $ServiceStateTypes->setStateType((int)$requestValues, true);
            }
        }

        return $ServiceStateTypes;
    }

    /**
     * @return HostStates
     */
    public function getHostStates(){
        $field = $this->HostStateField;
        $HostStates = new HostStates();

        if($this->queryHasField($field)){
            $requestValues = $this->getQueryFieldValue($field);
            foreach($HostStates->getAvailableStateIds() as $stateName => $stateId){
                if(in_array($stateName, $requestValues, true)){
                    $HostStates->setState($stateId, true);
                }
            }
        }

        return $HostStates;
    }

    /**
     * @return StateTypes
     */
    public function getHostStateTypes(){
        $field = $this->HostStateTypeField;
        $HostStateTypes = new StateTypes();

        if($this->queryHasField($field)){
            $requestValues = $this->getQueryFieldValue($field);
            if($requestValues !== '') {
                $HostStateTypes->setStateType((int)$requestValues, true);
            }
        }

        return $HostStateTypes;
    }

    /**
     * @return false|float|int
     */
    public function getFrom(){
        if($this->queryHasField('from')){
            $value = strtotime($this->getQueryFieldValue('from'));
            if($value){
                return $value;
            }
        }
        return time() - (3600 * 24 * 30);
    }

    /**
     * @return false|float|int
     */
    public function getTo(){
        if($this->queryHasField('to')){
            $value = strtotime($this->getQueryFieldValue('to'));
            if($value){
                return $value;
            }
        }
        return time() + (3600 * 24 * 30 * 2);
    }


    /**
     * @return \CakeRequest
     */
    public function getRequest(){
        return $this->Request;
    }


    /**
     * @param $field
     * @return bool
     */
    public function queryHasField($field) {
        return isset($this->Request->query['filter'][$field]);
    }

    /**
     * @param $field
     * @return null|mixed
     */
    public function getQueryFieldValue($field, $strict = false) {
        if ($this->queryHasField($field)) {
            if ($strict === false) {
                return $this->Request->query['filter'][$field];
            }

            if ($strict === true) {
                $value = $this->Request->query['filter'][$field];
                if (is_array($value)) {
                    $value = array_filter($value, function ($val) {
                        if ($val === null || $val === '') {
                            return false;
                        }
                        return true;
                    });
                }
                if (!empty($value)) {
                    return $value;
                }
            }

        }
        return null;
    }


    /**
     * @param string $default
     * @return string
     */
    public function getSort($default = '') {
        if (isset($this->Request->query['sort'])) {
            return $this->Request->query['sort'];
        }
        return $default;
    }

    /**
     * @param string $default
     * @return string
     */
    public function getDirection($default = '') {
        if (isset($this->Request->query['direction'])) {
            return $this->Request->query['direction'];
        }
        return $default;
    }

    /**
     * @param string $sort
     * @param string $direction
     * @return array
     */
    public function getOrderForPaginator($sort = '', $direction = '') {
        return [
            $this->getSort($sort) => $this->getDirection($direction)
        ];
    }

    /**
     * @param int $default
     * @return int
     */
    public function getPage($default = 1) {
        if (isset($this->Request->query['page'])) {
            return (int)$this->Request->query['page'];
        }
        return $default;
    }

}

