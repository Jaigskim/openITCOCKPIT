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

use App\Model\Table\CommandargumentsTable;
use App\Model\Table\CommandsTable;
use App\Model\Table\ContactgroupsTable;
use App\Model\Table\ContactsTable;
use App\Model\Table\ContainersTable;
use App\Model\Table\DocumentationsTable;
use App\Model\Table\HostsTable;
use App\Model\Table\HosttemplatecommandargumentvaluesTable;
use App\Model\Table\HosttemplatesTable;
use App\Model\Table\TimeperiodsTable;
use Cake\ORM\TableRegistry;
use itnovum\openITCOCKPIT\Core\AngularJS\Api;
use itnovum\openITCOCKPIT\Core\KeyValueStore;
use itnovum\openITCOCKPIT\Core\Views\ContainerPermissions;
use itnovum\openITCOCKPIT\Database\PaginateOMat;
use itnovum\openITCOCKPIT\Filter\HosttemplateFilter;


/**
 * @property Hosttemplate $Hosttemplate
 * @property Timeperiod $Timeperiod
 * @property Contact $Contact
 * @property Contactgroup $Contactgroup
 * @property Container $Container
 * @property Customvariable $Customvariable
 * @property Hosttemplatecommandargumentvalue $Hosttemplatecommandargumentvalue
 *
 * @property AppPaginatorComponent $Paginator
 */
class HosttemplatesController extends AppController {
    public $uses = [
        'Hosttemplate',
        'Timeperiod',
        'Command',
        'Contact',
        'Contactgroup',
        'Container',
        'Customvariable',
        'Hosttemplatecommandargumentvalue',
        'Hostcommandargumentvalue',
        'Hostgroup',
        'Documentation'
    ];

    //public $layout = 'Admin.default';
    public $layout = 'blank';

    public function index() {
        if (!$this->isAngularJsRequest()) {
            //Only ship HTML Template
            return;
        }

        /** @var $HosttemplatesTable HosttemplatesTable */
        $HosttemplatesTable = TableRegistry::getTableLocator()->get('Hosttemplates');

        $HosttemplateFilter = new HosttemplateFilter($this->request);
        $PaginateOMat = new PaginateOMat($this->Paginator, $this, $this->isScrollRequest(), $HosttemplateFilter->getPage());

        $MY_RIGHTS = $this->MY_RIGHTS;
        if ($this->hasRootPrivileges) {
            $MY_RIGHTS = [];
        }
        $hosttemplates = $HosttemplatesTable->getHosttemplatesIndex($HosttemplateFilter, $PaginateOMat, $MY_RIGHTS);

        foreach ($hosttemplates as $index => $hosttemplate) {
            $hosttemplates[$index]['Hosttemplate']['allow_edit'] = true;
            if ($this->hasRootPrivileges === false) {
                $hosttemplates[$index]['Hosttemplate']['allow_edit'] = $this->isWritableContainer($hosttemplate['Hosttemplate']['container_id']);
            }
        }


        $this->set('all_hosttemplates', $hosttemplates);
        $toJson = ['all_hosttemplates', 'paging'];
        if ($this->isScrollRequest()) {
            $toJson = ['all_hosttemplates', 'scroll'];
        }
        $this->set('_serialize', $toJson);
    }

    /**
     * @param null|int $id
     */
    public function view($id = null) {
        if (!$this->isApiRequest()) {
            throw new MethodNotAllowedException();
        }

        /** @var $HosttemplatesTable HosttemplatesTable */
        $HosttemplatesTable = TableRegistry::getTableLocator()->get('Hosttemplates');

        if (!$HosttemplatesTable->existsById($id)) {
            throw new NotFoundException(__('Invalid host template'));
        }

        $hosttemplate = $HosttemplatesTable->getHosttemplateById($id, [
            'Containers',
            'Hosttemplatecommandargumentvalues',
            'Customvariables'
        ]);


        if (!$this->allowedByContainerId($hosttemplate['Hosttemplate']['container']['id'])) {
            throw new ForbiddenException('403 Forbidden');
        }

        $this->set('hosttemplate', $hosttemplate);
        $this->set('_serialize', ['hosttemplate']);
    }

