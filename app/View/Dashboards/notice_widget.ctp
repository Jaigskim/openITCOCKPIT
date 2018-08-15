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
<div class="testContainer">
    <flippy vertical
        class="fancy"
        flip="['custom:FLIP_EVENT_OUT']"
        flip-back="['custom:FLIP_EVENT_IN']"
        duration="800"
        timing-function="ease-in-out">

    <flippy-front>
        <a href="javascript:void(0);" class="btn btn-default btn-xs txt-color-blueDark" ng-click="hideConfig()">
            <i class="fa fa-cog fa-sm"></i>
        </a>
        <div class="padding-10" >
            <div style="display:inline"
                 ng-bind-html="widget.WidgetNotice.htmlContent | trustAsHtml"></div>
        </div>
    </flippy-front>
    <flippy-back>
        <a href="javascript:void(0);" class="btn btn-default btn-xs txt-color-blueDark" ng-click="showConfig()">
            <i class="fa fa-eye fa-sm"></i>
        </a>
        <div class="padding-top-10">
            <textarea name="notes" class="form-control notice-text" maxlength="4000" cols="30" rows="10"
                      ng-model-options="{debounce: 500}" ng-model="widget.WidgetNotice.note">
            </textarea>
            <span class="note"><i class="fa fa-code text-primary"></i>
                <?php echo __('Insert text, html or markdown code'); ?>
            </span>
        </div>
    </flippy-back>
</flippy>
</div>