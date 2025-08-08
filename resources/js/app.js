import 'jquery';
import 'moment';
import 'daterangepicker';
import '@fortawesome/fontawesome-free/css/all.min.css';
import 'daterangepicker/daterangepicker.css';

import Alpine from 'alpinejs';
import adminDashboard from './alpine/adminDashboard';

// Initialize jQuery globally
window.$ = window.jQuery = require('jquery');

// Register Alpine components
document.addEventListener('alpine:init', () => {
    Alpine.data('adminDashboard', adminDashboard);
});

window.Alpine = Alpine;
Alpine.start();