import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import './signature';
import './geolocation';

Alpine.plugin(collapse);
window.Alpine = Alpine;
Alpine.start();
