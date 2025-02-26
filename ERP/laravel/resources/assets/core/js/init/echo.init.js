$(function() {
    window.echo = new Echo.default({
        broadcaster: 'pusher',
        key: config('pusher.app.key'),
        enabledTransports: ['ws', 'wss'],
        disabledTransports: ['sockjs', 'xhr_polling', 'xhr_streaming'],
        forceTLS: config('ws.scheme') == 'https',
        wsHost: config('ws.host'),
        wsPort: config('ws.port'),
        wssPort: config('ws.port'),
        authEndpoint: route('broadcasting.auth'),
        disableStats: true
    });
});