// Do not try to load $, _, Backbone if they're included with wp_enqueue_script

if (typeof jQuery === 'function') {
  define( 'jquery', function () { console.log('Bundled $'); return jQuery; });
}

if (typeof _ === 'function') {
  define( 'underscore', function () { console.log('Bundled _'); return _; });
}

if (typeof Backbone === 'function') {
  define( 'backbone', function () { console.log('Bundled Backbone'); return Backbone; });
}

/**
 * Main requirejs config
 * @type {Object}
 */
require.config({
    catchError: { define: true },
    paths: merge_options( {
        "underscore": MyApp.paths.coreIncludesPath + "/underscore.min",
        "backbone": MyApp.paths.coreIncludesPath + "/backbone.min",
        "stackmob": "../vendor/stackmob/stackmob-js-0.9.2",
    }, wprjs_paths ),
    shim: merge_options( {
        "underscore": {
            deps: [],
            exports: "_"
        },
        "backbone": {
            deps: ["jquery", "underscore"],
            exports: "Backbone"
        },
        "stackmob": {
            deps: ["backbone"],
            exports: "StackMob"
        }
    }, wprjs_shim )
});

// App init
require( [ "router", "backbone", "models/user" ],
    function ( Router, Backbone, AppUser ) {
        // Save the reference to router for easy recycling
        MyApp.router = new Router();
        AppUser.initialize();
        Backbone.history.start({pushState: true});
    }
);

/**
 * Overwrites obj1's values with obj2's and adds obj2's if non existent in obj1
 * @param obj1
 * @param obj2
 * @returns obj3 a new object based on obj1 and obj2
 */
function merge_options(obj1,obj2){
    var obj3 = {};
    for (var attrname in obj1) { obj3[attrname] = obj1[attrname]; }
    for (var attrname in obj2) { obj3[attrname] = obj2[attrname]; }
    return obj3;
}