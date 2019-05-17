<?php

namespace App\Model\Table;

use App\Lib\Traits\Cake2ResultTableTrait;
use App\Lib\Traits\CustomValidationTrait;
use App\Lib\Traits\PaginationAndScrollIndexTrait;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use itnovum\openITCOCKPIT\Filter\HostescalationsFilter;

/**
 * Hostescalations Model
 *
 * @property \App\Model\Table\ContainersTable|\Cake\ORM\Association\BelongsTo $Containers
 * @property \App\Model\Table\HostescalationTable|\Cake\ORM\Association\HasMany $Hosts
 * @property \App\Model\Table\HostescalationTable|\Cake\ORM\Association\HasMany $Hostgroups
 *
 * @method \App\Model\Entity\Hostescalation get($primaryKey, $options = [])
 * @method \App\Model\Entity\Hostescalation newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Hostescalation[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Hostescalation|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Hostescalation|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Hostescalation patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Hostescalation[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Hostescalation findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class HostescalationsTable extends Table {

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

        $this->setTable('hostescalations');
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
            'joinTable'    => 'contacts_to_hostescalations',
            'saveStrategy' => 'replace'
        ]);
        $this->belongsToMany('Contactgroups', [
            'joinTable'    => 'contactgroups_to_hostescalations',
            'saveStrategy' => 'replace'
        ]);

        $this->belongsToMany('Hosts', [
            'className'    => 'Hosts',
            'through'      => 'HostescalationsHostMemberships',
            'saveStrategy' => 'replace'
        ]);
        $this->belongsToMany('HostsExcluded', [
            'className'        => 'Hosts',
            'through'          => 'HostescalationsHostMemberships',
            'targetForeignKey' => 'host_id',
            'saveStrategy'     => 'replace'
        ]);
        $this->belongsToMany('Hostgroups', [
            'through'      => 'HostescalationsHostgroupMemberships',
            'saveStrategy' => 'replace'

        ]);
        $this->belongsToMany('HostgroupsExcluded', [
            'className'        => 'Hostgroups',
            'through'          => 'HostescalationsHostgroupMemberships',
            'targetForeignKey' => 'hostgroup_id',
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
            ->requirePresence('hosts', true, __('You have to choose at least one host.'))
            ->allowEmptyString('hosts', false)
            ->multipleOptions('hosts', [
                'min' => 1
            ], __('You have to choose at least one host.'));

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
                'rule'    => [$this, 'checkEscalateOptionsHostEscalation'], //\App\Lib\Traits\CustomValidationTrait
                'message' => __('You must specify at least one escalate option.')
            ]);

        $validator
            ->boolean('escalate_on_down')
            ->requirePresence('escalate_on_down', 'create')
            ->allowEmptyString('escalate_on_down', true)
            ->add('escalate_on_down', 'custom', [
                'rule'    => [$this, 'checkEscalateOptionsHostEscalation'], //\App\Lib\Traits\CustomValidationTrait
                'message' => __('You must specify at least one escalate option.')
            ]);

        $validator
            ->boolean('escalate_on_unreachable')
            ->requirePresence('escalate_on_unreachable', 'create')
            ->allowEmptyString('escalate_on_unreachable', true)
            ->add('escalate_on_unreachable', 'custom', [
                'rule'    => [$this, 'checkEscalateOptionsHostEscalation'], //\App\Lib\Traits\CustomValidationTrait
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
        return $this->exists(['Hostescalations.id' => $id]);
    }

    /**
     * @param HostescalationsFilter $HostescalationsFilter
     * @param null $PaginateOMat
     * @param array $MY_RIGHTS
     * @return array
     */
    public function getHostescalationsIndex(HostescalationsFilter $HostescalationsFilter, $PaginateOMat = null, $MY_RIGHTS = []) {
        $query = $this->find('all')
            ->contain([
                'Contacts'      => function (Query $q) {
                    return $q->enableAutoFields(false)
                        ->select([
                            'Contacts.id',
                            'Contacts.name'
                        ]);
                },
                'Contactgroups' => [
                    'Containers' => function (Query $q) {
                        return $q->enableAutoFields(false)
                            ->select([
                                'Contactgroups.id',
                                'Containers.name'
                            ]);
                    },
                ],
                'Timeperiods'   => function (Query $q) {
                    return $q->enableAutoFields(false)
                        ->select([
                            'Timeperiods.id',
                            'Timeperiods.name'
                        ]);
                },
                'Hosts'         => function (Query $q) {
                    return $q->enableAutoFields(false)
                        ->where([
                            'HostescalationsHostMemberships.excluded' => 0
                        ])
                        ->select([
                            'Hosts.id',
                            'Hosts.name',
                            'Hosts.disabled'
                        ]);
                },
                'HostsExcluded' => function (Query $q) {
                    return $q->enableAutoFields(false)
                        ->where([
                            'HostescalationsHostMemberships.excluded' => 1
                        ])
                        ->select([
                            'HostsExcluded.id',
                            'HostsExcluded.name',
                            'HostsExcluded.disabled'
                        ]);
                },

                'Hostgroups'         => [
                    'Containers' => function (Query $q) {
                        return $q->enableAutoFields(false)
                            ->where([
                                'HostescalationsHostgroupMemberships.excluded' => 0
                            ])
                            ->select([
                                'Hostgroups.id',
                                'Containers.name'
                            ]);
                    },
                ],
                'HostgroupsExcluded' => [
                    'Containers' => function (Query $q) {
                        return $q->enableAutoFields(false)
                            ->where([
                                'HostescalationsHostgroupMemberships.excluded' => 1
                            ])
                            ->select([
                                'HostgroupsExcluded.id',
                                'Containers.name'
                            ]);
                    },
                ]
            ])
            ->group('Hostescalations.id')
            ->disableHydration();
        $indexFilter = $HostescalationsFilter->indexFilter();

        $containFilter = [
            'Hosts.name'              => '',
            'HostsExcluded.name'      => '',
            'Hostgroups.name'         => '',
            'HostgroupsExcluded.name' => ''
        ];
        if (!empty($indexFilter['Hosts.name LIKE'])) {
            $containFilter['Hosts.name'] = [
                'Hosts.name LIKE' => $indexFilter['Hosts.name LIKE']
            ];
            $query->matching('Hosts', function ($q) use ($containFilter) {
                return $q->where($containFilter['Hosts.name']);
            });
            unset($indexFilter['Hosts.name LIKE']);
        }

        if (!empty($indexFilter['HostsExcluded.name LIKE'])) {
            $containFilter['HostsExcluded.name'] = [
                'HostsExcluded.name LIKE' => $indexFilter['HostsExcluded.name LIKE']
            ];
            $query->matching('HostsExcluded', function ($q) use ($containFilter) {
                return $q->where($containFilter['HostsExcluded.name']);
            });
            unset($indexFilter['HostsExcluded.name LIKE']);

        }
        if (!empty($indexFilter['Hostgroups.name LIKE'])) {
            $containFilter['Hostgroups.name'] = [
                'Containers.name LIKE' => $indexFilter['Hostgroups.name LIKE']
            ];
            $query->matching('Hostgroups.Containers', function ($q) use ($containFilter) {
                return $q->where($containFilter['Hostgroups.name']);
            });
            unset($indexFilter['Hostgroups.name LIKE']);
        }
        if (!empty($indexFilter['HostgroupsExcluded.name LIKE'])) {
            $containFilter['HostgroupsExcluded.name'] = [
                'Containers.name LIKE' => $indexFilter['HostgroupsExcluded.name LIKE']
            ];
            $query->matching('HostgroupsExcluded.Containers', function ($q) use ($containFilter) {
                return $q->where($containFilter['HostgroupsExcluded.name']);
            });
            unset($indexFilter['HostgroupsExcluded.name LIKE']);
        }
        if(!empty($MY_RIGHTS)){
            $indexFilter['Hostescalations.container_id IN'] = $MY_RIGHTS;
        }
        $query->where($indexFilter);
        $query->order($HostescalationsFilter->getOrderForPaginator('Hostescalations.first_notification', 'asc'));
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
     * @param array|int $hosts
     * @param array|int $excluded_hosts
     * @return array
     */
    public function parseHostMembershipData($hosts = [], $excluded_hosts = []) {
        $hostmembershipData = [];
        foreach ($hosts as $host) {
            $hostmembershipData[] = [
                'id'        => $host,
                '_joinData' => [
                    'excluded' => 0
                ]
            ];
        }
        foreach ($excluded_hosts as $excluded_host) {
            $hostmembershipData[] = [
                'id'        => $excluded_host,
                '_joinData' => [
                    'excluded' => 1
                ]
            ];
        }
        return $hostmembershipData;
    }

    /**
     * @param array $hostgroups
     * @param array $excluded_hostgroups
     * @return array
     */
    public function parseHostgroupMembershipData($hostgroups = [], $excluded_hostgroups = []) {
        $hostgroupmembershipData = [];
        foreach ($hostgroups as $hostgroup) {
            $hostgroupmembershipData[] = [
                'id'        => $hostgroup,
                '_joinData' => [
                    'excluded' => 0
                ]
            ];
        }
        foreach ($excluded_hostgroups as $excluded_hostgroup) {
            $hostgroupmembershipData[] = [
                'id'        => $excluded_hostgroup,
                '_joinData' => [
                    'excluded' => 1
                ]
            ];
        }
        return $hostgroupmembershipData;
    }
    /**
     * @param null|string $uuid
     * @return array
     */
    public function getHostescalationsForExport($uuid = null) {
        $query = $this->find()
            ->contain([
                'Hosts'       =>
                    function (Query $q) {
                        return $q->enableAutoFields(false)
                            ->where([
                                'Hosts.disabled' => 0
                            ])
                            ->select(['uuid']);
                    },
                'Hostgroups'  =>
                    function (Query $q) {
                        return $q->enableAutoFields(false)
                            ->select(['uuid']);
                    },
                'Timeperiods' =>
                    function (Query $q) {
                        return $q->enableAutoFields(false)
                            ->select(['uuid']);
                    },
                'Contacts' =>
                    function (Query $q) {
                        return $q->enableAutoFields(false)
                            ->select(['uuid']);
                    },
                'Contactgroups'  =>
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
                'escalate_on_down',
                'escalate_on_unreachable'
            ]);
        if ($uuid !== null) {
            $query->where([
                'Hostescalations.uuid' => $uuid
            ]);
        }
        $query->all();
        return $query;
    }
}
