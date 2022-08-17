define([
    'uiComponent',
    'ko',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/model/payment/method-converter',
    'Aheadworks_Sarp2/js/customer/subscriptions/edit-payment/action/get-payment-information',
    'Magento_Checkout/js/model/checkout-data-resolver'
], function (
    Component,
    ko,
    quote,
    paymentService,
    methodConverter,
    getPaymentInformation,
    checkoutDataResolver
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Aheadworks_Sarp2/customer/subscriptions/edit-payment/payment',
            activeMethod: ''
        },
        isVisible: ko.observable(false),
        quoteIsVirtual: false,
        isPaymentMethodsAvailable: ko.observable(true),

        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.initData();

            return this;
        },

        /**
         * Resolve payment and shipping and set up payments
         */
        initData: function () {
            var self = this;

            checkoutDataResolver.resolvePaymentMethod();
            checkoutDataResolver.resolveShippingAddress();
            quote.paymentMethod.subscribe(function () {
                checkoutDataResolver.resolveBillingAddress();
            }, this);

            if (window.checkoutConfig.paymentMethods) {
                paymentService.setPaymentMethods(methodConverter(window.checkoutConfig.paymentMethods));
                self.isVisible(true);
            } else {
                getPaymentInformation().done(function () {
                    self.isVisible(true);
                });
            }
        },

        /**
         * @return {*}
         */
        getFormKey: function () {
            return window.checkoutConfig.formKey;
        }
    });
});
