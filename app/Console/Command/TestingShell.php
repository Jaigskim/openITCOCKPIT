<?php
// Copyright (C) <2015>  <it-novum GmbH>
//
// This file is dual licensed
//
// 1.
//	This program is free software: you can redistribute it and/or modify
//	it under the terms of the GNU General Public License as published by
//	the Free Software Foundation, version 3 of the License.
//
//	This program is distributed in the hope that it will be useful,
//	but WITHOUT ANY WARRANTY; without even the implied warranty of
//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//	GNU General Public License for more details.
//
//	You should have received a copy of the GNU General Public License
//	along with this program.  If not, see <http://www.gnu.org/licenses/>.
//

// 2.
//	If you purchased an openITCOCKPIT Enterprise Edition you can use this file
//	under the terms of the openITCOCKPIT Enterprise Edition license agreement.
//	License agreement and license key will be shipped with the order
//	confirmation.

use App\Model\Table\HostgroupsTable;
use App\Model\Table\HostsTable;
use App\Model\Table\HosttemplatesTable;
use App\Model\Table\ProxiesTable;
use Cake\ORM\TableRegistry;
use itnovum\openITCOCKPIT\Core\Comparison\HostComparison;
use itnovum\openITCOCKPIT\Core\Comparison\HostComparisonForSave;
use itnovum\openITCOCKPIT\Core\HostConditions;

class TestingShell extends AppShell {
    /*
     * This is a test and debuging shell for development purposes
     */
    public $uses = [
        'Systemsetting',
        MONITORING_CORECONFIG_MODEL,
        'Host',
        'Servicetemplate',
        'Hosttemplate',
        'Service',
        'Hostgroup',
        MONITORING_HOSTSTATUS,
        MONITORING_SERVICESTATUS,
        'Servicetemplateeventcommandargumentvalue',
        'Serviceeventcommandargumentvalue',
        'Command',
        'Contact',
        'Contactgroup',
        'Servicegroup',
        'Timeperiod',
        'Macro',
        'Hostescalation',
        'Hostcommandargumentvalue',
        'Servicecommandargumentvalue',
        'Aro',
        'Aco',
        'Calendar',
        'Container',
        'User',
        'Browser'
    ];

    public function main() {
        //debug($this->Aro->find('all'));
        //debug($this->Aco->find('all', ['recursive' => -1]));

        //Load CakePHP4 Models
        /** @var $Proxy ProxiesTable */
        //$Proxy = TableRegistry::getTableLocator()->get('Proxies');
        //print_r($Proxy->getSettings());

        /*
         * Lof of space for your experimental code :)
         */

        /** @var $HostsTable HostsTable */
        $HostsTable = TableRegistry::getTableLocator()->get('Hosts');
    }

    public function getOptionParser() {
        $parser = parent::getOptionParser();
        $parser->addOptions([
            'type'     => ['short' => 't', 'help' => __d('oitc_console', 'Type of the notification host or service')],
            'hostname' => ['help' => __d('oitc_console', 'The uuid of the host')],
        ]);

        return $parser;
    }

}
