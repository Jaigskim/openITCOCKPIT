angular.module('openITCOCKPIT').directive('templateDiff', function($http, $interval){
    return {
        restrict: 'E',
        templateUrl: '/angular/template_diff.html',
        replace: true,
        scope: {
            'value': '=',
            'templateValue': '=',
            'callback': '=',
            'field': '=',
        },

        controller: function($scope){

            $scope.hasDiff = false;

            $scope.restoreDefault = function(){
                $scope.callback($scope.field);
            };

            $scope.$watch('value', function(){
                $scope.hasDiff = $scope.value != $scope.templateValue;
            });

        },

        link: function(scope, element, attr){
        }
    };
});