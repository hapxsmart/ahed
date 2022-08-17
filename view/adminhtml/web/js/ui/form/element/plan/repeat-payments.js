define([
    'Magento_Ui/js/form/element/abstract'
], function (Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            repeatPaymentsOptions: [],
            repeatPaymentsToValuesMap: {
                1: {billingPeriod: 'day', billingFrequency: 1},
                2: {billingPeriod: 'week', billingFrequency: 1},
                3: {billingPeriod: 'month', billingFrequency: 1},
                4: {billingPeriod: 'year', billingFrequency: 1}
            },
            billingPeriodOptions: [],
            billingFrequencyOptions: [],
            repeatPaymentsValue: 1,
            billingFrequencyDefaultValue: 1,
            billingPeriodDefaultValue: 'day',
            billingFrequencyVisible: false,
            billingPeriodVisible: false,
            expandOptionValue: 100,
            listens: {
                'repeatPaymentsValue': 'processRepeatPaymentsValue'
            }
        },

        /**
         * @inheritdoc
         */
        initConfig: function (config) {
            this._super()
                .initValuesToRepeatPaymentsMap(config);

            return this;
        },

        /**
         * @inheritdoc
         */
        initObservable: function () {
            this._super()
                .observe([
                    'repeatPaymentsValue',
                    'billingFrequencyValue',
                    'billingPeriodValue',
                    'billingFrequencyVisible',
                    'billingPeriodVisible'
                ]);

            return this;
        },

        /**
         * @inheritdoc
         */
        setInitialValue: function () {
            this.setInitialBillingPeriodValue()
                .setInitialBillingFrequencyValue()
                .setInitialRepeatPaymentsValue();

            return this;
        },

        /**
         * Set initial billing period value
         *
         * @returns {Object}
         */
        setInitialBillingPeriodValue: function () {
            this.initialBillingPeriodValue = this.getInitialBillingPeriodValue();
            if (this.billingPeriodValue.peek() !== this.initialBillingPeriodValue) {
                this.billingPeriodValue(this.initialBillingPeriodValue);
            }
            this.on('billingPeriodValue', this.onUpdate.bind(this));

            return this;
        },

        /**
         * Set initial billing frequency value
         *
         * @returns {Object}
         */
        setInitialBillingFrequencyValue: function () {
            this.initialBillingFrequencyValue = this.getInitialBillingFrequencyValue();
            if (this.billingFrequencyValue.peek() !== this.initialBillingFrequencyValue) {
                this.billingFrequencyValue(this.initialBillingFrequencyValue);
            }
            this.on('billingFrequencyValue', this.onUpdate.bind(this));

            return this;
        },

        /**
         * Set initial repeat payments value
         *
         * @returns {Object}
         */
        setInitialRepeatPaymentsValue: function () {
            var key = this.billingPeriodValue() + '-' + this.billingFrequencyValue(),
                value = key in this.valuesToRepeatPaymentsMap
                    ? this.valuesToRepeatPaymentsMap[key]
                    : this.expandOptionValue;

            if (this.billingPeriodValue() && this.billingFrequencyValue()) {
                this.repeatPaymentsValue(value);
            }

            return this;
        },

        /**
         * Get initial billing period value
         *
         * @returns {string}
         */
        getInitialBillingPeriodValue: function () {
            return this._getInitialValue([this.billingPeriodValue(), this.billingPeriodDefaultValue]);
        },

        /**
         * Get initial billing frequency value
         *
         * @returns {integer}
         */
        getInitialBillingFrequencyValue: function () {
            return this._getInitialValue([this.billingFrequencyValue(), this.billingFrequencyDefaultValue]);
        },

        /**
         * Determines initial value
         *
         * @param {Array} values
         * @returns {string|integer}
         * @private
         */
        _getInitialValue: function (values) {
            var value;

            values.some(function (v) {
                if (v !== null && v !== undefined && v != '') {
                    value = v;
                    return true;
                }
                return false;
            });

            return this.normalizeData(value);
        },

        /**
         * @inheritdoc
         */
        hasChanged: function () {
            var billingPeriodNotEqual = this.billingPeriodValue() != this.initialBillingPeriodValue,
                billingFrequencyNotEqual = this.billingFrequencyValue() != this.initialBillingFrequencyValue;

            return billingPeriodNotEqual || billingFrequencyNotEqual;
        },

        /**
         * Init values to repeat payments map
         *
         * @param {Object} config
         * @returns {Object}
         */
        initValuesToRepeatPaymentsMap: function (config) {
            this.valuesToRepeatPaymentsMap = {};
            for (var optionValue in config.repeatPaymentsToValuesMap) {
                if (config.repeatPaymentsToValuesMap.hasOwnProperty(optionValue)) {
                    var mapValue = config.repeatPaymentsToValuesMap[optionValue],
                        key = mapValue.billingPeriod + '-' + mapValue.billingFrequency;

                    this.valuesToRepeatPaymentsMap[key] = optionValue;
                }
            }

            return this;
        },

        /**
         * Process repeat payments value change
         *
         * @param {integer} optionValue
         */
        processRepeatPaymentsValue: function (optionValue) {
            var isExpand = !(optionValue in this.repeatPaymentsToValuesMap),
                mapOptions;

            this.billingFrequencyVisible(isExpand);
            this.billingPeriodVisible(isExpand);

            if (!isExpand) {
                mapOptions = this.repeatPaymentsToValuesMap[optionValue];
                this.billingPeriodValue(mapOptions.billingPeriod);
                this.billingFrequencyValue(mapOptions.billingFrequency);
            }
        }
    });
});
