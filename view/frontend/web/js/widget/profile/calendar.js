define([
    'jquery',
    'moment',
    'mage/calendar'
], function($, moment) {
    'use strict';

    $.widget('mage.awSarp2Calendar', {
        options: {
            earliestDate: '',
            calendarDateFormat: 'mm/dd/yyyy',
            momentDateFormat: 'MM/DD/YYYY'
        },
        previousDate: '',
        minDate: '',

        /**
         * Initialize widget
         */
        _create: function() {
            this.initInterval();
            this.initCalendar();
            this.element.on('change', $.proxy(this.dateChange, this));
            this.element.on('focus', $.proxy(this.dateInputFocus, this));
        },

        /**
         * Date change on input element
         */
        dateChange: function() {
            var newDate = $(this.element).val();

            if (newDate
                && !moment(newDate, this.options.momentDateFormat, true).isValid()
                || moment(newDate, this.options.momentDateFormat, true).isBefore(this.minDate, 'day')
            ) {
                $(this.element).val(this.previousDate);
            }
        },

        /**
         * Focus on input element
         */
        dateInputFocus: function() {
            this.previousDate = $(this.element).val();
        },

        /**
         * Init date interval
         */
        initInterval: function() {
            this.minDate = new moment(this.options.earliestDate, this.options.momentDateFormat, true);
        },

        /**
         * Initialize Calendar
         */
        initCalendar: function() {
            var config = {
                dateFormat: this.options.calendarDateFormat,
                minDate: this.minDate.toDate(),
                setDate: 0,
                showOn: 'button'
            };

            $(this.element).calendar(config);
        }
    });

    return $.mage.awSarp2Calendar;
});
