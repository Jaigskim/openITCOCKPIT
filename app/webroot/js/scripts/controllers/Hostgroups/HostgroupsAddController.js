angular.module('openITCOCKPIT')
    .controller('HostgroupsAddController', function($scope, $http, $state, NotyService){

        $scope.data = {
            createAnother: false
        };

        var clearForm = function(){
            $scope.post = {
                Hostgroup: {
                    description: '',
                    hostgroup_url: '',
                    container: {
                        name: '',
                        parent_id: 0
                    },
                    hosts: {
                        _ids: []
                    },
                    hosttemplates: {
                        _ids: []
                    }
                }
            };
        };
        clearForm();

        $scope.init = true;
        $scope.load = function(){
            $http.get("/hostgroups/loadContainers.json", {
                params: {
                    'angular': true
                }
            }).then(function(result){
                $scope.containers = result.data.containers;
                $scope.init = false;
            });
        };

        $scope.loadHosts = function(searchString){
            $http.get("/hostgroups/loadHosts.json", {
                params: {
                    'angular': true,
                    'containerId': $scope.post.Hostgroup.container.parent_id,
                    'filter[Hosts.name]': searchString,
                    'selected[]': $scope.post.Hostgroup.hosts._ids
                }
            }).then(function(result){
                $scope.hosts = result.data.hosts;
            });
        };

        $scope.loadHosttemplates = function(searchString){
            $http.get("/hostgroups/loadHosttemplates.json", {
                params: {
                    'angular': true,
                    'containerId': $scope.post.Hostgroup.container.parent_id,
                    'filter[Hosttemplates.name]': searchString,
                    'selected[]': $scope.post.Hostgroup.hosttemplates._ids
                }
            }).then(function(result){
                $scope.hosttemplates = result.data.hosttemplates;
            });
        };

        $scope.submit = function(){
            $http.post("/hostgroups/add.json?angular=true",
                $scope.post
            ).then(function(result){
                var url = $state.href('HostgroupsEdit', {id: result.data.id});
                NotyService.genericSuccess({
                    message: '<u><a href="' + url + '" class="txt-color-white"> '
                        + $scope.successMessage.objectName
                        + '</a></u> ' + $scope.successMessage.message
                });

                if($scope.data.createAnother === false){
                    $state.go('HostgroupsIndex').then(function(){
                        NotyService.scrollTop();
                    });
                }else{
                    clearForm();
                    $scope.errors = {};
                    NotyService.scrollTop();
                }

                console.log('Data saved successfully');
            }, function errorCallback(result){

                NotyService.genericError();

                if(result.data.hasOwnProperty('error')){
                    $scope.errors = result.data.error;
                }
            });

        };


        $scope.$watch('post.Hostgroup.container.parent_id', function(){
            if($scope.init){
                return;
            }

            if($scope.post.Hostgroup.container.parent_id == 0){
                //Create another
                return;
            }

            $scope.loadHosts('');
            $scope.loadHosttemplates('');
        }, true);

        $scope.load();

    });