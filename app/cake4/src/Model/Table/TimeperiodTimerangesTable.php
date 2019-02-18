<?php

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TimeperiodTimeranges Model
 *
 * @property \App\Model\Table\TimeperiodsTable|\Cake\ORM\Association\BelongsTo $Timeperiods
 *
 * @method \App\Model\Entity\TimeperiodTimerange get($primaryKey, $options = [])
 * @method \App\Model\Entity\TimeperiodTimerange newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\TimeperiodTimerange[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\TimeperiodTimerange|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\TimeperiodTimerange|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\TimeperiodTimerange patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\TimeperiodTimerange[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\TimeperiodTimerange findOrCreate($search, callable $callback = null, $options = [])
 */
class TimeperiodTimerangesTable extends Table {

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        parent::initialize($config);

        $this->setTable('timeperiod_timeranges');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Timeperiods', [
            'foreignKey' => 'timeperiod_id',
            'joinType'   => 'INNER'
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
            ->integer('day')
            ->requirePresence('day', 'create')
            ->allowEmptyString('day', false);

        $validator
            ->scalar('start')
            ->maxLength('start', 5)
            ->allowEmptyString('start', true)
            ->regex('start', '/(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]|(24:00)$/', 'Please use HH:mm format')
            ->add('start', 'custom', [
                'rule'    => function ($value, $context) {
                    if (!empty($context['data']['start']) && !empty($context['data']['end'])) {
                        return $context['data']['start'] < $context['data']['end'];
                    }
                    return true;
                },
                'message' => __('The start time must be before the end time.')
            ]);

        $validator
            ->scalar('end')
            ->maxLength('end', 5)
            ->allowEmptyString('end', true)
            ->regex('end', '/(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]|(24:00)$/', 'Please use HH:mm format')
            ->add('end', 'custom', [
                'rule'    => function ($value, $context) {
                    if (!empty($context['data']['start']) && !empty($context['data']['end'])) {
                        return $context['data']['start'] < $context['data']['end'];
                    }
                    return true;
                },
                'message' => __('The end time must be after the start time.')
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
        $rules->add($rules->existsIn(['timeperiod_id'], 'Timeperiods'));
        return $rules;
    }
}
