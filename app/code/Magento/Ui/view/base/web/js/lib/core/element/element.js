/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'underscore',
    'mageUtils',
    'uiRegistry',
    'uiEvents',
    'uiClass',
    './links',
    '../storage'
], function (ko, _, utils, registry, Events, Class, links) {
    'use strict';

    var Module;

    /**
     * Creates observable property using knockouts'
     * 'observableArray' or 'observable' methods,
     * depending on a type of 'value' parameter.
     *
     * @param {Object} obj - Object to whom property belongs.
     * @param {String} key - Key of the property.
     * @param {*} value - Initial value.
     */
    function observable(obj, key, value) {
        var method = Array.isArray(value) ? 'observableArray' : 'observable';

        if (_.isFunction(obj[key]) && !ko.isObservable(obj[key])) {
            return;
        }

        if (ko.isObservable(value)) {
            value = value();
        }

        ko.isObservable(obj[key]) ?
            obj[key](value) :
            obj[key] = ko[method](value);
    }

    /**
     * Creates observable propery using 'track' method.
     *
     * @param {Object} obj - Object to whom property belongs.
     * @param {String} key - Key of the property.
     * @param {*} value - Initial value.
     */
    function accessor(obj, key, value) {
        if (_.isFunction(obj[key]) || ko.isObservable(obj[key])) {
            return;
        }

        obj[key] = value;

        if (!ko.es5.isTracked(obj, key)) {
            ko.track(obj, [key]);
        }
    }

    Module = _.extend({
        defaults: {
            template: '',
            registerNodes: true,
            storageConfig: {
                provider: 'localStorage',
                namespace: '${ $.name }',
                path: '${ $.storageConfig.provider }:${ $.storageConfig.namespace }'
            },
            maps: {
                imports: {},
                exports: {}
            },
            modules: {
                storage: '${ $.storageConfig.provider }'
            }
        },

        /**
         * Initializes model instance.
         *
         * @returns {Element} Chainable.
         */
        initialize: function () {
            this._super()
                .initObservable()
                .initModules()
                .initStatefull()
                .initLinks();

            return this;
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Element} Chainable.
         */
        initObservable: function () {
            return this;
        },

        /**
         * Initializes statefull properties
         * based on the keys of 'statefull' object.
         *
         * @returns {Element} Chainable.
         */
        initStatefull: function () {
            var statefull = this.statefull || {};

            _.each(statefull, function (path, key) {
                if (!path) {
                    return;
                }

                this.setStatefull(key, path);
            }, this);

            return this;
        },

        /**
         * Parses 'modules' object and creates
         * async wrappers for specified components.
         *
         * @returns {Element} Chainable.
         */
        initModules: function () {
            var modules = this.modules || {};

            _.each(modules, function (component, property) {
                this[property] = registry.async(component);
            }, this);

            return this;
        },

        /**
         * Initializes links between properties.
         *
         * @returns {Element} Chainbale.
         */
        initLinks: function () {
            this.setListeners(this.listens)
                .setLinks(this.links, 'imports')
                .setLinks(this.links, 'exports');

            _.each({
                exports: this.exports,
                imports: this.imports
            }, this.setLinks, this);

            return this;
        },

        /**
         * Makes specified property to be stored automatically.
         *
         * @param {String} key - Name of the property
         *      that will be stored.
         * @param {String} [path=key] - Path to the property in storage.
         * @returns {Element} Chainable.
         */
        setStatefull: function (key, path) {
            var link = {};

            path        = !_.isString(path) || !path ? key : path;
            link[key]   = this.storageConfig.path + '.' + path;

            this.setLinks(link, 'imports')
                .setLinks(link, 'exports');

            return this;
        },

        /**
         * Returns path to elements' template.
         *
         * @returns {String}
         */
        getTemplate: function () {
            return this.template;
        },

        /**
         * Returns value of the nested property.
         *
         * @param {String} path - Path to the property.
         * @returns {*} Value of the property.
         */
        get: function (path) {
            return utils.nested(this, path);
        },

        /**
         * Sets provided value as a value of the specified nested property.
         * Triggers changes notifications, if value has mutated.
         *
         * @param {String} path - Path to property.
         * @param {*} value - New value of the property.
         * @returns {Element} Chainable.
         */
        set: function (path, value) {
            var data = this.get(path),
                diffs;

            if (!_.isFunction(data)) {
                diffs = utils.compare(data, value, path);

                utils.nested(this, path, value);

                this._notifyChanges(diffs);
            } else {
                utils.nested(this, path, value);
            }

            return this;
        },

        /**
         * Removes nested property from the object.
         *
         * @param {String} path - Path to the property.
         * @returns {Element} Chainable.
         */
        remove: function (path) {
            var data,
                diffs;

            if (!path) {
                return this;
            }

            data = utils.nested(this, path);

            if (!_.isUndefined(data) && !_.isFunction(data)) {
                diffs = utils.compare(data, undefined, path);

                utils.nestedRemove(this, path);

                this._notifyChanges(diffs);
            }

            return this;
        },

        /**
         * Destroys current instance along with all of its' children.
         */
        destroy: function () {
            this._dropHandlers()
                ._clearRefs();
        },

        /**
         * Removes events listeners.
         * @private
         *
         * @returns {Element} Chainable.
         */
        _dropHandlers: function () {
            this.off();

            return this;
        },

        /**
         * Removes all references to current instance and
         * calls 'destroy' method on all of its' children.
         * @private
         *
         * @returns {Element} Chainable.
         */
        _clearRefs: function () {
            registry.remove(this.name);

            return this;
        },

        /**
         * Creates observable properties for the current object.
         *
         * If 'useTrack' flag is set to 'true' then each property will be
         * created with a ES5 get/set accessor descriptors, instead of
         * making them an observable functions.
         * See 'knockout-es5' library for more information.
         *
         * @param {Boolean} [useAccessors=false] - Whether to create an
         *      observable function or to use property accesessors.
         * @param {(Object|String|Array)} properties - List of observable properties.
         * @returns {Element} Chainable.
         *
         * @example Sample declaration and equivalent knockout methods.
         *      this.key = 'value';
         *      this.array = ['value'];
         *
         *      this.observe(['key', 'array']);
         *      =>
         *          this.key = ko.observable('value');
         *          this.array = ko.observableArray(['value']);
         *
         * @example Another syntaxes of the previous example.
         *      this.observe({
         *          key: 'value',
         *          array: ['value']
         *      });
         */
        observe: function (useAccessors, properties) {
            var model = this,
                trackMethod;

            if (typeof useAccessors !== 'boolean') {
                properties   = useAccessors;
                useAccessors = false;
            }

            trackMethod = useAccessors ? accessor : observable;

            if (_.isString(properties)) {
                properties = properties.split(' ');
            }

            if (Array.isArray(properties)) {
                properties.forEach(function (key) {
                    trackMethod(model, key, model[key]);
                });
            } else if (typeof properties === 'object') {
                _.each(properties, function (value, key) {
                    trackMethod(model, key, value);
                });
            }

            return this;
        },

        /**
         * Delegates call to 'observe' method but
         * with a predefined 'useAccessors' flag.
         *
         * @param {(String|Array|Object)} properties - List of observable properties.
         * @returns {Element} Chainable.
         */
        track: function (properties) {
            this.observe(true, properties);

            return this;
        },

        /**
         * Invokes subscribers for the provided changes.
         *
         * @param {Object} diffs - Object with changes descriptions.
         * @returns {Element} Chainable.
         */
        _notifyChanges: function (diffs) {
            diffs.changes.forEach(function (change) {
                this.trigger(change.path, change.value, change);
            }, this);

            _.each(diffs.containers, function (changes, name) {
                var value = utils.nested(this, name);

                this.trigger(name, value, changes);
            }, this);

            return this;
        },

        /**
         * Extracts all stored data and sets it to element.
         *
         * @returns {Element} Chainable.
         */
        restore: function () {
            var ns = this.storageConfig.namespace,
                storage = this.storage();

            if (storage) {
                utils.extend(this, storage.get(ns));
            }

            return this;
        },

        /**
         * Stores value of the specified property in components' storage module.
         *
         * @param {String} property
         * @param {*} [data=this[property]]
         * @returns {Element} Chainable.
         */
        store: function (property, data) {
            var ns = this.storageConfig.namespace,
                path = utils.fullPath(ns, property);

            if (arguments.length < 2) {
                data = this.get(property);
            }

            this.storage('set', path, data);

            return this;
        },

        /**
         * Extracts specified property from storage.
         *
         * @param {String} [property] - Name of the property
         *      to be extracted. If not specified then all of the
         *      stored will be returned.
         * @returns {*}
         */
        getStored: function (property) {
            var ns = this.storageConfig.namespace,
                path = utils.fullPath(ns, property),
                storage = this.storage(),
                data;

            if (storage) {
                data = storage.get(path);
            }

            return data;
        },

        /**
         * Removes stored property.
         *
         * @param {String} property - Property to be removed from storage.
         * @returns {Element} Chainable.
         */
        removeStored: function (property) {
            var ns = this.storageConfig.namespace,
                path = utils.fullPath(ns, property);

            this.storage('remove', path);

            return this;
        }
    }, Events, links);

    return Class.extend(Module);
});
