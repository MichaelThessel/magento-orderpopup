/*global localStorage: false, jQuery: false*/

/*
 * New Order Notification
 */
(function ($) {
    "use strict";

    $.fn.orderPopup = function (options) {
        var buildPopup,
            showPopup,
            hidePopup,
            getIndex,
            isDisabled,
            hasLocalStorage,
            run;

        /*
         * Builds a popup
         */
        buildPopup = $.proxy(function (data) {
            var popup = $('<div></div>'),
                button = $('<span>x</span>').addClass('disable'),
                content,
                image;

            button.click($.proxy(function () {
                localStorage.orderPopupDisable = Date.now();
                this.remove();
            }, this));

            content = $('<div></div>').addClass('content').append(data.content);
            image = $('<a></a>').attr('href', data.productUrl).append($('<img />').attr('src', data.imageUrl));

            popup.append(button).append(image).append(content);

            // Add UTM tracking
            popup.find('a').each(function() {
                $(this).attr('href', $(this).attr('href') + '?utm_source=salesnotice&utm_medium=notification&utm_content=recentsales');
            });

            return popup;
        }, this);

        /*
         * Displays a popup
         */
        showPopup = $.proxy(function () {
            if (!options.popups) {
                $.get(
                    '/orderpopup/ajax/load',
                    null,
                    $.proxy(function(data, status) {
                        options.popups = data.popups;
                        options.batchId = data.batchId;

                        showPopup();
                    })
                );
                return;
            }

            var popup = buildPopup(options.popups[getIndex(options.batchId, options.popups.length)]);
            this.append(popup);
        }, this);

        /*
         * Hides a popup
         */
        hidePopup = $.proxy(function () {
            this.empty();
        }, this);

        /*
         * Returns next popup index
         */
        getIndex = function (batchId, count) {
            var bi,
                newIndex;

            if (localStorage.orderPopup === undefined) {
                localStorage.orderPopup = batchId + ':' + '0';
                return 0;
            }

            bi = localStorage.orderPopup.split(':');
            newIndex = parseInt(bi[1], 10) + 1;

            // Check if new batch
            if (bi[0] !== batchId) {
                localStorage.orderPopup = batchId + ':' + '0';
                return 0;
            }

            // Check if batch size is exceeded
            if (newIndex >= count) {
                localStorage.orderPopup = batchId + ':' + '0';
                return 0;
            }

            localStorage.orderPopup = batchId + ':' + newIndex;
            return newIndex;
        };

        /*
         * Returns whether or not the popup is disabled
         */
        isDisabled = function () {
            if (localStorage.orderPopupDisable === undefined) {
                return false;
            }

            return (localStorage.orderPopupDisable + 86400 * 1000 > Date.now());
        };

        /*
         * Detects if local storage is available
         */
        hasLocalStorage = function () {
            var hasLocalStorage = true,
                testKey = "hasLocalStorage";

            try {
                localStorage.setItem(testKey, 1);
                localStorage.removeItem(testKey);
            }
            catch (err) {
                hasLocalStorage = false;
            }

            return hasLocalStorage;
        };


        /*
         * Show popups
         */
        run = $.proxy(function () {
            if (!hasLocalStorage()) {
                return;
            }

            if (isDisabled()) {
                return;
            }

            setTimeout(function () {
                showPopup();

                // Hide popup and schedule next execution
                setTimeout(function () {
                    hidePopup();
                    var newDelay = parseInt(Math.random() * options.settings.delay, 10);
                    setTimeout(function () { run(); }, newDelay);
                }, options.settings.delay_dialog);
            }, options.settings.delay_initial);
        }, this);

        run();

        return this;
    };
}(jQuery));
