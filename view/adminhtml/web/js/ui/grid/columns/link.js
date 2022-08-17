define([
    'Magento_Ui/js/grid/columns/column'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Aheadworks_Sarp2/ui/grid/cells/link'
        },

        /**
         * Check if link url specified
         *
         * @param row
         * @returns {Boolean}
         */
        hasLink: function(row) {
            return !!row[this.index + '_url'];
        },

        /**
         * Get plain text
         *
         * @param row
         * @returns {String}
         */
        getPlainText: function(row) {
            return row[this.index];
        },

        /**
         * Get link text
         *
         * @param row
         * @returns {String}
         */
        getLinkText: function(row) {
            return row[this.index + '_text'];
        },

        /**
         * Get link hint
         *
         * @param row
         * @returns {String}
         */
        getLinkHint: function(row) {
            return row[this.index + '_hint'];
        },

        /**
         * Get lnk url
         *
         * @param row
         * @returns {String}
         */
        getLinkUrl: function(row) {
            return row[this.index + '_url'];
        }
    });
});