    /**
     * @param null|int $hosttemplatetype_id
     */
    public function add($hosttemplatetype_id = null) {
        if (!$this->isApiRequest()) {
            //Only ship HTML template for angular
            return;
        }

        if ($this->request->is('post')) {
            /** @var $HosttemplatesTable HosttemplatesTable */
            $HosttemplatesTable = TableRegistry::getTableLocator()->get('Hosttemplates');
            $this->request->data['Hosttemplate']['uuid'] = UUID::v4();
            $this->request->data['Hosttemplate']['hosttemplatetype_id'] = GENERIC_HOSTTEMPLATE;

            if ($hosttemplatetype_id !== null && is_numeric($hosttemplatetype_id)) {
                //Legacy???
                $this->request->data['Hosttemplate']['hosttemplatetype_id'] = $hosttemplatetype_id;
            }

            $hosttemplate = $HosttemplatesTable->newEntity();
            $hosttemplate = $HosttemplatesTable->patchEntity($hosttemplate, $this->request->data('Hosttemplate'));

            $HosttemplatesTable->save($hosttemplate);
            if ($hosttemplate->hasErrors()) {
                $this->response->statusCode(400);
                $this->set('error', $hosttemplate->getErrors());
                $this->set('_serialize', ['error']);
                return;
            } else {
                //No errors

                $User = new \itnovum\openITCOCKPIT\Core\ValueObjects\User($this->Auth);

                $extDataForChangelog = $HosttemplatesTable->resolveDataForChangelog($this->request->data);
                $changelog_data = $this->Changelog->parseDataForChangelog(
                    'add',
                    'hosttemplates',
                    $hosttemplate->get('id'),
                    OBJECT_HOSTTEMPLATE,
                    $hosttemplate->get('container_id'),
                    $User->getId(),
                    $hosttemplate->get('name'),
                    array_merge($this->request->data, $extDataForChangelog)
                );

                if ($changelog_data) {
                    CakeLog::write('log', serialize($changelog_data));
                }


                if ($this->request->ext == 'json') {
                    $this->serializeCake4Id($hosttemplate); // REST API ID serialization
                    return;
                }
            }
            $this->set('hosttemplate', $hosttemplate);
            $this->set('_serialize', ['hosttemplate']);
        }
    }

    /**
     * @param null|int $id
     * @param null|int $hosttemplatetype_id
     * @deprecated
     */
    public function edit($id = null, $hosttemplatetype_id = null) {
        if (!$this->isApiRequest()) {
            //Only ship HTML template for angular
            return;
        }

        /** @var $HosttemplatesTable HosttemplatesTable */
        $HosttemplatesTable = TableRegistry::getTableLocator()->get('Hosttemplates');
        /** @var $CommandsTable CommandsTable */
        $CommandsTable = TableRegistry::getTableLocator()->get('Commands');

        if (!$HosttemplatesTable->existsById($id)) {
            throw new NotFoundException(__('Host template not found'));
        }

        $hosttemplate = $HosttemplatesTable->getHosttemplateForEdit($id);
        $hosttemplateForChangeLog = $hosttemplate;

        if (!$this->allowedByContainerId($hosttemplate['Hosttemplate']['container_id'])) {
            $this->render403();
            return;
        }

        if ($this->request->is('get') && $this->isAngularJsRequest()) {
            //Return contact information
            $commands = $CommandsTable->getCommandByTypeAsList(HOSTCHECK_COMMAND);
            $this->set('commands', Api::makeItJavaScriptAble($commands));
            $this->set('hosttemplate', $hosttemplate);
            $this->set('_serialize', ['hosttemplate', 'commands']);
            return;
        }

        if ($this->request->is('post') && $this->isAngularJsRequest()) {
            //Update contact data
            $User = new \itnovum\openITCOCKPIT\Core\ValueObjects\User($this->Auth);
            $hosttemplateEntity = $HosttemplatesTable->get($id);
            $hosttemplateEntity = $HosttemplatesTable->patchEntity($hosttemplateEntity, $this->request->data('Hosttemplate'));
            $HosttemplatesTable->save($hosttemplateEntity);
            if ($hosttemplateEntity->hasErrors()) {
                $this->response->statusCode(400);
                $this->set('error', $hosttemplateEntity->getErrors());
                $this->set('_serialize', ['error']);
                return;
            } else {
                //No errors

                $changelog_data = $this->Changelog->parseDataForChangelog(
                    'edit',
                    'hosttemplates',
                    $hosttemplateEntity->id,
                    OBJECT_HOSTTEMPLATE,
                    $hosttemplateEntity->get('container_id'),
                    $User->getId(),
                    $hosttemplateEntity->name,
                    array_merge($HosttemplatesTable->resolveDataForChangelog($this->request->data), $this->request->data),
                    array_merge($HosttemplatesTable->resolveDataForChangelog($hosttemplateForChangeLog), $hosttemplateForChangeLog)
                );
                if ($changelog_data) {
                    CakeLog::write('log', serialize($changelog_data));
                }

                if ($this->request->ext == 'json') {
                    $this->serializeCake4Id($hosttemplateEntity); // REST API ID serialization
                    return;
                }
            }
            $this->set('hosttemplate', $hosttemplateEntity);
            $this->set('_serialize', ['hosttemplate']);
        }
    }

