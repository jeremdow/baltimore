self.addEventListener('push', function (event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    var sendNotification = function(message) {

        var title = "Open Social",
            icon = '/sites/default/files/images/touch/open-social.png';

        message = message || 'Message received!';

        return self.registration.showNotification(title, {
            body: message,
            icon: icon
        });
    };

    if (event.data) {
        var data = event.data.json();
        event.waitUntil(
            sendNotification(data.notification.message)
        );
    } else {
        event.waitUntil(
            self.registration.pushManager.getSubscription().then(function(subscription) {
                if (!subscription) {
                    return;
                }
                    return fetch('/send/notification/getPayload?endpoint=' + encodeURIComponent(subscription)).then(function (response) {
                        //console.log(response.json());
                        if (response.status !== 200) {
                            throw new Error();
                        }
                        // Examine the text in the response
                        return response.json().then(function (data) {
                            if (data.error || !data.notification) {
                                throw new Error();
                            }
                            //console.log(data.notification.message);
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