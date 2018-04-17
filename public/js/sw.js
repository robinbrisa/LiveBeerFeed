/*
*
*  Push Notifications codelab
*  Copyright 2015 Google Inc. All rights reserved.
*
*  Licensed under the Apache License, Version 2.0 (the "License");
*  you may not use this file except in compliance with the License.
*  You may obtain a copy of the License at
*
*      https://www.apache.org/licenses/LICENSE-2.0
*
*  Unless required by applicable law or agreed to in writing, software
*  distributed under the License is distributed on an "AS IS" BASIS,
*  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
*  See the License for the specific language governing permissions and
*  limitations under the License
*
*/

/* eslint-env browser, serviceworker, es6 */

'use strict';

/* eslint-disable max-len */

const applicationServerPublicKey = 'BPcHVWxT9OtIubNDDePH2yP6QaNRJ3JvbLAMXPGt-FigOR5i8Yl5fomNN6ZHDTG67EQIAaDGnRZQeAZW1NuuElQ';

/* eslint-enable max-len */

function urlB64ToUint8Array(base64String) {
	const padding = '='.repeat((4 - base64String.length % 4) % 4);
	const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
	
	const rawData = window.atob(base64);
	const outputArray = new Uint8Array(rawData.length);

	for (let i = 0; i < rawData.length; ++i) {
		outputArray[i] = rawData.charCodeAt(i);
	}
	return outputArray;
}

self.addEventListener('push', function(event) {
	if (!(self.Notification && self.Notification.permission === 'granted')) {
		console.log("Received notification but permission was not granted");
		return;
	}
	
	console.log('[SW] Push Received.');
	
	var data = {};
	if (event.data) {
		data = event.data.json();
	}
	
	var title = data.title || "Live Beer Feed Notification";
	var options = {
		body: data.message || "You got a notification!",
		vibrate: [150, 150, 150, 150, 150],
		icon: data.icon || "/images/events/notification/beer_icon.png",
		badge: "/images/events/notification/beer_icon.png",
		data: data.more
	};
	
	event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function(event) {
	event.notification.close();
	
	console.log('[SW] Clicked');
	
	var url = 'https://www.livebeerfeed.com';

	if (event.notification.data.eventID) {
		url += '/event/' + event.notification.data.eventID;
	}
	
	event.waitUntil(clients.openWindow(url));
});

self.addEventListener('pushsubscriptionchange', function(event) {
  console.log('[Service Worker]: \'pushsubscriptionchange\' event fired.');
  const applicationServerKey = urlB64ToUint8Array(applicationServerPublicKey);
  event.waitUntil(
    self.registration.pushManager.subscribe({
      userVisibleOnly: true,
      applicationServerKey: applicationServerKey
    })
    .then(function(newSubscription) {
      // TODO: Send to application server
      console.log('[Service Worker] New subscription: ', newSubscription);
    })
  );
});
