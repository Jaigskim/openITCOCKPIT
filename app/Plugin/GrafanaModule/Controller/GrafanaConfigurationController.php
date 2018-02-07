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


use GuzzleHttp\Client;
use itnovum\openITCOCKPIT\Grafana\GrafanaApiConfiguration;

class GrafanaConfigurationController extends GrafanaModuleAppController {

    public $layout = 'angularjs';

    public $uses = [
        'Hostgroup',
        'Container',
        'GrafanaModule.GrafanaConfiguration',
        'GrafanaModule.GrafanaConfigurationHostgroupMembership',
        'Proxy'
    ];

    public $components = [
        'CustomValidationErrors'
    ];

    public function index() {
        //$this->request->data = Hash::merge($grafanaConfiguration, $this->request->data);

        //Save POST||PUT Request
        if ($this->request->is('post') || $this->request->is('put')) {
            $_hostgroups = (is_array($this->request->data('GrafanaConfiguration.Hostgroup'))) ? $this->request->data('GrafanaConfiguration.Hostgroup') : [];
            $_hostgroups_excluded = (is_array($this->request->data('GrafanaConfiguration.Hostgroup_excluded'))) ? $this->request->data('GrafanaConfiguration.Hostgroup_excluded') : [];

            $this->GrafanaConfiguration->set($this->request->data);
            if ($this->GrafanaConfiguration->validates()) {
                $this->request->data['GrafanaConfiguration']['id'] = 1;
                $this->request->data['GrafanaConfigurationHostgroupMembership'] = $this->GrafanaConfiguration->parseHostgroupMembershipData(
                    $_hostgroups,
                    $_hostgroups_excluded
                );

                /* Delete old hostgroup associations */
                $this->GrafanaConfigurationHostgroupMembership->deleteAll(true);

                if ($this->GrafanaConfiguration->saveAll($this->request->data)) {

                    if ($this->isAngularJsRequest()) {
                        $this->setFlash(__('Grafana configuration successfully saved'));
                    }
                    $this->serializeId();
                    return;
                }
            }else{
                $this->serializeErrorMessage();
                return;
            }
        }

        //Ship data for GET requests
        $hostgroups = $this->Hostgroup->findList([
            'recursive'  => -1,
            'contain'    => [
                'Container'
            ],
            'order'      => [
                'Container.name' => 'asc',
            ],
            'conditions' => [
                'Container.parent_id' => $this->MY_RIGHTS,
            ],
        ]);
        $customFieldsToRefill = [
            'GrafanaConfiguration' => [
                'use_https',
                'ignore_ssl_certificate',
                'use_proxy'
            ]
        ];
        $this->CustomValidationErrors->checkForRefill($customFieldsToRefill);

        $grafanaConfiguration = $this->GrafanaConfiguration->find('first', [
            'recursive' => -1,
            'contain'   => [
                'GrafanaConfigurationHostgroupMembership'
            ]
        ]);

        if (empty($grafanaConfiguration)) {
            //Default GrafanaConfiguration
            $grafanaConfiguration = [
                'GrafanaConfiguration' => [
                    'id'                     => 1, //its 1 every time
                    'api_url'                => '',
                    'api_key'                => '',
                    'graphite_prefix'        => '',
                    'use_https'              => '1',
                    'use_proxy'              => '0',
                    'ignore_ssl_certificate' => '0',
                    'dashboard_style'        => 'light',
                    'Hostgroup'              => [],
                    'Hostgroup_excluded'     => []
                ]
            ];
        }

        if (!empty($grafanaConfiguration['GrafanaConfigurationHostgroupMembership'])) {
            $grafanaConfiguration['GrafanaConfiguration']['Hostgroup'] = Hash::combine($grafanaConfiguration['GrafanaConfigurationHostgroupMembership'], '{n}[excluded=0].hostgroup_id', '{n}[excluded=0].hostgroup_id');
            $grafanaConfiguration['GrafanaConfiguration']['Hostgroup_excluded'] = Hash::combine($grafanaConfiguration['GrafanaConfigurationHostgroupMembership'], '{n}[excluded=1].hostgroup_id', '{n}[excluded=1].hostgroup_id');
        }

        $this->set('grafanaConfiguration', $grafanaConfiguration);
        $this->set('_serialize', ['grafanaConfiguration']);
    }

    public function loadHostgroups() {
        if (!$this->isApiRequest()) {
            //Only ship template for AngularJs
            return;
        }

        $hostgroups = $this->Hostgroup->findList([
            'recursive'  => -1,
            'contain'    => [
                'Container'
            ],
            'order'      => [
                'Container.name' => 'asc',
            ],
            'conditions' => [
                'Container.parent_id' => $this->MY_RIGHTS,
            ],
        ]);

        $hostgroups = $this->Container->makeItJavaScriptAble($hostgroups);

        $this->set('hostgroups', $hostgroups);
        $this->set('_serialize', ['hostgroups']);
    }

    public function testGrafanaConnection() {
        //$this->autoRender = false;
        //$this->allowOnlyAjaxRequests();
        if (!$this->isApiRequest()) {
            //Only ship template for AngularJs
            return;
        }

        if ($this->request->is('post') || $this->request->is('put')) {
            $config = $this->request->data;
            /*$this->set('config', $config);
            $this->set('_serialize', ['config']); */
            //$config = json_decode($config);

            $GrafanaApiConfiguration = GrafanaApiConfiguration::fromArray($config);

            $client = $this->GrafanaConfiguration->testConnection($GrafanaApiConfiguration, $this->Proxy->getSettings());
            if ($client instanceof Client) {
                $status = ['status' => true];
            } else {
                $client = (json_decode($client)) ? json_decode($client) : ['message' => $client];
                $status = [
                    'status' => false,
                    'msg'    => $client
                ];
            }


            $this->set('status', $status);
            $this->set('_serialize', ['status']);
        }
    }
}
