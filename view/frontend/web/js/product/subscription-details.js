define([
    'jquery',
    'underscore',
    'mage/template',
    'awSarp2SubscriptionOptionStorage',
    'Aheadworks_Sarp2/js/product/config/provider',
], function (
    $,
    _,
    mageTemplate,
    sarpStorage,
    sarpConfigProvider
) {
    'use strict';

    $.widget('mage.awSarp2SubscriptionDetails', {
        options: {
            details: '[data-role=aw-sarp2-subscription-details]',
            detailsList: '[data-role=aw-sarp2-subscription-details-list]',
            detailsListItemTemplate: '[data-role=details-item-template]',
            priceBoxSelector: null,
            handlers: {
                'firstPayment': '_handlerFirstPayment',
                'trialPayment': '_handlerTrialPayment',
            },
            performUpdateAfterInit: false
        },

        /**
         * @inheritdoc
         * */
        _create: function () {
            this.options.priceBoxSelector = sarpConfigProvider.getMainPriceBoxSelector();
            this.options.priceBoxSelector += '[data-product-id=' + sarpConfigProvider.getProductId() + ']';

            this._bind();
        },

        /**
         * Event binding
         */
        _bind: function () {
            var self = this;

            this._on({
                'updateDetails': 'onUpdate',
                'updatePriceBoxes': 'onUpdatePriceBoxes'
            });

            $(this.options.priceBoxSelector).on('afterUpdatePrice', function () {
                self.element.trigger('updatePriceBoxes');
            })
        },

        /**
         * @inheritdoc
         */
        _init: function () {
            sarpStorage.set('subscription_details', this.element);

            if (this.options.performUpdateAfterInit) {
                this.onUpdate();
            }
        },

        /**
         * On update details event handler
         */
        onUpdate: function () {
            this.update();
        },

        /**
         * On update details price boxes event handler
         */
        onUpdatePriceBoxes: function () {
            this.updatePriceBoxes();
        },

        /**
         * Show details list
         */
        show: function() {
            this.element.css('opacity', 0).slideDown('slow').animate({
                opacity: 1
            }, {
                queue: false,
                duration: 'slow'
            });
        },

        /**
         * Hide details list
         */
        hide: function() {
            this.element.slideUp('slow').animate({
                opacity: 0
            }, {
                queue: false,
                duration: 'slow'
            });
        },

        /**
         * Update details
         */
        update: function () {
            var details,
                subscriptionOptionId = sarpStorage.get('subscription_option_id');

            details = sarpConfigProvider.getSubscriptionDetails(subscriptionOptionId);
            if (!_.isEmpty(details)) {
                this.refreshDetailsLabel(details);
                this._refreshDetailsList(details);
                this.show();
            }
        },

        /**
         * Refresh subscription details label
         *
         * @param {Array} details
         */
        refreshDetailsLabel: function (details) {
            let detailsLabel = document.getElementById('subscription-detail_label');
            detailsLabel.innerText = details.label.value;
        },

        /**
         * Refresh subscription details list
         *
         * @param {Array} details
         */
        _refreshDetailsList: function (details) {
            var detailsList = this.element.find(this.options.detailsList),
                templateNodes = $(this.options.details + ' ' + this.options.detailsListItemTemplate),
                type, template, $templateNode;

            detailsList.html('');

            _.each(templateNodes, function (templateNode) {
                $templateNode = $(templateNode);
                type = $templateNode.data('itemType');
                if (type && details[type] !== undefined) {
                    if (details[type]['isShow']) {
                        template = mageTemplate($templateNode.html());
                        $(template(details[type])).appendTo(detailsList);
                    }
                }
            }, this);
        },

        /**
         * Update price boxes in details list
         */
        updatePriceBoxes: function () {
            var priceType, handlerId, price, formattedPrice, handler,
                priceBox = $(this.options.priceBoxSelector),
                subscriptionPriceBoxes = $('[data-role="details-price-box"]', this.element);

            _.each(subscriptionPriceBoxes, function (detailsPriceBox) {
                handlerId = $(detailsPriceBox).data('handler');
                priceType = $(detailsPriceBox).data('priceType');
                price = $('[data-price-type="' + priceType + '"]', priceBox).data('price');

                if (price) {
                    handler = this._getHandler(handlerId);
                    if (handler) {
                        formattedPrice = priceBox.priceBox(
                            'formatPrice',
                            handler.apply(
                                this,
                                [$(detailsPriceBox), price]
                            )
                        );
                    } else {
                        formattedPrice = price.formatted;
                    }

                    $(detailsPriceBox)
                        .html(formattedPrice)
                        .css('opacity', 0).slideDown('slow').animate({
                        opacity: 1
                    }, {
                        queue: false,
                        duration: 'slow'
                    });
                }
            }, this);
        },

        /**
         * Calculate custom options price
         *
         * @param priceType
         * @return {number}
         * @private
         */
        _calculateCustomOptionPrice: function(priceType) {
            var selectedOptionPrice,
                price = 0;

            selectedOptionPrice = sarpStorage.get('selected_option_price');
            if (selectedOptionPrice) {
                _.each(selectedOptionPrice, function (option) {
                    if (option[priceType]) {
                        price += option[priceType]['amount'];
                    }
                });
            }

            return price;
        },

        /**
         * Calculate custom options price
         *
         * @param priceType
         * @return {number}
         * @private
         */
        _calculateBundleOptionPrice: function(priceType) {
            var selectedOptionPrice,
                price = 0;

            selectedOptionPrice = sarpStorage.get('selected_bundle_option_trial_price');
            if (selectedOptionPrice) {
                _.each(selectedOptionPrice, function (option) {
                    if (option[priceType]) {
                        price += option[priceType]['amount'];
                    }
                });
            }

            return price;
        },

        /**
         * Calculate custom options price
         *
         * @param priceType
         * @return {number}
         * @private
         */
        _calculateTrialPrice: function(priceType) {
            var installmentsMode,
                trialPriceBox,
                firstPriceBox,
                trialPrice = null;

            trialPriceBox = $('[data-role="details-price-box"][data-handler="trialPayment"]', this.element);
            if (trialPriceBox.length && trialPriceBox.data('value') !== undefined) {
                trialPrice = Number.parseFloat(trialPriceBox.data('value'));
                if (isNaN(trialPrice)) {
                    trialPrice = null;
                }
            } else {
                firstPriceBox = $('[data-role="details-price-box"][data-handler="firstPayment"]', this.element);
                if (firstPriceBox.length && firstPriceBox.data('trialPayment') !== undefined
                ) {
                    trialPrice = Number.parseFloat(firstPriceBox.data('trialPayment'));
                    if (isNaN(trialPrice)) {
                        trialPrice = null;
                    }
                }
            }

            if (trialPrice !== null) {
                installmentsMode = sarpConfigProvider.getInstallmentsMode(
                    sarpStorage.get('subscription_option_id')
                );
                if (!(installmentsMode && installmentsMode.enabled)) {
                    trialPrice += this._calculateCustomOptionPrice(priceType);
                }
                trialPrice += this._calculateBundleOptionPrice(priceType);
            }

            return trialPrice;
        },

        /**
         * Retrieve handler bu id
         *
         * @param handlerId
         * @return {null|Function}
         * @private
         */
        _getHandler: function(handlerId) {
            var handler = this.options.handlers[handlerId];

            if (handler instanceof Function) {
                return handler;
            }
            if (handler in this) {
                return this[handler];
            }

            return null;
        },

        /**
         * Handler. Recalculate first payment amount
         *
         * @param detailsPriceBox
         * @param totalPrice
         * @return {Number}
         * @private
         */
        _handlerFirstPayment: function(detailsPriceBox, totalPrice) {
            var installmentsMode,
                firstPaymentPrice,
                trialPrice,
                priceType = detailsPriceBox.data('priceType'),
                initialFee = Number.parseFloat(detailsPriceBox.data('initialFee'));

            trialPrice = this._calculateTrialPrice(priceType);
            if (trialPrice !== null) {
                firstPaymentPrice = trialPrice;
            } else {
                firstPaymentPrice = totalPrice.final;

                installmentsMode = sarpConfigProvider.getInstallmentsMode(
                    sarpStorage.get('subscription_option_id')
                );
                if (installmentsMode && installmentsMode.enabled && installmentsMode.isTrial) {
                    firstPaymentPrice -= this._calculateCustomOptionPrice(priceType);
                }
            }

            firstPaymentPrice += initialFee;

            return firstPaymentPrice;
        },

        /**
         * Handler. Recalculate trial payment amount
         *
         * @param detailsPriceBox
         * @return {Number}
         * @private
         */
        _handlerTrialPayment: function(detailsPriceBox) {
            var priceType = detailsPriceBox.data('priceType');
            return this._calculateTrialPrice(priceType);
        }
    });

    return $.mage.awSarp2SubscriptionDetails;
});