    /**
     * @param null $id
     * @deprecated
     */
    public function delete($id = null) {
        if (!$this->request->is('post')) {
            throw new MethodNotAllowedException();
        }

        /** @var $HosttemplatesTable HosttemplatesTable */
        $HosttemplatesTable = TableRegistry::getTableLocator()->get('Hosttemplates');

        if (!$HosttemplatesTable->existsById($id)) {
            throw new NotFoundException(__('Host template not found'));
        }

        $hosttemplate = $HosttemplatesTable->get($id);

        if (!$this->allowedByContainerId($hosttemplate->get('container_id'))) {
            $this->render403();
            return;
        }

        if (!$HosttemplatesTable->allowDelete($id)) {
            $usedBy = [
                [
                    'baseUrl' => '#',
                    'state'   => 'HosttemplatesUsedBy',
                    'message' => __('Used by other objects'),
                    'module'  => 'Core'
                ]
            ];

            $this->response->statusCode(400);
            $this->set('success', false);
            $this->set('id', $id);
            $this->set('message', __('Issue while deleting host template'));
            $this->set('usedBy', $usedBy);
            $this->set('_serialize', ['success', 'id', 'message', 'usedBy']);
            return;
        }


        if ($HosttemplatesTable->delete($hosttemplate)) {
            $User = new \itnovum\openITCOCKPIT\Core\ValueObjects\User($this->Auth);
            $changelog_data = $this->Changelog->parseDataForChangelog(
                'delete',
                'hosttemplates',
                $id,
                OBJECT_HOSTTEMPLATE,
                $hosttemplate->get('container_id'),
                $User->getId(),
                $hosttemplate->get('name'),
                [
                    'Hosttemplate' => $hosttemplate->toArray()
                ]
            );
            if ($changelog_data) {
                CakeLog::write('log', serialize($changelog_data));
            }

            //Delete Documentation record if exists
            /** @var $DocumentationsTable DocumentationsTable */
            $DocumentationsTable = TableRegistry::getTableLocator()->get('Documentations');

            $documentation = $DocumentationsTable->getDocumentationByUuid($hosttemplate->get('uuid'));
            if ($documentation) {
                $DocumentationsTable->delete($documentation);
            }

            $this->set('success', true);
            $this->set('_serialize', ['success']);
            return;
        }

        $this->response->statusCode(500);
        $this->set('success', false);
        $this->set('_serialize', ['success']);
    }

