'use strict';

const applicationServerPublicKey = 'BPcHVWxT9OtIubNDDePH2yP6QaNRJ3JvbLAMXPGt-FigOR5i8Yl5fomNN6ZHDTG67EQIAaDGnRZQeAZW1NuuElQ';
const pushButton = $("#subscribe");

var translation = translation || { subscribe: 'Subscribe to live notifications', unsubscribe: 'Unsubscribe from notifications', blocked: 'Notifications have been blocked', not_supported: 'Notifications are not supported' };

let isSubscribed = false;
let swRegistration = null;

function urlB64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - base64String.length % 4) % 4);
  const base64 = (base64String + padding)
    .replace(/\-/g, '+')
    .replace(/_/g, '/');

  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);

  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  return outputArray;
}

if ('serviceWorker' in navigator && 'PushManager' in window) {
	navigator.serviceWorker.register('/js/sw.js').then(function(swReg) {
	    console.log('Service Worker is registered', swReg);
	    swRegistration = swReg;
	    initializeUI();
	})
	.catch(function(error) {
		console.error('Service Worker Error', error);
	});
} else {
	pushButton.html(translation.not_supported);
}

function initializeUI() {
	pushButton.click(function () {
		pushButton.attr('disabled', true);
	    if (isSubscribed) {
	        unsubscribeUser();
	    } else {
	    	subscribeUser();
	    }
	});
	
	swRegistration.pushManager.getSubscription().then(function(subscription) {
		isSubscribed = !(subscription === null);
	    if (isSubscribed) {
	      console.log('User IS subscribed.');
	      updateSubscriptionOnServer(subscription, 'PATCH')
	    } else {
	      console.log('User is NOT subscribed.');
	    }
	    updateBtn();
	});
}

function subscribeUser() {
	const applicationServerKey = urlB64ToUint8Array(applicationServerPublicKey);
	swRegistration.pushManager.subscribe({
	    userVisibleOnly: true,
	    applicationServerKey: applicationServerKey
	})
	.then(function(subscription) {
		console.log('User is subscribed.');
		updateSubscriptionOnServer(subscription, "POST");
		isSubscribed = true;
		updateBtn();
	})
	.catch(function(err) {
		console.log('Failed to subscribe the user: ', err);
		updateBtn();
	});
}

function unsubscribeUser() {
	swRegistration.pushManager.getSubscription()
	.then(function(subscription) {
		if (subscription) {
		    updateSubscriptionOnServer(subscription, "DELETE");
			return subscription.unsubscribe();
	    }
	})
	.catch(function(error) {
	    console.log('Error unsubscribing', error);
	})
	.then(function() {
	    console.log('User is unsubscribed.');
	    isSubscribed = false;

	    updateBtn();
	});
}

function updateSubscriptionOnServer(subscription, method) {
	if (subscription) {
	    const key = subscription.getKey('p256dh');
	    const token = subscription.getKey('auth');
	    const contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];
	    $.ajax({
	    	url: '/ajax/pushSubscription',
	    	method: method,
	    	data : {
	    		event: $('#event-info').data('event-id'),
		        endpoint: subscription.endpoint,
		        expirationTime: subscription.expirationTime,
	            publicKey: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
	            authToken: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null,
		        contentEncoding
	    	}
	    });
	}
}

function updateBtn() {
	if (Notification.permission === 'denied') {
	    pushButton.html(translation.blocked);
	    pushButton.attr('disabled', true);
	    updateSubscriptionOnServer(null);
	    return;
	}
	if (isSubscribed) {
		pushButton.html(translation.unsubscribe);
	} else {
		pushButton.html('<i class="fa fa-envelope button-animation"></i> ' + translation.subscribe + ' <i class="fa fa-hand-o-up faa-vertical animated button-animation" aria-hidden="true"></i>');
	}
	pushButton.attr('disabled', false);
}