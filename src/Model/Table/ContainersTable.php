<?php

namespace App\Model\Table;

use Cake\Cache\Cache;
use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\ForbiddenException;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use itnovum\openITCOCKPIT\Core\ContainerNestedSet;

/**
 * Containers Model
 *
 * @property \App\Model\Table\ContainertypesTable|\Cake\ORM\Association\BelongsTo $Containertypes
 * @property \App\Model\Table\ContainersTable|\Cake\ORM\Association\BelongsTo $ParentContainers
 * @property \App\Model\Table\AutomapsTable|\Cake\ORM\Association\HasMany $Automaps
 * @property \App\Model\Table\AutoreportsTable|\Cake\ORM\Association\HasMany $Autoreports
 * @property \App\Model\Table\CalendarsTable|\Cake\ORM\Association\HasMany $Calendars
 * @property \App\Model\Table\ChangelogsToContainersTable|\Cake\ORM\Association\HasMany $ChangelogsToContainers
 * @property \App\Model\Table\ContactgroupsTable|\Cake\ORM\Association\HasMany $Contactgroups
 * @property \App\Model\Table\ContactsToContainersTable|\Cake\ORM\Association\HasMany $ContactsToContainers
 * @property \App\Model\Table\ContainersTable|\Cake\ORM\Association\HasMany $ChildContainers
 * @property \App\Model\Table\GrafanaUserdashboardsTable|\Cake\ORM\Association\HasMany $GrafanaUserdashboards
 * @property \App\Model\Table\HostdependenciesTable|\Cake\ORM\Association\HasMany $Hostdependencies
 * @property \App\Model\Table\HostescalationsTable|\Cake\ORM\Association\HasMany $Hostescalations
 * @property \App\Model\Table\HostgroupsTable|\Cake\ORM\Association\HasMany $Hostgroups
 * @property \App\Model\Table\HostsTable|\Cake\ORM\Association\HasMany $Hosts
 * @property \App\Model\Table\HostsToContainersTable|\Cake\ORM\Association\HasMany $HostsToContainers
 * @property \App\Model\Table\HosttemplatesTable|\Cake\ORM\Association\HasMany $Hosttemplates
 * @property \App\Model\Table\IdoitObjectsTable|\Cake\ORM\Association\HasMany $IdoitObjects
 * @property \App\Model\Table\IdoitObjecttypesTable|\Cake\ORM\Association\HasMany $IdoitObjecttypes
 * @property \App\Model\Table\InstantreportsTable|\Cake\ORM\Association\HasMany $Instantreports
 * @property \App\Model\Table\LocationsTable|\Cake\ORM\Association\HasMany $Locations
 * @property \App\Model\Table\MapUploadsTable|\Cake\ORM\Association\HasMany $MapUploads
 * @property \App\Model\Table\MapsToContainersTable|\Cake\ORM\Association\HasMany $MapsToContainers
 * @property \App\Model\Table\MkagentsTable|\Cake\ORM\Association\HasMany $Mkagents
 * @property \App\Model\Table\NmapConfigurationsTable|\Cake\ORM\Association\HasMany $NmapConfigurations
 * @property \App\Model\Table\RotationsToContainersTable|\Cake\ORM\Association\HasMany $RotationsToContainers
 * @property \App\Model\Table\SatellitesTable|\Cake\ORM\Association\HasMany $Satellites
 * @property \App\Model\Table\ServicedependenciesTable|\Cake\ORM\Association\HasMany $Servicedependencies
 * @property \App\Model\Table\ServiceescalationsTable|\Cake\ORM\Association\HasMany $Serviceescalations
 * @property \App\Model\Table\ServicegroupsTable|\Cake\ORM\Association\HasMany $Servicegroups
 * @property \App\Model\Table\ServicetemplategroupsTable|\Cake\ORM\Association\HasMany $Servicetemplategroups
 * @property \App\Model\Table\ServicetemplatesTable|\Cake\ORM\Association\HasMany $Servicetemplates
 * @property \App\Model\Table\TenantsTable|\Cake\ORM\Association\HasMany $Tenants
 * @property \App\Model\Table\TimeperiodsTable|\Cake\ORM\Association\HasMany $Timeperiods
 * @property \App\Model\Table\UsersToContainersTable|\Cake\ORM\Association\HasMany $UsersToContainers
 *
 * @method \App\Model\Entity\Container get($primaryKey, $options = [])
 * @method \App\Model\Entity\Container newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Container[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Container|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Container|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Container patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Container[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Container findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TreeBehavior
 */
