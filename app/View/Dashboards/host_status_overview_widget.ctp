<?php
// Copyright (C) <2015>  <it-novum GmbH>
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
?>
<style>
    .status-widget {
        height: 150px !important;
    }

    .bg-host-0, .bg-service-0 {
        background-color: #449D44 !important;
    }

    .bg-service-1 {
        background-color: #DF8F1D !important;
    }

    .bg-host-1, .bg-service-2 {
        background-color: #C9302C !important;
    }

    .bg-host-2, .bg-service-3 {
        background-color: #92A2A8 !important;
    }

    .bg-host-background-icon:after {
        font-family: "FontAwesome";
        padding-left: 5px;
        position: absolute;
        bottom: 0px;
        right: 5px;
        font-size: calc(2.5vw + 2.5vh);
    }

    .bg-host-front-0:after {
        content: "\f058";
        color: rgba(255, 255, 255, 0.1);
    }

    .bg-host-front-1:after {
        content: "\f06a";
        color: rgba(255, 255, 255, 0.1);
    }

    .bg-host-front-2:after {
        content: "\f06a";
        color: rgba(255, 255, 255, 0.1);
    }

    .statusCountText {
        font-size: 4.5em;
    }


</style>
<flippy vertical
        class="fancy status-widget"
        flip="['custom:FLIP_EVENT_OUT']"
        flip-back="['custom:FLIP_EVENT_IN']"
        duration="800"
        timing-function="ease-in-out">

    <flippy-front
            class="bg-host-{{filter.Hoststatus.current_state}} bg-host-background-icon bg-host-front-{{filter.Hoststatus.current_state}}">
        <a href="javascript:void(0);" class="btn btn-default btn-xs txt-color-blueDark" ng-click="showConfig()">
            <i class="fa fa-cog fa-sm"></i>
        </a>
        <div class="padding-5 statusCountText" id="host-status-front-{{widget.id}}">
            <div class="row text-center">
                <div class="col col-lg-12 txt-color-white">
                    {{ statusCount | number }}
                </div>
            </div>
        </div>
    </flippy-front>
    <flippy-back>
        <a href="javascript:void(0);" class="btn btn-default btn-xs txt-color-blueDark" ng-click="hideConfig()">
            <i class="fa fa-eye fa-sm"></i>
        </a>
        <div class="padding-top-10">
            <div class="row">
                <div class="form-group smart-form">
                    <label class="input"> <i class="icon-prepend fa fa-filter"></i>
                        <input type="text" class="input-sm"
                               placeholder="<?php echo __('Filter by host name'); ?>"
                               ng-model="filter.Host.name"
                               ng-model-options="{debounce: 500}">
                    </label>
                </div>
            </div>
            <div class="row padding-top-10">
                <div class="row">

                    <div class="col-xs-12 col-sm-6">
                        <fieldset>
                            <legend><?php echo __('Host status'); ?></legend>
                            <div class="radio radio-success">
                                <input type="radio"
                                       id="widget-radio0-{{widget.id}}"
                                       ng-model="filter.Hoststatus.current_state"
                                       ng-value="0">
                                <label for="widget-radio0-{{widget.id}}">
                                    <?php echo __('Up'); ?>
                                </label>
                            </div>

                            <div class="radio radio-danger">
                                <input type="radio"
                                       id="widget-radio1-{{widget.id}}"
                                       ng-model="filter.Hoststatus.current_state"
                                       ng-value="1">
                                <label for="widget-radio1-{{widget.id}}">
                                    <?php echo __('Down'); ?>
                                </label>
                            </div>

                            <div class="radio radio-default">
                                <input type="radio"
                                       id="widget-radio2-{{widget.id}}"
                                       ng-model="filter.Hoststatus.current_state"
                                       ng-value="2">
                                <label for="widget-radio2-{{widget.id}}">
                                    <?php echo __('Unreachable'); ?>
                                </label>
                            </div>
                        </fieldset>
                    </div>

                    <div class="col-xs-12 col-sm-6" ng-show="filter.Hoststatus.current_state > 0">
                        <fieldset>
                            <legend><?php echo __('Properties'); ?></legend>
                            <div class="form-group smart-form">
                                <label class="checkbox small-checkbox-label display-inline margin-right-5"">
                                    <input type="checkbox" name="checkbox" checked="checked"
                                           ng-model="filter.Hoststatus.not_acknowledged">
                                    <i class="checkbox-primary"></i>
                                    <?php echo __('Not Acknowledged'); ?>
                                </label>
                            </div>

                            <div class="form-group smart-form">
                                <label class="checkbox small-checkbox-label display-inline margin-right-5">
                                    <input type="checkbox" name="checkbox" checked="checked"
                                           ng-model="filter.Hoststatus.not_in_downtime">
                                    <i class="checkbox-primary"></i>
                                    <?php echo __('Not in Downtime'); ?>
                                </label>
                            </div>
                        </fieldset>
                    </div>

                    <div class="row">
                        <div class="col-xs-12">
                            <button class="btn btn-primary pull-right" ng-click="saveHoststatusOverview()">
                                <?php echo __('Save'); ?>
                            </button>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </flippy-back>
</flippy>
