<?php

namespace App\Model\Table;

use Cake\Cache\Cache;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Model;

/**
 * Systemsettings Model
 *
 * @method \App\Model\Entity\Systemsetting get($primaryKey, $options = [])
 * @method \App\Model\Entity\Systemsetting newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Systemsetting[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Systemsetting|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Systemsetting|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Systemsetting patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Systemsetting[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Systemsetting findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class SystemsettingsTable extends Table {
    use LocatorAwareTrait;


    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        parent::initialize($config);

        $this->setTable('systemsettings');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
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

        return $validator;
    }


    /**
     * @param bool $asEntity
     * @return array|Query
     */
    public function getSystemsettings($asEntity = false) {
        $query = $this->find('all');
        if ($asEntity) {
            return $query->all()->toArray();
        }
        return $query->disableHydration()->toArray();
    }

    /**
     * @return array
     */
    public function getSettings() {
        $systemsettings = $this->getSystemsettings();
        $all_systemsettings = [];
        foreach ($systemsettings as $systemsetting) {
            $all_systemsettings[$systemsetting['section']][] = $systemsetting;
        }
        // sort the list like it is in openITCOCKPIT\InitialDatabase\Systemsettings
        // it is just sorting, no deletions, no additions
        require_once OLD_APP . 'src' . DS . 'itnovum' . DS . 'openITCOCKPIT' . DS . 'InitialDatabase' . DS . 'Systemsetting.php';
        $mySytemsettings = new \itnovum\openITCOCKPIT\InitialDatabase\Systemsetting(new Model());
        $myData = $mySytemsettings->getData();
        $sortedSystemSettingsSchema = $sortedSystemSettings = [];

        foreach ($myData as $singleSetting) {
            $sortedSystemSettingsSchema[$singleSetting['Systemsetting']['section']][] = $singleSetting['Systemsetting']['key'];
        }

        foreach ($sortedSystemSettingsSchema as $sSectionName => $sSection) {
            foreach ($sSection as $sSettingOptionKey) {
                // looping through our Settings
                foreach ($all_systemsettings as $nsSectionName => $nsSection) {
                    //  debug($nsSectionName);
                    // debug($nsSection);
                    if ($sSectionName === $nsSectionName) {
                        foreach ($nsSection as $nsSectionK => $nsSettingOption) {
                            if ($sSettingOptionKey === $nsSettingOption['key']) {
                                $sortedSystemSettings[$sSectionName][] = $nsSettingOption;
                                unset($all_systemsettings[$nsSectionName][$nsSectionK]);
                                break 2;
                            }
                        }
                    }
                }
            }
        }

        // if in DB there are some options, but not in Schema, we place them at the end
        foreach ($all_systemsettings as $nsSectionName => $nsSection) {
            foreach ($nsSection as $nsSettingOption) {
                $sortedSystemSettings[$nsSectionName][] = $nsSettingOption;
            }
        }
        return $sortedSystemSettings;
    }

    /**
     * @return array
     */
    public function findAsArray() {
        $systemsettings = $this->getSettings();
        $return = [];
        if (!is_null($systemsettings)) {
            foreach ($systemsettings as $key => $value) {
                $return[$key] = [];
                foreach ($value as $systemsetting) {
                    $return[$key][$systemsetting['key']] = $systemsetting['value'];
                }
            }
        }
        return $return;
    }

    /**
     * @param string $section
     * @return array
     */
    public function findAsArraySection($section = '') {
        $query = $this->find()->where([
            'section' => $section
        ]);
        $systemsettings = $query->disableHydration()->toArray();

        $return = [];
        if (!is_null($systemsettings)) {
            foreach ($systemsettings as $values) {
                $return[$section][$values['key']] = $values['value'];
            }
        }
        return $return;
    }

    /**
     * @return mixed
     */
    public function getMasterInstanceName() {
        if (!Cache::read('systemsettings_master_instance', 'permissions')) {
            $name = $this->findAsArraySection('FRONTEND')['FRONTEND']['FRONTEND.MASTER_INSTANCE'];
            Cache::write('systemsettings_master_instance', $name, 'permissions');
        }
        return Cache::read('systemsettings_master_instance', 'permissions');
    }

    /**
     * @return mixed
     */
    public function getQueryHandlerPath() {
        if (!Cache::read('systemsettings_qh_path', 'permissions')) {
            $path = $this->findAsArraySection('MONITORING')['MONITORING']['MONITORING.QUERY_HANDLER'];
            Cache::write('systemsettings_qh_path', $path, 'permissions');
        }
        return Cache::read('systemsettings_qh_path', 'permissions');
    }
}
