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
use App\Form\DowntimereportForm;
use App\Lib\Interfaces\DowntimehistoryHostsTableInterface;
use App\Model\Table\HostsTable;
use Cake\ORM\TableRegistry;
use itnovum\openITCOCKPIT\Core\DowntimeHostConditions;
use itnovum\openITCOCKPIT\Core\DowntimeServiceConditions;
use itnovum\openITCOCKPIT\Core\HoststatusFields;
use itnovum\openITCOCKPIT\Core\Reports\DaterangesCreator;
use itnovum\openITCOCKPIT\Core\Reports\DowntimeReportBarChartWidgetDataPreparer;
use itnovum\openITCOCKPIT\Core\Reports\DowntimeReportPieChartWidgetDataPreparer;
use itnovum\openITCOCKPIT\Core\Reports\StatehistoryConverter;
use itnovum\openITCOCKPIT\Core\ServicestatusFields;
use itnovum\openITCOCKPIT\Core\StatehistoryHostConditions;
use itnovum\openITCOCKPIT\Core\StatehistoryServiceConditions;
use itnovum\openITCOCKPIT\Core\Views\UserTime;
use Statusengine2Module\Model\Table\StatehistoryHostsTable;
use Statusengine2Module\Model\Table\StatehistoryServicesTable;

/**
 * @property Downtimereport $Downtimereport
 * @property Host $Host
 * @property Service $Service
 * @property Timeperiod $Timeperiod
 */
class DowntimereportsController extends AppController {
    public $layout = 'blank';


