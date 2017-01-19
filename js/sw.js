// self.addEventListener('push', function(event) {
//     console.log('[Service Worker] Push Received.');
//     var payload = event.data ? event.data.text() : 'no payload';
//    event.waitUntil(self.registration.showNotification('Open Social', {
//             body: payload
//         })
//     );
//     // var notificationTitle = 'Social PWA';
//     // var notificationOptions = {
//     //     body: 'Open Social says Hello!',
//     //     icon: '/sites/default/files/images/touch/open-social.png',
//     //     badge: ''
//     // };
//     //
//     // event.waitUntil(self.registration.showNotification(notificationTitle, notificationOptions));
// });

self.addEventListener('push', function (event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    var sendNotification = function(message, tag) {

        var title = "Open Social",
            icon = '/sites/default/files/images/touch/open-social.png';

        message = message || 'No Payload';
        tag = tag || 'general';

        return self.registration.showNotification(title, {
            body: message,
            icon: icon,
            tag: tag
        });
    };

    if (event.data) {
        var data = event.data.json();
        event.waitUntil(
            sendNotification(data.message, data.tag)
        );
    } else {
        event.waitUntil(
            self.registration.pushManager.getSubscription().then(function(subscription) {
                if (!subscription) {
                    return;
                }
                var data = subscription.endpoint.replace('https://fcm.googleapis.com/fcm/send/','');
                return fetch('/send/notification/' + data).then(function (response) {
                    //console.log(response.json());
                    if (response.status !== 200) {
                        throw new Error();
                    }
                    console.log('kaas');
                    // Examine the text in the response
                    return response.json().then(function (data) {
                        console.log(data);
                        if (data.error || !data.notification) {
                            throw new Error();
                        }
                        console.log(data.notification.message);
                        return sendNotification(data.notification.message);
                    });
                }).catch(function () {
                    return sendNotification();
                });
            })
        );
    }
});


self.addEventListener('notificationclick', function(event) {
    console.log('[Service Worker] Notification click Received.');

    event.notification.close();

    event.waitUntil(
        clients.openWindow('https://social.dev')
    );
});

self.addEventListener('message', function (event) {
    var message = event.data;
});