/**
 * @file
 * Registers the Service Worker. See /social_pwa/js/sw.js.
 */

(function ($, Drupal, drupalSettings) {

    'use strict';

    Drupal.behaviors.serviceWorkerLoad = {
        attach: function () {

            console.log('Main.js Loaded.');

            // TODO: Should not be public. Rewrite the subscription process.
            const vapidPublicKey = 'BFhe5EFfcPn0XDnBAgNGPIqKocwI-yimiWet1fQXNbFtCwlRzmGVDTJoG8fjxjXEXmFqt8BzcaDtkFyTdUk2cb8';

            var isSubscribed = false;
            var swRegistration = null;

            function urlBase64ToUint8Array(base64String) {
                const padding = '='.repeat((4 - base64String.length % 4) % 4);
                const base64 = (base64String + padding)
                    .replace(/\-/g, '+')
                    .replace(/_/g, '/');

                const rawData = window.atob(base64);
                const outputArray = new Uint8Array(rawData.length);

                for (var i = 0; i < rawData.length; ++i) {
                    outputArray[i] = rawData.charCodeAt(i);
                }
                return outputArray;
            }

            navigator.serviceWorker.addEventListener('message', function(e) {
                var message = e.data;
            });

            if ('serviceWorker' in navigator && 'PushManager' in window) {
                console.log('Service Worker and Push is supported.');

                navigator.serviceWorker.register('/sw.js')
                    .then(function (swReg) {
                        console.log('Service Worker is registered', swReg);

                        swRegistration = swReg;
                        checkSubscription();
                    })
                    .catch(function (error) {
                        console.error('Service Worker Error', error);
                    });
            } else {
                console.warn('Push messaging is not supported');
            }

            function checkSubscription() {
                // Set the initial subscription value
                swRegistration.pushManager.getSubscription()
                    .then(function (subscription) {
                        isSubscribed = !(subscription === null);

                        if (isSubscribed) {
                            console.log('User IS subscribed.');
                        } else {
                            console.log('User is NOT subscribed.');
                        }

                        subscribeUser();
                    });
            }

            // TODO: Rewrite the subscribeUser function because of vapid.
            function subscribeUser() {
                // Creating an overlay to provide focus to the permission prompt.
                $('body').append('<div class="social_pwa--overlay" style="width: 100%; height: 100%; position: fixed; background-color: rgba(0,0,0,0.5); left: 0; top: 0; z-index: 999;"></div>');
                const applicationServerKey = urlBase64ToUint8Array(vapidPublicKey);
                navigator.serviceWorker.ready.then(function(swRegistration) {
                    swRegistration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: applicationServerKey
                    })
                        .then(function (subscription) {
                            console.log('User is subscribed:', subscription);
                            // Delete the overlay since the user has accepted.
                            $('.social_pwa--overlay').remove();

                            updateSubscriptionOnServer(subscription);

                            isSubscribed = true;

                        })
                        .catch(function (err) {
                            console.log('Failed to subscribe the user: ', err);
                            // Delete the overlay since the user has denied.
                            $('.social_pwa--overlay').remove();
                        });
                })
            }

            function updateSubscriptionOnServer(subscription) {

                    var key = subscription.getKey('p256dh');
                    var token = subscription.getKey('auth');
                    // console.log(key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null);
                    // console.log(token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null);

                    var sub = JSON.stringify({
                        'endpoint': getEndpoint(subscription),
                        'key': key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
                        'token': token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null
                    });

                    $.ajax({
                        url: '/subscription',
                        type: 'POST',
                        data: sub,
                        dataType: "json",
                        contentType: "application/json;charset=utf-8",
                        async: true,
                        fail: function(msg) {
                            console.log('Something went wrong during subscription update.');
                        },
                        complete: function(msg) {
                            console.log('Subscription added to database.');
                        }
                    });
                return true;
            }

            function getEndpoint(pushSubscription) {
                var endpoint = pushSubscription.endpoint;
                var subscriptionId = pushSubscription.subscriptionId;

                // fix Chrome < 45
                if (subscriptionId && endpoint.indexOf(subscriptionId) === -1) {
                    endpoint += '/' + subscriptionId;
                }

                return endpoint;
            }

        }
    }

})(jQuery, Drupal, drupalSettings);
