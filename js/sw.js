self.addEventListener('install', function(event) {
    self.skipWaiting();
});

self.addEventListener('activate', function(event) {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('push', function (event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    var sendNotification = function(payload, icon) {
        var title = "Open Social",
            icon = icon | '/sites/default/files/images/touch/open-social.png';
        payload = payload || 'You\'ve received a message!';
        return self.registration.showNotification(title, {
            body: payload,
            icon: icon
        });
    };

    if (event.data) {
        var data = event.data.json();
        event.waitUntil(
            // Retrieve a list of the clients of this service worker.
            self.clients.matchAll().then(function(clientList) {
                // Check if there's at least one focused client.
                var focused = clientList.some(function(client) {
                    return client.focused;
                });

                // The page is focused, don't show the notification.
                if (focused) {
                    console.log('[Service Worker] Push received: ' + data.message);
                    return true;
                }
                // The page is still open but unfocused.
                else if (clientList.length > 0) {
                    sendNotification(data.message)
                }
                // The page is closed, send a push!
                else {
                    sendNotification(data.message)
                }
            })
        );
    }

});

self.addEventListener('notificationclick', function(event) {
    // Close the notification when the user clicks it.
    event.notification.close();

    event.waitUntil(
        // Retrieve a list of the clients of this service worker.
        self.clients.matchAll().then(function(clientList) {
            // If there is at least one client, focus it.
            if (clientList.length > 0) {
                return clientList[0].focus();
            }
            // Otherwise, open a new page.
            return self.clients.openWindow('/');
        })
    );
});