define(
    [],
    function () {
        'use strict';

        var flag = false;

        return {

            /**
             * Get is subscription product flag
             *
             * @returns {Boolean}
             */
            isSubscription: function () {
                return flag;
            },

            /**
             * Set subscription product flag
             *
             * @param {Boolean} isSubscription
             */
            setIsSubscription: function (isSubscription) {
                flag = isSubscription;
            }
        };
    }
);