    /**
     * @param null $id
     * @deprecated
     */
    public function copy($id = null) {
        if (!$this->isAngularJsRequest()) {
            //Only ship HTML Template
            return;
        }

        /** @var $HosttemplatesTable HosttemplatesTable */
        $HosttemplatesTable = TableRegistry::getTableLocator()->get('Hosttemplates');

        if ($this->request->is('get')) {
            $hosttemplates = $HosttemplatesTable->getHosttemplatesForCopy(func_get_args());
            /** @var $CommandsTable CommandsTable */
            $CommandsTable = TableRegistry::getTableLocator()->get('Commands');
            $commands = $CommandsTable->getCommandByTypeAsList(HOSTCHECK_COMMAND);
            $this->set('hosttemplates', $hosttemplates);
            $this->set('commands', Api::makeItJavaScriptAble($commands));
            $this->set('_serialize', ['hosttemplates', 'commands']);
            return;
        }

        $hasErrors = false;

        if ($this->request->is('post')) {
            $Cache = new KeyValueStore();

            $postData = $this->request->data('data');
            $User = new \itnovum\openITCOCKPIT\Core\ValueObjects\User($this->Auth);

            foreach ($postData as $index => $hosttemplateData) {
                if (!isset($hosttemplateData['Hosttemplate']['id'])) {
                    //Create/clone hosttemplate
                    $sourceHosttemplateId = $hosttemplateData['Source']['id'];
                    if (!$Cache->has($sourceHosttemplateId)) {
                        $sourceHosttemplate = $HosttemplatesTable->getHosttemplateForEdit($sourceHosttemplateId);
                        $sourceHosttemplate = $sourceHosttemplate['Hosttemplate'];
                        unset($sourceHosttemplate['id'], $sourceHosttemplate['uuid']);

                        foreach ($sourceHosttemplate['hosttemplatecommandargumentvalues'] as $i => $hosttemplatecommandargumentvalues) {
                            unset($sourceHosttemplate['hosttemplatecommandargumentvalues'][$i]['id']);
                            unset($sourceHosttemplate['hosttemplatecommandargumentvalues'][$i]['hosttemplate_id']);
                        }

                        $Cache->set($sourceHosttemplateId, $sourceHosttemplate);
                    }

                    $sourceHosttemplate = $Cache->get($sourceHosttemplateId);

                    $newHosttemplateData = $sourceHosttemplate;
                    $newHosttemplateData['uuid'] = UUID::v4();
                    $newHosttemplateData['name'] = $hosttemplateData['Hosttemplate']['name'];
                    $newHosttemplateData['description'] = $hosttemplateData['Hosttemplate']['description'];
                    $newHosttemplateData['command_id'] = $hosttemplateData['Hosttemplate']['command_id'];
                    if (!empty($hosttemplateData['Hosttemplate']['hosttemplatecommandargumentvalues'])) {
                        $newHosttemplateData['hosttemplatecommandargumentvalues'] = $hosttemplateData['Hosttemplate']['hosttemplatecommandargumentvalues'];
                    }

                    $newHosttemplateEntity = $HosttemplatesTable->newEntity($newHosttemplateData);
                }

                $action = 'copy';
                if (isset($hosttemplateData['Hosttemplate']['id'])) {
                    //Update existing hosttemplates
                    //This happens, if a user copy multiple hosttemplates, and one run into an validation error
                    //All hosttemplates without validation errors got already saved to the database
                    $newHosttemplateEntity = $HosttemplatesTable->get($hosttemplateData['Hosttemplate']['id']);
                    $newHosttemplateEntity = $HosttemplatesTable->patchEntity($newHosttemplateEntity, $hosttemplateData['Hosttemplate']);
                    $newHosttemplateData = $newHosttemplateEntity->toArray();
                    $action = 'edit';
                }
                $HosttemplatesTable->save($newHosttemplateEntity);

                $postData[$index]['Error'] = [];
                if ($newHosttemplateEntity->hasErrors()) {
                    $hasErrors = true;
                    $postData[$index]['Error'] = $newHosttemplateEntity->getErrors();
                } else {
                    //No errors
                    $postData[$index]['Hosttemplate']['id'] = $newHosttemplateEntity->get('id');

                    $changelog_data = $this->Changelog->parseDataForChangelog(
                        $action,
                        'hosttemplates',
                        $postData[$index]['Hosttemplate']['id'],
                        OBJECT_HOSTTEMPLATE,
                        [ROOT_CONTAINER],
                        $User->getId(),
                        $newHosttemplateEntity->get('name'),
                        ['Hosttemplate' => $newHosttemplateData]
                    );
                    if ($changelog_data) {
                        CakeLog::write('log', serialize($changelog_data));
                    }
                }
            }
        }

        if ($hasErrors) {
            $this->response->statusCode(400);
        }
        $this->set('result', $postData);
        $this->set('_serialize', ['result']);
    }


