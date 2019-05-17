<?php

namespace App\Model\Table;

use App\Lib\Traits\Cake2ResultTableTrait;
use App\Lib\Traits\CustomValidationTrait;
use App\Lib\Traits\PaginationAndScrollIndexTrait;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use itnovum\openITCOCKPIT\Filter\ServiceescalationsFilter;

/**
 * Serviceescalations Model
 *
 * @property \App\Model\Table\ContainersTable|\Cake\ORM\Association\BelongsTo $Containers
 * @property \App\Model\Table\ServiceescalationTable|\Cake\ORM\Association\HasMany $Services
 * @property \App\Model\Table\ServiceescalationTable|\Cake\ORM\Association\HasMany $Servicegroups
 *
 * @method \App\Model\Entity\Serviceescalation get($primaryKey, $options = [])
 * @method \App\Model\Entity\Serviceescalation newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Serviceescalation[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Serviceescalation|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Serviceescalation|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Serviceescalation patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Serviceescalation[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Serviceescalation findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ServiceescalationsTable extends Table {

    use Cake2ResultTableTrait;
    use PaginationAndScrollIndexTrait;
    use CustomValidationTrait;


    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        parent::initialize($config);
        $this->addBehavior('Timestamp');

        $this->setTable('serviceescalations');
        $this->setPrimaryKey('id');

        $this->belongsTo('Containers', [
            'foreignKey' => 'container_id',
            'joinType'   => 'INNER'
        ]);
        $this->belongsTo('Timeperiods', [
            'foreignKey' => 'timeperiod_id',
            'joinType'   => 'INNER'
        ]);
        $this->belongsToMany('Contacts', [
            'joinTable'    => 'contacts_to_serviceescalations',
            'saveStrategy' => 'replace'
        ]);
        $this->belongsToMany('Contactgroups', [
            'joinTable'    => 'contactgroups_to_serviceescalations',
            'saveStrategy' => 'replace'
        ]);

        $this->belongsToMany('Services', [
            'className'    => 'Services',
            'through'      => 'ServiceescalationsServiceMemberships',
            'saveStrategy' => 'replace'
        ]);
        $this->belongsToMany('ServicesExcluded', [
            'className'        => 'Services',
            'through'          => 'ServiceescalationsServiceMemberships',
            'targetForeignKey' => 'service_id',
            'saveStrategy'     => 'replace'
        ]);
        $this->belongsToMany('Servicegroups', [
            'through'      => 'ServiceescalationsServicegroupMemberships',
            'saveStrategy' => 'replace'

        ]);
        $this->belongsToMany('ServicegroupsExcluded', [
            'className'        => 'Servicegroups',
            'through'          => 'ServiceescalationsServicegroupMemberships',
            'targetForeignKey' => 'servicegroup_id',
            'saveStrategy'     => 'replace'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator) {
        $validator
            ->integer('id')
            ->allowEmptyString('id', 'create');

        $validator
            ->scalar('uuid')
            ->maxLength('uuid', 37)
            ->requirePresence('uuid', 'create')
            ->allowEmptyString('uuid', false)
            ->add('uuid', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->integer('container_id')
            ->greaterThan('container_id', 0)
            ->requirePresence('container_id')
            ->allowEmptyString('container_id', false);

        $validator
            ->add('contacts', 'custom', [
                'rule'    => [$this, 'atLeastOne'],
                'message' => __('You must specify at least one contact or contact group.')
            ]);

        $validator
            ->add('contactgroups', 'custom', [
                'rule'    => [$this, 'atLeastOne'],
                'message' => __('You must specify at least one contact or contact group.')
            ]);

        $validator
            ->requirePresence('services', true, __('You have to choose at least one service.'))
            ->allowEmptyString('services', false)
            ->multipleOptions('services', [
                'min' => 1
            ], __('You have to choose at least one service.'));

        $validator
            ->integer('timeperiod_id')
            ->greaterThan('timeperiod_id', 0)
            ->requirePresence('timeperiod_id')
            ->allowEmptyString('timeperiod_id', false);

        $validator
            ->integer('first_notification')
            ->greaterThan('first_notification', 0)
            ->lessThanField('first_notification', 'last_notification', __('The first notification must be before the last notification.'),
                function ($context) {
                    return !($context['data']['last_notification'] === 0);
                })
            ->requirePresence('first_notification')
            ->allowEmptyString('first_notification', false);

        $validator
            ->integer('last_notification')
            ->greaterThanOrEqual('last_notification', 0)
            ->greaterThanField('last_notification', 'first_notification', __('The first notification must be before the last notification.'),
                function ($context) {
                    return !($context['data']['last_notification'] === 0);
                })
            ->requirePresence('last_notification')
            ->allowEmptyString('last_notification', false);

        $validator
            ->integer('notification_interval')
            ->greaterThan('notification_interval', 0)
            ->requirePresence('notification_interval')
            ->allowEmptyString('notification_interval', false);

        $validator
            ->boolean('escalate_on_recovery')
            ->requirePresence('escalate_on_recovery', 'create')
            ->allowEmptyString('escalate_on_recovery', true)
            ->add('escalate_on_recovery', 'custom', [
                'rule'    => [$this, 'checkEscalateOptionsServiceEscalation'], //\App\Lib\Traits\CustomValidationTrait
                'message' => __('You must specify at least one escalate option.')
            ]);

        $validator
            ->boolean('escalate_on_warning')
            ->requirePresence('escalate_on_warning', 'create')
            ->allowEmptyString('escalate_on_warning', true)
            ->add('escalate_on_warning', 'custom', [
                'rule'    => [$this, 'checkEscalateOptionsServiceEscalation'], //\App\Lib\Traits\CustomValidationTrait
                'message' => __('You must specify at least one escalate option.')
            ]);

        $validator
            ->boolean('escalate_on_critical')
            ->requirePresence('escalate_on_critical', 'create')
            ->allowEmptyString('escalate_on_critical', true)
            ->add('escalate_on_warning', 'custom', [
                'rule'    => [$this, 'checkEscalateOptionsServiceEscalation'], //\App\Lib\Traits\CustomValidationTrait
                'message' => __('You must specify at least one escalate option.')
            ]);

        $validator
            ->boolean('escalate_on_unknown')
            ->requirePresence('escalate_on_unknown', 'create')
            ->allowEmptyString('escalate_on_unknown', true)
            ->add('escalate_on_unknown', 'custom', [
                'rule'    => [$this, 'checkEscalateOptionsServiceEscalation'], //\App\Lib\Traits\CustomValidationTrait
                'message' => __('You must specify at least one escalate option.')
            ]);

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules) {
        $rules->add($rules->isUnique(['uuid']));

        return $rules;
    }

    /**
     * @param mixed $value
     * @param array $context
     * @return bool
     *
     * Custom validation rule for contacts and or contact groups
     */
    public function atLeastOne($value, $context) {
        return !empty($context['data']['contacts']['_ids']) || !empty($context['data']['contactgroups']['_ids']);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function existsById($id) {
        return $this->exists(['Serviceescalations.id' => $id]);
    }

    /**
     * @param ServiceescalationsFilter $ServiceescalationsFilter
     * @param null $PaginateOMat
     * @param array $MY_RIGHTS
     * @return array
     */
    public function getServiceescalationsIndex(ServiceescalationsFilter $ServiceescalationsFilter, $PaginateOMat = null, $MY_RIGHTS = []) {
        $query = $this->find('all')
            ->contain([
                'Contacts'         => function (Query $q) {
                    return $q->enableAutoFields(false)
                        ->select([
                            'Contacts.id',
                            'Contacts.name'
                        ]);
                },
                'Contactgroups'    => [
                    'Containers' => function (Query $q) {
                        return $q->enableAutoFields(false)
                            ->select([
                                'Contactgroups.id',
                                'Containers.name'
                            ]);
                    },
                ],
                'Timeperiods'      => function (Query $q) {
                    return $q->enableAutoFields(false)
                        ->select([
                            'Timeperiods.id',
                            'Timeperiods.name'
                        ]);
                },
                'Services'         => function (Query $q) {
                    return $q->enableAutoFields(false)
                        ->where([
                            'ServiceescalationsServiceMemberships.excluded' => 0
                        ])
                        ->innerJoinWith('Servicetemplates')
                        ->innerJoinWith('Hosts')
                        ->select([
                            'Services.id',
                            'servicename' => $q->newExpr('CONCAT(Hosts.name, "/", IF(Services.name IS NULL, Servicetemplates.name, Services.name))'),
                            'Services.disabled'
                        ]);
                },
                'ServicesExcluded' => function (Query $q) {
                    return $q->enableAutoFields(false)
                        ->where([
                            'ServiceescalationsServiceMemberships.excluded' => 1
                        ])
                        ->innerJoinWith('Servicetemplates')
                        ->innerJoinWith('Hosts')
                        ->select([
                            'ServicesExcluded.id',
                            'servicename' => $q->newExpr('CONCAT(Hosts.name, "/", IF(ServicesExcluded.name IS NULL, Servicetemplates.name, ServicesExcluded.name))'),
                            'ServicesExcluded.disabled'
                        ]);
                },

                'Servicegroups'         => [
                    'Containers' => function (Query $q) {
                        return $q->enableAutoFields(false)
                            ->where([
                                'ServiceescalationsServicegroupMemberships.excluded' => 0
                            ])
                            ->select([
                                'Servicegroups.id',
                                'Containers.name'
                            ]);
                    },
                ],
                'ServicegroupsExcluded' => [
                    'Containers' => function (Query $q) {
                        return $q->enableAutoFields(false)
                            ->where([
                                'ServiceescalationsServicegroupMemberships.excluded' => 1
                            ])
                            ->select([
                                'ServicegroupsExcluded.id',
                                'Containers.name'
                            ]);
                    },
                ]
            ])
            ->group('Serviceescalations.id')
            ->disableHydration();
        $indexFilter = $ServiceescalationsFilter->indexFilter();
        $containFilter = [
            'Servicegroups.name'              => '',
            'ServicegroupsExcluded.name'      => ''
        ];

        if(!empty($indexFilter['Services.servicename LIKE'])){
            $query->innerJoinWith ('Services', function ($q) use ($containFilter) {
                return $q->innerJoinWith('Hosts')
                    ->innerJoinWith('Servicetemplates')
                    ->select([
                        'servicename' => $q->newExpr('CONCAT(Hosts.name, "/", IF(Services.name IS NULL, Servicetemplates.name, Services.name))')
                    ]);
            });
            $query->having([
                'servicename LIKE ' => $indexFilter['Services.servicename LIKE']
            ]);
        }

        unset($indexFilter['Services.servicename LIKE']);

        if(!empty($indexFilter['ServicesExcluded.servicename LIKE'])){
            $query->innerJoinWith ('ServicesExcluded', function ($q) use ($containFilter) {
                return $q->innerJoinWith('Hosts')
                    ->innerJoinWith('Servicetemplates')
                    ->select([
                        'servicename' => $q->newExpr('CONCAT(Hosts.name, "/", IF(ServicesExcluded.name IS NULL, Servicetemplates.name, ServicesExcluded.name))')
                    ]);
            });
            $query->having([
                'servicename LIKE ' => $indexFilter['ServicesExcluded.servicename LIKE']
            ]);
        }

        unset($indexFilter['ServicesExcluded.servicename LIKE']);


        if (!empty($indexFilter['Servicegroups.name LIKE'])) {
            $containFilter['Servicegroups.name'] = [
                'Containers.name LIKE' => $indexFilter['Servicegroups.name LIKE']
            ];
            $query->matching('Servicegroups.Containers', function ($q) use ($containFilter) {
                return $q->where($containFilter['Servicegroups.name']);
            });
            unset($indexFilter['Servicegroups.name LIKE']);
        }
        if (!empty($indexFilter['ServicegroupsExcluded.name LIKE'])) {
            $containFilter['ServicegroupsExcluded.name'] = [
                'Containers.name LIKE' => $indexFilter['ServicegroupsExcluded.name LIKE']
            ];
            $query->matching('ServicegroupsExcluded.Containers', function ($q) use ($containFilter) {
                return $q->where($containFilter['ServicegroupsExcluded.name']);
            });
            unset($indexFilter['ServicegroupsExcluded.name LIKE']);
        }
        if (!empty($MY_RIGHTS)) {
            $indexFilter['Serviceescalations.container_id IN'] = $MY_RIGHTS;
        }
        //debug((string)$query);
        $query->where($indexFilter);


        $query->order($ServiceescalationsFilter->getOrderForPaginator('Serviceescalations.first_notification', 'asc'));
        if ($PaginateOMat === null) {
            //Just execute query
            $result = $query->toArray();
        } else {
            if ($PaginateOMat->useScroll()) {
                $result = $this->scrollCake4($query, $PaginateOMat->getHandler(), false);
            } else {
                $result = $this->paginate($query, $PaginateOMat->getHandler(), false);
            }
        }
        return $result;
    }

    /**
     * @param array|int $services
     * @param array|int $excluded_services
     * @return array
     */
    public function parseServiceMembershipData($services = [], $excluded_services = []) {
        $servicemembershipData = [];
        foreach ($services as $serviceId) {
            $servicemembershipData[] = [
                'id'        => $serviceId,
                '_joinData' => [
                    'excluded' => 0
                ]
            ];
        }
        foreach ($excluded_services as $excluded_serviceId) {
            $servicemembershipData[] = [
                'id'        => $excluded_serviceId,
                '_joinData' => [
                    'excluded' => 1
                ]
            ];
        }
        return $servicemembershipData;
    }

    /**
     * @param array $servicegroups
     * @param array $excluded_servicegroups
     * @return array
     */
    public function parseServicegroupMembershipData($servicegroups = [], $excluded_servicegroups = []) {
        $servicegroupmembershipData = [];
        foreach ($servicegroups as $servicegroupId) {
            $servicegroupmembershipData[] = [
                'id'        => $servicegroupId,
                '_joinData' => [
                    'excluded' => 0
                ]
            ];
        }
        foreach ($excluded_servicegroups as $excluded_servicegroupId) {
            $servicegroupmembershipData[] = [
                'id'        => $excluded_servicegroupId,
                '_joinData' => [
                    'excluded' => 1
                ]
            ];
        }
        return $servicegroupmembershipData;
    }

    /**
     * @param null|string $uuid
     * @return array
     */
    public function getServiceescalationsForExport($uuid = null) {
        $query = $this->find()
            ->contain([
                'Services'      =>
                    function (Query $q) {
                        return $q->enableAutoFields(false)
                            ->where([
                                'Services.disabled' => 0
                            ])
                            ->select(['uuid']);
                    },
                'Servicegroups' =>
                    function (Query $q) {
                        return $q->enableAutoFields(false)
                            ->select(['uuid']);
                    },
                'Timeperiods'   =>
                    function (Query $q) {
                        return $q->enableAutoFields(false)
                            ->select(['uuid']);
                    },
                'Contacts'      =>
                    function (Query $q) {
                        return $q->enableAutoFields(false)
                            ->select(['uuid']);
                    },
                'Contactgroups' =>
                    function (Query $q) {
                        return $q->enableAutoFields(false)
                            ->select(['uuid']);
                    }
            ])
            ->select([
                'id',
                'uuid',
                'timeperiod_id',
                'first_notification',
                'last_notification',
                'notification_interval',
                'escalate_on_recovery',
                'escalate_on_warning',
                'escalate_on_critical',
                'escalate_on_unknown'
            ]);
        if ($uuid !== null) {
            $query->where([
                'Serviceescalations.uuid' => $uuid
            ]);
        }
        $query->all();
        return $query;
    }
}
