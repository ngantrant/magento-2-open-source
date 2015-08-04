/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('select-version', ['ngStorage'])
    .controller('selectVersionController', ['$scope', '$http', '$localStorage', function ($scope, $http, $localStorage) {
        $scope.packages = [{
            name: '',
            version: ''
        }];
        $scope.readyForNext = false;
        $scope.upgradeProcessed = false;
        $scope.upgradeProcessError = false;
        $scope.componentsProcessed = false;
        $scope.componentsProcessError = false;

        $http.get('index.php/select-version/systemPackage', {'responseType' : 'json'})
            .success(function (data) {
                if (data.responseType != 'error') {
                    $scope.versions = data.package.versions;
                    $scope.packages[0].name = data.package.package;
                    $scope.packages[0].version = $scope.versions[0].id;
                    $scope.selectedOption = $scope.versions[0].id;
                    $scope.readyForNext = true;
                } else {
                    $scope.upgradeProcessError = true;
                }
                $scope.upgradeProcessed = true;
            })
            .error(function (data) {
                $scope.upgradeProcessError = true;
            });

        $scope.updateComponents = {
            yes: false,
            no: true
        };
        $scope.$watch('updateComponents.no', function() {
            if (angular.equals($scope.updateComponents.no, true)) {
                $scope.updateComponents.yes = false;
            }
        });

        $scope.$watch('updateComponents.yes', function() {
            if (angular.equals($scope.updateComponents.yes, true)
                && !$scope.componentsProcessed && !$scope.componentsProcessError) {
                $scope.readyForNext = false;
                $http.get('index.php/other-components-grid/components', {'responseType' : 'json'}).
                    success(function(data) {
                        if (data.responseType != 'error') {
                            $scope.components = data.components;
                            $scope.total = data.total;
                            var keys = Object.keys($scope.components);
                            for (var i = 0; i < $scope.total; i++) {
                                $scope.packages.push({
                                    name: keys[i],
                                    version: $scope.components[keys[i]].updates[0]
                                });
                            }
                            $scope.readyForNext = true;
                        } else {
                            $scope.componentsProcessError = true;
                        }
                        $scope.componentsProcessed = true;
                    })
                    .error(function (data) {
                        $scope.componentsProcessError = true;
                    });
            }
            if (angular.equals($scope.updateComponents.yes, true)) {
                $scope.updateComponents.no = false;
            }
        });

        $scope.updateOtherComponentsList = function(name, $version) {
            for (var i = 0; i < $scope.total; i++) {
                if ($scope.packages[i + 1].name === name) {
                    $scope.packages[i + 1].version = $version;
                    $scope.components[i].version = $version;
                }
            }
        };

        $scope.removeComponentsFromList = function(name) {
            var found = false;
            for (var i = 0; i < $scope.total; i++) {
                if ($scope.packages[i + 1].name === name) {
                    $scope.packages.splice(i + 1, 1);
                    $scope.total = $scope.total - 1;
                    found = true;
                }
            }
            if (!found) {
                $scope.packages.push({
                    name: name,
                    version: $scope.components[name].updates[0]
                });
                $scope.total = $scope.total + 1;
            }
        };

        $scope.update = function() {
            $scope.packages[0].version = $scope.selectedOption;
            if (angular.equals($scope.updateComponents.no, true)) {
                if ($scope.total > 0) {
                    $scope.packages.splice(1, $scope.total);
                }
            } else {
                for (var i = 0; i < $scope.total; i++) {
                    if ($scope.packages[i + 1].version.indexOf(" (latest)") > -1) {
                        $scope.packages[i + 1].version = $scope.packages[i + 1].version.replace(" (latest)", "");
                    }
                }
            }
            $localStorage.packages = $scope.packages;
            $scope.nextState();
        };
    }]);
