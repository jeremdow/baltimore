if( 'function' === typeof importScripts) {
    importScripts('https://www.gstatic.com/firebasejs/3.6.4/firebase-app.js');
    importScripts('https://www.gstatic.com/firebasejs/3.6.4/firebase-messaging.js');
}

firebase.initializeApp({
    apiKey: "AIzaSyDISUwloZ-D4kqJVAm0-6m-fZzovQCnY8w",
    messagingSenderId: "205759468478"
});

const messaging = firebase.messaging();

self.addEventListener('push', function(e) {
    console.log('[Service Worker] Push Received.');

    var notificationTitle = 'Social PWA';
    var notificationOptions = {
        body: 'Gorilla',
        //icon: '/sites/default/files/images/touch/gg-icon-256x256.png',
        badge: ''
    };

    e.waitUntil(self.registration.showNotification(notificationTitle, notificationOptions));
});

self.addEventListener('notificationclick', function(e) {
    console.log('[Service Worker] Notification click Received.');

    e.notification.close();

    e.waitUntil(
        clients.openWindow('https://social.dev')
    );
});