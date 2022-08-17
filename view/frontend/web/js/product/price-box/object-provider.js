define([
    'jquery',
], function ($) {
    'use strict';

    return {
        /**
         * Retrieve price-box jQuery element by selector
         *
         * @param {String} selector
         * @return {jQuery}
         */
        getDomElement: function (selector) {
            return $(selector);
        },

        /**
         * Retrieve price-box widget object by selector
         *
         * @param {String} selector
         * @return {Object|null}
         */
        getWidgetObject: function (selector) {
            var element = this.getDomElement(selector);

            return element.length
                ? element.data(this._getWidgetDataObjectId())
                : null;
        },

        /**
         * Get price-box widget data id
         *
         * @return {string}
         * @private
         */
        _getWidgetDataObjectId: function () {
            return 'magePriceBox'
        }
    };
});
