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
use itnovum\openITCOCKPIT\Filter\ServicegroupFilter;


/**
 * @property Service $Service
 * @property Servicegroup $Servicegroup
 * @property Host $Host
 * @property Servicetemplate $Servicetemplate
 * @property TreeComponent $Tree
 */
class ServicegroupsController extends AppController {
    public $uses = [
        'Servicegroup',
        'Container',
        'Service',
        'Servicetemplate',
        'User',
        MONITORING_OBJECTS,
        MONITORING_SERVICESTATUS,
        'Host',
    ];
    //public $layout = 'Admin.default';
    public $layout = 'angularjs';
    public $components = [
        'Paginator',
        'ListFilter.ListFilter',
        'RequestHandler',
    ];
    public $helpers = [
        'ListFilter.ListFilter',
        'Status'
    ];
    public $listFilters = [
        'index' => [
            'fields' => [
                'Container.name' => ['label' => 'Name', 'searchType' => 'wildcard'],
                'Servicegroup.description' => ['label' => 'Alias', 'searchType' => 'wildcard'],
            ],
        ],
    ];

    public function index() {
        if (!$this->isApiRequest()) {
            //Only ship template for AngularJs
            return;
        }
        $ServicegroupFilter = new ServicegroupFilter($this->request);
        $query = [
            'recursive' => -1,
            'contain' => [
                'Container',
                'Service' => [
                    'fields' => [
                        'Service.id',
                        'Service.name'
                    ],
                    'Servicetemplate' => [
                        'fields' => [
                            'Servicetemplate.name'
                        ]
                    ],
                    'Host' => [
                        'Container',
                        'fields' => [
                            'Host.id',
                            'Host.name'
                        ]
                    ]
                ],
                'Servicetemplate' => [
                    'fields' => [
                        'Servicetemplate.id',
                        'Servicetemplate.template_name',
                        'Servicetemplate.name'
                    ]
                ]
            ],
            'conditions' => $ServicegroupFilter->indexFilter(),
            'order'      => $ServicegroupFilter->getOrderForPaginator('Container.name', 'asc'),
            'limit'      => $this->Paginator->settings['limit']
        ];

        if (!$this->hasRootPrivileges) {
            $query['conditions']['Container.parent_id'] = $this->MY_RIGHTS;
        }

        if ($this->isApiRequest() && !$this->isAngularJsRequest()) {
            unset($query['limit']);
            $servicegroups = $this->Servicegroup->find('all', $query);
        } else {
            $this->Paginator->settings = $query;
            $this->Paginator->settings['page'] = $ServicegroupFilter->getPage();
            $servicegroups = $this->Paginator->paginate();
        }
        $all_servicegroups = [];
        foreach ($servicegroups as $servicegroup) {
            $servicegroup['Servicegroup']['allowEdit'] = $this->hasPermission('edit', 'servicegroups');;
            if ($this->hasRootPrivileges === false && $servicegroup['Servicegroup']['allowEdit'] === true) {
                $servicegroup['Servicegroup']['allowEdit'] = $this->allowedByContainerId($servicegroup['Container']['parent_id']);
            }
            foreach ($servicegroup['Service'] as $key => $service) {
                $servicegroup['Service'][$key]['allowEdit'] = $this->hasPermission('edit', 'services');
                $servicegroup['Service'][$key]['Host']['allowEdit'] = $this->hasPermission('edit', 'hosts');

                if ($this->hasRootPrivileges === false && $servicegroup['Service'][$key]['Host']['allowEdit'] === true) {
                    $containerIdsToCheck = Hash::extract($service, 'Service.{n}.Host.HostsToContainer.container_id');
                    $servicegroup['Service'][$key]['Host']['allowEdit'] = $this->allowedByContainerId($containerIdsToCheck);
                }
            }


            foreach ($servicegroup['Servicetemplate'] as $key => $servicetemplate) {
                $servicegroup['Servicetemplate'][$key]['allowEdit'] = $this->hasPermission('edit', 'servicetemplates');
                if ($this->hasRootPrivileges === false && $servicegroup['Servicetemplate'][$key]['allowEdit'] === true) {
                    $servicegroup['Servicetemplate'][$key]['allowEdit'] = $this->allowedByContainerId($servicetemplate['container_id']);
                }
            }
            $all_servicegroups[] = [
                'Servicegroup'      => $servicegroup['Servicegroup'],
                'Container'         => $servicegroup['Container'],
                'Service'           => $servicegroup['Service'],
                'Servicetemplate'   => $servicegroup['Servicetemplate']
            ];

        }
        $this->set(compact(['all_servicegroups']));
        $this->set('_serialize', ['all_servicegroups', 'paging']);
    }

