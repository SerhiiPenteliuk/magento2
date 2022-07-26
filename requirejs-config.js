var config = {

    deps: [
        "js/custom-js",
    ],

    map: {
        '*': {
            'selectize': 'js/jquery.nice-select'
        }
    },
    "shim": {
        "selectize": ["jquery"]
    }
};
