import './bootstrap';
import './live-search';
import { createApp } from 'vue';
import { createPinia } from 'pinia';

// Import Vue components
import BookingCalendar from './components/BookingCalendar.vue';
import QrScanner from './components/QrScanner.vue';
import PaymentForm from './components/PaymentForm.vue';
import ParishionerSearch from './components/ParishionerSearch.vue';

// Initialize Vue apps on specific pages
const mountComponent = (selector, component, props = {}) => {
    const el = document.querySelector(selector);
    if (el) {
        const app = createApp(component, props);
        app.use(createPinia());
        app.mount(el);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    mountComponent('#booking-calendar', BookingCalendar);
    mountComponent('#qr-scanner', QrScanner);
    mountComponent('#payment-form', PaymentForm);
    mountComponent('#parishioner-search', ParishionerSearch);
});

// Global utilities
window.confirmDelete = (message = 'Are you sure you want to delete this record? This action cannot be undone.') => {
    return confirm(message);
};

window.formatCurrency = (amount) => {
    return '₱' + parseFloat(amount).toLocaleString('en-PH', { minimumFractionDigits: 2 });
};
