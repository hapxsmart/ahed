define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/url-builder',
    'mage/storage',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/payment/method-converter',
    'Magento_Checkout/js/model/payment-service'
], function ($, quote, urlBuilder, storage, errorProcessor, methodConverter, paymentService) {
    'use strict';

    return function (deferred, messageContainer) {
        var serviceUrl = urlBuilder.createUrl('/awSarp2/carts/mine/payment-information', {});

        deferred = deferred || $.Deferred();

        return storage.get(
            serviceUrl, false
        ).done(function (response) {
            paymentService.setPaymentMethods(methodConverter(response['payment_methods']));
            deferred.resolve();
        }).fail(function (response) {
            errorProcessor.process(response, messageContainer);
            deferred.reject();
        });
    };
});