    public function view($id = null) {
        if (!$this->isApiRequest()) {
            throw new MethodNotAllowedException();
        }
        if (!$this->Servicegroup->exists($id)) {
            throw new NotFoundException(__('Invalid Servicegroup'));
        }

        $servicegroup = $this->Servicegroup->findById($id);
        if (!$this->allowedByContainerId(Hash::extract($servicegroup, 'Container.parent_id'))) {
            $this->render403();

            return;
        }

        $this->set('servicegroup', $servicegroup);
        $this->set('_serialize', ['servicegroup']);
    }

    public function edit($id = null) {
        if (!$this->isApiRequest()) {
            //Only ship HTML template for angular
            return;
        }

        if (!$this->Servicegroup->exists($id)) {
            throw new NotFoundException(__('Invalid service group'));
        }

        $servicegroup = $this->Servicegroup->find('first', [
            'recursive'  => -1,
            'contain'    => [
                'Service' => [
                    'fields' => [
                        'Service.id',
                        'Service.name'
                    ],
                    'Host'         => [
                        'fields' => [
                            'Host.id',
                            'Host.name'
                        ]
                    ],
                    'Servicetemplate'         => [
                        'fields' => [
                            'Servicetemplate.id',
                            'Servicetemplate.name'
                        ]
                    ],
                ],
                'Servicetemplate' => [
                    'fields' => [
                        'Servicetemplate.id',
                        'Servicetemplate.name'
                    ]
                ],
                'Container'
            ],
            'conditions' => [
                'Servicegroup.id' => $id,
            ],
        ]);
        if (!$this->allowedByContainerId($servicegroup['Container']['parent_id'])) {
            $this->render403();
            return;
        }

        $ext_data_for_changelog = [];
        $containerId = $servicegroup['Container']['parent_id'];
        if ($this->request->is('post') || $this->request->is('put')) {
            $this->request->data['Servicegroup']['id'] = $id;
            if ($this->request->data('Servicegroup.Service')) {
                foreach ($this->request->data['Servicegroup']['Service'] as $service_id) {
                    $service = $this->Service->find('first', [
                        'contain'    => [
                            'Host.name',
                            'Servicetemplate.name'
                        ],
                        'fields'     => [
                            'Service.id',
                            'Service.name',
                        ],
                        'conditions' => [
                            'Service.id' => $service_id,
                        ],
                    ]);
                    $ext_data_for_changelog['Service'][] = [
                        'id'   => $service_id,
                        'name' => sprintf(
                            '%s | %s',
                            $service['Host']['name'],
                            ($service['Service']['name'])?$service['Service']['name']:$service['Servicetemplate']['name']
                        )
                    ];
                }
            }
            if ($this->request->data('Servicegroup.Servicetemplate')) {
                foreach ($this->request->data['Servicegroup']['Servicetemplate'] as $servicetemplate_id) {
                    $servicetemplate = $this->Servicetemplate->find('first', [
                        'recursive'    => -1,
                        'fields'     => [
                            'Servicetemplate.id',
                            'Servicetemplate.name',
                        ],
                        'conditions' => [
                            'Servicetemplate.id' => $servicetemplate_id,
                        ],
                    ]);
                    $ext_data_for_changelog['Servicetemplate'][] = [
                        'id'   => $servicetemplate_id,
                        'name' => $servicetemplate['Servicetemplate']['name'],
                    ];
                }
            }
        }

        if ($this->request->is('post') || $this->request->is('put')) {
            $userId = $this->Auth->user('id');
            $this->request->data['Service'] = (!empty($this->request->data('Servicegroup.Service'))) ? $this->request->data('Servicegroup.Service') : [];
            //Add container id (of the service group container itself) to the request data
            $this->request->data['Container']['id'] = $servicegroup['Servicegroup']['container_id'];
            $this->request->data['Servicetemplate'] = (!empty($this->request->data('Servicegroup.Servicetemplate'))) ? $this->request->data('Servicegroup.Servicetemplate') : [];
            if ($this->Servicegroup->saveAll($this->request->data)) {
                Cache::clear(false, 'permissions');
                $changelog_data = $this->Changelog->parseDataForChangelog(
                    $this->params['action'],
                    $this->params['controller'],
                    $this->Servicegroup->id,
                    OBJECT_SERVICEGROUP,
                    $this->request->data('Container.parent_id'),
                    $userId,
                    $this->request->data('Container.name'),
                    array_merge($this->request->data, $ext_data_for_changelog),
                    $servicegroup
                );
                if ($changelog_data) {
                    CakeLog::write('log', serialize($changelog_data));
                }
                $this->setFlash(__('<a href="/servicegroups/edit/%s">Servicegroup</a> successfully saved', $this->Servicegroup->id));
            } else {
                if ($this->request->ext == 'json') {
                    $this->serializeErrorMessage();
                    return;
                }
            }
        }
        $this->set('servicegroup', $servicegroup);
        $this->set('_serialize', ['servicegroup']);
    }

