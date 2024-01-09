import './bootstrap';

// import Alpine from 'alpinejs';
// window.Alpine = Alpine;
// Alpine.start();

if(window.UpChecker.user){

    console.log('Laravel Echo private channel '+`user.${window.UpChecker.user.id}`);

    Echo.private(`user.${window.UpChecker.user.id}`)
        .listen('ProjectCreatedEvent', (e) => {
            console.log(e);
        })
        .listen('ProjectUpdatedEvent', (e) => {
            console.log(e);
        })
        .listen('ProjectDeletedEvent', (e) => {
            console.log(e);
        })
        .listen('EndpointCreatedEvent', (e) => {
            console.log(e);
        })
        .listen('EndpointUpdatedEvent', (e) => {
            console.log(e);
        })
        .listen('EndpointDeletedEvent', (e) => {
            console.log(e);
        })
        .listen('CheckupCreatedEvent', (e) => {
            console.log(e);
        });

}




