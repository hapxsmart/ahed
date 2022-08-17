define([
    'jquery',
    'mage/utils/wrapper',
], function ($, wrapper) {
    'use strict';

    return function (selectPaymentInformationAction) {
        return wrapper.wrap(selectPaymentInformationAction, function (original, messageContainer, paymentData) {
            if (!window.checkoutConfig.paymentEditMode) {
                return original(messageContainer, paymentData);
            }
        });
    };
});
