<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ServicetemplatesToServicegroups Model
 *
 * @property \App\Model\Table\ServicetemplatesTable&\Cake\ORM\Association\BelongsTo $Servicetemplates
 * @property \App\Model\Table\ServicegroupsTable&\Cake\ORM\Association\BelongsTo $Servicegroups
 *
 * @method \App\Model\Entity\ServicetemplatesToServicegroup get($primaryKey, $options = [])
 * @method \App\Model\Entity\ServicetemplatesToServicegroup newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\ServicetemplatesToServicegroup[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ServicetemplatesToServicegroup|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ServicetemplatesToServicegroup saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ServicetemplatesToServicegroup patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ServicetemplatesToServicegroup[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\ServicetemplatesToServicegroup findOrCreate($search, callable $callback = null, $options = [])
 */
class ServicetemplatesToServicegroupsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('servicetemplates_to_servicegroups');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Servicetemplates', [
            'foreignKey' => 'servicetemplate_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Servicegroups', [
            'foreignKey' => 'servicegroup_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['servicetemplate_id'], 'Servicetemplates'));
        $rules->add($rules->existsIn(['servicegroup_id'], 'Servicegroups'));

        return $rules;
    }
}
