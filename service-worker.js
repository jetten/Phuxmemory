self.addEventListener('push', function(event) {



  var payload = event.data.json();
  event.waitUntil(self.registration.showNotification(payload.title, {
      body: payload.body,
      requireInteraction: isPersistent(payload.persistent),
      silent: true,
      data: payload.url
    })
  );
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();

    event.waitUntil(
      self.clients.matchAll().then(function(clientList) {
        if (clientList.length > 0) {
          return clientList[0].focus();
        }
        return self.clients.openWindow(event.notification.data);
      })
    );
});

function isPersistent(payloadPesistentString) {
  if(payloadPesistentString === "true") {
    return true;
  } else {
    return false;
  }
}
