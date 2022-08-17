define([
    'jquery',
    'Magento_Ui/js/form/form',
    'Magento_Ui/js/form/adapter'
], function ($, Form, adapter) {
    'use strict';

    return Form.extend({

        /**
         * @inheritDoc
         */
        initAdapter: function () {
            adapter.on({
                'reset': this.reset.bind(this),
                'save': this.save.bind(this, true, {}),
                'saveAndContinue': this.save.bind(this, false, {}),
                'saveAndDuplicate': this.save.bind(this, true, {back: 'duplicate'})
            }, this.selectorPrefix, this.eventPrefix);

            return this;
        }
    });
});
