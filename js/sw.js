/**
 * @file
 * The Service Worker.
 */

self.addEventListener('push', function(e) {
    console.log('[Service Worker] Push Received.');
    //console.log(`[Service Worker] Push had this data: "${event.data.text()}"`);

    var notificationTitle = 'Social PWA';
    var notificationOptions = {
        body: 'Gorilla',
        icon: '/sites/default/files/images/touch/gg-icon-256x256.png',
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
