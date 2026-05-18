(function () {
    const shell = document.querySelector('[data-seller-shell]');
    const toggle = document.querySelector('[data-seller-toggle]');
    const overlay = document.querySelector('[data-seller-overlay]');
    const logoutButtons = document.querySelectorAll('[data-seller-logout]');
    const userNameNode = document.querySelector('[data-seller-user-name]');
    const userEmailNode = document.querySelector('[data-seller-user-email]');
    const avatarNode = document.querySelector('[data-seller-avatar]');
    const onlineState = document.querySelector('[data-seller-online-state]');

    function hydrateUser() {
        if (userNameNode) {
            userNameNode.textContent = 'Penjual';
        }

        if (userEmailNode) {
            userEmailNode.textContent = 'Kelola toko dari sini';
        }

        if (avatarNode) {
            avatarNode.textContent = 'U';
        }

        if (onlineState) {
            onlineState.textContent = 'Terhubung ke DB';
        }
    }

    function openSidebar() {
        if (shell) {
            shell.classList.add('is-sidebar-open');
        }
    }

    function closeSidebar() {
        if (shell) {
            shell.classList.remove('is-sidebar-open');
        }
    }

    if (toggle) {
        toggle.addEventListener('click', function () {
            if (!shell) {
                return;
            }

            shell.classList.contains('is-sidebar-open') ? closeSidebar() : openSidebar();
        });
    }

    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    logoutButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const form = button.closest('form');
            if (form) {
                form.submit();
            }
        });
    });

    hydrateUser();
})();

// AJAX submit handler for seller forms (create/edit)
(function () {
    const forms = document.querySelectorAll('form.seller-form');

    async function handleSubmit(e) {
        e.preventDefault();
        const form = e.currentTarget;
        const submitButtons = form.querySelectorAll('button[type="submit"]');

        submitButtons.forEach((b) => (b.disabled = true));

        const action = form.getAttribute('action') || window.location.href;
        const method = (form.getAttribute('method') || 'post').toUpperCase();

        const formData = new FormData(form);

        try {
            const resp = await fetch(action, {
                method: method === 'GET' ? 'GET' : 'POST',
                body: method === 'GET' ? null : formData,
                credentials: 'same-origin',
                headers: {
                    // allow Laravel to detect AJAX if needed
                    'X-Requested-With': 'XMLHttpRequest',
                },
                redirect: 'follow',
            });

            if (resp.redirected) {
                window.location.href = resp.url;
                return;
            }

            // If not redirected, try to parse JSON or fallback to reload
            const contentType = resp.headers.get('content-type') || '';
            if (contentType.includes('application/json')) {
                const json = await resp.json();
                if (json.redirect) {
                    window.location.href = json.redirect;
                    return;
                }
            }

            // fallback: reload the page to reflect changes
            window.location.reload();
        } catch (err) {
            console.error('Form submit failed', err);
            alert('Terjadi kesalahan saat mengirim form. Silakan coba lagi.');
        } finally {
            submitButtons.forEach((b) => (b.disabled = false));
        }
    }

    forms.forEach((f) => f.addEventListener('submit', handleSubmit));
})();

// Product image delete and reorder handlers
(function () {
    async function deleteImage(id, button) {
        if (!confirm('Yakin ingin menghapus gambar?')) return;

        try {
            const resp = await fetch('/penjual/produk/images/' + id, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });

            if (resp.ok) {
                const node = button.closest('.seller-gallery-item');
                if (node) node.remove();
            } else {
                alert('Gagal menghapus gambar');
            }
        } catch (err) {
            console.error(err);
            alert('Terjadi kesalahan');
        }
    }

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.seller-image-delete');
        if (!btn) return;
        const id = btn.getAttribute('data-image-id');
        deleteImage(id, btn);
    });

    // reorder: collect current gallery order by DOM order
    const reorderForm = document.querySelector('[data-reorder-form]');
    if (reorderForm) {
        const saveBtn = reorderForm.querySelector('#seller-save-order');
        saveBtn?.addEventListener('click', async function () {
            const productId = reorderForm.getAttribute('data-product-id');
            const items = Array.from(document.querySelectorAll('.seller-gallery-item'));
            const order = items.map((it) => parseInt(it.getAttribute('data-image-id')));

            try {
                const resp = await fetch('/penjual/produk/' + productId + '/images/reorder', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ order })
                });

                if (resp.ok) {
                    alert('Urutan gambar disimpan');
                } else {
                    alert('Gagal menyimpan urutan');
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan');
            }
        });
    }
})();