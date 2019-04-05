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
?>

<div id="angularAddHostsToHostgroup" class="modal" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary txt-color-white">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo __('Append host/s to host group'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12">
                        <?php echo __('Selected hosts'); ?>
                    </div>

                    <div class="col-xs-12 margin-top-10">
                        <ul>
                            <li ng-repeat="(id, hostName) in objects">
                                {{ hostName }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-success" ui-sref="HostgroupsAdd({ids:hostIds})"
                        data-dismiss="modal">
                    <?php echo __('Create new hostgroup'); ?>
                </button>

                <button type="button" class="btn btn-primary" ui-sref="HostgroupsAppend({ids:hostIds})"
                        data-dismiss="modal">
                    <?php echo __('Append existing host group'); ?>
                </button>

                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <?php echo __('Cancel'); ?>
                </button>
            </div>
        </div>

    </div>
</div>