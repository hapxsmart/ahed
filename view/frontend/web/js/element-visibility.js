define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('mage.awSarp2ElementVisibility', {
        options: {
            elementSelector: '',
            eventName: 'updateVisibility',
            eventTarget: ''
        },

        /**
         * @inheritdoc
         */
        _create: function () {
            this._bind();
        },

        /**
         * Event binding
         */
        _bind: function () {
            var handlers = {};

            handlers[this.options.eventName + ' ' + this.options.eventTarget] = 'handleVisibility';
            this._on(handlers);
        },

        /**
         * Handle element visibility
         *
         * @param {Event} event
         * @param {*} value
         */
        handleVisibility: function (event, value) {
            var isVisible = value == 0,
                element = this.element.find(this.options.elementSelector);

            if (isVisible) {
                element.show();
            } else {
                element.hide();
            }
        }
    });

    return $.mage.awSarp2ElementVisibility;
});
