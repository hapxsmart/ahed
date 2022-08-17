define([
    'jquery',
    'uiComponent',
    'uiRegistry',
    'Aheadworks_Sarp2/js/customer/subscriptions/edit-payment/action/save-payment',
    'Magento_Checkout/js/model/full-screen-loader'
], function (
    $,
    Component,
    registry,
    savePaymentAction,
    fullScreenLoader
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Aheadworks_Sarp2/customer/subscriptions/edit-payment/actions-toolbar/renderer/default',
            methodCode: null
        },
        methodRendererComponent: null,
        origPlaceOrderAction: null,
        isSaveActionAllowed: true,

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super().initMethodsRenderComponent();

            return this;
        },

        /**
         * Perform before actions
         *
         * @returns {Deferred}
         */
        _beforeAction: function () {
            var deferred = $.Deferred(),
                component = this._getMethodRenderComponent();

            if (this.validate(component)) {
                deferred.resolve();
            } else {
                deferred.reject();
            }

            return deferred;
        },

        /**
         * Validate component
         *
         * @param {Object} component
         * @returns {boolean}
         */
        validate: function (component) {
            return component.validate();
        },

        /**
         * Apply save action instead of place order for payment component
         *
         * @param {Object} elem
         * @param {Object} event
         * @returns {boolean}
         */
        applySaveAction: function (elem, event) {
            var paymentComponent = this._getMethodRenderComponent(),
                data = paymentComponent.getData();

            if (event) {
                event.preventDefault();
            }

            fullScreenLoader.startLoader();
            savePaymentAction(data);
        },

        /**
         * Is in context mode available
         *
         * @returns {boolean}
         */
        isInContext: function () {
            return false;
        },

        /**
         * Init method renderer component
         *
         * @returns {Component}
         */
        initMethodsRenderComponent: function () {
            if (this.methodCode) {
                this.methodRendererComponent = registry.get('payment.payments-list.' + this.methodCode);
                this.origPlaceOrderAction = this.methodRendererComponent.placeOrder.bind(this.methodRendererComponent);
                this.methodRendererComponent.placeOrder = this.applySaveAction.bind(this);
                this.isSaveActionAllowed = this.methodRendererComponent.isPlaceOrderActionAllowed;
            }

            return this;
        },

        /**
         * Get method renderer component
         *
         * @returns {Component}
         */
        _getMethodRenderComponent: function () {
            if (!this.methodRendererComponent) {
                this.initMethodsRenderComponent();
            }
            return this.methodRendererComponent;
        },

        /**
         * Save payment details
         *
         * @param {Object} data
         * @param {Object} event
         */
        savePaymentDetails: function (data, event) {
            var self = this;

            if (event) {
                event.preventDefault();
            }
            this._beforeAction().done(function () {
                try {
                    self._getMethodRenderComponent().placeOrder(data, event);
                } catch (error) {
                    console.log(error);
                    self._getMethodRenderComponent().messageContainer.addErrorMessage(error);
                }
            });
        }
    });
});
