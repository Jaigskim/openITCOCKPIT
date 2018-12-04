<?php
// Copyright (C) <2018>  <it-novum GmbH>
//
// This file is dual licensed
//
// 1.
//  This program is free software: you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation, version 3 of the License.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
// 2.
//  If you purchased an openITCOCKPIT Enterprise Edition you can use this file
//  under the terms of the openITCOCKPIT Enterprise Edition license agreement.
//  License agreement and license key will be shipped with the order
//  confirmation.

namespace itnovum\openITCOCKPIT\ConfigGenerator;


class GraphiteWeb extends ConfigGenerator implements ConfigInterface {

    protected $templateDir = 'config';

    protected $template = 'graphite.php.tpl';


    /**
     * @see self::__construct()
     * @var string
     */
    protected $outfile = '';

    /**
     * @var string
     */
    protected $commentChar = '//';

    protected $defaults = [
        'string' => [
            'graphite_web_host' => '127.0.0.1',
            'graphite_prefix'   => 'openitcockpit'
        ],
        'int'    => [
            'graphite_web_port' => 8888
        ],
        'bool'   => [
            'use_https' => false,
            'use_proxy' => false
        ]
    ];

    protected $dbKey = 'GraphiteWeb';

    public function __construct() {
        $this->outfile = APP . 'Config' . DS . 'graphite.php';
    }

    /**
     * @param array $data
     * @return array|bool|true
     */
    public function customValidationRules($data) {
        return true;
    }

    /**
     * @return string
     */
    public function getAngularDirective() {
        return 'graphite-web-cfg';
    }

    /**
     * @param string $key
     * @return string
     */
    public function getHelpText($key) {
        $help = [
            'graphite_web_host' => __('IP-Address of the Graphite-Web server openITCOCKPIT should use to query data.'),
            'graphite_web_port' => __('Port of the Graphite-Web server.'),
            'graphite_prefix'   => __('Prefix added to every metric stored in carbon'),
            'use_https'         => __('Use HTTPS to connect to Graphite-Web server.'),
            'use_proxy'         => __('Use configured proxy server to connect to Graphite-Web server.')
        ];

        if (isset($help[$key])) {
            return $help[$key];
        }

        return '';
    }

    /**
     * Save the configuration as text file on disk
     *
     * @param array $dbRecords
     * @return bool|int
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function writeToFile($dbRecords) {
        $config = $this->mergeDbResultWithDefaultConfiguration($dbRecords);
        $configToExport = [];
        foreach ($config as $type => $fields) {
            foreach ($fields as $key => $value) {
                $configToExport[$key] = $value;
            }
        }

        return $this->saveConfigFile($configToExport);
    }

    /**
     * @param array $dbRecords
     * @return bool|array
     */
    public function migrate($dbRecords) {
        //return $this->mergeDbResultWithDefaultConfiguration($dbRecords);

        \Configure::load('graphite');
        $configFromFile = \Configure::read('graphite');
        debug($configFromFile);
        die();

        foreach ($config['string'] as $field => $value) {
            if (isset($configFromFile['SSH'][$field])) {
                if ($config['string'][$field] != $configFromFile['SSH'][$field]) {
                    $config['string'][$field] = $configFromFile['SSH'][$field];
                }
            }
        }

        if (isset($configFromFile['SSH']['port'])) {
            if ($config['int']['remote_port'] != $configFromFile['SSH']['port']) {
                $config['int']['remote_port'] = $configFromFile['SSH']['port'];
            }
        }

        return $config;
    }

}