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

declare(strict_types=1);

namespace App\Controller;

use App\Model\Table\ContactgroupsTable;
use App\Model\Table\ContactsTable;
use App\Model\Table\ContainersTable;
use App\Model\Table\ServiceescalationsTable;
use App\Model\Table\ServicegroupsTable;
use App\Model\Table\ServicesTable;
use App\Model\Table\TimeperiodsTable;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use itnovum\openITCOCKPIT\Core\AngularJS\Api;
use itnovum\openITCOCKPIT\Database\PaginateOMat;
use itnovum\openITCOCKPIT\Filter\ServiceescalationsFilter;

/**
 * @property \App\Model\Entity\Serviceescalation $Serviceescalation
 * @property \App\Model\Entity\Timeperiod $Timeperiod
 * @property \App\Model\Entity\Service Service
 * @property \App\Model\Entity\Servicegroup $Servicegroup
 * @property \App\Model\Entity\Contact $Contact
 * @property \App\Model\Entity\Contactgroup $Contactgroup
 * @property ServiceescalationServiceMembership $ServiceescalationServiceMembership
 * @property ServiceescalationServicegroupMembership $ServiceescalationServicegroupMembership
 * @property \App\Model\Entity\Container $Container
 */
class ServiceescalationsController extends AppController {

    public $layout = 'blank';


    public function index() {
        if (!$this->isAngularJsRequest()) {
            //Only ship HTML Template
            return;
        }

        /** @var $ServiceescalationsTable ServiceescalationsTable */
        $ServiceescalationsTable = TableRegistry::getTableLocator()->get('Serviceescalations');

        $ServiceescalationsFilter = new ServiceescalationsFilter($this->request);
        $PaginateOMat = new PaginateOMat($this->Paginator, $this, $this->isScrollRequest(), $ServiceescalationsFilter->getPage());

        $MY_RIGHTS = $this->MY_RIGHTS;
        if ($this->hasRootPrivileges) {
            $MY_RIGHTS = [];
        }
        $serviceescalations = $ServiceescalationsTable->getServiceescalationsIndex($ServiceescalationsFilter, $PaginateOMat, $MY_RIGHTS);
        foreach ($serviceescalations as $index => $serviceescalation) {
            $serviceescalations[$index]['allowEdit'] = $this->isWritableContainer($serviceescalation['container_id']);
        }


        $this->set('all_serviceescalations', $serviceescalations);
        $toJson = ['all_serviceescalations', 'paging'];
        if ($this->isScrollRequest()) {
            $toJson = ['all_serviceescalations', 'scroll'];
        }
        $this->set('_serialize', $toJson);
    }

    public function view($id = null) {
        if (!$this->isApiRequest()) {
            throw new MethodNotAllowedException();
        }

        /** @var $ServiceescalationsTable ServiceescalationsTable */
        $ServiceescalationsTable = TableRegistry::getTableLocator()->get('Serviceescalations');

        if (!$ServiceescalationsTable->exists($id)) {
            throw new NotFoundException(__('Service escalation not found'));
        }

        $serviceescalation = $ServiceescalationsTable->getServiceescalationById($id);
        if (!$this->allowedByContainerId(Hash::extract($serviceescalation, 'Serviceescalation.container_id'))) {
            $this->render403();
            return;
        }

        $this->set('serviceescalation', $serviceescalation);
        $this->set('_serialize', ['serviceescalation']);
    }

