define([
    'Magento_Ui/js/form/provider',
    'Magento_Ui/js/modal/confirm',
    'mage/translate'
], function (Provider, confirm, Translate) {
    'use strict';

    return Provider.extend({
        /** @inheritdoc **/
        save: function (options) {
            let self = this;
            confirm({
                content: Translate('Are you sure you want to save the changes?'),
                actions: {
                    confirm: function () {
                        let data = self.get('data');

                        self.client.save(data, options);
                    }
                }
            });

            return this;
        }
    });
});