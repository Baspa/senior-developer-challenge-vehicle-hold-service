import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('dashboard', () => ({

}));

Alpine.data('vehicleCard', (initial) => ({
    vehicle: { id: initial.id, name: initial.name, vin: initial.vin },
    hold: initial.hold,
    apiKey: document.querySelector('meta[name="api-key"]').content,
}));

Alpine.start();