angular.module('openITCOCKPIT')
    .controller('SystemdowntimesAddServicedowntimeController', function($scope, $state, $http, QueryStringService, $stateParams, NotyService, RedirectService){

            $scope.init = true;

            $scope.data = {
                createAnother: false
            };

            var clearForm = function(){
                $scope.post = {
                    Systemdowntime: {
                        is_recurring: 0,
                        weekdays: {},
                        day_of_month: '',
                        from_date: '',
                        from_time: '',
                        to_date: '',
                        to_time: '',
                        duration: 15,
                        downtimetype: 'service',
                        downtimetype_id: '0',
                        objecttype_id: 2048,     //OBJECT_SERVICE
                        object_id: [],
                        comment: '',
                        is_recursive: 0
                    }
                };
            };
            clearForm();

            if($stateParams.id !== null){
                $scope.post.Systemdowntime.object_id.push(parseInt($stateParams.id, 10));
            }

            $scope.loadDefaults = function(){
                $http.get("/angular/getDowntimeData.json", {
                    params: {
                        'angular': true
                    }
                }).then(function(result){
                    var fromDate = $scope.parseDateTime(result.data.defaultValues.js_from);
                    $scope.post.Systemdowntime.from_date = fromDate;
                    $scope.post.Systemdowntime.from_time = fromDate;
                    var toDate = $scope.parseDateTime(result.data.defaultValues.js_to);
                    $scope.post.Systemdowntime.to_date = toDate;
                    $scope.post.Systemdowntime.to_time = toDate;
                    $scope.post.Systemdowntime.comment = result.data.defaultValues.comment;
                    $scope.post.Systemdowntime.duration = result.data.defaultValues.duration;
                    $scope.post.Systemdowntime.downtimetype_id = result.data.defaultValues.downtimetype_id;
                });
            };

            $scope.parseDateTime = function(jsStringData) {
                var splitData = jsStringData.split(',');
                var date = new Date(splitData[0], splitData[1] - 1, splitData[2]);
                date.setHours(splitData[3], splitData[4], "00");
                return date;
            };

            $scope.loadServices = function(searchString){
                $http.get("/services/loadServicesByStringCake4/1.json", {
                    params: {
                        'angular': true,
                        'filter[servicename]': searchString,
                        'selected[]': $scope.post.Systemdowntime.object_id
                    }
                }).then(function(result){
                    $scope.services = result.data.services;
                });
            };

            $scope.submit = function(){
                var submitObject = {
                    Systemdowntime: {
                        is_recurring: $scope.post.Systemdowntime.is_recurring,
                        weekdays: $scope.post.Systemdowntime.weekdays,
                        day_of_month: $scope.post.Systemdowntime.day_of_month,
                        from_date: $scope.post.Systemdowntime.from_date.toLocaleDateString('de-DE', {day:"2-digit", month: "2-digit", year:"numeric"}),
                        from_time: $scope.post.Systemdowntime.from_time.toLocaleTimeString('de-DE', {hour:"2-digit", minute: "2-digit"}),
                        to_date: $scope.post.Systemdowntime.to_date.toLocaleDateString('de-DE', {day:"2-digit", month: "2-digit", year:"numeric"}),
                        to_time: $scope.post.Systemdowntime.to_time.toLocaleTimeString('de-DE', {hour:"2-digit", minute: "2-digit"}),
                        duration: $scope.post.Systemdowntime.duration,
                        downtimetype: $scope.post.Systemdowntime.downtimetype,
                        downtimetype_id: $scope.post.Systemdowntime.downtimetype_id,
                        objecttype_id: $scope.post.Systemdowntime.objecttype_id,     //OBJECT_HOST
                        object_id: $scope.post.Systemdowntime.object_id,
                        comment: $scope.post.Systemdowntime.comment,
                        is_recursive: $scope.post.Systemdowntime.is_recursive
                    }
                };

                $http.post("/systemdowntimes/addServicedowntime.json?angular=true",
                    submitObject
                ).then(function(result){
                    NotyService.genericSuccess({
                        message: $scope.successMessage.objectName + ' ' + $scope.successMessage.message
                    });

                    if($scope.data.createAnother === false){
                        if($scope.post.Systemdowntime.is_recurring){
                            RedirectService.redirectWithFallback('SystemdowntimesService');
                            return;
                        }

                        RedirectService.redirectWithFallback('DowntimesService');
                    }else{
                        clearForm();
                        $scope.loadDefaults();
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

            //Fire on page load
            $scope.loadDefaults();
            $scope.loadServices('');
        }
    );
