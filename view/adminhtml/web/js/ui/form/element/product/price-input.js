define([
    'underscore',
    'Magento_Ui/js/form/element/abstract'
], function (_, Element) {
    'use strict';

    return Element.extend({

        /**
         * @inheritdoc
         */
        _setClasses: function () {
            this._super();
            _.extend(this.additionalClasses, {_disabled: false});

            return this;
        }
    });
});
