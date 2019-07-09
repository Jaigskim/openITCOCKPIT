<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * Proxies Model
 *
 * @method \App\Model\Entity\Proxy get($primaryKey, $options = [])
 * @method \App\Model\Entity\Proxy newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Proxy[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Proxy|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Proxy|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Proxy patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Proxy[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Proxy findOrCreate($search, callable $callback = null, $options = [])
 */
class ProxiesTable extends Table {

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        parent::initialize($config);

        $this->setTable('proxies');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
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
            ->allowEmptyString('id', null,  'create');

        $validator
            ->scalar('ipaddress')
            ->maxLength('ipaddress', 255)
            ->requirePresence('ipaddress', 'create')
            ->notEmptyString('ipaddress');

        $validator
            ->integer('port', 'This field needs to be numeric.')
            ->requirePresence('port', 'create')
            ->notEmptyString('port');

        $validator
            ->boolean('enabled')
            ->requirePresence('enabled', 'create')
            ->notEmptyString('enabled');

        return $validator;
    }

    /**
     * Get Proxy Settings
     * @return array
     */
    public function getSettings() {
        $result = $this->find()->first();
        $settings = ['ipaddress' => '', 'port' => 0, 'enabled' => false];
        if (!is_null($result)) {
            $proxy = $result->toArray();
            $settings = Hash::merge($settings, $proxy);
        }
        return $settings;
    }
}
