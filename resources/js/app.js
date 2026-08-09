import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Toast notification component
Alpine.data('toast', () => ({
    show: false,
    message: '',
    type: 'success',
    timeout: null,

    notify(message, type = 'success', duration = 3000) {
        this.message = message;
        this.type = type;
        this.show = true;

        if (this.timeout) clearTimeout(this.timeout);
        this.timeout = setTimeout(() => {
            this.show = false;
        }, duration);
    },

    init() {
        // Listen for toast events
        window.addEventListener('toast', (event) => {
            this.notify(event.detail.message, event.detail.type || 'success');
        });

        // Auto-show flash messages
        const flashMessage = document.querySelector('meta[name="flash-message"]');
        if (flashMessage) {
            this.notify(flashMessage.content, flashMessage.getAttribute('data-type') || 'success');
        }
    }
}));

// Confirmation dialog component
Alpine.data('confirmDialog', () => ({
    show: false,
    title: '',
    message: '',
    confirmText: 'Ya',
    cancelText: 'Batal',
    onConfirm: null,

    open(title, message, onConfirm, confirmText = 'Ya') {
        this.title = title;
        this.message = message;
        this.confirmText = confirmText;
        this.onConfirm = onConfirm;
        this.show = true;
    },

    confirm() {
        if (this.onConfirm) this.onConfirm();
        this.show = false;
    },

    cancel() {
        this.show = false;
    }
}));

// Dropdown component
Alpine.data('dropdown', () => ({
    open: false,
    toggle() {
        this.open = !this.open;
    },
    close() {
        this.open = false;
    }
}));

// Sidebar toggle for mobile
Alpine.data('sidebar', () => ({
    open: false,
    toggle() {
        this.open = !this.open;
    }
}));

// Search component
Alpine.data('globalSearch', () => ({
    query: '',
    results: [],
    loading: false,
    showResults: false,

    async search() {
        if (this.query.length < 2) {
            this.results = [];
            this.showResults = false;
            return;
        }

        this.loading = true;
        this.showResults = true;

        try {
            const response = await fetch(`/admin/search?q=${encodeURIComponent(this.query)}`);
            this.results = await response.json();
        } catch (e) {
            this.results = [];
        }

        this.loading = false;
    },

    close() {
        setTimeout(() => {
            this.showResults = false;
        }, 200);
    }
}));

// Format currency
window.formatRupiah = function(number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(number);
};

Alpine.start();
