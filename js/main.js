/**
 * @file
 * Registers the Service Worker. See /social_pwa/js/sw.js.
 */

(function ($, Drupal, drupalSettings) {

    'use strict';

    Drupal.behaviors.serviceWorkerLoad = {
        attach: function () {

            console.log('MAIN Loaded.');

            const applicationServerPublicKey = 'BDKOV1CjMAQ6L_eSfJWsZbSS2qv_QwCAYA-ltYzmTPX-AWEDIDRwCTsFaxCCip_WzmwRVnbvwFAtMS00W7JZopw';

            var isSubscribed = false;
            var swRegistration = null;

            function urlB64ToUint8Array(base64String) {
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

            function subscribeUser() {
                const applicationServerKey = urlB64ToUint8Array(applicationServerPublicKey);
                swRegistration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: applicationServerKey
                })
                    .then(function (subscription) {
                        console.log('User is subscribed:', subscription);

                        updateSubscriptionOnServer(subscription);

                        isSubscribed = true;

                    })
                    .catch(function (err) {
                        console.log('Failed to subscribe the user: ', err);
                    });
            }

            function updateSubscriptionOnServer(subscription) {
                // TODO: Send subscription to application server

                if (subscription) {
                    console.log('TODO: send the following to the database');
                    console.log(JSON.stringify(subscription));
                }
            }
        }
    }

})(jQuery, Drupal, drupalSettings);