angular.module('openITCOCKPIT')
    .controller('ContactgroupsEditController', function($scope, $http, SudoService, $state, $stateParams, NotyService){

        $scope.id = $stateParams.id;

        $scope.init = true;


        $scope.loadContainers = function(){
            var params = {
                'angular': true
            };

            $http.get("/contactgroups/loadContainers.json", {
                params: params
            }).then(function(result){
                $scope.containers = result.data.containers;
                $scope.init = false;
            });
        };

        $scope.loadContactgroup = function(){
            var params = {
                'angular': true
            };

            $http.get("/contactgroups/edit/" + $scope.id + ".json", {
                params: params
            }).then(function(result){
                $scope.post = result.data.contactgroup;
                $scope.init = false;
            }, function errorCallback(result){
                if(result.status === 403){
                    $state.go('403');
                }

                if(result.status === 404){
                    $state.go('404');
                }
            });
        };

        $scope.loadContacts = function(){
            var id = $scope.post.Contactgroup.container.parent_id;
            $http.post("/contactgroups/loadContacts/" + id + ".json?angular=true", {}).then(function(result){
                $scope.contacts = result.data.contacts;
            });
        };


        $scope.submit = function(){
            $http.post("/contactgroups/edit/" + $scope.id + ".json?angular=true",
                $scope.post
            ).then(function(result){
                NotyService.genericSuccess();
                $state.go('ContactgroupsIndex').then(function(){
                    NotyService.scrollTop();
                });

                console.log('Data saved successfully');
            }, function errorCallback(result){

                NotyService.genericError();

                if(result.data.hasOwnProperty('error')){
                    $scope.errors = result.data.error;
                }
            });

        };


        $scope.loadContainers();
        $scope.loadContactgroup();

        $scope.$watch('post.Contactgroup.container.parent_id', function(){
            if($scope.init){
                return;
            }
            $scope.loadContacts();
        }, true);

    });
