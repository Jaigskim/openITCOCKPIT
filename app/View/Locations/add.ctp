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
$timezones = CakeTime::listTimezones();
?>
<div class="row">
    <div class="col-xs-12 col-sm-7 col-md-7 col-lg-4">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-location-arrow "></i>
            <?php echo __('Locations'); ?>
            <span>>
                <?php echo __('Add'); ?>
            </span>
        </h1>
    </div>
</div>
<div id="error_msg"></div>

<div class="jarviswidget" id="wid-id-0">
    <header>
        <span class="widget-icon"> <i class="fa fa-location-arrow"></i> </span>
        <h2><?php echo __('Create new location'); ?></h2>
        <div class="widget-toolbar" role="menu">
            <?php if ($this->Acl->hasPermission('index', 'locations')): ?>
                <a back-button fallback-state='LocationsIndex' class="btn btn-default btn-xs">
                    <i class="glyphicon glyphicon-white glyphicon-arrow-left"></i> <?php echo __('Back to list'); ?>
                </a>
            <?php endif; ?>
        </div>
    </header>
    <div>
        <div class="widget-body">
            <form ng-submit="submit();" class="form-horizontal"
                  ng-init="successMessage=
            {objectName : '<?php echo __('Location'); ?>' , message: '<?php echo __('created successfully'); ?>'}">
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-8">

                        <div class="row">

                            <div class="form-group required" ng-class="{'has-error': errors.container.name}">
                                <label class="col-xs-12 col-lg-2 control-label">
                                    <?php echo __('Name'); ?>
                                </label>
                                <div class="col-xs-12 col-lg-10">
                                    <div class="input-group" style="width: 100%;">
                                        <input
                                                class="form-control"
                                                type="text"
                                                ng-model="post.container.name">
                                    </div>
                                    <div ng-repeat="error in errors.container.name">
                                        <div class="help-block text-danger">{{ error }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group" ng-class="{'has-error': errors.description}">
                                <label class="col-xs-12 col-lg-2 control-label">
                                    <?php echo __('Description'); ?>
                                </label>
                                <div class="col-xs-12 col-lg-10">
                                    <div class="input-group" style="width: 100%;">
                                        <input
                                                class="form-control"
                                                type="text"
                                                ng-model="post.description">
                                    </div>
                                    <div ng-repeat="error in errors.description">
                                        <div class="help-block text-danger">{{ error }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group" ng-class="{'has-error': errors.latitude}">
                                <label class="col-xs-12 col-lg-2 control-label">
                                    <?php echo __('Latitude'); ?>
                                </label>
                                <div class="col-xs-12 col-lg-10">
                                    <div class="input-group" style="width: 100%;">
                                        <input class="form-control"
                                            type="text"
                                            ng-model="post.latitude">
                                    </div>
                                    <div ng-repeat="error in errors.latitude">
                                        <div class="help-block text-danger">{{ error }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group" ng-class="{'has-error': errors.longitude}">
                                <label class="col-xs-12 col-lg-2 control-label">
                                    <?php echo __('Longitude'); ?>
                                </label>
                                <div class="col-xs-12 col-lg-10">
                                    <div class="input-group" style="width: 100%;">
                                        <input class="form-control"
                                            type="text"
                                            ng-model="post.longitude">
                                    </div>
                                    <div ng-repeat="error in errors.longitude">
                                        <div class="help-block text-danger">{{ error }}</div>
                                    </div>
                                </div>
                            </div>


                            <div class="form-group">
                                <label class="col-xs-12 col-lg-2 control-label">
                                    <?php echo __('Timezone'); ?>
                                </label>
                                <div class="col-xs-12 col-lg-10">
                                    <select class="form-control"
                                            chosen="{}"
                                            ng-init="post.Location.timezone = post.Location.timezone || 'Europe/Berlin'"
                                            ng-model="post.Location.timezone">
                                        <?php foreach ($timezones as $continent => $continentTimezons): ?>
                                            <optgroup label="<?php echo h($continent); ?>">
                                                <?php foreach ($continentTimezons as $timezoneKey => $timezoneName): ?>
                                                    <option value="<?php echo h($timezoneKey); ?>"><?php echo h($timezoneName); ?></option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php endforeach;; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>


                <div class="col-xs-12 margin-top-10 margin-bottom-10">
                    <div class="well formactions ">
                        <div class="pull-right">

                            <label>
                                <input type="checkbox" ng-model="data.createAnother">
                                <?php echo _('Create another'); ?>
                            </label>

                            <button type="submit" class="btn btn-primary">
                                <?php echo __('Create location'); ?>
                            </button>
                            <a back-button fallback-state='LocationsIndex'
                               class="btn btn-default"><?php echo __('Cancel'); ?></a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
