import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();




if(window.UpChecker.user){

    console.log('Echo private channel '+`user.${window.UpChecker.user.id}`);

    Echo.private(`user.${window.UpChecker.user.id}`)
        .listen('EndpointCreatedEvent', (e) => {
            console.log(e);
        })
        .listen('EndpointDeletedEvent', (e) => {
            console.log(e);
        });

}




