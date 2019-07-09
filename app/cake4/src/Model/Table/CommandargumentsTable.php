<?php

namespace App\Model\Table;

use App\Lib\Traits\Cake2ResultTableTrait;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Commandarguments Model
 *
 * @property \App\Model\Table\CommandsTable|\Cake\ORM\Association\BelongsTo $Commands
 * @property \App\Model\Table\HostcommandargumentvaluesTable|\Cake\ORM\Association\HasMany $Hostcommandargumentvalues
 * @property \App\Model\Table\HosttemplatecommandargumentvaluesTable|\Cake\ORM\Association\HasMany $Hosttemplatecommandargumentvalues
 * @property \App\Model\Table\ServicecommandargumentvaluesTable|\Cake\ORM\Association\HasMany $Servicecommandargumentvalues
 * @property \App\Model\Table\ServiceeventcommandargumentvaluesTable|\Cake\ORM\Association\HasMany $Serviceeventcommandargumentvalues
 * @property \App\Model\Table\ServicetemplatecommandargumentvaluesTable|\Cake\ORM\Association\HasMany $Servicetemplatecommandargumentvalues
 * @property \App\Model\Table\ServicetemplateeventcommandargumentvaluesTable|\Cake\ORM\Association\HasMany $Servicetemplateeventcommandargumentvalues
 *
 * @method \App\Model\Entity\Commandargument get($primaryKey, $options = [])
 * @method \App\Model\Entity\Commandargument newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Commandargument[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Commandargument|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Commandargument|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Commandargument patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Commandargument[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Commandargument findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CommandargumentsTable extends Table {

    use Cake2ResultTableTrait;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        parent::initialize($config);

        $this->setTable('commandarguments');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Commands', [
            'foreignKey' => 'command_id',
            'joinType'   => 'INNER'
        ]);

        /*
        $this->hasMany('Hostcommandargumentvalues', [
            'foreignKey' => 'commandargument_id'
        ]);
        $this->hasMany('Hosttemplatecommandargumentvalues', [
            'foreignKey' => 'commandargument_id'
        ]);
        $this->hasMany('Servicecommandargumentvalues', [
            'foreignKey' => 'commandargument_id'
        ]);
        $this->hasMany('Serviceeventcommandargumentvalues', [
            'foreignKey' => 'commandargument_id'
        ]);
        $this->hasMany('Servicetemplatecommandargumentvalues', [
            'foreignKey' => 'commandargument_id'
        ]);
        $this->hasMany('Servicetemplateeventcommandargumentvalues', [
            'foreignKey' => 'commandargument_id'
        ]);
        */
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
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('name')
            ->maxLength('name', 10)
            ->requirePresence('name', 'create')
            ->allowEmptyString('name', null, false);

        $validator
            ->scalar('human_name')
            ->maxLength('human_name', 255)
            ->requirePresence('human_name', 'create')
            ->allowEmptyString('human_name', null, false);

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
        $rules->add($rules->existsIn(['command_id'], 'Commands'));

        return $rules;
    }

    /**
     * @param int $id
     * @return array
     */
    public function getByCommandId($id) {
        $query = $this->find()
            ->where(['Commandarguments.command_id' => $id])
            ->disableHydration()
            ->all();

        return $this->formatResultAsCake2($query->toArray(), false);
    }
}
