define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';

    return function (setPaymentInformationExtended) {
        return wrapper.wrap(setPaymentInformationExtended, function (original, messageContainer, paymentData, skipBilling) {
            if (!window.checkoutConfig.paymentEditMode) {
                original(messageContainer, paymentData, skipBilling);
            }
        });
    };
});
