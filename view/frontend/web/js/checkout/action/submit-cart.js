define([
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/url-builder',
    'mage/storage',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/full-screen-loader'
], function (
    quote,
    urlBuilder,
    storage,
    customer,
    errorProcessor,
    fullScreenLoader
) {
    'use strict';

    return function (paymentData, messageContainer) {
        var serviceUrl, payload;

        payload = {
            cartId: quote.getQuoteId(),
            billingAddress: quote.billingAddress(),
            paymentMethod: paymentData
        };

        if (customer.isLoggedIn()) {
            serviceUrl = urlBuilder.createUrl('/awSarp2/carts/mine/payment-information', {});
        } else {
            serviceUrl = urlBuilder.createUrl('/awSarp2/guest-carts/:quoteId/payment-information', {
                quoteId: quote.getQuoteId()
            });
            payload.email = quote.guestEmail;
        }

        fullScreenLoader.startLoader();

        return storage.post(
            serviceUrl, JSON.stringify(payload)
        ).fail(
            function (response) {
                errorProcessor.process(response, messageContainer);
            }
        ).always(
            function () {
                fullScreenLoader.stopLoader();
            }
        );
    };
});
