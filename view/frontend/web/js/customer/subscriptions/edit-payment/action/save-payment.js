define([
    'Magento_Checkout/js/model/quote',
    'mageUtils'
], function (quote, utils) {
    'use strict';

    return function (paymentData) {
        var payload = {
            billing_address: quote.billingAddress(),
            payment: paymentData
        };

        utils.submit({
            url: window.checkoutConfig.savePaymentUrl,
            data: payload
        });
    };
});
