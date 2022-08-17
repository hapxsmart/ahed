define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('mage.awSarp2PlanSelect', {
        options: {
            planSelectSelector: '',
            initialFeeInputSelector: ''
        },

        /**
         * Initialize widget
         */
        _create: function() {
            this._bind();
        },

        /**
         * Event binding
         */
        _bind: function () {
            $(this.options.planSelectSelector).on('change', this.onChangePlan.bind(this));
            this.onChangePlan();
        },

        /**
         * On plan changed
         */
        onChangePlan: function () {
            var $initialFeeInput = $(this.options.initialFeeInputSelector),
                $selected = $(this.options.planSelectSelector + " option:selected"),
                isEnabled = $selected.data('is_enabled') === 1,
                isInitialFeeEnabled = $selected.data('is_initial_fee_enabled') === 1;

            if (isEnabled && isInitialFeeEnabled) {
                $initialFeeInput.prop('disabled', false);
            } else {
                $initialFeeInput.prop('disabled', true);
                $initialFeeInput.val('');
            }
        }
    });

    return $.mage.awSarp2PlanSelect;
});