    public function add() {
        if (!$this->isApiRequest()) {
            //Only ship HTML template for angular
            return;
        }

        if ($this->request->is('post') || $this->request->is('put')) {
            $userId = $this->Auth->user('id');
            $ext_data_for_changelog = [];
            App::uses('UUID', 'Lib');
            if ($this->request->data('Servicegroup.Service')) {
                foreach ($this->request->data['Servicegroup']['Service'] as $service_id) {
                    $service = $this->Service->find('first', [
                        'contain'    => [
                            'Host.name',
                            'Servicetemplate.name'
                        ],
                        'fields'     => [
                            'Service.id',
                            'Service.name',
                        ],
                        'conditions' => [
                            'Service.id' => $service_id,
                        ],
                    ]);
                    $ext_data_for_changelog['Service'][] = [
                        'id'   => $service_id,
                        'name' => sprintf(
                            '%s | %s',
                            $service['Host']['name'],
                            ($service['Service']['name'])?$service['Service']['name']:$service['Servicetemplate']['name']
                        )
                    ];
                }
            }
            if ($this->request->data('Servicegroup.Servicetemplate')) {
                foreach ($this->request->data['Servicegroup']['Servicetemplate'] as $servicetemplate_id) {
                    $servicetemplate = $this->Servicetemplate->find('first', [
                        'recursive'    => -1,
                        'fields'     => [
                            'Servicetemplate.id',
                            'Servicetemplate.name',
                        ],
                        'conditions' => [
                            'Servicetemplate.id' => $servicetemplate_id,
                        ],
                    ]);
                    $ext_data_for_changelog['Servicetemplate'][] = [
                        'id'   => $servicetemplate_id,
                        'name' => $servicetemplate['Servicetemplate']['name'],
                    ];
                }
            }

            $this->request->data['Servicegroup']['uuid'] = UUID::v4();
            $this->request->data['Container']['containertype_id'] = CT_SERVICEGROUP;
            $this->request->data['Service'] = (!empty($this->request->data('Servicegroup.Service'))) ? $this->request->data('Servicegroup.Service') : [];
            $this->request->data['Servicetemplate'] = (!empty($this->request->data('Servicegroup.Servicetemplate'))) ? $this->request->data('Servicegroup.Servicetemplate') : [];


            if ($this->Servicegroup->saveAll($this->request->data)) {
                Cache::clear(false, 'permissions');
                $changelog_data = $this->Changelog->parseDataForChangelog(
                    $this->params['action'],
                    $this->params['controller'],
                    $this->Servicegroup->id,
                    OBJECT_SERVICEGROUP,
                    $this->request->data('Container.parent_id'),
                    $userId,
                    $this->request->data('Container.name'),
                    array_merge($this->request->data, $ext_data_for_changelog)
                );
                if ($changelog_data) {
                    CakeLog::write('log', serialize($changelog_data));
                }

                if ($this->request->ext == 'json') {
                    if ($this->isAngularJsRequest()) {
                        $this->setFlash(__('<a href="/servicegroups/edit/%s">Servicegroup</a> successfully saved', $this->Servicegroup->id));
                    }
                    $this->serializeId();
                    return;
                }
            } else {
                if ($this->request->ext == 'json') {
                    $this->serializeErrorMessage();
                    return;
                }
                $this->setFlash(__('Could not save data'), false);
            }
        }
    }


    public function loadContainers() {
        if (!$this->isAngularJsRequest()) {
            throw new MethodNotAllowedException();
        }

        if ($this->hasRootPrivileges === true) {
            $containers = $this->Tree->easyPath($this->MY_RIGHTS, OBJECT_SERVICEGROUP, [], $this->hasRootPrivileges);
        } else {
            $containers = $this->Tree->easyPath($this->getWriteContainers(), OBJECT_SERVICEGROUP, [], $this->hasRootPrivileges);
        }
        $containers = $this->Container->makeItJavaScriptAble($containers);


        $this->set('containers', $containers);
        $this->set('_serialize', ['containers']);
    }

