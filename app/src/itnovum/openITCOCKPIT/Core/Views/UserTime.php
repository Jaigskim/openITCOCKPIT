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

namespace itnovum\openITCOCKPIT\Core\Views;


use CakeTime;
use DateTime;

class UserTime {

    /**
     * @var string
     */
    private $timezone;

    /**
     * @var string
     */
    private $format;

    /**
     * UserTime constructor.
     * @param string $timezone
     * @param string $format
     */
    public function __construct($timezone, $format) {
        $this->timezone = $timezone;
        $this->format = $format;
    }

    /**
     * @param int $t_time
     * @return string
     */
    public function format($t_time){
        return CakeTime::format($t_time, $this->format, false, $this->timezone);
    }

    /**
     * Formats a given value in seconds to a human short readable string with time units
     * Example 58536006 will return:
     * 1Y 10M 8D 12h 0m 6s
     *
     * @param integer $seconds to format
     *
     * @return string $ as human date
     * @author Daniel Ziegler <daniel.ziegler@it-novum.com>
     * @since  3.0
     */
    public function secondsInHumanShort($duration)
    {

        if ($duration == '') {
            $duration = 0;
        }

        $zero = new DateTime("@0");
        $seconds = new DateTime("@$duration");
        $closure = function ($duration) {
            //Check how much "time" we need
            if ($duration >= 31536000) {
                // 1 year or more
                return '%y'.__('Y').' %m'.__('M').' %d'.__('D').' %h'.__('h').' %i'.__('m').' %s'.__('s');
            } elseif ($duration >= 2678400) {
                // 1 month or more
                return '%m'.__('M').' %d'.__('D').' %h'.__('h').' %i'.__('m').' %s'.__('s');
            } elseif ($duration >= 86400) {
                // 1 day or more
                return '%a'.__('D').' %h'.__('h').' %i'.__('m').' %s'.__('s');
            } elseif ($duration >= 3600) {
                // 1 hour or more
                return '%h'.__('h').' %i'.__('m').' %s'.__('s');
            } elseif ($duration >= 60) {
                // 1 minute or more
                return '%i'.__('m').' %s'.__('s');
            } elseif ($duration >= 0) {
                // 0 second or more
                return '%s'.__('s');
            }
        };

        $format = $closure($duration);

        return $zero->diff($seconds)->format($format);
    }

}