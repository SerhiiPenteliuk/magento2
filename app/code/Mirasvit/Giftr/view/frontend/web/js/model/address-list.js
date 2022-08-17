define(
    [
        'ko',
        './registrant-address'
    ],
    function(ko, defaultProvider) {
        "use strict";

        return ko.observable(defaultProvider.getRegistrantAddress());
    }
);