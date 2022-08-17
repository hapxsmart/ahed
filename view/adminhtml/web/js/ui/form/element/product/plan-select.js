define([
    'underscore',
    'Magento_Ui/js/form/element/select'
], function (_, Select) {
    'use strict';

    /**
     * Retrieve option flag value
     *
     * @param {Object} option
     * @param {String} flag
     * @param {Boolean} defaultValue
     * @returns {Boolean}
     */
    function getOptionFlag (option, flag, defaultValue) {
        return _.isUndefined(option)
            ? defaultValue
            : option[flag] == '1';
    }

    /**
     * Retrieve option field value
     *
     * @param {Object} option
     * @param {String} field
     * @param {*} defaultValue
     * @returns {*}
     */
    function getOptionFieldValue (option, field, defaultValue) {
        return _.isUndefined(option)
            ? defaultValue
            : option[field];
    }

    return Select.extend({
        defaults: {
            isInitialFeeDisabled: true,
            isAutoTrialPriceDisabled: true,
            isTrialPriceDisabled: true,
            isAutoRegularPriceDisabled: false,
            isRegularPriceDisabled: true,
            autoTrialPrice: '',
            isAutoTrialPrice: true,
            autoRegularPrice: '',
            isAutoRegularPrice: true,
            initialFee: '',
            trialPrice: '',
            regularPrice: '',
            defaultPriceValue: '0.00',
            isUsedAdvancedPricing: '0',
            exports: {
                isInitialFeeDisabled: '${ $.parentName }.initial_fee:disabled',
                isTrialPriceDisabled: '${ $.parentName }.trial_price:disabled',
                autoTrialPrice: '${ $.parentName }.trial_price:autoValue',
                isAutoTrialPriceDisabled: '${ $.parentName }.trial_price:isUseAutoDisabled',
                isRegularPriceDisabled: '${ $.parentName }.regular_price:disabled',
                autoRegularPrice: '${ $.parentName }.regular_price:autoValue',
                isAutoRegularPriceDisabled: '${ $.parentName }.regular_price:isUseAutoDisabled'
            },
            links: {
                initialFee: '${ $.provider }:${ $.parentScope }.initial_fee',
                trialPrice: '${ $.provider }:${ $.parentScope }.trial_price',
                regularPrice: '${ $.provider }:${ $.parentScope }.regular_price'
            },
            imports: {
                isAutoTrialPrice: '${ $.parentName }.trial_price:isUseAuto',
                isAutoRegularPrice: '${ $.parentName }.regular_price:isUseAuto',
                isUsedAdvancedPricing: '${ $.ns }.${ $.ns }.sarp2-subscription-configuration' +
                    '.container_aw_sarp2_is_used_advanced_pricing.aw_sarp2_is_used_advanced_pricing:value'
            },
            listens: {
                value: 'switchDisabled setAutoTrialPrice setAutoRegularPrice',
                isUsedAdvancedPricing: 'setAutoTrialPrice setAutoRegularPrice'
            }
        },

        /**
         * @inheritdoc
         */
        initObservable: function () {
            this._super()
                .observe([
                    'isInitialFeeDisabled',
                    'isTrialPriceDisabled',
                    'autoTrialPrice',
                    'isAutoTrialPriceDisabled',
                    'isAutoRegularPriceDisabled',
                    'autoRegularPrice',
                    'isRegularPriceDisabled',
                    'isAutoTrialPrice',
                    'isAutoRegularPrice',
                    'initialFee',
                    'trialPrice',
                    'regularPrice',
                    'isUsedAdvancedPricing'
                ]);

            return this;
        },

        /**
         * @inheritdoc
         */
        setInitialValue: function () {
            this._super();
            var option = this._getCurrentOption(),
                isAllDisabled = !getOptionFlag(option, 'is_enabled', false),
                isPlanInitialFeeDisabled = !getOptionFlag(option, 'is_initial_fee_enabled', false),
                isPlanTrialPriceDisabled = !getOptionFlag(option, 'is_trial_price_enabled', false);

            if (isPlanInitialFeeDisabled) {
                this.initialFee(this.defaultPriceValue);
            }
            if (isPlanTrialPriceDisabled) {
                this.trialPrice(this.defaultPriceValue);
            } else if (!isAllDisabled
                && !_.isNull(this.trialPrice())
                && !_.isEmpty(this.trialPrice())
            ) {
                this.isTrialPriceDisabled(false);
            }

            if (!isAllDisabled
                && !_.isNull(this.regularPrice())
                && !_.isEmpty(this.regularPrice())
            ) {
                this.isRegularPriceDisabled(false);
            }

            return this;
        },

        /**
         * Switch disabled status of linked components
         */
        switchDisabled: function () {
            var option = this._getCurrentOption(),
                isAllDisabled = !getOptionFlag(option, 'is_enabled', false),
                isInitialFeeDisabled,
                isTrialPriceDisabled;

            if (!isAllDisabled) {
                isInitialFeeDisabled = !getOptionFlag(option, 'is_initial_fee_enabled', false);
                this.isInitialFeeDisabled(isInitialFeeDisabled);
                if (isInitialFeeDisabled) {
                    this.initialFee(this.defaultPriceValue);
                }

                isTrialPriceDisabled = !getOptionFlag(option, 'is_trial_price_enabled', false);
                if (!this.isAutoTrialPrice()) {
                    this.isTrialPriceDisabled(isTrialPriceDisabled);
                    this.trialPrice(this.defaultPriceValue);
                }
                this.isAutoTrialPriceDisabled(isTrialPriceDisabled);

                if (!this.isAutoRegularPrice()) {
                    this.isRegularPriceDisabled(false);
                }
                this.isAutoRegularPriceDisabled(false);
            } else {
                this.isInitialFeeDisabled(true);
                this.isAutoTrialPriceDisabled(true);
                this.isTrialPriceDisabled(true);
                this.isAutoRegularPriceDisabled(true);
                this.isRegularPriceDisabled(true);
            }
        },

        /**
         * Set trial price auto value
         */
        setAutoTrialPrice: function () {
            var option = this._getCurrentOption(),
                field = 'auto_trial_price';

            if (this.isUsedAdvancedPricing() === '1') {
                field = 'auto_trial_price_advanced';
            }
            this.autoTrialPrice(
                getOptionFlag(option, 'is_trial_price_enabled', false)
                    ? getOptionFieldValue(option, field, this.defaultPriceValue)
                    : this.defaultPriceValue
            );
        },

        /**
         * Set regular price auto value
         */
        setAutoRegularPrice: function () {
            var field = 'auto_regular_price';

            if (this.isUsedAdvancedPricing() === '1') {
                field = 'auto_regular_price_advanced';
            }
            this.autoRegularPrice(
                getOptionFieldValue(this._getCurrentOption(), field, this.defaultPriceValue)
            );
        },

        /**
         * Get current option
         *
         * @returns {Object|undefined}
         */
        _getCurrentOption: function () {
            return _.find(this.options(), function (option) {
                return option.value == this.value();
            }, this);
        }
    });
});
