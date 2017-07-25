/**
 * custom.js
 * @author      {@link https://socialveo.com Socialveo}
 * @copyright   Copyright (C) 2017 Socialveo Sagl - All Rights Reserved
 * @license     Proprietary Software Socialveo (C) 2017, Socialveo Sagl {@link https://socialveo.com/legal Socialveo Legal Policies}
 */
/* jshint node: true */
'use strict';

(function($) {
    var openNavigation = function($item) {
        var $e = $item.parent();
        if ($e[0] && $e.is(".submenu")) {
            $e.addClass("in");
            $e.prev().removeClass("active");
            openNavigation($e);
        }
    }

    $(document).ready(function() {
        openNavigation($("#navigation .list-group-item.active"));
        openNavigation($("#navigation .submenu.active"));

        $('.list-group-item[data-toggle="collapse"] + .submenu.collapse').each(function() {
            if (!$(this).is(".in")) {
                $(this).prev().addClass("collapsed");
            }
        })
    });
}) (jQuery);