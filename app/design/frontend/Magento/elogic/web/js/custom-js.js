define([
        "jquery",
        "selectize",
        "sticky"
    ],
    function ($) {
        "use strict";

        $(function () {
            $('select').niceSelect();

            $('.page-header').sticky({
                container: '.page-wrapper'
            });
        });
    });
