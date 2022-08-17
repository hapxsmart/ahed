define([
    'underscore',
    'Magento_Ui/js/grid/columns/multiselect'
], function (_, Multiselect) {
    'use strict';

    return Multiselect.extend({
        defaults: {
            additionalFilteringKeys: []
        },

        /**
         * @inheritDoc
         */
        getFiltering: function () {
            var params = this._super(),
                source = this.source(),
                additionalParams = {};

            if (!source) {
                return params;
            }

            additionalParams = _.pick(
                source.get('params'),
                this.additionalFilteringKeys
            );

            return _.extend(
                params,
                additionalParams
            );
        }
    });
});
