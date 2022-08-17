define([
    'jquery',
    'underscore',
    'Aheadworks_Nmi/js/view/payment/method-renderer/hosted-fields',
], function ($, _, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            paymentSelector: '#profile_save_payment_button',
        },

        /**
         * Triggers when payment method change
         */
        onActiveChange: function () {
            var self = this;

            if (!this.active() || !this.renderer) {
                return;
            }

            $.async(this.paymentSelector, function () {
                self.initNmi();
            });
        },

        /**
         * @inheritdoc
         */
        placeOrder: function (key) {
            if (!key || quote.totals()['grand_total'] > 0) {
                return this._super(key);
            }

            return quote.isAwSarp2QuoteMixed() || quote.isAwSarp2QuoteSubscription()
                ? placeMixedOrderAction(this)
                : this._super(key);
        }
    });
});
