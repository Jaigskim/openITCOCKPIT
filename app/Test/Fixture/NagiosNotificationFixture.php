<?php

/**
 * NagiosNotification Fixture
 */
class NagiosNotificationFixture extends CakeTestFixture {

    /**
     * Fields
     *
     * @var array
     */
    public $fields = [
        'notification_id'     => ['type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'],
        'instance_id'         => ['type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false],
        'notification_type'   => ['type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false],
        'notification_reason' => ['type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false],
        'object_id'           => ['type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index'],
        'start_time'          => ['type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00', 'key' => 'primary'],
        'start_time_usec'     => ['type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false],
        'end_time'            => ['type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'],
        'end_time_usec'       => ['type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false],
        'state'               => ['type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false],
        'output'              => ['type' => 'string', 'null' => false, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'],
        'long_output'         => ['type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_swedish_ci', 'charset' => 'utf8'],
        'escalated'           => ['type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false],
        'contacts_notified'   => ['type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false],
        'indexes'             => [
            'PRIMARY'    => ['column' => ['notification_id', 'start_time'], 'unique' => 1],
            'top10'      => ['column' => ['object_id', 'start_time', 'contacts_notified'], 'unique' => 0],
            'start_time' => ['column' => 'start_time', 'unique' => 0]
        ],
        'tableParameters'     => ['charset' => 'utf8', 'collate' => 'utf8_swedish_ci', 'engine' => 'InnoDB', 'comment' => 'Historical record of host and service notifications']
    ];

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'notification_id'     => 1,
            'instance_id'         => 1,
            'notification_type'   => 1,
            'notification_reason' => 1,
            'object_id'           => 1,
            'start_time'          => '2017-01-27 16:51:10',
            'start_time_usec'     => 1,
            'end_time'            => '2017-01-27 16:51:10',
            'end_time_usec'       => 1,
            'state'               => 1,
            'output'              => 'Lorem ipsum dolor sit amet',
            'long_output'         => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'escalated'           => 1,
            'contacts_notified'   => 1
        ],
    ];

}
