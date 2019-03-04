<?php

namespace Statusengine2Module\Model\Table;

use App\Lib\Exceptions\InvalidArgumentException;
use App\Lib\Interfaces\ServicestatusTableInterface;
use App\Lib\Traits\Cake2ResultTableTrait;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use itnovum\openITCOCKPIT\Core\ServicestatusConditions;
use itnovum\openITCOCKPIT\Core\ServicestatusFields;

/**
 * Servicestatus Model
 *
 * @property \Statusengine2Module\Model\Table\ObjectsTable|\Cake\ORM\Association\BelongsTo $Object
 *
 * @method \Statusengine2Module\Model\Entity\Servicestatus get($primaryKey, $options = [])
 * @method \Statusengine2Module\Model\Entity\Servicestatus newEntity($data = null, array $options = [])
 * @method \Statusengine2Module\Model\Entity\Servicestatus[] newEntities(array $data, array $options = [])
 * @method \Statusengine2Module\Model\Entity\Servicestatus|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Statusengine2Module\Model\Entity\Servicestatus|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Statusengine2Module\Model\Entity\Servicestatus patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Statusengine2Module\Model\Entity\Servicestatus[] patchEntities($entities, array $data, array $options = [])
 * @method \Statusengine2Module\Model\Entity\Servicestatus findOrCreate($search, callable $callback = null, $options = [])
 */
class ServicestatusTable extends Table implements ServicestatusTableInterface {

    use Cake2ResultTableTrait;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        parent::initialize($config);

        $this->setTable('nagios_servicestatus');
        $this->setDisplayField('servicestatus_id');
        $this->setPrimaryKey('servicestatus_id');

        $this->belongsTo('Objects', [
            'foreignKey' => 'service_object_id',
            'joinType'   => 'INNER',
            'className'  => 'Statusengine2Module.Objects'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator) {
        //Readonly table
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
        //Readonly table
        return $rules;
    }

    /**
     * @param null $uuid
     * @param ServicestatusFields $ServicestatusFields
     * @param null|ServicestatusConditions $ServicestatusConditions
     * @return array|bool
     */
    public function byUuidMagic($uuid, ServicestatusFields $ServicestatusFields, $ServicestatusConditions = null) {
        if ($uuid === null || empty($uuid)) {
            return [];
        }

        $select = $ServicestatusFields->getFields();

        $query = $this->find()
            ->select($select)
            ->contain([
                'Objects' => [
                    'fields' => [
                        'Objects.name2'
                    ]
                ]
            ]);

        $where = [];
        if ($ServicestatusConditions !== null) {
            if ($ServicestatusConditions->hasConditions()) {
                $where = $ServicestatusConditions->getConditions();
            }
        }

        $where['Objects.objecttype_id'] = 2;
        $findType = 'all';
        if (is_array($uuid)) {
            $where['Objects.name2 IN'] = $uuid;
        } else {
            $where['Objects.name2'] = $uuid;
            $findType = 'first';
        }

        $query->where($where);
        $query->disableHydration();

        if ($findType === 'all') {
            $result = $query->all();
            $result = $this->formatResultAsCake2($result->toArray());

            $return = [];
            foreach ($result as $record) {
                $return[$record['Servicestatus']['object']['name2']] = $record;
            }

            return $return;
        }

        if ($findType === 'first') {
            $result = $query->first();
            return $this->formatFirstResultAsCake2($result);
        }

        return [];
    }

    /**
     * @param string $uuid
     * @param ServicestatusFields $ServicestatusFields
     * @param null|ServicestatusConditions $ServicestatusConditions
     * @return array|bool
     */
    public function byUuid($uuid, ServicestatusFields $ServicestatusFields, $ServicestatusConditions = null) {
        return $this->byUuidMagic($uuid, $ServicestatusFields, $ServicestatusConditions);
    }

    /**
     * @param array $uuids
     * @param ServicestatusFields $ServicestatusFields
     * @param null $ServicestatusConditions
     * @return array|bool
     * @throws InvalidArgumentException
     */
    public function byUuids($uuids, ServicestatusFields $ServicestatusFields, $ServicestatusConditions = null) {
        if (!is_array($uuids)) {
            throw new InvalidArgumentException('$uuids need to be an array!');
        }
        return $this->byUuidMagic($uuids, $ServicestatusFields, $ServicestatusConditions);
    }
}
