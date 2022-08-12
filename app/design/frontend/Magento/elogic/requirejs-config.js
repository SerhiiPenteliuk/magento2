var config = {

    deps: [
        "js/custom-js",
    ],

    map: {
        '*': {
            'selectize': 'js/jquery.nice-select',
            'CustomWidget': 'js/mywidget'
        }
    },
    shim: {
        "selectize": ['jquery'],
        'CustomWidget': ['jquery', 'jquery/ui']
    }
};
