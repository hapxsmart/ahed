define([
    'jquery',
    'Magento_Ui/js/modal/confirm'
], function($, confirm) {

    $.widget('awsarp2.awSarp2ButtonControl', {
        options: {
            newLocation: '',
            confirm: {
                enabled: false,
                message: ''
            }
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
        _bind: function() {
            this._on({
                'click': '_onButtonClick'
            });
        },

        /**
         * Click event handler
         *
         * @param {Object} event
         */
        _onButtonClick: function(event) {
            var self = this;

            event.preventDefault();
            if (this.options.confirm.enabled) {
                confirm({
                    modalClass: 'confirm aw-sarp2__confirm',
                    content: this.options.confirm.message,
                    actions: {
                        confirm: function () {
                            self._action();
                        }
                    },
                    buttons: [
                        {
                            text: $.mage.__('No'),
                            class: 'action-secondary action-dismiss',
                            click: function (event) {
                                this.closeModal(event);
                            }
                        },
                        {
                            text: $.mage.__('Yes'),
                            class: 'action-primary action-accept',
                            click: function (event) {
                                this.closeModal(event, true);
                            }
                        }
                    ]
                });
            } else {
                this._action();
            }
        },

        /**
         * Button action
         */
        _action: function() {
            this._redirectToUrl();
        },

        /**
         * Redirect to url
         */
        _redirectToUrl: function () {
            window.location = this.options.newLocation;
        }
    });

    return $.awsarp2.awSarp2ButtonControl;
});