    public function index() {
        if (!$this->isApiRequest()) {
            //Only ship HTML template
            return;
        }
        $downtimeReportForm = new DowntimereportForm();
        $downtimeReportForm->execute($this->request->data);

        $User = new \itnovum\openITCOCKPIT\Core\ValueObjects\User($this->Auth);
        $UserTime = UserTime::fromUser($User);

        if (!empty($downtimeReportForm->getErrors())) {
            $this->response->statusCode(400);
            $this->set('error', $downtimeReportForm->getErrors());
            $this->set('_serialize', ['error']);
            return;
        } else {
            $TimeperiodsTable = TableRegistry::getTableLocator()->get('Timeperiods');
            $timeperiod = $TimeperiodsTable->getTimeperiodWithTimerangesById($this->request->data('timeperiod_id'));
            $reflectionState = $this->request->data('reflection_state');
            if (empty($timeperiod['Timeperiod']['timeperiod_timeranges'])) {
                $this->response->statusCode(400);
                $this->set('error', [
                    'timeperiod_id' => [
                        'empty' => 'There are no time frames defined. Time evaluation report data is not available for the selected period.'
                    ]
                ]);
                $this->set('_serialize', ['error']);
                return;
            }
            /** @var HostsTable $HostsTable */
            $HostsTable = TableRegistry::getTableLocator()->get('Hosts');
            $fromDate = strtotime($this->request->data('from_date') . ' 00:00:00');
            $toDate = strtotime($this->request->data('to_date') . ' 23:59:59');

            /**
             * @todo show error message if hostUuids are emtpy
             */
            $hostsUuids = $HostsTable->getHostsByContainerId($this->MY_RIGHTS, 'list', 'uuid');
            $DowntimeHostConditions = new DowntimeHostConditions();
            $DowntimeHostConditions->setFrom($fromDate);
            $DowntimeHostConditions->setTo($toDate);
            $DowntimeHostConditions->setContainerIds($this->MY_RIGHTS);
            $DowntimeHostConditions->setOrder(['DowntimeHosts.scheduled_start_time' => 'asc']);
            $DowntimeHostConditions->setHostUuid(array_keys($hostsUuids));
            /** @var DowntimehistoryHostsTableInterface $DowntimehistoryHostsTable */
            $DowntimehistoryHostsTable = $this->DbBackend->getDowntimehistoryHostsTable();

            $downtimes['Hosts'] = $DowntimehistoryHostsTable->getDowntimes($DowntimeHostConditions);

            $hosts = [];
            $services = [];
            $reportData = [];
            foreach ($downtimes['Hosts'] as $hostDowntime) {
                $hosts[$hostDowntime->get('Hosts')['uuid']] = [
                    'Host' => $hostDowntime->get('Hosts')
                ];
            }

            if ($this->request->data('evaluation_type') === 1) { //Evaluation with services
                $DowntimeServiceConditions = new DowntimeServiceConditions();
                $DowntimeServiceConditions->setFrom($fromDate);
                $DowntimeServiceConditions->setTo($toDate);
                $DowntimeServiceConditions->setContainerIds($this->MY_RIGHTS);
                $DowntimeServiceConditions->setOrder(['DowntimeServices.scheduled_start_time' => 'asc']);
                $DowntimeServiceConditions->setHostUuid(array_keys($hostsUuids));
                /** @var DowntimehistoryHostsTableInterface $DowntimehistoryHostsTable */
                $DowntimehistoryServicesTable = $this->DbBackend->getDowntimehistoryServicesTable();

                $downtimes['Services'] = $DowntimehistoryServicesTable->getDowntimes($DowntimeServiceConditions);
                foreach ($downtimes['Services'] as $serviceDowntime) {
                    $hosts[$serviceDowntime->get('Hosts')['uuid']] = $serviceDowntime->get('Hosts');
                    $services[$serviceDowntime->get('Services')['uuid']] = [
                        'Service'         => $serviceDowntime->get('Services'),
                        'Host'            => $serviceDowntime->get('Hosts'),
                        'Servicetemplate' => $serviceDowntime->get('Servicetemplates')
                    ];
                }
            }
            if (empty($downtimes['Hosts']) && empty($downtimes['Services'])) {
                $this->response->statusCode(400);
                $this->set('error', [
                    'no_downtimes' => [
                        'empty' => __('No downtimes within specified time found (%s - %s) !', $this->request->data('from_date'), $this->request->data('to_date'))
                    ]
                ]);
                $this->set('_serialize', ['error']);
                return;
            }

            $timeSlices = DaterangesCreator::createDateRanges(
                $fromDate,
                $toDate,
                $timeperiod['Timeperiod']['timeperiod_timeranges']
            );
            $totalTime = Hash::apply(
                array_map(function ($timeSlice) {
                    return $timeSlice['end'] - $timeSlice['start'];
                }, $timeSlices),
                '{n}',
                'array_sum'
            );

            foreach (array_keys($hosts) as $uuid) {
                $allStatehistories = [];
                $host = Set::classicExtract(
                    $HostsTable->getHostByUuid($uuid, false),
                    '{(id|address|name|description)}'
                );

                //Process conditions
                $Conditions = new StatehistoryHostConditions();
                $Conditions->setOrder(['StatehistoryHosts.state_time' => 'desc']);
                if ($reflectionState === 2) { // type 2 hard state only
                    $Conditions->setHardStateTypeAndUpState(true); // 1 => Hard State
                }
                $Conditions->setFrom($fromDate);
                $Conditions->setTo($toDate);
                $Conditions->setHostUuid($uuid);

                $StatehistoryHostsTable = $this->DbBackend->getStatehistoryHostsTable();

                $statehistoriesHost = $StatehistoryHostsTable->getStatehistoryIndex($Conditions);
                if (empty($statehistoriesHost)) {
                    $record = $StatehistoryHostsTable->getLastRecord($Conditions);
                    if (!empty($record)) {
                        $statehistoriesHost[] = $record->set('state_time', $fromDate);
                    }
                }

                if (empty($statehistoriesHost)) {
                    $HoststatusTable = $this->DbBackend->getHoststatusTable();
                    $HoststatusFields = new HoststatusFields($this->DbBackend);
                    $HoststatusFields->currentState()
                        ->lastHardState()
                        ->isHardstate()
                        ->lastStateChange();
                    $hoststatus = $HoststatusTable->byUuid($uuid, $HoststatusFields);
                    if (!empty($hoststatus)) {
                        $Hoststatus = new \itnovum\openITCOCKPIT\Core\Hoststatus($hoststatus['Hoststatus']);
                        if ($Hoststatus->getLastStateChange() <= $fromDate) {
                            $stateHistoryHost['StatehistoryHost'] = [
                                'state_time'      => $fromDate,
                                'state'           => $Hoststatus->currentState(),
                                'last_state'      => $Hoststatus->currentState(),
                                'last_hard_state' => $Hoststatus->getLastHardState(),
                                'state_type'      => $Hoststatus->getStateType()
                            ];
                            $StatehistoryHost = new \itnovum\openITCOCKPIT\Core\Views\StatehistoryHost($stateHistoryHost['StatehistoryHost']);
                            $statehistoriesHost[$uuid]['Statehistory'][] = $StatehistoryHost;
                        }
                    }
                }

                /** @var StatehistoryHostsTable $statehistoryHost */
                foreach ($statehistoriesHost as $statehistoryHost) {
                    $StatehistoryHost = new \itnovum\openITCOCKPIT\Core\Views\StatehistoryHost($statehistoryHost->toArray(), $UserTime);
                    $allStatehistories[] = $StatehistoryHost->toArray();
                }

                $reportData[$uuid]['Host'] = $host;
                $reportData[$uuid]['Host']['reportData'] = StatehistoryConverter::generateReportData(
                    $timeSlices,
                    $allStatehistories,
                    ($reflectionState === 2),
                    true
                );
            }
            foreach (array_keys($services) as $uuid) {
                $allStatehistories = [];
                //Process conditions
                $Conditions = new StatehistoryServiceConditions();
                $Conditions->setOrder(['StatehistoryServices.state_time' => 'desc']);
                if ($reflectionState === 2) { // type 2 hard state only
                    $Conditions->setHardStateTypeAndUpState(true); // 1 => Hard State
                }
                $Conditions->setFrom($fromDate);
                $Conditions->setTo($toDate);
                $Conditions->setServiceUuid($uuid);

                $StatehistoryServicesTable = $this->DbBackend->getStatehistoryServicesTable();
                $statehistoriesService = $StatehistoryServicesTable->getStatehistoryIndex($Conditions);

                if (empty($statehistoriesService)) {
                    $record = $StatehistoryServicesTable->getLastRecord($Conditions);
                    if (!empty($record)) {
                        $statehistoriesService[] = $record->set('state_time', $fromDate);
                    }
                }
                if (empty($statehistoriesService)) {
                    $ServicestatusTable = $this->DbBackend->getServicestatusTable();
                    $ServicestatusFields = new ServicestatusFields($this->DbBackend);
                    $ServicestatusFields->currentState()
                        ->lastHardState()
                        ->isHardstate()
                        ->lastStateChange();
                    $servicestatus = $ServicestatusTable->byUuid($uuid, $ServicestatusFields);
                    if (!empty($servicestatus)) {
                        $Servicestatus = new \itnovum\openITCOCKPIT\Core\Servicestatus($servicestatus['Servicestatus']);
                        if ($Servicestatus->getLastStateChange() <= $fromDate) {
                            $stateHistoryService['StatehistoryService'] = [
                                'state_time'      => $fromDate,
                                'state'           => $Servicestatus->currentState(),
                                'last_state'      => $Servicestatus->currentState(),
                                'last_hard_state' => $Servicestatus->getLastHardState(),
                                'state_type'      => $Servicestatus->getStateType()
                            ];
                            $stateHistoryService = new \itnovum\openITCOCKPIT\Core\Views\StatehistoryService($stateHistoryService['StatehistoryService']);
                            $statehistoriesService[$uuid]['Statehistory'][] = $stateHistoryService;
                        }
                    }
                }

                /** @var StatehistoryServicesTable $statehistoryService */
                foreach ($statehistoriesService as $statehistoryService) {
                    $StatehistoryService = new \itnovum\openITCOCKPIT\Core\Views\StatehistoryService($statehistoryService->toArray(), $UserTime);
                    $allStatehistories[] = $StatehistoryService->toArray();
                }
                $reportData[$services[$uuid]['Host']['uuid']]['Services'][$uuid] = $services[$uuid];

                $reportData[$services[$uuid]['Host']['uuid']]['Services'][$uuid]['Service']['reportData'] = StatehistoryConverter::generateReportData(
                    $timeSlices,
                    $allStatehistories,
                    ($reflectionState === 2),
                    false
                );
            }
            $downtimeReport = [];
            foreach ($reportData as $reportResult) {
                if ($reportResult['Host']['reportData'][1] > 0) {
                    $downtimeReport['hostsWithOutages'][] = $reportResult;
                } else {
                    $downtimeReport['hostsWithoutOutages'][] = $reportResult;
                }
            }
            $downtimeReport['hostsWithOutages'] = Hash::sort(
                $downtimeReport['hostsWithOutages'],
                '{n}.Host.reportdata.1',
                'desc'
            );

            $downtimeReport['hostsWithOutages'] = array_chunk($downtimeReport['hostsWithOutages'], 10);
            if (!empty($downtimeReport['hostsWithOutages'])) {
                $hostBarChartData = DowntimeReportBarChartWidgetDataPreparer::getDataForHostBarChart($downtimeReport['hostsWithOutages'], $totalTime);
                foreach ($downtimeReport['hostsWithOutages'] as $chunkKey => $hostsArray) {
                    $downtimeReport['hostsWithOutages'][$chunkKey]['barChartData'] = $hostBarChartData;
                    foreach ($hostsArray as $key => $hostData) {
                        $downtimeReport['hostsWithOutages'][$chunkKey][$key]['Host']['pieChartData'] = DowntimeReportPieChartWidgetDataPreparer::getDataForHostPieChartWidget(
                            $hostData,
                            $totalTime,
                            $UserTime
                        );
                        if (!empty($hostData['Services'])) {
                            foreach ($hostData['Services'] as $uuid => $serviceData) {
                                $downtimeReport['hostsWithOutages'][$chunkKey][$key]['Services'][$uuid]['pieChartData'] = DowntimeReportPieChartWidgetDataPreparer::getDataForServicePieChart(
                                    $serviceData,
                                    $totalTime,
                                    $UserTime
                                );
                            }
                        }
                    }
                }
            }
            $this->set('downtimes', $downtimes);
            $this->set('downtimeReport', $downtimeReport);
            $this->set('_serialize', ['downtimes', 'downtimeReport', 'hostBarChartData']);
        }
    }

    public function createPdfReport() {
        $this->set('downtimeReportData', $this->Session->read('downtimeReportData'));
        $this->set('downtimeReportDetails', $this->Session->read('downtimeReportDetails'));
        if ($this->Session->check('downtimeReportData')) {
            $this->Session->delete('downtimeReportData');
        }
        if ($this->Session->check('downtimeReportDetails')) {
            $this->Session->delete('downtimeReportDetails');
        }
        $binary_path = '/usr/bin/wkhtmltopdf';
        if (file_exists('/usr/local/bin/wkhtmltopdf')) {
            $binary_path = '/usr/local/bin/wkhtmltopdf';
        }
        $this->pdfConfig = [
            'engine'             => 'CakePdf.WkHtmlToPdf',
            'margin'             => [
                'bottom' => 15,
                'left'   => 0,
                'right'  => 0,
                'top'    => 15,
            ],
            'encoding'           => 'UTF-8',
            'download'           => true,
            'binary'             => $binary_path,
            'orientation'        => 'portrait',
            'filename'           => 'Downtimereport.pdf',
            'no-pdf-compression' => '*',
            'image-dpi'          => '900',
            'background'         => true,
            'no-background'      => false,
        ];
    }
}
