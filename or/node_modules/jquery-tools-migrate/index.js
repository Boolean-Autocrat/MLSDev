/**
 * jQuery Tools Migrate - v1.0.0 - 2016-06-07
 * https://github.com/keithws/jquery-tools-migrate
 * Inspired by jQuery Migrate and adapted to jQuery Tools
 * @author Keith W. Shaw <keith.w.shaw@gmail.com>
 * @license MIT
 */
(function jQueryToolsMigrate (jQuery) {

    "use strict";

    var old, warnedAbout;

    warnedAbout = {};

    // List of warnings already given; public read only
    jQuery.tools.migrateWarnings = [];

    // Set to true to prevent console output; migrateWarnings still maintained
    // jQuery.tools.migrateMute = false;

    // Show a message on the console so devs know we're active
    if (!jQuery.tools.migrateMute && window.console && window.console.log) {

        window.console.log("JQTMIGRATE: Logging is active");

    }

    // Set to false to disable traces that appear with warnings
    if (typeof jQuery.tools.migrateTrace === "undefined") {

        jQuery.tools.migrateTrace = true;

    }

    // Forget any warnings we've already given; public
    jQuery.tools.migrateReset = function migreateReset () {

        warnedAbout = {};
        jQuery.tools.migrateWarnings.length = 0;

    };

    /**
     * helper function to log messages and trace
     * @param {String} msg The message
     * @return {undefined} undefined
     */
    function migrateWarn (msg) {

        if (!warnedAbout[msg]) {

            warnedAbout[msg] = true;
            jQuery.tools.migrateWarnings.push(msg);
            if (window.console && console.warn && !jQuery.tools.migrateMute) {

                console.warn("JQTMIGRATE: " + msg);
                if (jQuery.tools.migrateTrace && console.trace) {

                    console.trace();

                }

            }

        }

    }

    old = {
        "tabs": jQuery.fn.tabs,
        "tooltip": jQuery.fn.tooltip,
        "overlay": jQuery.fn.overlay,
        "scrollable": jQuery.fn.scrollable,
        "validator": jQuery.fn.validator,
        "rangeinput": jQuery.fn.rangeinput,
        "dateinput": jQuery.fn.dateinput,
        "mask": jQuery.fn.mask,
        "expose": jQuery.fn.expose,
        "flashembed": window.flashembed,
        "history": jQuery.fn.history,
        "mousewheel": jQuery.fn.mousewheel
    };

    if (jQuery && jQuery.fn.tabs) {

        jQuery.fn.tabs = function migrateTabs () {

            migrateWarn("jQuery Tools Tabs is DEPRECATED");

            return old.tabs.apply(this, arguments);

        };

    }

    if (jQuery && jQuery.fn.tooltip) {

        jQuery.fn.tooltip = function migrateTooltip () {

            migrateWarn("jQuery Tools Tooltip is DEPRECATED");

            return old.tooltip.apply(this, arguments);

        };

    }

    if (jQuery && jQuery.fn.overlay) {

        jQuery.fn.overlay = function migrateOverlay () {

            migrateWarn("jQuery Tools Overlay is DEPRECATED");

            return old.overlay.apply(this, arguments);

        };

    }

    if (jQuery && jQuery.fn.scrollable) {

        jQuery.fn.scrollable = function migrateScrollable () {

            migrateWarn("jQuery Tools Scrollable is DEPRECATED");

            return old.scrollable.apply(this, arguments);

        };

    }

    if (jQuery && jQuery.fn.validator) {

        jQuery.fn.validator = function migrateValidator () {

            migrateWarn("jQuery Tools Form Validator is DEPRECATED");

            return old.validator.apply(this, arguments);

        };

    }

    if (jQuery && jQuery.fn.rangeinput) {

        jQuery.fn.rangeinput = function migrateRangeinput () {

            migrateWarn("jQuery Tools Rangeinput is DEPRECATED");

            return old.rangeinput.apply(this, arguments);

        };

    }

    if (jQuery && jQuery.fn.dateinput) {

        jQuery.fn.dateinput = function migrateDateinput () {

            migrateWarn("jQuery Tools Dateinput is DEPRECATED");

            return old.dateinput.apply(this, arguments);

        };

    }

    if (jQuery && jQuery.fn.mask) {

        jQuery.fn.mask = function migrateMask () {

            migrateWarn("jQuery Tools Mask is DEPRECATED");

            return old.mask.apply(this, arguments);

        };

    }

    if (jQuery && jQuery.fn.expose) {

        jQuery.fn.expose = function migrateExpose () {

            migrateWarn("jQuery Tools Expose is DEPRECATED");

            return old.expose.apply(this, arguments);

        };

    }

    if (window && window.flashembed) {

        window.flashembed = function migrateFlashembed () {

            migrateWarn("jQuery Tools Flashembed is DEPRECATED");

            return old.flashembed.apply(this, arguments);

        };

    }

    if (jQuery && jQuery.fn.history) {

        jQuery.fn.history = function migrateHistory () {

            migrateWarn("jQuery Tools History is DEPRECATED");

            return old.history.apply(this, arguments);

        };

    }

    if (jQuery && jQuery.fn.mousewheel) {

        jQuery.fn.mousewheel = function migrateMousewheel () {

            migrateWarn("jQuery Tools Mousewheel is DEPRECATED");

            return old.mousewheel.apply(this, arguments);

        };

    }

}(window.jQuery));
