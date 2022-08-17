define([
    'underscore',
    'ko'
], function (_, ko) {
    'use strict';

    var checkoutConfig = window.checkoutConfig,
        isQuoteMixed = ko.observable(!!Number(checkoutConfig.isAwSarp2QuoteMixed)),
        isQuoteSubscription = ko.observable(!!Number(checkoutConfig.isAwSarp2QuoteSubscription));

    return function (quote) {
        return _.extend(quote, {
            isAwSarp2QuoteMixed: isQuoteMixed,
            isAwSarp2QuoteSubscription: isQuoteSubscription
        });
    }
});
