(function(){

    var app = angular.module('TopTalApp', ['ngResource']);

    app.factory('UserService', [function() {
        var sdo = {
            username: null,
            auth_token:null,
            isLogged:function()
            {
                return this.auth_token !== null;
            }
        };
        return sdo;
    }]);

    app.factory('ActivitiesService',[function(){
        var activites = {
            activities:[]
        };
        return activites;
    }]);

    app.factory('sessionInjector', ['UserService',function(UserService) {
        var sessionInjector = {
            request: function(config)
            {
                if (UserService.isLogged()) {
                    config.headers['X-Toptal-Auth'] = UserService.auth_token;
                }
                return config;
            },
            response: function(response)
            {
                var auth = response.headers()['x-toptal-auth'];
                UserService.auth_token = auth!==undefined?auth:null;
                return response;
            },
            responseError: function(response)
            {
                var auth = response.headers()['x-toptal-auth'];
                UserService.auth_token = auth!==undefined?auth:null;
                return response;
            }
        };
        return sessionInjector;
    }]);

    app.config(['$httpProvider', function($httpProvider) {
        $httpProvider.interceptors.push('sessionInjector');
    }]);

    app.factory('Activity', ['$resource', function($resource)
        {
            return $resource( '/activity/:activityId', { activityId: '@activityId' },{'update': { method:'PUT' }});
        }
    ]);

    app.controller('PageController',['UserService', 'Activity', 'ActivitiesService',function(UserService, Activity, ActivitiesService){
        this.page = 'intro';
        this.userService = UserService;

        this.setCurrent = function(currentPage)
        {
            if (currentPage === 'activities'){
                Activity.query().$promise.then(function(activities) {
                    ActivitiesService.activities = activities;
                });

            }
            this.page = currentPage;
        };

        this.isCurrent = function(currentPage)
        {
            return this.page === currentPage;
        };
    }]);

    app.controller('SignUpController',['$http', 'UserService',function($http, UserService){
        this.user = {};
        this.userService = UserService;
        this.addUser = function(page)
        {
            $http.post('/user',this.user).success(function(data){
                UserService.username = data.username;
            }).then(function(){
                    page.setCurrent('activities');
                }
            );
        }
    }]);

    app.controller('SignInController',['$http', 'UserService',function($http, UserService){
        this.user = {};
        this.loginUser = function(page)
        {
            $http.get('/user?username='+this.user.username+'&password='+this.user.password).success(function(data){
                UserService.username = data.username;
            }).then(function(){
                    page.setCurrent('activities');
                }
            );
        };
        this.userService = UserService;
    }]);

    app.controller('ActivityController',['Activity', 'UserService', 'ActivitiesService', '$filter', function(Activity, UserService, ActivitiesService, $filter){
        this.actS = ActivitiesService;
        this.activity = {};
        this.filters = {
            workingHours:null,
            from:null,
            to:null
        };

        this.activitiesDump = null;

        var thisObj = this;

        this.filterChanged = function()
        {
            if (this.activitiesDump === null){
                this.activitiesDump = ActivitiesService.activities;
            }else{
                ActivitiesService.activities = this.activitiesDump;
            }

            if (this.filters.workingHours > 0){
                var dateHours = {};

                for(var index in ActivitiesService.activities)
                {
                    var activity = ActivitiesService.activities[index];
                    if (dateHours[activity.date] !== undefined){
                        dateHours[activity.date]+=activity.duration;
                    }else{
                        dateHours[activity.date]=activity.duration;
                    }
                }

                for(var index in ActivitiesService.activities)
                {
                    var activity = ActivitiesService.activities[index];
                    if (dateHours[activity.date] < this.filters.workingHours){
                        ActivitiesService.activities[index]['underhour'] = true;
                    }else{
                        ActivitiesService.activities[index]['underhour'] = false;
                    }
                }
            }

            if (this.filters.from || this.filters.to){
                ActivitiesService.activities = $filter('filter')(ActivitiesService.activities,function(value,index){
                    var date = new Date(value.date);
                    return (thisObj.filters.from?(date >= thisObj.filters.from):true) && (thisObj.filters.to?(date <= thisObj.filters.to):true);
                });
            }
        };

        this.addActivity = function()
        {
            Activity.save({},this.activity).$promise.then(function(activity) {
                ActivitiesService.activities.push(activity);
                thisObj.activity = {};
            });
        };

        this.clearActivity = function()
        {
            thisObj.activity = {};
        };

        this.editActivity = function()
        {
            Activity.update({activityId:this.activity.id},this.activity).$promise.then(function(activity) {
                thisObj.activity = {};
            });
        };

        this.initEdit = function(activity)
        {
            activity.date = new Date(activity.date);
            this.activity = activity;

        };

        this.deleteActivity = function(activity)
        {
            Activity.delete({activityId:activity.id}).$promise.then(function() {
                ActivitiesService.activities = $filter('filter')(ActivitiesService.activities,function(value,index){return value.id !== activity.id});
            });
        }


    }]);


    app.directive('signIn',function(){
       return {
           restrict:'E',
           templateUrl:"sign-in.html"
       };
    });

    app.directive('introText',function(){
        return {
            restrict:'E',
            templateUrl:"intro.html"
        };
    });

    app.directive('signUp',function(){
        return {
            restrict:'E',
            templateUrl:"sign-up.html"
        };
    });

    app.directive('activities',function(){
        return {
            restrict:'E',
            templateUrl:"activities.html"
        };
    })

})();