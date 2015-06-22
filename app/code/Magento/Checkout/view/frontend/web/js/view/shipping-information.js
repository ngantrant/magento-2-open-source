/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/model/step-navigator'
    ],
    function($, Component, quote, shippingService, stepNavigator) {
        'use strict';
        var countryData = window.checkoutConfig.countryData;
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping-information'
            },
            shippingAddress: quote.shippingAddress,

            isVisible: function() {
                return !quote.isVirtual() && stepNavigator.isProcessed('shipping');
            },

            getCountryName: function(countryId) {
                return (countryData[countryId] != undefined) ? countryData[countryId].name : "";
            },

            getShippingMethodTitle: function() {
                return shippingService.getTitleByCode(quote.shippingMethod())
            },

            back: function() {
                stepNavigator.back();
            }
        });
    }
);
