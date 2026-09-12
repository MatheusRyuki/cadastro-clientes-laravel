import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';

Alpine.plugin(focus);

const htmlFetchHeaders = {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'text/html',
};

function replaceHtmlAndInitTree(container, html) {
    container.innerHTML = html;
    Alpine.initTree(container);
    container.scrollTop = 0;
}

Alpine.store('toasts', {
    items: [],

    push(message, type = 'success') {
        const id = Date.now() + Math.random();
        this.items.push({ id, message, type });
        setTimeout(() => this.dismiss(id), 5000);
    },

    dismiss(id) {
        this.items = this.items.filter((item) => item.id !== id);
    },
});

Alpine.data('confirmModal', () => ({
    open: false,
    title: '',
    message: '',
    confirmLabel: '',
    cancelLabel: '',
    variant: 'destructive',
    form: null,

    show({ title, message, confirmLabel, cancelLabel, variant, form }) {
        this.title = title;
        this.message = message;
        this.confirmLabel = confirmLabel;
        this.cancelLabel = cancelLabel;
        this.variant = variant ?? 'destructive';
        this.form = form;
        this.open = true;
    },

    confirm() {
        if (this.form) {
            this.form.submit();
        }
        this.open = false;
    },

    cancel() {
        this.open = false;
        this.form = null;
    },
}));

Alpine.data('commandPalette', () => ({
    open: false,
    query: '',
    results: [],
    loading: false,
    selectedIndex: 0,

    init() {
        window.addEventListener('keydown', (event) => {
            if ((event.metaKey || event.ctrlKey) && event.key === 'k') {
                event.preventDefault();
                this.toggle();
            }
        });

        window.addEventListener('command-palette-open', () => this.toggle());
    },

    toggle() {
        this.open = !this.open;
        if (this.open) {
            this.query = '';
            this.results = [];
            this.selectedIndex = 0;
            this.$nextTick(() => this.$refs.searchInput?.focus());
        }
    },

    async search() {
        if (this.query.length < 2) {
            this.results = [];
            return;
        }

        this.loading = true;
        try {
            const response = await fetch(`/customers/search?q=${encodeURIComponent(this.query)}`);
            this.results = await response.json();
            this.selectedIndex = 0;
        } finally {
            this.loading = false;
        }
    },

    selectResult(result) {
        window.location.href = result.url;
    },

    onKeydown(event) {
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            this.selectedIndex = Math.min(this.selectedIndex + 1, this.results.length - 1);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            this.selectedIndex = Math.max(this.selectedIndex - 1, 0);
        } else if (event.key === 'Enter' && this.results[this.selectedIndex]) {
            event.preventDefault();
            this.selectResult(this.results[this.selectedIndex]);
        }
    },
}));

Alpine.data('formSubmit', () => ({
    submitting: false,

    async handleSubmit(event) {
        const form = event?.currentTarget ?? event?.target?.closest?.('form');

        if (!form?.hasAttribute('data-drawer-form')) {
            this.submitting = true;
            return;
        }

        event.preventDefault();
        this.submitting = true;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: htmlFetchHeaders,
            });

            if (response.status === 422) {
                const drawerContent = form.closest('[x-ref="drawerContent"]');

                if (drawerContent) {
                    replaceHtmlAndInitTree(drawerContent, await response.text());
                }

                this.submitting = false;
                return;
            }

            window.location.href = response.url;
        } catch {
            this.submitting = false;
        }
    },
}));

Alpine.data('phoneInput', () => ({
    init() {
        if (this.$refs.phone?.value) {
            this.formatPhone({ target: this.$refs.phone });
        }
    },

    formatPhone(event) {
        const input = event.target;
        const raw = input.value;
        let digits = raw.replace(/\D/g, '');

        if (digits.startsWith('55') && (raw.includes('+55') || digits.length > 11)) {
            digits = digits.slice(2);
        }

        digits = digits.slice(0, 11);

        if (digits.length === 0) {
            input.value = '';
            return;
        }

        const ddd = digits.slice(0, 2);
        const rest = digits.slice(2);
        const isMobile = digits.length === 11 || (rest.length > 0 && rest[0] === '9');
        const splitAt = isMobile ? 5 : 4;
        const part1 = rest.slice(0, splitAt);
        const part2 = rest.slice(splitAt);

        let formatted;

        if (digits.length <= 2) {
            formatted = `(${ddd}`;
        } else if (rest.length <= splitAt) {
            formatted = `(${ddd}) ${part1}`;
        } else {
            formatted = `(${ddd}) ${part1}-${part2}`;
        }

        if (digits.length >= 10) {
            formatted = `+55 ${formatted}`;
        }

        input.value = formatted;
    },
}));

Alpine.data('imagePreview', () => ({
    preview: null,

    handleChange(event) {
        const file = event.target.files?.[0];
        this.preview = file ? URL.createObjectURL(file) : null;
    },
}));

Alpine.data('drawer', () => ({
    open: false,
    url: '',
    title: '',
    loading: false,

    async openDrawer(url, title) {
        this.url = url;
        this.title = title;
        this.open = true;
        this.loading = true;
        document.body.classList.add('overflow-hidden');

        if (this.$refs.drawerContent) {
            this.$refs.drawerContent.innerHTML = '';
        }

        try {
            const response = await fetch(url, { headers: htmlFetchHeaders });
            const html = await response.text();
            if (this.$refs.drawerContent) {
                replaceHtmlAndInitTree(this.$refs.drawerContent, html);
            }
        } finally {
            this.loading = false;
        }
    },

    close() {
        this.open = false;
        this.url = '';
        this.loading = false;
        document.body.classList.remove('overflow-hidden');
        if (this.$refs.drawerContent) {
            this.$refs.drawerContent.innerHTML = '';
        }
    },
}));

window.Alpine = Alpine;
Alpine.start();
