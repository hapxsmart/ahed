define([
    'jquery',
    'Aheadworks_Sarp2/js/customer/subscriptions/edit-payment/view/actions-toolbar/renderer/default',
    'Magento_Checkout/js/model/full-screen-loader'
], function ($, Component, fullScreenLoader) {
    'use strict';

    return Component.extend({

        /**
         * @inheritdoc
         */
        savePaymentDetails: function () {
            var self = this,
                nmiComponent;

            fullScreenLoader.startLoader();

            this._beforeAction().done(function () {
                nmiComponent = self._getMethodRenderComponent();
                nmiComponent.getNmiOnHandleCallbackDeferred().done(function () {
                    nmiComponent.placeOrderClick();
                });
            });
        },

        /**
         * @inheritdoc
         */
        validate: function (component) {
            return component.validateFormFields();
        },
    });
});
