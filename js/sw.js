self.addEventListener('push', function (event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    var sendNotification = function(payload) {
        var title = "Open Social",
            icon = '/sites/default/files/images/touch/open-social.png';

        payload = payload || 'You\'ve received a message!';

        return self.registration.showNotification(title, {
            body: payload,
            icon: icon
        });
    };

    if (event.data) {
        var data = event.data.json();
        console.log(event.data);
        console.log(event.data.json());
        event.waitUntil(
            sendNotification(data.message)
        );
    }

    // CRUCIAL PART:
    // Create a controller where this sw.js fetches the last payload sent to
    // an subscription by checking it. When the browser is closed and the user
    // starts the browser, this sw.js should fetch the latest push notification.
    //
    // else {
    //     event.waitUntil(
    //         self.registration.pushManager.getSubscription().then(function(subscription) {
    //             if (!subscription) {
    //                 return;
    //             }
    //                 return fetch('/getPayload?endpoint=' + encodeURIComponent(subscription.endpoint)).then(function (response) {
    //                     if (response.status !== 200) {
    //                         throw new Error();
    //                     }
    //                     // Examine the text in the response
    //                     return response.json().then(function (data) {
    //                         if (data.error || !data) {
    //                             throw new Error();
    //                         }
    //                         return sendNotification(data);
    //                     });
    //                 }).catch(function () {
    //                     return sendNotification();
    //                 });
    //
    //         })
    //     );
    // }
});

// Build this so when the notification shows up and the user
// clicks the notification, the user will be redirected to
// the correct page.
self.addEventListener('notificationclick', function(event) {
    console.log('[Service Worker] Notification click Received.');

    event.notification.close();

    event.waitUntil(
        // Make this the url where it needs to go to
        clients.openWindow('https://social.dev')
    );
});