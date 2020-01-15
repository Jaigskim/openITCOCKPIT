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
$timezones = \itnovum\openITCOCKPIT\Core\Timezone::listTimezones();
?>
<ol class="breadcrumb page-breadcrumb">
    <li class="breadcrumb-item">
        <a ui-sref="DashboardsIndex">
            <i class="fa fa-home"></i> <?php echo __('Home'); ?>
        </a>
    </li>
    <li class="breadcrumb-item">
        <a ui-sref="TenantsIndex">
            <i class="fa fa-home"></i> <?php echo __('Tenants'); ?>
        </a>
    </li>
    <li class="breadcrumb-item">
        <i class="fa fa-plus"></i> <?php echo __('Add'); ?>
    </li>
</ol>


<div class="row">
    <div class="col-xl-12">
        <div id="panel-1" class="panel">
            <div class="panel-hdr">
                <h2>
                    <?php echo __('Tenants'); ?>
                    <span class="fw-300"><i><?php echo __('Create new tenant'); ?></i></span>
                </h2>
                <div class="panel-toolbar">
                    <?php if ($this->Acl->hasPermission('index', 'tenants')): ?>
                        <a back-button fallback-state='TenantsIndex' class="btn btn-default btn-xs mr-1 shadow-0">
                            <i class="glyphicon glyphicon-white glyphicon-arrow-left"></i> <?php echo __('Back to list'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="panel-container show">
                <div class="panel-content">
                    <form ng-submit="submit();" class="form-horizontal"
                          ng-init="successMessage=
            {objectName : '<?php echo __('Tenant'); ?>' , message: '<?php echo __('created successfully'); ?>'}">

                        <div class="form-group" ng-class="{'has-error': errors.name}">
                            <label class="control-label">
                                <?php echo __('Name'); ?>
                            </label>
                            <input
                                class="form-control"
                                type="text"
                                ng-model="post.container.name">
                            <div ng-repeat="error in errors.name">
                                <div class="help-block text-danger">{{ error }}</div>
                            </div>
                        </div>

                        <div class="form-group" ng-class="{'has-error': errors.description}">
                            <label class="control-label">
                                <?php echo __('Description'); ?>
                            </label>
                            <input
                                class="form-control"
                                type="text"
                                ng-model="post.description">
                            <div ng-repeat="error in errors.description">
                                <div class="help-block text-danger">{{ error }}</div>
                            </div>
                        </div>

                        <div class="form-group" ng-class="{'has-error': errors.firstname}">
                            <label class="control-label">
                                <?php echo __('First name'); ?>
                            </label>
                            <input
                                class="form-control"
                                type="text"
                                ng-model="post.firstname">
                            <div ng-repeat="error in errors.firstname">
                                <div class="help-block text-danger">{{ error }}</div>
                            </div>
                        </div>

                        <div class="form-group" ng-class="{'has-error': errors.lastname}">
                            <label class="control-label">
                                <?php echo __('Last name'); ?>
                            </label>
                            <input
                                class="form-control"
                                type="text"
                                ng-model="post.lastname">
                            <div ng-repeat="error in errors.lastname">
                                <div class="help-block text-danger">{{ error }}</div>
                            </div>
                        </div>

                         <div class="form-group" ng-class="{'has-error': errors.street}">
                            <label class="control-label">
                                <?php echo __('Street'); ?>
                            </label>
                            <input
                                class="form-control"
                                type="text"
                                ng-model="post.street">
                            <div ng-repeat="error in errors.street">
                                <div class="help-block text-danger">{{ error }}</div>
                            </div>
                        </div>

                         <div class="form-group" ng-class="{'has-error': errors.zipcode}">
                            <label class="control-label">
                                <?php echo __('Zip code'); ?>
                            </label>
                            <input
                                class="form-control"
                                type="number"
                                min="0"
                                ng-model="post.zipcode">
                            <div ng-repeat="error in errors.zipcode">
                                <div class="help-block text-danger">{{ error }}</div>
                            </div>
                        </div>

                        <div class="form-group" ng-class="{'has-error': errors.city}">
                            <label class="control-label">
                                <?php echo __('City'); ?>
                            </label>
                            <input
                                class="form-control"
                                type="text"
                                ng-model="post.city">
                            <div ng-repeat="error in errors.city">
                                <div class="help-block text-danger">{{ error }}</div>
                            </div>
                        </div>

                        <div class="card margin-top-10">
                            <div class="card-body">
                                <div class="float-right">
                                    <label>
                                        <input type="checkbox" ng-model="data.createAnother">
                                        <?php echo _('Create another'); ?>
                                    </label>
                                    <button class="btn btn-primary"
                                            type="submit"><?php echo __('Create tenant'); ?></button>
                                    <a back-button fallback-state='TenantsIndex'
                                       class="btn btn-default"><?php echo __('Cancel'); ?></a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