    public function loadServices($containerId = null) {
        $this->allowOnlyAjaxRequests();

        $services = $this->Host->servicesByContainerIds([ROOT_CONTAINER, $containerId], 'list', [
            'forOptiongroup' => true,
        ]);
        $services = $this->Service->makeItJavaScriptAble($services);

        $data = ['services' => $services];
        $this->set($data);
        $this->set('_serialize', array_keys($data));
    }

    public function loadServicetemplates($containerId = null) {
        $this->allowOnlyAjaxRequests();

        $servicetemplates = $this->Servicetemplate->servicetemplatesByContainerId([ROOT_CONTAINER, $containerId], 'list');
        $servicetemplates = $this->Servicetemplate->makeItJavaScriptAble($servicetemplates);

        $data = ['servicetemplates' => $servicetemplates];
        $this->set($data);
        $this->set('_serialize', array_keys($data));
    }

    public function delete($id = null) {
        $userId = $this->Auth->user('id');
        if (!$this->request->is('post')) {
            throw new MethodNotAllowedException();
        }
        if (!$this->Servicegroup->exists($id)) {
            throw new NotFoundException(__('invalid_servicegroup'));
        }
        $container = $this->Servicegroup->findById($id);

        if (!$this->allowedByContainerId(Hash::extract($container, 'Container.parent_id'))) {
            $this->render403();

            return;
        }

        if ($this->Container->delete($container['Servicegroup']['container_id'], true)) {
            Cache::clear(false, 'permissions');
            $changelog_data = $this->Changelog->parseDataForChangelog(
                $this->params['action'],
                $this->params['controller'],
                $id,
                OBJECT_SERVICEGROUP,
                $container['Container']['parent_id'],
                $userId,
                $container['Container']['name'],
                $container
            );
            if ($changelog_data) {
                CakeLog::write('log', serialize($changelog_data));
            }
            $this->setFlash(__('Servicegroup deleted'));
            $this->redirect(['action' => 'index']);
        }

        $this->setFlash(__('could not delete servicegroup'), false);
        $this->redirect(['action' => 'index']);
    }

    public function mass_delete($id = null) {
        $userId = $this->Auth->user('id');
        foreach (func_get_args() as $servicegroupId) {
            if ($this->Servicegroup->exists($servicegroupId)) {
                $servicegroup = $this->Servicegroup->find('first', [
                    'contain' => [
                        'Container',
                        'Service',
                    ],
                    'conditions' => [
                        'Servicegroup.id' => $servicegroupId,
                    ],
                ]);
                if ($this->allowedByContainerId(Hash::extract($servicegroup, 'Container.parent_id'))) {
                    if ($this->Container->delete($servicegroup['Servicegroup']['container_id'], true)) {
                        $changelog_data = $this->Changelog->parseDataForChangelog(
                            $this->params['action'],
                            $this->params['controller'],
                            $id,
                            OBJECT_SERVICEGROUP,
                            $servicegroup['Container']['parent_id'],
                            $userId,
                            $servicegroup['Container']['name'],
                            $servicegroup
                        );
                        if ($changelog_data) {
                            CakeLog::write('log', serialize($changelog_data));
                        }
                    }
                }
            }
        }
        Cache::clear(false, 'permissions');
        $this->setFlash(__('Servicegroups deleted'));
        $this->redirect(['action' => 'index']);
    }

    public function mass_add($id = null) {
        if ($this->request->is('post') || $this->request->is('put')) {
            $targetServicegroup = $this->request->data('Servicegroup.id');
            if ($this->Servicegroup->exists($targetServicegroup)) {
                $servicegroup = $this->Servicegroup->findById($targetServicegroup);
                //Save old services from this service group
                $servicegroupMembers = [];
                foreach ($servicegroup['Service'] as $service) {
                    $servicegroupMembers[] = $service['id'];
                }
                foreach ($this->request->data('Service.id') as $service_id) {
                    $servicegroupMembers[] = $service_id;
                }
                $servicegroup['Service'] = $servicegroupMembers;
                $servicegroup['Servicegroup']['Service'] = $servicegroupMembers;
                if ($this->Servicegroup->saveAll($servicegroup)) {
                    Cache::clear(false, 'permissions');
                    $this->setFlash(_('Servicegroup appended successfully'));
                    $this->redirect(['action' => 'index']);
                } else {
                    $this->setFlash(_('Could not append Servicegroup'), false);
                }
            } else {
                $this->setFlash('Servicegroup not found', false);
            }
        }

        $servicesToAppend = [];
        foreach (func_get_args() as $service_id) {
            $service = $this->Service->findById($service_id);
            $servicesToAppend[] = $service;
        }
        $containerIds = $this->Tree->resolveChildrenOfContainerIds($this->MY_RIGHTS);
        $servicegroups = $this->Servicegroup->servicegroupsByContainerId($containerIds, 'list');

        $this->set(compact(['servicesToAppend', 'servicegroups']));
        $this->set('back_url', $this->referer());
    }

