<?php

/**
 * NagiosExternalcommand Fixture
 */
class NagiosExternalcommandFixture extends CakeTestFixture {

    /**
     * Fields
     *
     * @var array
     */
    public $fields = [
        'externalcommand_id' => ['type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'],
        'instance_id'        => ['type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'key' => 'index'],
        'entry_time'         => ['type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00', 'key' => 'index'],
        'command_type'       => ['type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false],
        'command_name'       => ['type' => 'string', 'null' => false, 'length' => 128, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'],
        'command_args'       => ['type' => 'string', 'null' => true, 'default' => null, 'length' => 1000, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'],
        'indexes'            => [
            'PRIMARY'     => ['column' => 'externalcommand_id', 'unique' => 1],
            'instance_id' => ['column' => 'instance_id', 'unique' => 0],
            'entry_time'  => ['column' => 'entry_time', 'unique' => 0]
        ],
        'tableParameters'    => ['charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'InnoDB', 'comment' => 'Historical record of processed external commands']
    ];

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'externalcommand_id' => 1,
            'instance_id'        => 1,
            'entry_time'         => '2017-01-27 16:30:57',
            'command_type'       => 1,
            'command_name'       => 'Lorem ipsum dolor sit amet',
            'command_args'       => 'Lorem ipsum dolor sit amet'
        ],
    ];

}
