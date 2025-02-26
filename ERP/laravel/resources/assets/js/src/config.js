/**
 * Returns the value for the configuration
 * 
 * @param {string} key
 * @throws {Error}
 */
window.config = (() => {
    "use strict";
    
    const configs = {
        'root.url': process.env.MIX_ROOT_URL,
        'pusher.app.key': process.env.MIX_PUSHER_APP_KEY,
        'pusher.app.cluster': process.env.MIX_PUSHER_APP_CLUSTER,
        'ws.scheme': process.env.MIX_WS_SCHEME,
        'ws.host': process.env.MIX_WS_HOST,
        'ws.port': process.env.MIX_WS_PORT
    }

    return function config(key) {
        if (undefined === configs[key]) {
            throw new Error(`Invalid configuration: ${key}`)
        }

        return configs[key];
    }
})();