    public function usedBy($id = null) {
        if (!$this->isApiRequest()) {
            //Only ship HTML template for angular
            return;
        }

        if (!$this->Hosttemplate->exists($id)) {
            throw new NotFoundException(__('Invalid hosttemplate'));
        }

        $hosttemplate = $this->Hosttemplate->find('first', [
            'recursive'  => -1,
            'fields'     => [
                'Hosttemplate.name'
            ],
            'conditions' => [
                'Hosttemplate.id' => $id
            ]
        ]);

        if (!$this->allowedByContainerId(Hash::extract($hosttemplate, 'Container.id'), false)) {
            $this->render403();
            return;
        }

        $this->loadModel('Host');
        $hosts = $this->Host->find('all', [
            'recursive'  => -1,
            'order'      => [
                'Host.name' => 'ASC',
            ],
            'joins'      => [
                [
                    'table'      => 'hosts_to_containers',
                    'alias'      => 'HostsToContainers',
                    'type'       => 'LEFT',
                    'conditions' => [
                        'HostsToContainers.host_id = Host.id',
                    ],
                ],
            ],
            'conditions' => [
                'HostsToContainers.container_id' => $this->MY_RIGHTS,
                'Host.hosttemplate_id'           => $id,
            ],
            'contain'    => [
                'Container'
            ],
            'fields'     => [
                'Host.id',
                'Host.uuid',
                'Host.name',
                'Host.address',
            ],
            'group'      => 'Host.id'
        ]);

        $all_hosts = [];
        foreach ($hosts as $host) {
            $Host = new \itnovum\openITCOCKPIT\Core\Views\Host($host);
            if ($this->hasRootPrivileges) {
                $allowEdit = true;
            } else {
                $ContainerPermissions = new ContainerPermissions($this->MY_RIGHTS_LEVEL, $Host->getContainerIds());
                $allowEdit = $ContainerPermissions->hasPermission();
            }
            $tmpRecord = [
                'Host' => $Host->toArray()
            ];
            $tmpRecord['Host']['allow_edit'] = $allowEdit;
            $all_hosts[] = $tmpRecord;
        }


        $this->set(compact(['all_hosts', 'hosttemplate']));
        $this->set('_serialize', ['all_hosts', 'hosttemplate']);
    }

    /****************************
     *       AJAX METHODS       *
     ****************************/

    public function loadElementsByContainerId($container_id = null) {
        if (!$this->isAngularJsRequest()) {
            throw new MethodNotAllowedException();
        }

        /** @var $ContainersTable ContainersTable */
        $ContainersTable = TableRegistry::getTableLocator()->get('Containers');
        /** @var $ContactsTable ContactsTable */
        $ContactsTable = TableRegistry::getTableLocator()->get('Contacts');
        /** @var $ContactgroupsTable ContactgroupsTable */
        $ContactgroupsTable = TableRegistry::getTableLocator()->get('Contactgroups');
        /** @var $TimeperiodsTable TimeperiodsTable */
        $TimeperiodsTable = TableRegistry::getTableLocator()->get('Timeperiods');

        if (!$ContainersTable->existsById($container_id)) {
            throw new NotFoundException(__('Invalid Container'));
        }

        $containerIds = $ContainersTable->resolveChildrenOfContainerIds($container_id);

        $timeperiods = $TimeperiodsTable->timeperiodsByContainerId($containerIds, 'list');
        $timeperiods = Api::makeItJavaScriptAble($timeperiods);
        $checkperiods = $timeperiods;

        $contacts = $ContactsTable->contactsByContainerId($containerIds, 'list');
        $contacts = Api::makeItJavaScriptAble($contacts);

        $contactgroups = $ContactgroupsTable->getContactgroupsByContainerId($containerIds, 'list', 'id');
        $contactgroups = Api::makeItJavaScriptAble($contactgroups);

        $hostgroups = $this->Hostgroup->hostgroupsByContainerId($containerIds, 'list', 'id');
        $hostgroups = Api::makeItJavaScriptAble($hostgroups);

        $this->set(compact(['timeperiods', 'checkperiods', 'contacts', 'contactgroups', 'hostgroups']));
        $this->set('_serialize', ['timeperiods', 'checkperiods', 'contacts', 'contactgroups', 'hostgroups']);
    }

    /**
     * @param null|int $hosttemplateId
     */
    public function loadContainers($hosttemplateId = null) {
        if (!$this->isAngularJsRequest()) {
            throw new MethodNotAllowedException();
        }

        /** @var $ContainersTable ContainersTable */
        $ContainersTable = TableRegistry::getTableLocator()->get('Containers');

        if ($this->hasRootPrivileges === true) {
            $containers = $ContainersTable->easyPath($this->MY_RIGHTS, OBJECT_HOSTTEMPLATE, [], $this->hasRootPrivileges, [CT_HOSTGROUP]);
        } else {
            $containers = $ContainersTable->easyPath($this->getWriteContainers(), OBJECT_HOSTTEMPLATE, [], $this->hasRootPrivileges, [CT_HOSTGROUP]);
        }

        $areContainersRestricted = false;
        if (is_numeric($hosttemplateId)) {
            //Edit mode

            /** @var $HosttemplatesTable HosttemplatesTable */
            $HosttemplatesTable = TableRegistry::getTableLocator()->get('Hosttemplates');
            /** @var $HostsTable HostsTable */
            $HostsTable = TableRegistry::getTableLocator()->get('Hosts');

            $hosttemplatesContainerId = $HosttemplatesTable->getContainerIdById($hosttemplateId);
            $usedContainerIds = $HostsTable->getHostPrimaryContainerIdsByHosttemplateId($hosttemplateId);

            if (!empty($usedContainerIds)) {
                //This host template is used by some hosts.
                //Container options needs to be needs to be restricted if the hosts are using some sub containers...
                $restrictedContainers = [];
                foreach ($containers as $containerId => $path) {
                    $containerId = (int)$containerId;
                    if (in_array($containerId, [ROOT_CONTAINER, $hosttemplatesContainerId], true)) {
                        $restrictedContainers[$containerId] = $path;
                    } else {
                        $areContainersRestricted = true;
                    }
                }
                $containers = $restrictedContainers;
            }
        }


        $this->set('containers', Api::makeItJavaScriptAble($containers));
        $this->set('areContainersRestricted', $areContainersRestricted);
        $this->set('_serialize', ['containers', 'areContainersRestricted']);
    }

