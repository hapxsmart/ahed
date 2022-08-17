define([
    'jquery',
    'Aheadworks_Sarp2/js/customer/subscriptions/edit-payment/view/actions-toolbar/renderer/default'
], function ($, Component) {
    'use strict';

    return Component.extend({

        /**
         * @inheritDoc
         */
        savePaymentDetails: function (data, event) {
            var component = this._getMethodRenderComponent();

            if (event) {
                event.preventDefault();
            }
            this.validate(component);
            component.beforePlaceOrder();
        }
    });
});
