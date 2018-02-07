angular.module('openITCOCKPIT')
    .controller('RotationsAddController', function($scope, $http){

        $scope.post = {
            Rotation: {
                name: '',
                interval: 90,
                container_id: [],
                Map: []
            }
        };

        $scope.loadMaps = function(){
            $http.get("/map_module/rotations/loadMaps.json", {
                params: {
                    'angular': true
                }
            }).then(function(result){
                console.log(result.data);
                $scope.maps = result.data.maps;
            });
        };

        $scope.loadContainers = function(){
            $http.get("/map_module/rotations/loadContainers.json", {
                params: {
                    'angular': true
                }
            }).then(function(result){
                console.log(result.data);
                $scope.containers = result.data.containers;
            });
        };

        $scope.submit = function(){
            $http.post("/map_module/rotations/add.json?angular=true",
                $scope.post
            ).then(function(result){
                console.log('Data saved successfully');
                window.location.href = '/map_module/rotations/index';
            }, function errorCallback(result){
                if(result.data.hasOwnProperty('error')){
                    $scope.errors = result.data.error;
                }
            });

        };

        $scope.$watch('post', function(){
            console.log($scope.post);
        }, true);

        $scope.loadMaps();
        $scope.loadContainers();

    });