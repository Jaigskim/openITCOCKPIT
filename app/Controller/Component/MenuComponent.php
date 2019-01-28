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

/**
 * Class MenuComponent
 */
class MenuComponent extends Component {
    /**
     * @return array
     */
    public function compileMenu() {
        Configure::load('menu');

        $menu = Configure::read('menu');
        Configure::clear();

        $modulePlugins = array_filter(CakePlugin::loaded(), function ($value) {
            return strpos($value, 'Module') !== false;
        });
        foreach ($modulePlugins as $pluginName) {
            Configure::load($pluginName . '.' . 'menu');
        }

        $moduleMenu = Configure::read('menu');

        foreach($moduleMenu as $key => $value){
            if(!isset($menu[$key]) && !empty($moduleMenu[$key])){
                $menu[$key] = [];
            }
            if(isset($menu[$key]) && !empty($moduleMenu[$key])){
                if(!isset($menu[$key]['children']) && !empty($moduleMenu[$key]['children'])){
                    $menu[$key]['children'] = [];
                    if(!isset($menu[$key]['title']) && isset($moduleMenu[$key]['title'])) {
                        $menu[$key]['title'] = $moduleMenu[$key]['title'];
                    }
                    if(!isset($menu[$key]['icon']) && isset($moduleMenu[$key]['icon'])) {
                        $menu[$key]['icon'] = $moduleMenu[$key]['icon'];
                    }
                    if(!isset($menu[$key]['order']) && isset($moduleMenu[$key]['order'])) {
                        $menu[$key]['order'] = $moduleMenu[$key]['order'];
                    }
                }
                if(isset($menu[$key]['children']) && !empty($moduleMenu[$key]['children'])){
                    foreach($moduleMenu[$key]['children'] as $child){
                        $menu[$key]['children'][] = $child;
                    }
                }
                if(isset($moduleMenu[$key]['url'])){
                    $menu[$key] = $moduleMenu[$key];
                }
            }
        }

        /*echo "<pre>";
        print_r($menu);
        echo "</pre>";
        exit;
        echo(json_encode($menu, JSON_PRETTY_PRINT));
        exit;*/


        $menuOrder = [];

        foreach ($menu as $key => $menuItem) {
            if (isset($menuItem['order'])) {
                $menuOrder[$key] = $menuItem['order'];
            } else {
                $menuOrder[$key] = 9999;
            }
        }
        asort($menuOrder);

        $finalMenu = [];
        foreach ($menuOrder as $key => $order){
            if(isset($menu[$key]) && isset($menu[$key]['url'])){
                if(isset($finalMenu[$key])) {
                    $finalMenu[$key] = Hash::merge($finalMenu[$key], $menu[$key]);
                } else {
                    $finalMenu[$key] = $menu[$key];
                }
            } else if(isset($menu[$key]) && isset($menu[$key]['children']) && !empty($menu[$key]['children'])){
                if(isset($finalMenu[$key]) && isset($finalMenu[$key]['children'])) {
                    $finalMenu[$key]['children'] = Hash::merge($finalMenu[$key]['children'], $menu[$key]['children']);
                } else {
                    if(!isset($finalMenu[$key])){
                        $finalMenu[$key] = [];
                        if(!isset($finalMenu[$key]['title']) && isset($menu[$key]['title'])) {
                            $finalMenu[$key]['title'] = $menu[$key]['title'];
                        }
                        if(!isset($finalMenu[$key]['icon']) && isset($menu[$key]['icon'])) {
                            $finalMenu[$key]['icon'] = $menu[$key]['icon'];
                        }
                        if(!isset($finalMenu[$key]['order']) && isset($menu[$key]['order'])) {
                            $finalMenu[$key]['order'] = $menu[$key]['order'];
                        }
                    }
                    $finalMenu[$key]['children'] = $menu[$key]['children'];
                }

            }
        }

        /*echo "<pre>";
        print_r($finalMenu);
        echo "</pre>";
        exit;*/

        return $finalMenu;
    }

