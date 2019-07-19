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
?>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-file-image-o fa-fw "></i>
            <?php echo __('Adhoc Reports'); ?>
            <span>>
                <?php echo __('Current state report'); ?>
            </span>
        </h1>
    </div>
</div>

<div class="jarviswidget">
    <header>
        <span class="widget-icon"> <i class="fa fa-pencil-square-o"></i> </span>
        <h2><?php echo __('Create current state report'); ?></h2>
        <div class="widget-toolbar" role="menu">
            <a ui-sref="CurrentstatereportsIndex" class="btn btn-default btn-xs" iconcolor="white">
                <i class="glyphicon glyphicon-white glyphicon-arrow-left"></i> <?php echo __('Back to list'); ?>
            </a>
        </div>
    </header>
    <div>
        <div class="widget-body">
            <?php
            echo $this->Form->create('Currentstatereport', [
                'class' => 'form-horizontal clear',
            ]);
            ?>
            <div class="form-group required" ng-class="{'has-error': errors.Service}">
                <label class="col-xs-1 col-md-1 col-lg-1 control-label">
                    <?php echo __('Services'); ?>
                </label>
                <div class="col col-xs-10">
                    <select multiple
                            id="ServiceId"
                            data-placeholder="<?php echo __('Please choose'); ?>"
                            class="form-control"
                            chosen="services"
                            callback="loadServices"
                            ng-options="service.value.Service.id as service.value.Host.name + '/' +((service.value.Service.name)?service.value.Service.name:service.value.Servicetemplate.name) group by service.value.Host.name for service in services"
                            ng-model="post.Currentstatereport.Service">
                    </select>
                    <div ng-repeat="error in errors.Service">
                        <div class="help-block text-danger">{{ error }}</div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-1 col-md-1 col-lg-1 control-label">
                    <?php echo __('Report format'); ?>
                </label>
                <div class="col col-xs-10 col-md-10 col-lg-10">
                    <select
                            class="form-control"
                            ng-model="post.Currentstatereport.report_format">
                        <option value="1"><?php echo __('PDF'); ?></option>
                        <option value="2"><?php echo __('HTML'); ?></option>
                    </select>
                </div>
            </div>
        </div>
        <div class="padding-bottom-10">
            <div class="form-group" ng-class="{'has-error': errors.current_state}">
                <div class="col-xs-12 col-md-3">
                    <fieldset>
                        <legend><?php echo __('Service status'); ?></legend>
                        <div class="form-group smart-form required">
                            <label class="checkbox small-checkbox-label">
                                <input type="checkbox" name="checkbox" checked="checked"
                                       ng-model="filter.Servicestatus.current_state.ok"
                                       ng-model-options="{debounce: 500}">
                                <i class="checkbox-success"></i>
                                <?php echo __('Ok'); ?>
                            </label>

                            <label class="checkbox small-checkbox-label">
                                <input type="checkbox" name="checkbox" checked="checked"
                                       ng-model="filter.Servicestatus.current_state.warning"
                                       ng-model-options="{debounce: 500}">
                                <i class="checkbox-warning"></i>
                                <?php echo __('Warning'); ?>
                            </label>

                            <label class="checkbox small-checkbox-label">
                                <input type="checkbox" name="checkbox" checked="checked"
                                       ng-model="filter.Servicestatus.current_state.critical"
                                       ng-model-options="{debounce: 500}">
                                <i class="checkbox-danger"></i>
                                <?php echo __('Critical'); ?>
                            </label>

                            <label class="checkbox small-checkbox-label">
                                <input type="checkbox" name="checkbox" checked="checked"
                                       ng-model="filter.Servicestatus.current_state.unknown"
                                       ng-model-options="{debounce: 500}">
                                <i class="checkbox-default"></i>
                                <?php echo __('Unknown'); ?>
                            </label>
                        </div>
                    </fieldset>
                </div>
                <div class="col-xs-12 col-md-3">
                    <fieldset>
                        <legend><?php echo __('Acknowledgements'); ?></legend>
                        <div class="form-group smart-form">
                            <label class="checkbox small-checkbox-label">
                                <input type="checkbox" name="checkbox" checked="checked"
                                       ng-model="filter.Servicestatus.acknowledged"
                                       ng-model-options="{debounce: 500}">
                                <i class="checkbox-primary"></i>
                                <?php echo __('Acknowledge'); ?>
                            </label>

                            <label class="checkbox small-checkbox-label">
                                <input type="checkbox" name="checkbox" checked="checked"
                                       ng-model="filter.Servicestatus.not_acknowledged"
                                       ng-model-options="{debounce: 500}">
                                <i class="checkbox-primary"></i>
                                <?php echo __('Not acknowledged'); ?>
                            </label>
                        </div>
                    </fieldset>
                </div>

                <div class="col-xs-12 col-md-3">
                    <fieldset>
                        <legend><?php echo __('Downtimes'); ?></legend>
                        <div class="form-group smart-form">
                            <label class="checkbox small-checkbox-label">
                                <input type="checkbox" name="checkbox" checked="checked"
                                       ng-model="filter.Servicestatus.in_downtime"
                                       ng-model-options="{debounce: 500}">
                                <i class="checkbox-primary"></i>
                                <?php echo __('In downtime'); ?>
                            </label>

                            <label class="checkbox small-checkbox-label">
                                <input type="checkbox" name="checkbox" checked="checked"
                                       ng-model="filter.Servicestatus.not_in_downtime"
                                       ng-model-options="{debounce: 500}">
                                <i class="checkbox-primary"></i>
                                <?php echo __('Not in downtime'); ?>
                            </label>
                        </div>
                    </fieldset>
                </div>

                <div class="col-xs-12 col-md-3">
                    <fieldset>
                        <legend><?php echo __('Check type'); ?></legend>
                        <div class="form-group smart-form">
                            <label class="checkbox small-checkbox-label">
                                <input type="checkbox" name="checkbox" checked="checked"
                                       ng-model="filter.Servicestatus.active"
                                       ng-model-options="{debounce: 500}">
                                <i class="checkbox-primary"></i>
                                <?php echo __('Active service'); ?>
                            </label>
                        </div>
                        <div class="form-group smart-form">
                            <label class="checkbox small-checkbox-label">
                                <input type="checkbox" name="checkbox" checked="checked"
                                       ng-model="filter.Servicestatus.passive"
                                       ng-model-options="{debounce: 500}">
                                <i class="checkbox-primary"></i>
                                <?php echo __('Passive service'); ?>
                            </label>
                        </div>
                    </fieldset>
                </div>

            </div>
            <div class="row">
                <div class="alert alert-info" ng-show="generatingReport">
                    <i class="fa fa-spin fa-refresh"></i>
                    <?php echo __('Generating report...'); ?>
                </div>
            </div>
            <div ng-repeat="servicestatusObject in servicestatus">
                <div style="margin: 10px; padding: 2px 2px;">
                    <div style="padding: 10px 20px;" class="bg-{{servicestatusObject.Hoststatus.humanState}}">
                        <div class="row">
                            <div class="col-lg-10 no-padding font-md">
                                <span class="txt-color-white"
                                      style="font-size:20px;text-shadow: 2px 4px 3px rgba(0,0,0,0.3);">
                                    <i class="fa fa-lg fa-desktop"></i>
                                    {{servicestatusObject.Host.hostname}} ({{servicestatusObject.Host.address}})
                                </span>
                            </div>
                            <div class="col-lg-2 no-padding font-md txt-color-white text-right">
                                <i class="fa fa-calendar"></i>
                                {{servicestatusObject.Hoststatus.lastHardStateChange}}
                            </div>
                        </div>
                        <div style="border-top:1px solid whitesmoke;margin-top:10px;padding:10px 0px;"
                             class="txt-color-white">
                            <div class="row">
                                <div class="col-lg-12 no-padding font-sm">
                                    <div class="col-lg-1 no-padding">
                                        <?php echo __('Last check'); ?>
                                    </div>
                                    <div class="col-lg-1 no-padding">
                                        <?php echo __('Next check'); ?>
                                    </div>
                                    <div class="col-lg-1 no-padding">
                                        <?php echo __('State type'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12 no-padding font-md">
                                    <div class="col-lg-1 no-padding">
                                        {{servicestatusObject.Hoststatus.lastCheck}}
                                    </div>
                                    <div class="col-lg-1 no-padding">
                                        {{servicestatusObject.Hoststatus.nextCheck}}
                                    </div>
                                    <div class="col-lg-1 no-padding">
                                        <div class="row" ng-show="servicestatusObject.Hoststatus.isHardstate">
                                            <?php echo __('Hard state'); ?>
                                            ({{servicestatusObject.Hoststatus.current_check_attempt}}/{{servicestatusObject.Hoststatus.max_check_attempts}})
                                        </div>
                                        <div class="row" ng-show="!servicestatusObject.Hoststatus.isHardstate">
                                            <?php echo __('Soft state'); ?>
                                            ({{servicestatusObject.Hoststatus.current_check_attempt}}/{{servicestatusObject.Hoststatus.max_check_attempts}})
                                        </div>
                                    </div>
                                    <div class="col-lg-9 text-right">
                                        {{servicestatusObject.Hoststatus.output}}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div ng-if="servicestatusObject.Services" class="padding-bottom-5">
                                <div class="row txt-color-white no-padding">
                                    <div class="col-lg-12 font-xs no-padding">
                                        <div class="col-lg-1 no-padding">
                                        </div>
                                        <div class="col-lg-2 no-padding">
                                            <?php echo __('Service'); ?>
                                        </div>
                                        <div class="col-lg-1 no-padding">
                                            <?php echo __('State since'); ?>
                                        </div>
                                        <div class="col-lg-1 no-padding">
                                            <?php echo __('Last check'); ?>
                                        </div>
                                        <div class="col-lg-1 no-padding">
                                            <?php echo __('Next check'); ?>
                                        </div>
                                        <div class="col-lg-1 no-padding">
                                            <?php echo __('State type'); ?>
                                        </div>
                                        <div class="col-lg-3 no-padding">
                                            <?php echo __('Output'); ?>
                                        </div>
                                        <div class="col-lg-2 no-padding">
                                            <?php echo __('Performance data'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div ng-repeat="serviceDetails in servicestatusObject.Services"
                                 class="txt-color-white padding-2 font-sm"
                                 ng-style="{background: $index % 2 == 0 ? 'rgba(255, 255, 255, 0.3)' : 'rgba(255, 255, 255, 0.15)'}">
                                <div class="row">
                                    <div class="col-lg-12 no-padding">
                                        <div class="col-lg-1 no-padding">
                                            <span class="fa-stack">
                                            <i class="fa fa-gear fa-stack-2x txt-color-white"
                                               style="text-shadow: 1px 2px 1px rgba(0,0,0,0.3);"></i>
                                            <i class="fa fa-heartbeat fa-stack-1x cornered cornered-lr {{serviceDetails.Servicestatus.humanState}} font-sm"
                                               style="text-shadow: 1px 2px 1px rgba(0,0,0,0.3);"></i>
                                            </span>
                                            <span class="label bg-{{serviceDetails.Servicestatus.humanState}} text-uppercase padding-top-2 padding-bottom-2">
                                                {{serviceDetails.Servicestatus.humanState}}

                                            </span>
                                        </div>
                                        <div class="col-lg-2 no-padding">
                                            {{serviceDetails.Service.servicename}}
                                        </div>
                                        <div class="col-lg-1 no-padding">
                                            {{serviceDetails.Servicestatus.lastHardStateChange}}
                                        </div>
                                        <div class="col-lg-1 no-padding">
                                            {{serviceDetails.Servicestatus.lastCheck}}
                                        </div>
                                        <div class="col-lg-1 no-padding">
                                            {{serviceDetails.Servicestatus.nextCheck}}
                                        </div>
                                        <div class="col-lg-1 no-padding">
                                            <div class="row" ng-show="serviceDetails.Servicestatus.isHardstate">
                                                <?php echo __('Hard state'); ?>
                                                ({{serviceDetails.Servicestatus.current_check_attempt}}/{{serviceDetails.Servicestatus.max_check_attempts}})
                                            </div>
                                            <div class="row" ng-show="!serviceDetails.Servicestatus.isHardstate">
                                                <?php echo __('Soft state'); ?>
                                                ({{serviceDetails.Servicestatus.current_check_attempt}}/{{serviceDetails.Servicestatus.max_check_attempts}})
                                            </div>
                                        </div>
                                        <div class="col-lg-3 no-padding">
                                            {{serviceDetails.Servicestatus.output}}
                                        </div>
                                        <div class="col-md-2 text-left no-padding margin-top-5">
                                            <div class="col-md-4 no-padding font-xs">
                                                label
                                            </div>
                                            <div class="col-md-7 no-padding">
                                                <div id="text">gfdgfdg</div>
                                                <div id="prog-bar-cont">
                                                    <div id="prog-bar">
                                                        <div id="background"></div>

                                                    </div>
                                                </div>

                                            </div>
                                            <div class="col-md-1 no-padding text-center">
                                                <i class="fa fa-plus-square-o font-md pointer perfdataContainerShowDetails"
                                                   uuid="e642a75e-c833-4a03-8b45-f9c56bec134e"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="alert alert-info" ng-show="noDataFound">
                    {{ noDataFoundMessage }}
                </div>
            </div>
        </div>
        <div class="well formactions ">
            <div class="pull-right">
                <input type="button"
                       class="btn btn-primary"
                       value="<?php echo __('Create'); ?>"
                       ng-click="createCurrentStateReport()"
                >
                &nbsp;
                <a ui-sref="CurrentstatereportsIndex" class="btn btn-default">
                    <?php echo __('Cancel'); ?>
                </a>
            </div>
        </div>
    </div>
</div>