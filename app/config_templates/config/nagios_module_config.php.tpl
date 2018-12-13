<?php

{{STATIC_FILE_HEADER}}

$config = [
    'NagiosModule' => [
        'OBJECTTYPE_ID'   => [
            'HOST'              => 1,
            'SERVICE'           => 2,
            'HOSTGROUP'         => 3,
            'SERVICEGROUP'      => 4,
            'HOSTESCALATION'    => 5,
            'SERVICEESCALATION' => 6,
            'HOSTDEPENDENCY'    => 7,
            'SERVICEDEPENDENCY' => 8,
            'TIMEPERIOD'        => 9,
            'CONTACT'           => 10,
            'CONTACTGROUP'      => 11,
            'COMMAND'           => 12,
        ],
        'CONFIG_TYPE'     => 0,
        'INSTANCE_ID'     => 1,
        'PREFIX'          => '/opt/openitc/nagios/',
        'NAGIOS_CMD'      => 'var/rw/nagios.cmd',
        'BIN'             => 'bin/nagios',
        'ETC'             => 'etc',
        'ETC_BACKUP'      => '/opt/openitc/nagios/backup',
        'SLIDER_STEPSIZE' => {{SLIDER_STEPSIZE}},
        'SLIDER_MIN'      => {{SLIDER_MIN}},
        'SLIDER_MAX'      => {{SLIDER_MAX}},
        'CONFIGFILES'     => [
            'NAGIOS'           => [
                'FILE'         => 'etc/nagios.cfg',
                'TYPE'         => 'cfg',
                'DISPLAY_NAME' => 'Nagios (nagios.cfg)',
            ],
            'NDO2DB'           => [
                'FILE'         => 'etc/ndo2db.cfg',
                'TYPE'         => 'cfg',
                'DISPLAY_NAME' => 'NDO (ndo2db.cfg)',
            ],
            'NDOMOD'           => [
                'FILE'         => 'etc/ndomod.cfg',
                'TYPE'         => 'cfg',
                'DISPLAY_NAME' => 'NDO (ndomod.cfg)',
            ],
            'RESOURCE'         => [
                'FILE'         => 'etc/resource.cfg',
                'TYPE'         => 'cfg',
                'DISPLAY_NAME' => 'User defined macros',
            ],
            'PHPNSTA'          => [
                'FILE'         => 'bin/phpNSTA/config.php',
                'TYPE'         => 'php',
                'DISPLAY_NAME' => 'phpNSTA (config.php)',
            ],
            'NPCD'             => [
                'FILE'         => 'etc/pnp/npcd.cfg',
                'TYPE'         => 'cfg',
                'DISPLAY_NAME' => 'PNP (npcd.cfg)',
            ],
            'PROCESS_PERFDATA' => [
                'FILE'         => 'etc/pnp/process_perfdata.cfg',
                'TYPE'         => 'cfg',
                'DISPLAY_NAME' => 'PNP (process_perfdata.cfg)',
            ],
            'RRA'              => [
                'FILE'         => 'etc/pnp/rra.cfg',
                'TYPE'         => 'cfg',
                'DISPLAY_NAME' => 'RRDTools (rra.cfg)',
            ],
        ],
    ],
];
