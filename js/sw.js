self.addEventListener('push', function(event) {
    console.log('[Service Worker] Push Received.');

    var notificationTitle = 'Social PWA';
    var notificationOptions = {
        body: 'Gorilla says Hello!',
        //icon: '/sites/default/files/images/touch/gg-icon-256x256.png',
        badge: ''
    };

    event.waitUntil(self.registration.showNotification(notificationTitle, notificationOptions));

});

self.addEventListener('notificationclick', function(event) {
    console.log('[Service Worker] Notification click Received.');

    event.notification.close();

    event.waitUntil(
        clients.openWindow('https://social.dev')
    );
});