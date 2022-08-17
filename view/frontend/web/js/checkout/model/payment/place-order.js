define([
    'jquery',
    'Aheadworks_Sarp2/js/checkout/action/submit-cart',
    'Magento_Checkout/js/action/redirect-on-success',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_CheckoutAgreements/js/model/agreements-assigner'
], function (
    $,
    submitCartAction,
    redirectOnSuccessAction,
    additionalValidators,
    agreementsAssigner
) {
    'use strict';

    return function (methodRenderer) {
        var paymentData;

        if (methodRenderer.validate() && additionalValidators.validate()) {
            methodRenderer.isPlaceOrderActionAllowed(false);

            paymentData = methodRenderer.getData();
            agreementsAssigner(paymentData);

            $.when(submitCartAction(paymentData, methodRenderer.messageContainer)).fail(
                function () {
                    methodRenderer.isPlaceOrderActionAllowed(true);
                }
            ).done(
                function () {
                    methodRenderer.afterPlaceOrder();

                    if (methodRenderer.redirectAfterPlaceOrder) {
                        redirectOnSuccessAction.execute();
                    }
                }
            );

            return true;
        }
    };
});
