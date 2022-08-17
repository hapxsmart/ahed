define([
    'jquery',
    'Aheadworks_Sarp2/js/customer/subscriptions/edit-payment/view/actions-toolbar/renderer/default'
], function ($, Component) {
    'use strict';

    return Component.extend({

        /**
         * @inheritdoc
         */
        savePaymentDetails: function () {
            var self = this;

            this._beforeAction().done(function () {
                self._getMethodRenderComponent().placeOrderClick();
            });
        },

        /**
         * @inheritdoc
         */
        applySaveAction: function (key) {
            if (!key) {
                return this.origPlaceOrderAction();
            }

            return this._super();
        },

        /**
         * @inheritdoc
         */
        validate: function (component) {
            return this._super() && component.validateFormFields()
        },
    });
});
