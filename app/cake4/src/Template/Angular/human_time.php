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

<span ng-show="humanInterval.hours > 0 && humanInterval.hours < 2">
    {{humanInterval.hours}}
    <?php echo __('hour'); ?>
</span>

<span ng-show="humanInterval.hours >= 2">
    {{humanInterval.hours}}
    <?php echo __('hours'); ?>
</span>

<span ng-show="humanInterval.minutes > 0 && humanInterval.minutes < 2">
    <span ng-show="humanInterval.hours"><?php echo __('and'); ?></span>
    {{humanInterval.minutes}}
    <?php echo __('minute'); ?>
</span>

<span ng-show="humanInterval.minutes >= 2">
    <span ng-show="humanInterval.hours"><?php echo __('and'); ?></span>
    {{humanInterval.minutes}}
    <?php echo __('minutes'); ?>
</span>

<span ng-show="humanInterval.seconds > 0 && humanInterval.seconds < 2">
    <span ng-show="humanInterval.minutes"><?php echo __('and'); ?></span>
    {{humanInterval.seconds}}
    <?php echo __('second'); ?>
</span>

<span ng-show="humanInterval.seconds >= 2">
    <span ng-show="humanInterval.minutes"><?php echo __('and'); ?></span>
    {{humanInterval.seconds}}
    <?php echo __('seconds'); ?>
</span>
