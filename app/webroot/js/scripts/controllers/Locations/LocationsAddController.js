angular.module('openITCOCKPIT')
    .controller('LocationsAddController', function($scope, $http, $state, NotyService){

        $scope.data = {
            createAnother: false
        };

        var clearForm = function(){
            $scope.post = {
                id: 0,
                description: '',
                latitude: '',
                longitude: '',
                timezone: null,
                container: {
                    name: '',
                    parent_id: null
                }
            };
        };
        clearForm();

        $scope.load = function(){
            $http.get("/locations/loadContainers.json", {
                params: {
                    'angular': true
                }
            }).then(function(result){
                $scope.containers = result.data.containers;
                $scope.init = false;
            });
        };

        $scope.submit = function(){
            console.log($scope.post);
            //return;
            $http.post("/locations/add.json?angular=true",
                $scope.post
            ).then(function(result){

                var url = $state.href('LocationsEdit', {id: result.data.id});
                NotyService.genericSuccess({
                    message: '<u><a href="' + url + '" class="txt-color-white"> '
                        + $scope.successMessage.objectName
                        + '</a></u> ' + $scope.successMessage.message
                });

                if($scope.data.createAnother === false){
                    $state.go('LocationsIndex').then(function(){
                        NotyService.scrollTop();
                    });
                }else{
                    clearForm();
                    NotyService.scrollTop();
                }

            }, function errorCallback(result){
                NotyService.genericError();
                if(result.data.hasOwnProperty('error')){
                    $scope.errors = result.data.error;
                }
            });
        };

        $scope.load();

    });