    public function loadCommands() {
        if (!$this->isAngularJsRequest()) {
            throw new MethodNotAllowedException();
        }

        /** @var $CommandsTable CommandsTable */
        $CommandsTable = TableRegistry::getTableLocator()->get('Commands');
        $commands = $CommandsTable->getCommandByTypeAsList(HOSTCHECK_COMMAND);

        $this->set('commands', Api::makeItJavaScriptAble($commands));
        $this->set('_serialize', ['commands']);
    }

    /**
     * @param null $commandId
     * @param null $hosttemplateId
     */
    public function loadCommandArguments($commandId = null, $hosttemplateId = null) {
        if (!$this->isAngularJsRequest()) {
            throw new MethodNotAllowedException();
        }

        /** @var $CommandsTable CommandsTable */
        $CommandsTable = TableRegistry::getTableLocator()->get('Commands');
        /** @var $CommandargumentsTable CommandargumentsTable */
        $CommandargumentsTable = TableRegistry::getTableLocator()->get('Commandarguments');

        //HosttemplatecommandargumentvaluesTable

        if (!$CommandsTable->existsById($commandId)) {
            throw new NotFoundException(__('Invalid command'));
        }

        $hosttemplatecommandargumentvalues = [];

        if ($hosttemplateId != null) {
            //User passed an hosttemplateId, so we are in a non add mode!
            //Check if the hosttemplate has defined command arguments

            /** @var $HosttemplatecommandargumentvaluesTable HosttemplatecommandargumentvaluesTable */
            $HosttemplatecommandargumentvaluesTable = TableRegistry::getTableLocator()->get('Hosttemplatecommandargumentvalues');

            $hosttemplateCommandArgumentValues = $HosttemplatecommandargumentvaluesTable->getByHosttemplateIdAndCommandId($hosttemplateId, $commandId);

            foreach ($hosttemplateCommandArgumentValues as $hosttemplateCommandArgumentValue) {
                $hosttemplatecommandargumentvalues[] = [
                    'commandargument_id' => $hosttemplateCommandArgumentValue['commandargument_id'],
                    'hosttemplate_id'    => $hosttemplateCommandArgumentValue['hosttemplate_id'],
                    'value'              => $hosttemplateCommandArgumentValue['value'],
                    'commandargument'    => [
                        'name'       => $hosttemplateCommandArgumentValue['commandargument']['name'],
                        'human_name' => $hosttemplateCommandArgumentValue['commandargument']['human_name'],
                        'command_id' => $hosttemplateCommandArgumentValue['commandargument']['command_id'],
                    ]
                ];
            }
        }

        //Get command arguments
        if (empty($hosttemplatecommandargumentvalues)) {
            //Hosttemplate has no command arguments defined
            //Or we are in hosttemplates/add ?

            //Load command arguments of the check command
            foreach ($CommandargumentsTable->getByCommandId($commandId) as $commandargument) {
                $hosttemplatecommandargumentvalues[] = [
                    'commandargument_id' => $commandargument['Commandargument']['id'],
                    'value'              => '',
                    'commandargument'    => [
                        'name'       => $commandargument['Commandargument']['name'],
                        'human_name' => $commandargument['Commandargument']['human_name'],
                        'command_id' => $commandargument['Commandargument']['command_id'],
                    ]
                ];
            }
        };

        $this->set('hosttemplatecommandargumentvalues', $hosttemplatecommandargumentvalues);
        $this->set('_serialize', ['hosttemplatecommandargumentvalues']);
    }


}
