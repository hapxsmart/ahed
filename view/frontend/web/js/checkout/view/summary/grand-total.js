define([
    'Magento_Tax/js/view/checkout/cart/totals/grand-total',
    'Magento_Checkout/js/model/quote'
], function (Component, quote) {
    'use strict';

    return Component.extend({
        isQuoteSubscription: quote.isAwSarp2QuoteSubscription,
        isQuoteMixed: quote.isAwSarp2QuoteMixed
    });
});