    /**
     * @param $menu
     * @param $permissions
     *
     * @return array
     */
    public function filterMenuByAcl($menu, $permissions, $realUrl = false) {
        $_menu = [];
        foreach ($menu as $parentKey => $parentNode) {
            $_childNodes = [];
            $_parentNode = [];
            //Dashboard is always allowed
            if ($parentNode['url']['controller'] === 'dashboards' && $parentNode['url']['action'] === 'index') {
                if ($realUrl) {
                    $parentNode['url_array'] = $parentNode['url'];
                    $parentNode['url'] = Router::url($parentNode['url']);
                }
                $_menu[$parentKey] = $parentNode;
                continue;
            }

            if (isset($parentNode['children']) && !empty($parentNode['children'])) {
                /*if ($this->checkPermissions($parentNode['url']['plugin'], $parentNode['url']['controller'], $parentNode['url']['action'], $permissions)) {
                    $_parentNode = $parentNode;
                    unset($_parentNode['children']);
                    // special way for maps becouse the are multiple logical root elements for the "maps" element
                } else if ($parentNode['url']['controller'] == 'statusmaps') {
                    if ($this->checkPermissions($parentNode['url']['plugin'], 'automaps', $parentNode['url']['action'], $permissions) ||
                        $this->checkPermissions('map_module', 'maps', $parentNode['url']['action'], $permissions) ||
                        $this->checkPermissions('map_module', 'rotations', $parentNode['url']['action'], $permissions)
                    ) {
                        $_parentNode = $parentNode;
                        unset($_parentNode['children']);
                    }
                }*/

                foreach ($parentNode['children'] as $childKey => $childNode) {
                    if (!isset($childNode['url']['plugin'])) {
                        $childNode['url']['plugin'] = '';
                    }
                    if ($this->checkPermissions($childNode['url']['plugin'], $childNode['url']['controller'], $childNode['url']['action'], $permissions)) {
                        if ($realUrl) {
                            $childNode['url_array'] = $childNode['url'];
                            $childNode['url'] = Router::url($childNode['url']);
                        }
                        $_childNodes[$childKey] = $childNode;
                    } else {
                        //Check if we have any fallback actions like by DowntimesController
                        if (isset($childNode['fallback_actions'])) {
                            if (!is_array($childNode['fallback_actions'])) {
                                $childNode['fallback_actions'] = [$childNode['fallback_actions']];
                            }
                            foreach ($childNode['fallback_actions'] as $fallbackAction) {
                                if ($this->checkPermissions($childNode['url']['plugin'], $childNode['url']['controller'], $fallbackAction, $permissions)) {
                                    $childNode['url']['action'] = $fallbackAction;
                                    if ($realUrl) {
                                        $childNode['url_array'] = $childNode['url'];
                                        $childNode['url'] = Router::url($childNode['url']);
                                    }
                                    $_childNodes[$childKey] = $childNode;
                                    break;
                                }
                            }
                        }
                    }
                }
                if(!empty($_childNodes) && isset($parentNode['children'])){
                    unset($parentNode['children']);
                    $_parentNode = $parentNode;
                }

            } else {
                if ($this->checkPermissions($parentNode['url']['plugin'], $parentNode['url']['controller'], $parentNode['url']['action'], $permissions)) {
                    if ($realUrl) {
                        $parentNode['url_array'] = $parentNode['url'];
                        $parentNode['url'] = Router::url($parentNode['url']);
                    }
                    if(isset($parentNode['children']) && empty($parentNode['children'])){
                        unset($parentNode['children']);
                    }
                    $_menu[$parentKey] = $parentNode;
                }
            }

            if (!empty($_childNodes) && !empty($_parentNode)) {
                $_menu[$parentKey] = $_parentNode;
                $_menu[$parentKey]['children'] = $_childNodes;
                /*if ($realUrl) {
                    if (is_array($_menu[$parentKey]['url'])) {
                        $_menu[$parentKey]['url_array'] = $_menu[$parentKey]['url'];
                        $_menu[$parentKey]['url'] = Router::url($_menu[$parentKey]['url']);
                    }
                }*/
            }
        }

        /*echo "<pre>";
        print_r($_menu);
        echo "</pre>";
        exit;*/

        return $_menu;
    }

    /**
     * @param $string
     *
     * @return string
     */
    public function lower($string) {
        //return strtolower(Inflector::classify($string));
        return strtolower(str_replace('_', '', $string));
    }

    /**
     * @param string $plugin
     * @param string $controller
     * @param string $action
     * @param        $permissions
     *
     * @return bool
     */
    public function checkPermissions($plugin = '', $controller = '', $action = '', $permissions) {
        $controller = $this->lower($controller);
        $action = $this->lower($action);
        if ($plugin === '') {
            return isset($permissions[$controller][$action]);
        } else {
            $plugin = $this->lower($plugin);

            return isset($permissions[$plugin][$controller][$action]);
        }
    }

    /**
     * @param array $menu
     * @return array
     */
    public function forAngular($menu) {
        $jsMenu = [];
        foreach ($menu as $parentKey => $_parentNode) {
            $_parentNode['id'] = $parentKey;

            $parentNode = $_parentNode;
            $parentNode['children'] = [];
            if (isset($_parentNode['children'])) {
                foreach ($_parentNode['children'] as $childKey => $childNode) {
                    $childNode['id'] = $childKey;
                    $parentNode['children'][] = $childNode;
                }
            }
            $jsMenu[] = $parentNode;
        }

        return $jsMenu;
    }
}

