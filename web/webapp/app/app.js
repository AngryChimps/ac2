'use strict';

// Declare app level module which depends on views, and components
angular.module('myApp', [
    'ngRoute',
    'myApp.view1',
    'myApp.view2',
    'myApp.version',
    'facebook'
])
    .config(['$routeProvider', function($routeProvider) {
        $routeProvider.otherwise({redirectTo: '/view1'});
    }])
    .config(function(FacebookProvider) {
        // Set your appId through the setAppId method or
        // use the shortcut in the initialize method directly.
        FacebookProvider.init('378121139011542');
    })

    .controller('authenticationCtrl', function($scope, Facebook) {

        $scope.login = function() {
            console.log('logging in');
            // From now on you can use the Facebook service just as Facebook api says
            Facebook.login(function(response) {
console.log(response);
            });
        };

        $scope.getLoginStatus = function() {
            Facebook.getLoginStatus(function(response) {
                if(response.status === 'connected') {
                    $scope.loggedIn = true;
                } else {
                    $scope.loggedIn = false;
                }
            });
        };

        $scope.me = function() {
            Facebook.api('/me', function(response) {
                $scope.user = response;
            });
        };
    });;
