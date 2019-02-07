angular.module('openITCOCKPIT')
    .controller('NotificationsServiceNotificationController', function($scope, $http, $rootScope, $httpParamSerializer, SortService, QueryStringService, $stateParams, StatusHelperService, $interval){

        SortService.setSort(QueryStringService.getValue('sort', 'NotificationService.start_time'));
        SortService.setDirection(QueryStringService.getValue('direction', 'desc'));
        $scope.currentPage = 1;

        $scope.id = $stateParams.id;
        $scope.useScroll = true;

        var now = new Date();
        var flappingInterval;

        /*** Filter Settings ***/
        var defaultFilter = function(){
            $scope.filter = {
                Notification: {
                    state: {
                        ok: false,
                        warning: false,
                        critical: false,
                        unknown: false
                    },
                    state_types: {
                        soft: false,
                        hard: false
                    },
                    output: '',
                    author: ''
                },
                from: date('d.m.Y H:i', now.getTime() / 1000 - (3600 * 24 * 30)),
                to: date('d.m.Y H:i', now.getTime() / 1000 + (3600 * 24 * 30 * 2))
            };
        };
        /*** Filter end ***/

        $scope.init = true;
        $scope.showFilter = false;


        $scope.load = function(){

            $http.get("/notifications/serviceNotification/" + $scope.id + ".json", {
                params: {
                    'angular': true,
                    'scroll': $scope.useScroll,
                    'sort': SortService.getSort(),
                    'page': $scope.currentPage,
                    'direction': SortService.getDirection(),
                    'filter[NotificationService.output]': $scope.filter.Notification.output,
                    'filter[NotificationService.state][]': $rootScope.currentStateForApi($scope.filter.Notification.state),
                    'filter[from]': $scope.filter.from,
                    'filter[to]': $scope.filter.to
                }
            }).then(function(result){
                $scope.notifications = result.data.all_notifications;
                $scope.paging = result.data.paging;
                $scope.scroll = result.data.scroll;
                $scope.init = false;
            });

            $http.get("/services/serviceBrowserMenu/" + $scope.id + ".json", {
                params: {
                    'angular': true
                }
            }).then(function(result) {
                $scope.service = result.data.service;
                $scope.servicestatus = result.data.servicestatus;
                $scope.serviceStatusTextClass = StatusHelperService.getServicestatusTextColor($scope.servicestatus.currentState);

                $scope.serviceBrowserMenu = {
                    hostId: $scope.service.Host.id,
                    hostUuid: $scope.service.Host.uuid,
                    serviceId: $scope.service.Service.id,
                    serviceUuid: $scope.service.Service.uuid,
                    serviceType: $scope.service.Service.service_type,
                    allowEdit: $scope.service.Service.allowEdit,
                    serviceUrl: $scope.service.Service.service_url_replaced,
                    docuExists: result.data.docuExists,
                    isServiceBrowser: false
                };
            });
        };

        $scope.triggerFilter = function(){
            $scope.showFilter = !$scope.showFilter === true;
        };

        $scope.resetFilter = function(){
            defaultFilter();
        };


        $scope.changepage = function(page){
            if(page !== $scope.currentPage){
                $scope.currentPage = page;
                $scope.load();
            }
        };

        $scope.changeMode = function(val){
            $scope.useScroll = val;
            $scope.load();
        };

        $scope.startFlapping = function() {
            $scope.stopFlapping();
            flappingInterval = $interval(function() {
                if ($scope.flappingState === 0) {
                    $scope.flappingState = 1;
                } else {
                    $scope.flappingState = 0;
                }
            }, 750);
        };

        $scope.stopFlapping = function() {
            if (flappingInterval) {
                $interval.cancel(flappingInterval);
            }
            flappingInterval = null;
        };

        //Fire on page load
        defaultFilter();
        SortService.setCallback($scope.load);

        $scope.$watch('filter', function(){
            $scope.currentPage = 1;
            $scope.load();
        }, true);

        $scope.$watch('servicestatus.isFlapping', function() {
            if ($scope.servicestatus) {
                if ($scope.servicestatus.hasOwnProperty('isFlapping')) {
                    if ($scope.servicestatus.isFlapping === true) {
                        $scope.startFlapping();
                    }

                    if ($scope.servicestatus.isFlapping === false) {
                        $scope.stopFlapping();
                    }

                }
            }
        });

    });