define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';

    return function (getTotals) {
        return wrapper.wrap(getTotals, function (original, callbacks, deferred) {
            if (!window.checkoutConfig.paymentEditMode) {
                original(callbacks, deferred);
            }
        });
    };
});
