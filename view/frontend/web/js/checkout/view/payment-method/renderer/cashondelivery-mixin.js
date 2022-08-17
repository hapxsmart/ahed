define(
    [
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer',
        'Aheadworks_Sarp2/js/checkout/model/payment/place-order'
    ],
    function (quote, customer, placeMixedOrderAction) {
        'use strict';

        return function (renderer) {
            return renderer.extend({

                /**
                 * @inheritdoc
                 */
                placeOrder: function (data, event) {
                    if (quote.totals()['grand_total'] > 0) {
                        return this._super(data, event);
                    }

                    if (event) {
                        event.preventDefault();
                    }

                    return quote.isAwSarp2QuoteMixed() || quote.isAwSarp2QuoteSubscription() && customer.isLoggedIn()
                            ? placeMixedOrderAction(this)
                            : this._super(data, event);
                },

                /**
                 * @inheritdoc
                 */
                isRadioButtonVisible: function () {
                    return true;
                }
            });
        }
    }
);
