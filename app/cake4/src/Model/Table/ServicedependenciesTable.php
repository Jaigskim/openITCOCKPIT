<?php

namespace App\Model\Table;

use App\Lib\Traits\Cake2ResultTableTrait;
use App\Lib\Traits\CustomValidationTrait;
use App\Lib\Traits\PaginationAndScrollIndexTrait;
use Cake\Database\Expression\Comparison;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use itnovum\openITCOCKPIT\Filter\ServicedependenciesFilter;

/**
 * Servicedependencies Model
 *
 * @property \App\Model\Table\ContainersTable|\Cake\ORM\Association\BelongsTo $Containers
 * @property \App\Model\Table\ServicedependenciesTable|\Cake\ORM\Association\HasMany $Services
 * @property \App\Model\Table\ServicedependenciesTable|\Cake\ORM\Association\HasMany $Servicegroups
 *
 * @method \App\Model\Entity\Servicedependency get($primaryKey, $options = [])
 * @method \App\Model\Entity\Servicedependency newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Servicedependency[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Servicedependency|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Servicedependency|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Servicedependency patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Servicedependency[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Servicedependency findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ServicedependenciesTable extends Table {

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

        $this->setTable('servicedependencies');
        $this->setPrimaryKey('id');

        $this->belongsTo('Containers', [
            'foreignKey' => 'container_id',
            'joinType'   => 'INNER'
        ]);
        $this->belongsTo('Timeperiods', [
            'className'  => 'Timeperiods',
            'foreignKey' => 'timeperiod_id',
            'joinType'   => 'LEFT'
        ]);

        $this->belongsToMany('Services', [
            'className'    => 'Services',
            'through'      => 'ServicedependenciesServiceMemberships',
            'saveStrategy' => 'replace'
        ]);
        $this->belongsToMany('ServicesDependent', [
            'className'        => 'Services',
            'through'          => 'ServicedependenciesServiceMemberships',
            'targetForeignKey' => 'service_id',
            'saveStrategy'     => 'replace'
        ]);
        $this->belongsToMany('Servicegroups', [
            'through'      => 'ServicedependenciesServicegroupMemberships',
            'saveStrategy' => 'replace'
        ]);
        $this->belongsToMany('ServicegroupsDependent', [
            'className'        => 'Servicegroups',
            'through'          => 'ServicedependenciesServicegroupMemberships',
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
            ->requirePresence('services', true, __('You have to choose at least one service.'))
            ->allowEmptyString('services', false)
            ->multipleOptions('services', [
                'min' => 1
            ], __('You have to choose at least one service.'));

        $validator
            ->requirePresence('services_dependent', true, __('You have to choose at least one dependent service.'))
            ->allowEmptyString('services_dependent', false)
            ->multipleOptions('services_dependent', [
                'min' => 1
            ], __('You have to choose at least one dependent service.'));

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
     * @param int $id
     * @return bool
     */
    public function existsById($id) {
        return $this->exists(['Servicedependencies.id' => $id]);
    }

    /**
     * @param ServicedependenciesFilter $ServicedependenciesFilter
     * @param null $PaginateOMat
     * @param array $MY_RIGHTS
     * @return array
     */
    public function getServicedependenciesIndex(ServicedependenciesFilter $ServicedependenciesFilter, $PaginateOMat = null, $MY_RIGHTS = []) {
        $query = $this->find('all')
            ->contain([
                'Timeperiods'       => function (Query $q) {
                    return $q->enableAutoFields(false)
                        ->select([
                            'Timeperiods.id',
                            'Timeperiods.name'
                        ]);
                },
                'Services'          => function (Query $q) {
                    return $q->enableAutoFields(false)
                        ->where([
                            'ServicedependenciesServiceMemberships.dependent' => 0
                        ])
                        ->innerJoinWith('Servicetemplates')
                        ->innerJoinWith('Hosts')
                        ->select([
                            'Services.id',
                            'servicename' => $q->newExpr('CONCAT(Hosts.name, "/", IF(Services.name IS NULL, Servicetemplates.name, Services.name))'),
                            'Services.disabled'
                        ]);
                },
                'ServicesDependent' => function (Query $q) {
                    return $q->enableAutoFields(false)
                        ->where([
                            'ServicedependenciesServiceMemberships.dependent' => 1
                        ])
                        ->innerJoinWith('Servicetemplates')
                        ->innerJoinWith('Hosts')
                        ->select([
                            'ServicesDependent.id',
                            'servicename' => $q->newExpr('CONCAT(Hosts.name, "/", IF(ServicesDependent.name IS NULL, Servicetemplates.name, ServicesDependent.name))'),
                            'ServicesDependent.disabled'
                        ]);
                },

                'Servicegroups'          => [
                    'Containers' => function (Query $q) {
                        return $q->enableAutoFields(false)
                            ->where([
                                'ServicedependenciesServicegroupMemberships.dependent' => 0
                            ])
                            ->select([
                                'Servicegroups.id',
                                'Containers.name'
                            ]);
                    },
                ],
                'ServicegroupsDependent' => [
                    'Containers' => function (Query $q) {
                        return $q->enableAutoFields(false)
                            ->where([
                                'ServicedependenciesServicegroupMemberships.dependent' => 1
                            ])
                            ->select([
                                'ServicegroupsDependent.id',
                                'Containers.name'
                            ]);
                    },
                ]
            ])
            ->group('Servicedependencies.id')
            ->disableHydration();

        $indexFilter = $ServicedependenciesFilter->indexFilter();
        $containFilter = [
            'Servicegroups.name'         => '',
            'ServicegroupsDependent.name' => ''
        ];

        if (!empty($indexFilter['Services.servicename LIKE'])) {
            $query->innerJoinWith('Services', function ($q) use ($indexFilter) {
                return $q->innerJoinWith('Hosts')
                    ->innerJoinWith('Servicetemplates');
            });
            $where = new Comparison(
                'CONCAT(Hosts.name, "/", IF(Services.name IS NULL, Servicetemplates.name, Services.name))',
                $indexFilter['Services.servicename LIKE'],
                'string',
                'LIKE'
            );
            $query->where([
                'ServicedependenciesServiceMemberships.dependent' => 0,
                $where
            ]);
        }

        unset($indexFilter['Services.servicename LIKE']);

        if (!empty($indexFilter['ServicesDependent.servicename LIKE'])) {
            $query->innerJoinWith('ServicesDependent', function ($q) use ($indexFilter) {
                return $q->innerJoinWith('Hosts')
                    ->innerJoinWith('Servicetemplates');
            });
            $where = new Comparison(
                'CONCAT(Hosts.name, "/", IF(ServicesDependent.name IS NULL, Servicetemplates.name, ServicesDependent.name))',
                $indexFilter['ServicesDependent.servicename LIKE'],
                'string',
                'LIKE'
            );
            $query->where([
                'ServicedependenciesServiceMemberships.dependent' => 1,
                $where
            ]);
        }

        unset($indexFilter['ServicesDependent.servicename LIKE']);


        if (!empty($indexFilter['Servicegroups.name LIKE'])) {
            $containFilter['Servicegroups.name'] = [
                'Containers.name LIKE' => $indexFilter['Servicegroups.name LIKE']
            ];
            $query->innerJoinWith('Servicegroups.Containers', function ($q) use ($containFilter) {
                return $q->where([
                    'ServicedependenciesServicegroupMemberships.dependent' => 0,
                    $containFilter['Servicegroups.name']
                ]);
            });
            unset($indexFilter['Servicegroups.name LIKE']);
        }
        if (!empty($indexFilter['ServicegroupsDependent.name LIKE'])) {
            $containFilter['ServicegroupsDependent.name'] = [
                'Containers.name LIKE' => $indexFilter['ServicegroupsDependent.name LIKE']
            ];
            $query->innerJoinWith('ServicegroupsDependent.Containers', function ($q) use ($containFilter) {
                return $q->where([
                    'ServicedependenciesServicegroupMemberships.dependent' => 1,
                    $containFilter['ServicegroupsDependent.name']
                ]);
            });
            unset($indexFilter['ServicegroupsDependent.name LIKE']);
        }

        if (!empty($MY_RIGHTS)) {
            $indexFilter['Servicedependencies.container_id IN'] = $MY_RIGHTS;
        }
        $query->where($indexFilter);
        $query->order($ServicedependenciesFilter->getOrderForPaginator('Servicedependencies.id', 'asc'));

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
     * @param array|int $dependent_services
     * @return array
     */
    public function parseServiceMembershipData($services = [], $dependent_services = []) {
        $servicemembershipData = [];
        foreach ($services as $service) {
            $servicemembershipData[] = [
                'id'        => $service,
                '_joinData' => [
                    'dependent' => 0
                ]
            ];
        }
        foreach ($dependent_services as $dependent_service) {
            $servicemembershipData[] = [
                'id'        => $dependent_service,
                '_joinData' => [
                    'dependent' => 1
                ]
            ];
        }
        return $servicemembershipData;
    }

    /**
     * @param array $servicegroups
     * @param array $dependent_servicegroups
     * @return array
     */
    public function parseServicegroupMembershipData($servicegroups = [], $dependent_servicegroups = []) {
        $servicegroupmembershipData = [];
        foreach ($servicegroups as $servicegroup) {
            $servicegroupmembershipData[] = [
                'id'        => $servicegroup,
                '_joinData' => [
                    'dependent' => 0
                ]
            ];
        }
        foreach ($dependent_servicegroups as $dependent_servicegroup) {
            $servicegroupmembershipData[] = [
                'id'        => $dependent_servicegroup,
                '_joinData' => [
                    'dependent' => 1
                ]
            ];
        }
        return $servicegroupmembershipData;
    }

    /**
     * @param null|string $uuid
     * @return array
     */
    public function getServicedependenciesForExport($uuid = null) {
        $query = $this->find()
            ->contain([
                'services'      =>
                    function (Query $q) {
                        return $q->enableAutoFields(false)
                            ->where([
                                'Services.disabled' => 0
                            ])
                            ->select(['uuid']);
                    },
                'servicegroups' =>
                    function (Query $q) {
                        return $q->enableAutoFields(false)
                            ->select(['uuid']);
                    },
                'Timeperiods'   =>
                    function (Query $q) {
                        return $q->enableAutoFields(false)
                            ->select(['uuid']);
                    }
            ])
            ->select([
                'id',
                'uuid',
                'inherits_parent',
                'execution_fail_on_ok',
                'execution_fail_on_warning',
                'execution_fail_on_critical',
                'execution_fail_on_unknown',
                'execution_fail_on_pending',
                'execution_none',
                'notification_fail_on_ok',
                'notification_fail_on_warning',
                'notification_fail_on_critical',
                'notification_fail_on_unknown',
                'notification_fail_on_pending',
                'notification_none'
            ]);
        if ($uuid !== null) {
            $query->where([
                'Servicedependencies.uuid' => $uuid
            ]);
        }

        $query->all();

        return $query;
    }
}
