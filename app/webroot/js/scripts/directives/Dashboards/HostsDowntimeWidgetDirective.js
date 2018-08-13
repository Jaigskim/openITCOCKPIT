angular.module('openITCOCKPIT').directive('hostsDowntimeWidget', function($http){
    return {
        restrict: 'E',
        templateUrl: '/dashboards/hostsDowntimeWidget.html',
        scope: {
            'widget': '='
        },

        controller: function($scope){


            $scope.load = function(){
                $http.get("/angular/statuscount.json", {
                    params: {
                        'angular': true,
                        'recursive': true
                    }
                }).then(function(result){
                    $scope.hostCount = result.data.hoststatusSum;
                    $scope.serviceCount = result.data.servicestatusSum;
                    $scope.init = false;
                });
            };

            $scope.load();

        },

        link: function($scope, element, attr){

        }
    };
});