    public function edit($id = null) {
        if (!$this->isApiRequest()) {
            //Only ship HTML template for angular
            return;
        }

        /** @var $ServiceescalationsTable ServiceescalationsTable */
        $ServiceescalationsTable = TableRegistry::getTableLocator()->get('Serviceescalations');
        if (!$ServiceescalationsTable->existsById($id)) {
            throw new NotFoundException('Service escalation not found');
        }
        $serviceescalation = $ServiceescalationsTable->get($id, [
            'contain' => [
                'services'      => function (Query $q) {
                    return $q->enableAutoFields(false)
                        ->select(['id', 'name']);
                },
                'servicegroups' => function (Query $q) {
                    return $q->enableAutoFields(false)
                        ->select(['id']);
                },
                'contacts'      => function (Query $q) {
                    return $q->enableAutoFields(false)
                        ->select(['id']);
                },
                'contactgroups' => function (Query $q) {
                    return $q->enableAutoFields(false)
                        ->select(['id']);
                },
            ]
        ]);

        if (!$this->allowedByContainerId($serviceescalation->get('container_id'))) {
            $this->render403();
            return;
        }

        if ($this->request->is('post')) {
            /** @var $ServiceescalationsTable ServiceescalationsTable */
            $ServiceescalationsTable = TableRegistry::getTableLocator()->get('Serviceescalations');
            $data['services'] = $ServiceescalationsTable->parseServiceMembershipData(
                $this->request->data('Serviceescalation.services._ids'),
                $this->request->data('Serviceescalation.services_excluded._ids')
            );
            $data['servicegroups'] = $ServiceescalationsTable->parseServicegroupMembershipData(
                $this->request->data('Serviceescalation.servicegroups._ids'),
                $this->request->data('Serviceescalation.servicegroups_excluded._ids')
            );

            $data = array_merge($this->request->data('Serviceescalation'), $data);
            $serviceescalation = $ServiceescalationsTable->patchEntity($serviceescalation, $data);
            $ServiceescalationsTable->save($serviceescalation);

            if ($serviceescalation->hasErrors()) {
                $this->response->statusCode(400);
                $this->set('error', $serviceescalation->getErrors());
                $this->set('_serialize', ['error']);
                return;
            } else {
                if ($this->request->ext == 'json') {
                    $this->serializeCake4Id($serviceescalation); // REST API ID serialization
                    return;
                }
            }
        }
        $this->set('serviceescalation', $serviceescalation);
        $this->set('_serialize', ['serviceescalation']);
    }

    public function add() {
        if (!$this->isApiRequest()) {
            //Only ship HTML template for angular
            return;
        }

        if ($this->request->is('post')) {
            /** @var $ServiceescalationsTable ServiceescalationsTable */
            $ServiceescalationsTable = TableRegistry::getTableLocator()->get('Serviceescalations');
            $this->request->data['Serviceescalation']['uuid'] = \itnovum\openITCOCKPIT\Core\UUID::v4();
            $data['services'] = $ServiceescalationsTable->parseServiceMembershipData(
                $this->request->data('Serviceescalation.services._ids'),
                $this->request->data('Serviceescalation.services_excluded._ids')
            );
            $data['servicegroups'] = $ServiceescalationsTable->parseServicegroupMembershipData(
                $this->request->data('Serviceescalation.servicegroups._ids'),
                $this->request->data('Serviceescalation.servicegroups_excluded._ids')
            );

            $data = array_merge($this->request->data('Serviceescalation'), $data);
            $serviceescalation = $ServiceescalationsTable->newEntity($data);
            $ServiceescalationsTable->save($serviceescalation);

            if ($serviceescalation->hasErrors()) {
                $this->response->statusCode(400);
                $this->set('error', $serviceescalation->getErrors());
                $this->set('_serialize', ['error']);
                return;
            } else {
                if ($this->request->ext == 'json') {
                    $this->serializeCake4Id($serviceescalation); // REST API ID serialization
                    return;
                }
            }
            $this->set('serviceescalation', $serviceescalation);
            $this->set('_serialize', ['serviceescalation']);
        }
    }

