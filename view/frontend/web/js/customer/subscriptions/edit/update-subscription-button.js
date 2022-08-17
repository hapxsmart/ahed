define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'addToCart',
], function ($, confirm) {
    'use strict';

    $.widget('mage.awSarp2UpdateSubscriptionButton', $.mage.addToCart, {
        options: {
            updateModeModal: {
                isEnable: false,
                title: '',
                content: '',
                buttons: {
                    permanently: {
                        text: '',
                        class: 'action-secondary'
                    },
                    oneTime: {
                        text: '',
                        class: 'action-secondary'
                    }
                },
                formInputSelector: '#aw-sarp2-is-one-time-flag'
            }
        },

        /**
         * @inheritDoc
         */
        _addToCartSubmit: function (event) {
            var self = this,
                _super = this._super.bind(this);

            if (!this.options.updateModeModal.isEnable) {
                return this._super();
            }

            event.preventDefault();

            if ($(this.options.cartForm).valid()) {
                confirm({
                    title: this.options.updateModeModal.title,
                    content: this.options.updateModeModal.content,
                    actions: {
                        confirm: function () {
                            _super(event);
                        }
                    },
                    buttons: [
                        {
                            text: this.options.updateModeModal.buttons.oneTime.text,
                            class: this.options.updateModeModal.buttons.oneTime.class,
                            click: function (event) {
                                self._setFlag(1);
                                this.closeModal(event, true);
                            }
                        }, {
                            text: this.options.updateModeModal.buttons.permanently.text,
                            class: this.options.updateModeModal.buttons.permanently.class,
                            click: function (event) {
                                self._setFlag(0);
                                this.closeModal(event, true);
                            }
                        }
                    ]
                });
            }
        },

        /**
         * Set one time flag
         *
         * @param {Number} flag
         * @private
         */
        _setFlag: function (flag) {
            $(this.options.updateModeModal.formInputSelector).val(flag);
        }
    });

    return $.mage.awSarp2UpdateSubscriptionButton;
});