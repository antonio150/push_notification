self.addEventListener('push',event =>{
    let title = 'Notification';
    let body = 'You have a new message.';

    if(event.data){
        try{
            const data = event.data.json();
            title = data.title || title;
            body = data.body || body;
        }catch(e){
            body = event.data.text();
        }
    }

    event.waitUntil(
        self.registration.showNotification(title,{
            body: body,
            icon: '/icons/icon-192x192.png',
            tag: 'symfony-push',
        })
    );

    self.addEventListener('notificationclick', event => {
        event.notification.close();
        event.waitUntil(
            clients.openWindow("/")
        )
    })
})