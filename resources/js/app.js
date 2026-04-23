import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

window.Echo.private(`advertisement.dairy`)
    .listen('AdvertisementStatusUpdated', (event) => {
        alert(`New Advertisement Update: ${event.message}`);
    });

window.Echo.private(`advertisement.superintendent`)
    .listen('AdvertisementStatusUpdated', (event) => {
        alert(`New Advertisement Update: ${event.message}`);
    });

window.Echo.private(`advertisement.deputy_director`)
    .listen('AdvertisementStatusUpdated', (event) => {
        alert(`New Advertisement Update: ${event.message}`);
    });

window.Echo.private(`advertisement.director_general`)
    .listen('AdvertisementStatusUpdated', (event) => {
        alert(`New Advertisement Update: ${event.message}`);
    });

