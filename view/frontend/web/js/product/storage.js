define([
], function () {
    'use strict';

    var privateData = {};

    return {

        /**
         * Retrieve storage data by key
         *
         * @param {String} key
         * @returns {Object}
         */
        get: function (key) {
            return privateData[key];
        },

        /**
         * Set data to storage by key
         *
         * @param {String} key
         * @param {Object} value
         */
        set: function (key, value) {
            privateData[key] = value;
        },

        /**
         * Remove data from storage by key
         *
         * @param {String} key
         */
        remove: function (key) {
            delete(privateData[key]);
        }
    }
});