    public function delete($id = null) {
        if (!$this->request->is('post')) {
            throw new MethodNotAllowedException();
        }

        /** @var $ServiceescalationsTable ServiceescalationsTable */
        $ServiceescalationsTable = TableRegistry::getTableLocator()->get('Serviceescalations');

        if (!$ServiceescalationsTable->exists($id)) {
            throw new NotFoundException(__('Service escalation not found'));
        }

        $serviceescalation = $ServiceescalationsTable->getServiceescalationById($id);
        if (!$this->allowedByContainerId(Hash::extract($serviceescalation, 'Serviceescalation.container_id'))) {
            $this->render403();
            return;
        }
        $serviceescalationEntity = $ServiceescalationsTable->get($id);
        if ($ServiceescalationsTable->delete($serviceescalationEntity)) {
            $this->set('success', true);
            $this->set('_serialize', ['success']);
            return;
        }

        $this->response->statusCode(500);
        $this->set('success', false);
        $this->set('_serialize', ['success']);
        return;
    }

    public function loadElementsByContainerId($containerId = null) {
        if (!$this->isApiRequest()) {
            throw new MethodNotAllowedException(__('This is only allowed via API.'));
            return;
        }

        /** @var $ContainersTable ContainersTable */
        $ContainersTable = TableRegistry::getTableLocator()->get('Containers');
        /** @var $TimeperiodsTable TimeperiodsTable */
        $TimeperiodsTable = TableRegistry::getTableLocator()->get('Timeperiods');
        /** @var $ContactsTable ContactsTable */
        $ContactsTable = TableRegistry::getTableLocator()->get('Contacts');
        /** @var $ContactgroupsTable ContactgroupsTable */
        $ContactgroupsTable = TableRegistry::getTableLocator()->get('Contactgroups');
        /** @var $ServicegroupsTable ServicegroupsTable */
        $ServicegroupsTable = TableRegistry::getTableLocator()->get('Servicegroups');

        if (!$ContainersTable->existsById($containerId)) {
            throw new NotFoundException(__('Invalid container'));
        }

        $containerIds = $ContainersTable->resolveChildrenOfContainerIds($containerId);

        $servicegroups = $ServicegroupsTable->servicegroupsByContainerId($containerIds, 'list', 'id');
        $servicegroups = Api::makeItJavaScriptAble($servicegroups);
        $servicegroupsExcluded = $servicegroups;

        $timeperiods = $TimeperiodsTable->timeperiodsByContainerId($containerIds, 'list');
        $timeperiods = Api::makeItJavaScriptAble($timeperiods);

        $contacts = $ContactsTable->contactsByContainerId($containerIds, 'list');
        $contacts = Api::makeItJavaScriptAble($contacts);

        $contactgroups = $ContactgroupsTable->getContactgroupsByContainerId($containerIds, 'list', 'id');
        $contactgroups = Api::makeItJavaScriptAble($contactgroups);

        $this->set('servicegroups', $servicegroups);
        $this->set('servicegroupsExcluded', $servicegroupsExcluded);
        $this->set('timeperiods', $timeperiods);
        $this->set('contacts', $contacts);
        $this->set('contactgroups', $contactgroups);

        $this->set('_serialize', [
            'servicegroups',
            'servicegroupsExcluded',
            'timeperiods',
            'contacts',
            'contactgroups'
        ]);
    }

    public function loadContainers() {
        if (!$this->isAngularJsRequest()) {
            throw new MethodNotAllowedException();
        }

        /** @var $ContainersTable ContainersTable */
        $ContainersTable = TableRegistry::getTableLocator()->get('Containers');

        if ($this->hasRootPrivileges === true) {
            $containers = $ContainersTable->easyPath($this->MY_RIGHTS, OBJECT_SERVICE, [], $this->hasRootPrivileges, [CT_HOSTGROUP, CT_SERVICEGROUP, CT_CONTACTGROUP]);
        } else {
            $containers = $ContainersTable->easyPath($this->getWriteContainers(), OBJECT_SERVICE, [], $this->hasRootPrivileges, [CT_HOSTGROUP, CT_SERVICEGROUP, CT_CONTACTGROUP]);
        }

        $this->set('containers', Api::makeItJavaScriptAble($containers));
        $this->set('_serialize', ['containers']);
    }
}