class ContainersTable extends Table {

    /**
     * @var null|array
     */
    private $containerCache = null;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void {
        parent::initialize($config);

        $this->setTable('containers');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Tree');

        $this->hasMany('Contactgroups', [
            'foreignKey'       => 'container_id',
            'cascadeCallbacks' => true
        ])->setDependent(true);

        $this->hasMany('Hostgroups', [
            'foreignKey'       => 'container_id',
            'cascadeCallbacks' => true
        ])->setDependent(true);

        $this->hasMany('Servicegroups', [
            'foreignKey'       => 'container_id',
            'cascadeCallbacks' => true
        ])->setDependent(true);

        $this->hasMany('Servicetemplategroups', [
            'foreignKey'       => 'container_id',
            'cascadeCallbacks' => true
        ])->setDependent(true);

        $this->hasMany('Locations', [
            'foreignKey'       => 'container_id',
            'cascadeCallbacks' => true
        ])->setDependent(true);

        $this->hasMany('MapsToContainers', [
            'foreignKey'       => 'container_id',
            'cascadeCallbacks' => true
        ]);

        //$this->belongsTo('ParentContainers', [
        //    'className' => 'Containers',
        //    'foreignKey' => 'parent_id'
        //]);

        /*
        $this->hasMany('Automaps', [
            'foreignKey' => 'container_id'
        ]);
        $this->hasMany('Autoreports', [
            'foreignKey' => 'container_id'
        ]);
        $this->hasMany('Calendars', [
            'foreignKey' => 'container_id'
        ]);
        $this->hasMany('ChangelogsToContainers', [
            'foreignKey' => 'container_id'
        ]);

        $this->hasMany('ContactsToContainers', [
            'foreignKey' => 'container_id'
        ]);
        $this->hasMany('ChildContainers', [
            'className' => 'Containers',
            'foreignKey' => 'parent_id'
        ]);
        $this->hasMany('GrafanaUserdashboards', [
            'foreignKey' => 'container_id'
        ]);
        $this->hasMany('Hostdependencies', [
            'foreignKey' => 'container_id'
        ]);
        $this->hasMany('Hostescalations', [
            'foreignKey' => 'container_id'
        ]);
        $this->hasMany('Hostgroups', [
            'foreignKey' => 'container_id'
        ]);
        $this->hasMany('Hosts', [
            'foreignKey' => 'container_id'
        ]);
        $this->hasMany('HostsToContainers', [
            'foreignKey' => 'container_id'
        ]);
        $this->hasMany('Hosttemplates', [
            'foreignKey' => 'container_id'
        ]);
        $this->hasMany('IdoitObjects', [
            'foreignKey' => 'container_id'
        ]);
        $this->hasMany('IdoitObjecttypes', [
            'foreignKey' => 'container_id'
        ]);
        $this->hasMany('Instantreports', [
            'foreignKey' => 'container_id'
        ]);
        $this->hasMany('Locations', [
            'foreignKey' => 'container_id'
        ]);
        $this->hasMany('MapUploads', [
            'foreignKey' => 'container_id'
        ]);
        $this->hasMany('Mkagents', [
            'foreignKey' => 'container_id'
        ]);
        $this->hasMany('NmapConfigurations', [
            'foreignKey' => 'container_id'
        ]);
        $this->hasMany('RotationsToContainers', [
            'foreignKey' => 'container_id'
        ]);
        $this->hasMany('Satellites', [
            'foreignKey' => 'container_id'
        ]);
        $this->hasMany('Servicedependencies', [
            'foreignKey' => 'container_id'
        ]);
        $this->hasMany('Serviceescalations', [
            'foreignKey' => 'container_id'
        ]);
        $this->hasMany('Servicetemplategroups', [
            'foreignKey' => 'container_id'
        ]);
        $this->hasMany('Servicetemplates', [
            'foreignKey' => 'container_id'
        ]);
        $this->hasMany('Tenants', [
            'foreignKey' => 'container_id'
        ]);
        $this->hasMany('Timeperiods', [
            'foreignKey' => 'container_id'
        ]);
        $this->hasMany('UsersToContainers', [
            'foreignKey' => 'container_id'
        ]);
        */
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->allowEmptyString('name', __('This field cannot be left blank.'), false)
            ->add('name', 'custom', [
                'rule'    => function ($value, $context) {
                    if (isset($context['data']['containertype_id']) && $context['data']['containertype_id'] == CT_TENANT) {
                        if (isset($context['data']['id'])) {
                            //In post data is an ID given
                            //May be an update of a Tenant container
                            $count = $this->find()
                                ->where(function (QueryExpression $exp) use ($context) {
                                    return $exp
                                        ->eq('Containers.name', $context['data']['name'])
                                        ->eq('Containers.containertype_id', CT_TENANT)
                                        ->notEq('Containers.id', $context['data']['id']);
                                })
                                ->count();
                        } else {
                            //No ID given in POST data. Check if a tenant with this name already exists
                            $count = $this->find()
                                ->where([
                                    'Containers.name'             => $context['data']['name'],
                                    'Containers.containertype_id' => CT_TENANT
                                ])
                                ->count();
                        }


                        return $count === 0;
                    }

                    return true;
                },
                'message' => __('This name already exists.')
            ]);

        $validator
            ->scalar('parent_id')
            ->numeric('parent_id')
            ->greaterThan('parent_id', 0)
            ->allowEmptyString('parent_id', __('This field cannot be left blank.'), false);

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker {
        //$rules->add($rules->existsIn(['parent_id'], 'ParentContainers'));

        return $rules;
    }


    /**
     * @param int|array $ids
     * @param array $options
     * @param array $valide_types
     * @return array
     *
     * ### Options
     * - `delimiter`   The delimiter for the path (default /)
     * - `order`       Order of the returned array asc|desc (default asc)
     */
    private function path($ids, $options = [], $valide_types = [CT_GLOBAL, CT_TENANT, CT_LOCATION, CT_NODE]) {
        $_options = [
            'delimiter'    => '/',
            'valide_types' => $valide_types,
            'order'        => 'asc',
        ];
        $options = Hash::merge($_options, $options);

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $node = $this->find()
            ->where(['id IN ' => $ids])
            ->disableHydration()
            ->all()
            ->toArray();

        $paths = [];
        foreach ($node as $container) {
            $containerTypeId = (int)$container['containertype_id'];
            if (in_array($containerTypeId, $options['valide_types'], true)) {
                $paths[$container['id']] = '/' . $this->treePath($container['id'], $options['delimiter']);
            }
        }

        if ($options['order'] === 'asc') {
            asort($paths);
        }

        if ($options['order'] === 'desc') {
            arsort($paths);
        }

        return $paths;
    }

    /**
     * Returns tha path to a single node in the tree
     *
     * @param integer $id of the container
     * @param string $delimiter (default /)
     *
     * @return string with the path to the container
     */
    public function treePath($id = null, $delimiter = '/') {
        try {
            $containerNames = [];
            $tree = $this->find('path', ['for' => $id])
                ->disableHydration()
                ->toArray();

            foreach ($tree as $node) {
                $containerNames[] = $node['name'];
            }

            return implode($delimiter, $containerNames);

        } catch (RecordNotFoundException $e) {
            return '';
        }
    }


    /**
     * @param int|array $id
     * @param int $ObjectsByConstancName
     * @param array $options
     * @param bool $hasRootPrivileges
     * @param array $exclude Array of container tyoes which gets excluded from result
     * @return array
     *
     * Returns:
     * [
     *     1 => '/root',
     *     2 => '/root/tenant'
     * ]
     *
     * ### Options
     * - `delimiter`   The delimiter for the path (default /)
     * - `order`       Order of the returned array asc|desc (default asc)
     *
     * @throws \Exception
     */
    public function easyPath($id, $ObjectsByConstancName, $options = [], $hasRootPrivileges = false, $exclude = []) {
        if ($this->containerCache === null) {
            $query = $this->find('all')
                ->disableHydration()
                ->toArray();

            $this->containerCache = $query;
        }

        if ($hasRootPrivileges == false) {
            if (is_array($id)) {
                // User has no root privileges so we need to delete the root container
                $id = $this->removeRootContainer($id);
            } else {
                if ($id == ROOT_CONTAINER) {
                    throw new ForbiddenException(__('You need root privileges'));
                }
            }
        }

        if (empty($ObjectsByConstancName)) {
            return [];
        }

        //Container implementation in PHP but fast
        $ContainerNestedSet = ContainerNestedSet::fromCake4($this->containerCache, $hasRootPrivileges);
        return $ContainerNestedSet->easyPath($id, $ObjectsByConstancName, $exclude);

        //Plain ORM but bad performance
        //$Constants = new Constants();
        //return $this->path($id, $options, $Constants->containerProperties($ObjectsByConstancName, $exclude));
    }

    /**
     * @param int|array $containerIds
     * @param bool $resolveRoot
     * @param array $includeContainerTypes
     * @return array
     */
    public function resolveChildrenOfContainerIds($containerIds, $resolveRoot = false, $includeContainerTypes = []) {
        if (!is_array($containerIds)) {
            $containerIds = [$containerIds];
        }

        $containerIds = array_unique($containerIds);
        $result = [ROOT_CONTAINER];
        foreach ($containerIds as $containerId) {
            $containerId = (int)$containerId;
            if ($containerId === ROOT_CONTAINER && $resolveRoot === false) {
                continue;
            }

            $cacheKey = 'TreeComponentResolveChildrenOfContainerIds:' . $containerId . ':false';
            if ($resolveRoot) {
                $cacheKey = 'TreeComponentResolveChildrenOfContainerIds:' . $containerId . ':true';
            }

            $tmpResult = Cache::remember($cacheKey, function () use ($containerId) {
                try {
                    $query = $this->find('children', [
                        'for' => $containerId
                    ])->disableHydration()->select(['id', 'containertype_id'])->all();
                    return $query->toArray();
                } catch (RecordNotFoundException $e) {
                    return [];
                }
            }, 'migration');

            if (!empty($includeContainerTypes)) {
                $tmpResult = Hash::extract($tmpResult, '{n}[containertype_id=/^(' . implode('|', $includeContainerTypes) . ')$/].id');
            } else {
                $tmpResult = Hash::extract($tmpResult, '{n}.id');
            }
            $result = array_merge($result, $tmpResult);
            $result[] = $containerId;
        }

        return array_unique($result);
    }

    /**
     * Remove the ROOT_CONTAINER from a given array with container ids as value
     *
     * @param array $containerIds
     *
     * @return array
     */
    public function removeRootContainer($containerIds) {
        $result = [];
        foreach ($containerIds as $containerId) {
            $containerId = (int)$containerId;
            if ($containerId !== ROOT_CONTAINER) {
                $result[] = $containerId;
            }
        }

        return $result;
    }

    public function getPathByIdAndCacheResult($id, $cacheKey) {
        $cacheKey = sprintf('%s:%s', $cacheKey, $id);
        $path = Cache::remember($cacheKey, function () use ($id) {
            try {
                $path = $this->find('path', ['for' => $id])
                    ->disableHydration()
                    ->all()
                    ->toArray();
                return $path;
            } catch (RecordNotFoundException $e) {
                return [];
            }
        }, 'migration');
        return $path;
    }

    /**
     * @param int $id
     * @param string $delimiter
     * @return string
     */
    public function getPathByIdAsString($id, $delimiter = '/') {
        $path = $this->find('path', ['for' => $id])
            ->disableHydration()
            ->all()
            ->toArray();
        $nodes = [];
        foreach ($path as $node) {
            $nodes[] = $node['name'];
        }

        return $delimiter . implode($delimiter, $nodes);
    }

    public function getAllContainerByParentId($parentContainerId) {
        if (!is_array($parentContainerId)) {
            $parentContainerId = [$parentContainerId];
        }

        $containers = $this->find()
            ->where(['Containers.parent_id IN' => $parentContainerId])
            ->disableHydration()
            ->all()
            ->toArray();

        if ($containers === null) {
            return [];
        }

        return $containers;
    }

    /**
     * @param int $id
     * @return bool|mixed
     * @link https://book.cakephp.org/3.0/en/orm/behaviors/tree.html#deleting-nodes
     */
    public function deleteContainerById($id) {
        $container = $this->get($id);
        return $this->delete($container);
    }

    /**
     * @param $id
     * @param bool $threaded
     * @return array
     */
    public function getChildren($id, $threaded = false) {
        try {
            $query = $this->find('children', [
                'for' => $id
            ]);

            if ($threaded) {
                $query->find('threaded');
            }

            return $query->disableHydration()
                ->all()
                ->toArray();

        } catch (RecordNotFoundException $e) {
            return [];
        }
    }

    /**
     * @param $browserAsNest
     * @param $MY_RIGHTS
     * @param $containerTypes
     * @return array
     */
    public function getFirstContainers($browserAsNest, $MY_RIGHTS, $containerTypes) {
        $containers = [];
        foreach ($browserAsNest as $container) {
            if (in_array($container['id'], $MY_RIGHTS) && in_array($container['containertype_id'], $containerTypes)) {
                $containers[] = $container;
                continue;
            }

            foreach ($container['children'] as $childContainer) {
                $results = $this->getFirstContainers([$childContainer], $MY_RIGHTS, $containerTypes);
                foreach ($results as $result) {
                    $containers[] = $result;
                }
            }
        }

        return $containers;
    }

    /**
     * !!! ONLY USE THIS FOR DISPLAY PURPOSE !!!
     *
     * @param int $hostPrimaryContainerId
     * @param array $hostSharingContainerIds
     * @param array $MY_RIGHTS
     * @return null|array
     */
    public function getFakePrimaryContainerForHostEditDisplay($hostPrimaryContainerId, $hostSharingContainerIdsParam, $MY_RIGHTS) {
        $hostSharingContainerIds = [];
        foreach ($hostSharingContainerIdsParam as $hostSharingContainerId) {
            $hostSharingContainerId = (int)$hostSharingContainerId;
            $hostSharingContainerIds[$hostSharingContainerId] = $hostSharingContainerId;
        }


        $containerIdUserHasPermissionsOn = null;
        foreach ($MY_RIGHTS as $MY_RIGHT_CONTAINER_ID) {
            if (isset($hostSharingContainerIds[$MY_RIGHT_CONTAINER_ID])) {
                //Get the first container id that the user has permissions for
                $containerIdUserHasPermissionsOn = $hostSharingContainerIds[$MY_RIGHT_CONTAINER_ID];
                break;
            }
        }

        //get the name of the container
        if ($containerIdUserHasPermissionsOn !== null) {
            $path = '/' . $this->treePath($containerIdUserHasPermissionsOn);
            // $path contains a sharing container.
            // to let the select box display a shard container as read only, we set the primaryContainerId
            // as for the $path
            // THIS IS ONLY USED FOR DISPLAY PURPOSE
            return [
                $hostPrimaryContainerId => $path
            ];
        }

        return null;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function existsById($id) {
        return $this->exists(['Containers.id' => $id]);
    }


    /**
     * @param $id
     * @param array $MY_RIGHTS
     * @return array
     */
    public function getContainerById($id, $MY_RIGHTS = []) {
        $query = $this->find()
            ->select([
                'Containers.id',
                'Containers.name',
                'Containers.containertype_id',
                'Containers.lft',
                'Containers.rght'
            ])
            ->where([
                'Containers.id' => $id
            ]);

        if (!empty($MY_RIGHTS)) {
            $query->andWhere([
                'Containers.id IN' => $MY_RIGHTS
            ]);
        }
        $result = $query->first();
        if (empty($result)) {
            return [];
        }
        return $result->toArray();
    }


    /**
     * @param $containerId
     * @return array
     */
    public function getContainerWithAllChildren($containerId) {
        $containersMap = [
            'nodes' => [],
            'edges' => []
        ];
        $query = $this->find('children', ['for' => $containerId]);

        $query->select([
            'Containers.id',
            'Containers.parent_id',
            'Containers.name',
            'Containers.containertype_id',
            'Containers.lft',
            'Containers.rght'
        ])
            ->disableHydration();
        $childrenContainers = $query->toArray();

        return $childrenContainers;
        //debug($childrenContainers);
        /**
         * 'CT_GLOBAL'               => 1,
         * 'CT_TENANT'               => 2,
         * 'CT_LOCATION'             => 3,
         * 'CT_NODE'                 => 5,
         * 'CT_CONTACTGROUP'         => 6,
         * 'CT_HOSTGROUP'            => 7,
         * 'CT_SERVICEGROUP'         => 8,
         * 'CT_SERVICETEMPLATEGROUP' => 9,
         */

        foreach ($childrenContainers as $childContainer) {
            switch ($childContainer['containertype_id']) {
                case CT_TENANT:
                case CT_LOCATION:
                case CT_NODE:
                    break;
                case CT_CONTACTGROUP:
                    break;
                case CT_HOSTGROUP:
                    break;
                case CT_SERVICEGROUP:
                    break;
                case CT_SERVICETEMPLATEGROUP:
                    break;
            }

        }
        debug($childrenContainers);
        die();
        foreach ($childrenContainers as $child) {
            echo "<br />{$child->name} has " . ' --- ' . $child->containertype_id . ' ->>>> ' . count($child->children) . " direct children<br />";
            debug($child->children);
        }

        return $containersMap;
    }
}