    public function listToPdf() {
        $this->layout = 'Admin.default';

        $ServicegroupFilter = new ServicegroupFilter($this->request);
        $query = [
            'recursive'  => -1,
            'contain'    => [
                'Container',
                'Service' => [
                    'fields' => [
                        'Service.id',
                        'Service.name',
                        'Service.uuid'
                    ],
                    'Host' => [
                        'fields' => [
                            'Host.id',
                            'Host.name'
                        ],
                    ],
                    'Servicetemplate' => [
                        'fields' => [
                            'Servicetemplate.name'
                        ],
                    ]
                ],
                'Servicetemplate'

            ],
            'order'      => $ServicegroupFilter->getOrderForPaginator('Container.name', 'asc'),
            'conditions' => $ServicegroupFilter->indexFilter(),
        ];



        if (!$this->hasRootPrivileges) {
            $query['conditions']['Container.parent_id'] = $this->MY_RIGHTS;
        }
        $servicegroups = $this->Servicegroup->find('all', $query);
        $servicegroupCount = count($servicegroups);
        $serviceUuids = Hash::extract($servicegroups, '{n}.Service.{n}.uuid');
        $servicegroupstatus = $this->Servicestatus->byUuids(array_unique($serviceUuids));
        $hostsArray = [];
        $serviceCount = 0;

        foreach($servicegroups as $servicegroup){
            foreach($servicegroup['Service'] as $service){
                $serviceCount++;
                $hostsArray[$service['Host']['id']] = $service['Host']['name'];
            }
        }
        $hostCount = sizeof($hostsArray);
        $this->set(compact('servicegroups', 'servicegroupstatus', 'servicegroupCount', 'hostCount', 'serviceCount'));

        $filename = 'Servicegroups_' . strtotime('now') . '.pdf';
        $binary_path = '/usr/bin/wkhtmltopdf';
        if (file_exists('/usr/local/bin/wkhtmltopdf')) {
            $binary_path = '/usr/local/bin/wkhtmltopdf';
        }
        $this->pdfConfig = [
            'engine' => 'CakePdf.WkHtmlToPdf',
            'margin' => [
                'bottom' => 15,
                'left' => 0,
                'right' => 0,
                'top' => 15,
            ],
            'encoding' => 'UTF-8',
            'download' => true,
            'binary' => $binary_path,
            'orientation' => 'portrait',
            'filename' => $filename,
            'no-pdf-compression' => '*',
            'image-dpi' => '900',
            'background' => true,
            'no-background' => false,
        ];
    }

    public function loadServicegroupsByContainerId() {
        if (!$this->isApiRequest()) {
            //Only ship template for AngularJs
            return;
        }

        $containerId = $this->request->query('containerId');
        $selected = $this->request->query('selected');
        $ServicegroupFilter = new ServicegroupFilter($this->request);

        $containerIds = [ROOT_CONTAINER, $containerId];
        if ($containerId == ROOT_CONTAINER) {
            $containerIds = $this->Tree->resolveChildrenOfContainerIds(ROOT_CONTAINER, true);
        }

        $query = [
            'recursive' => -1,
            'contain' => [
                'Container'
            ],
            'order' => $ServicegroupFilter->getOrderForPaginator('Container.name', 'asc'),
            'conditions' => $ServicegroupFilter->indexFilter(),
            'limit' => $this->Paginator->settings['limit']
        ];

        if ($this->isApiRequest() && !$this->isAngularJsRequest()) {
            unset($query['limit']);
            $servicegroups = $this->Servicegroup->find('all', $query);
        } else {
            $this->Paginator->settings = $query;
            $this->Paginator->settings['page'] = $ServicegroupFilter->getPage();
            $servicegroups = $this->Paginator->paginate();
        }

        $this->set(compact(['servicegroups']));
        $this->set('_serialize', ['servicegroups']);
    }
}
