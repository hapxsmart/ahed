define([
    'underscore',
    'ko',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/payment/method-list',
    'Magento_Checkout/js/action/select-payment-method'
], function (
    _,
    ko,
    quote,
    methodList,
    selectPaymentMethodAction
) {
    'use strict';

    var checkoutConfig = window.checkoutConfig,
        mixedPaymentMethodList = ko.observableArray(checkoutConfig.awSarp2MixedPaymentMethodList || []);

    /**
     * Check if free payment method
     *
     * @param {Object} paymentMethod
     * @returns {boolean}
     */
    function isFreePaymentMethod (paymentMethod) {
        return paymentMethod.method == 'free';
    }

    /**
     * Check if mixed payment method
     *
     * @param {Object} paymentMethod
     * @returns {boolean}
     */
    function isMixedPaymentMethod (paymentMethod) {
        var found = _.find(mixedPaymentMethodList(), function (methodCode) {
            return paymentMethod.method == methodCode;
        });

        return !_.isUndefined(found);
    }

    return function (paymentService) {
        return _.extend(paymentService, {

            /**
             * Retrieve sarp mixed payment list
             *
             * @return {Object}
             */
            getMixedMethodList: function () {
                return mixedPaymentMethodList;
            },

            /**
             * @inheritdoc
             */
            setPaymentMethods: function (methods) {
                var grandTotal = quote.totals()['grand_total'],
                    methodIsAvailable,
                    methodNames;

                this.isFreeAvailable = !!_.find(methods, isFreePaymentMethod);

                if (quote.isAwSarp2QuoteSubscription() || quote.isAwSarp2QuoteMixed()) {
                    if (grandTotal <= 0) {
                        methods = _.filter(methods, isMixedPaymentMethod);
                    }
                } else {
                    if (this.isFreeAvailable && grandTotal <= 0) {
                        methods = _.filter(methods, isFreePaymentMethod);
                    }
                }

                if (quote.paymentMethod()) {
                    methodIsAvailable = methods.some(function (item) {
                        return item.method === quote.paymentMethod().method;
                    });
                    if (!methodIsAvailable) {
                        selectPaymentMethodAction(null);
                    }
                }

                methodNames = _.pluck(methods, 'method');
                _.map(methodList(), function (existingMethod) {
                    var existingMethodIndex = methodNames.indexOf(existingMethod.method);

                    if (existingMethodIndex !== -1) {
                        methods[existingMethodIndex] = existingMethod;
                    }
                });

                methodList(methods);
            },

            /**
             * @inheritdoc
             */
            getAvailablePaymentMethods: function () {
                var allMethods = methodList().slice(),
                    grandTotal = quote.totals()['grand_total'];

                if (quote.isAwSarp2QuoteSubscription() || quote.isAwSarp2QuoteMixed()) {
                    if (grandTotal > 0) {
                        return _.reject(allMethods, isFreePaymentMethod);
                    } else {
                        return _.filter(allMethods, isMixedPaymentMethod)
                    }
                }

                if (!this.isFreeAvailable) {
                    return allMethods;
                }

                if (grandTotal > 0) {
                    return _.reject(allMethods, isFreePaymentMethod);
                } else {
                    return _.filter(allMethods, isFreePaymentMethod);
                }
            }
        });
    }
});
