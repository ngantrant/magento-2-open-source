/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'Magento_Ui/js/lib/collapsible'
], function ($, _, Collapsible) {
    'use strict';

    return Collapsible.extend({

        defaults: {
            template: 'ui/grid/exportButton',
            params: {
                filters: {}
            },
            filtersConfig: {
                provider: '${ $.provider }',
                path: 'params.filters'
            },
            imports: {
                'params.filters': '${ $.filtersConfig.provider }:${ $.filtersConfig.path }'
            }
        },

        applyOption: function (action) {
            location.href = action.url + '?' + $.param({'filters': this.params.filters});
        }
    });
});
