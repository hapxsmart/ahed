define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';

    $.widget('mage.awSarp2Tooltip', {
        options: {
            tooltips: '[data-role=tooltip]',
            tooltipAction: '[data-role=tooltip-action]',
            tooltipContentClass: 'aw-sarp2-tooltip-content',
            tooltipContentIdAttr: 'data-content-id',
            tooltipContentIsHidingAttr: 'data-hiding'
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
            $(this.options.tooltips).click(this.onClick.bind(this))
                .mouseenter(this.onMouseEnter.bind(this))
                .mouseleave(this.onMouseLeave.bind(this));
            $(window).on('resize', this.adjust.bind(this));
        },

        /**
         * On click event handler
         *
         * @param {Event} event
         */
        onClick: function (event) {
            event.preventDefault();
        },

        /**
         * On mouse enter event handler
         *
         * @param {Event} event
         */
        onMouseEnter: function (event) {
            var element = $(event.currentTarget);

            if (!this._isContentShown(element)) {
                this._showContent(element);
            }
        },

        /**
         * On mouse leave event handler
         *
         * @param {Event} event
         */
        onMouseLeave: function (event) {
            var element = $(event.currentTarget);

            if (this._isContentShown(element)) {
                this._hideContent(element);
            }
        },

        /**
         * Check if tooltip content shown for specified element
         *
         * @param {jQuery} element
         * @returns {Boolean}
         */
        _isContentShown: function (element) {
            return !!element.attr(this.options.tooltipContentIdAttr);
        },

        /**
         * Show tooltip content for specified element
         *
         * @param {jQuery} element
         */
        _showContent: function (element) {
            var content = $('<div></div>'),
                htmlId = _.uniqueId('tooltip-content_');

            content.addClass(this.options.tooltipContentClass)
                .attr('id', htmlId)
                .text(element.data('content'));
            $('body').append(content);
            this._adjustPosition(element, content);

            element.attr(this.options.tooltipContentIdAttr, htmlId);
        },

        /**
         * Hide tooltip content of specified element
         *
         * @param {jQuery} element
         */
        _hideContent: function (element) {
            var content = this._getContent(element),
                isHiding = !!element.attr(this.options.tooltipContentIsHidingAttr),
                self = this;

            if (content && !isHiding) {
                element.attr(this.options.tooltipContentIsHidingAttr, '1');
                _.delay(function (elem, contentElem) {
                    contentElem.remove();
                    elem.removeAttr(self.options.tooltipContentIdAttr)
                        .removeAttr(self.options.tooltipContentIsHidingAttr);
                }, 1000, element, content);
            }
        },

        /**
         * Get tooltip content for specified element
         *
         * @param {jQuery} element
         * @returns {jQuery|null}
         */
        _getContent: function (element) {
            var htmlId = element.attr(this.options.tooltipContentIdAttr);

            return !_.isUndefined(htmlId)
                ? $('#' + htmlId)
                : null;
        },

        /**
         * Adjust tooltips content positions
         */
        adjust: function () {
            var tooltips = $(this.options.tooltips),
                self = this;

            tooltips.each(function () {
                var element = $(this),
                    content = self._getContent(element);

                if (content) {
                    self._adjustPosition(element, content);
                }
            });
        },

        /**
         * Adjust tooltip content position
         *
         * @param {jQuery} element
         * @param {jQuery} tooltipContent
         */
        _adjustPosition: function (element, tooltipContent) {
            var offset = element.offset(),
                deltaY = tooltipContent.outerHeight() + 20,
                deltaX = tooltipContent.outerWidth() - (parseInt(tooltipContent.css('padding-right')) + 32),

                /**
                 * Adjust coordinate
                 *
                 * @param {Number} coordinate
                 * @param {Number} delta
                 * @returns {Number}
                 */
                adjustCoordinate = function (coordinate, delta) {
                    return coordinate - delta < 0
                        ? 0
                        : coordinate - delta;
                };

            offset.top = adjustCoordinate(offset.top, deltaY);
            offset.left = adjustCoordinate(offset.left, deltaX);

            tooltipContent.offset(offset);
        }
    });

    return $.mage.awSarp2Tooltip;
});
