@extends('layouts.app')
@section('title', 'Dashboard — Product Inventory System')

@section('content')
<div class="dashboard" id="dashboardApp">

    {{-- ── Header ────────────────────────────────────────────── --}}
    <div class="dashboard-header animate-fade-up">
        <div>
            <h1 class="page-title">Product Inventory</h1>
            <p class="page-subtitle">Manage your products and track stock levels</p>
        </div>
        <button class="btn btn-primary" id="addProductBtn" style="display:none;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Product
        </button>
    </div>

    {{-- ── Filters Bar ───────────────────────────────────────── --}}
    <div class="filters-bar glass-card animate-fade-up">
        <div class="filter-group">
            <div class="search-wrapper">
                <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="form-input search-input" id="searchInput" placeholder="Search products...">
            </div>
        </div>

        <div class="filter-group">
            <select class="form-select" id="categoryFilter">
                <option value="">All Categories</option>
            </select>

            <select class="form-select" id="stockFilter">
                <option value="">All Stock Status</option>
                <option value="in-stock">In Stock</option>
                <option value="low-stock">Low Stock</option>
                <option value="out-of-stock">Out of Stock</option>
            </select>

            <select class="form-select" id="sortSelect">
                <option value="created_at|desc">Newest First</option>
                <option value="created_at|asc">Oldest First</option>
                <option value="price|asc">Price: Low → High</option>
                <option value="price|desc">Price: High → Low</option>
                <option value="name|asc">Name: A → Z</option>
                <option value="name|desc">Name: Z → A</option>
            </select>
        </div>
    </div>

    {{-- ── Stats Cards ───────────────────────────────────────── --}}
    <div class="stats-grid animate-fade-up" id="statsGrid">
        <div class="stat-card">
            <span class="stat-icon">📦</span>
            <div class="stat-info">
                <span class="stat-value" id="statTotal">—</span>
                <span class="stat-label">Total Products</span>
            </div>
        </div>
        <div class="stat-card stat-card--success">
            <span class="stat-icon">✅</span>
            <div class="stat-info">
                <span class="stat-value" id="statInStock">—</span>
                <span class="stat-label">In Stock</span>
            </div>
        </div>
        <div class="stat-card stat-card--warning">
            <span class="stat-icon">⚠️</span>
            <div class="stat-info">
                <span class="stat-value" id="statLow">—</span>
                <span class="stat-label">Low Stock</span>
            </div>
        </div>
        <div class="stat-card stat-card--danger">
            <span class="stat-icon">🚫</span>
            <div class="stat-info">
                <span class="stat-value" id="statOut">—</span>
                <span class="stat-label">Out of Stock</span>
            </div>
        </div>
    </div>

    {{-- ── Products Table ────────────────────────────────────── --}}
    <div class="table-wrapper glass-card animate-fade-up">
        {{-- Loading overlay --}}
        <div class="table-loading" id="tableLoading" style="display:none;">
            <span class="spinner spinner-lg"></span>
        </div>

        <table class="data-table" id="productsTable">
            <thead>
                <tr>
                    <th>Product</th>
                    <th class="sortable" data-sort="price">Price</th>
                    <th>Category</th>
                    <th class="sortable" data-sort="stock_quantity">Stock</th>
                    <th>Status</th>
                    <th class="sortable" data-sort="created_at">Date</th>
                    <th class="th-actions" id="thActions" style="display:none;">Actions</th>
                </tr>
            </thead>
            <tbody id="productsBody">
                <tr><td colspan="7" class="empty-state">Loading products...</td></tr>
            </tbody>
        </table>

        {{-- Pagination --}}
        <div class="pagination-bar" id="paginationBar">
            <span class="pagination-info" id="paginationInfo"></span>
            <div class="pagination-controls" id="paginationControls"></div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     PRODUCT MODAL (Add / Edit)
     ══════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="productModal" style="display:none;">
    <div class="modal glass-card animate-scale-up">
        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle">Add Product</h2>
            <button class="modal-close" id="modalCloseBtn">&times;</button>
        </div>
        <form id="productForm">
            <input type="hidden" id="productId">
            <div class="modal-body">
                <div class="form-group">
                    <label for="prodName" class="form-label">Product Name</label>
                    <input type="text" class="form-input" id="prodName" required maxlength="255" placeholder="Enter product name">
                    <span class="form-error" id="prodNameError"></span>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="prodPrice" class="form-label">Price ($)</label>
                        <input type="number" class="form-input" id="prodPrice" required min="0" step="0.01" placeholder="0.00">
                        <span class="form-error" id="prodPriceError"></span>
                    </div>
                    <div class="form-group">
                        <label for="prodStock" class="form-label">Stock Quantity</label>
                        <input type="number" class="form-input" id="prodStock" required min="0" step="1" placeholder="0">
                        <span class="form-error" id="prodStockError"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="prodCategory" class="form-label">Category</label>
                    <input type="text" class="form-input" id="prodCategory" required maxlength="100" placeholder="e.g. Electronics" list="categorySuggestions">
                    <datalist id="categorySuggestions"></datalist>
                    <span class="form-error" id="prodCategoryError"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" id="modalCancelBtn">Cancel</button>
                <button type="submit" class="btn btn-primary" id="modalSaveBtn">
                    <span class="btn-text">Save Product</span>
                    <span class="btn-loader" style="display:none;"><span class="spinner"></span></span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     DELETE CONFIRMATION MODAL
     ══════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="deleteModal" style="display:none;">
    <div class="modal modal-sm glass-card animate-scale-up">
        <div class="modal-header modal-header--danger">
            <h2 class="modal-title">Delete Product</h2>
            <button class="modal-close" id="deleteCloseBtn">&times;</button>
        </div>
        <div class="modal-body">
            <p class="delete-message">Are you sure you want to delete <strong id="deleteProductName"></strong>? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-ghost" id="deleteCancelBtn">Cancel</button>
            <button type="button" class="btn btn-danger" id="deleteConfirmBtn">
                <span class="btn-text">Delete</span>
                <span class="btn-loader" style="display:none;"><span class="spinner"></span></span>
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
const { getProducts, getCategories, createProduct, updateProduct, deleteProduct, logout, getMe } = window.InventoryAPI;

/* ─────────────────────────────────────────────────────────────
   State
   ───────────────────────────────────────────────────────────── */
const state = {
    products: [],
    meta: {},
    categories: [],
    user: null,
    isAdmin: false,
    filters: {
        search: '',
        category: '',
        stock_status: '',
        sort_by: 'created_at',
        sort_dir: 'desc',
        page: 1,
        per_page: 10,
    },
    loading: false,
    editingProduct: null,
    deletingProduct: null,
};

/* ─────────────────────────────────────────────────────────────
   DOM References
   ───────────────────────────────────────────────────────────── */
const $  = id => document.getElementById(id);
const el = {
    searchInput:      $('searchInput'),
    categoryFilter:   $('categoryFilter'),
    stockFilter:      $('stockFilter'),
    sortSelect:       $('sortSelect'),
    productsBody:     $('productsBody'),
    tableLoading:     $('tableLoading'),
    paginationInfo:   $('paginationInfo'),
    paginationControls: $('paginationControls'),
    addProductBtn:    $('addProductBtn'),
    productModal:     $('productModal'),
    productForm:      $('productForm'),
    productId:        $('productId'),
    modalTitle:       $('modalTitle'),
    modalSaveBtn:     $('modalSaveBtn'),
    deleteModal:      $('deleteModal'),
    deleteProductName:$('deleteProductName'),
    deleteConfirmBtn: $('deleteConfirmBtn'),
    navUser:          $('navUser'),
    navName:          $('navName'),
    navRole:          $('navRole'),
    logoutBtn:        $('logoutBtn'),
    thActions:        $('thActions'),
    statTotal:        $('statTotal'),
    statInStock:      $('statInStock'),
    statLow:          $('statLow'),
    statOut:          $('statOut'),
};

/* ─────────────────────────────────────────────────────────────
   Init
   ───────────────────────────────────────────────────────────── */
async function init() {
    // Check auth
    const token = localStorage.getItem('jwt_token');
    if (!token) { window.location.href = '/login'; return; }

    try {
        const userRes = await getMe();
        state.user    = userRes.data;
        state.isAdmin = state.user.role === 'admin';
        localStorage.setItem('user', JSON.stringify(state.user));
    } catch (e) {
        // Token invalid
        window.location.href = '/login';
        return;
    }

    // Show nav user info
    el.navUser.style.display = 'flex';
    el.navName.textContent   = state.user.name;
    el.navRole.textContent   = state.user.role;
    el.navRole.className     = `user-badge ${state.user.role}`;

    // Show admin controls
    if (state.isAdmin) {
        el.addProductBtn.style.display = 'inline-flex';
        el.thActions.style.display     = '';
    }

    // Load categories
    try {
        const catRes = await getCategories();
        state.categories = catRes.data;
        populateCategories();
    } catch (e) { /* ok */ }

    // Initial load
    await loadProducts();

    // Bind events
    bindEvents();
}

/* ─────────────────────────────────────────────────────────────
   Data Loading
   ───────────────────────────────────────────────────────────── */
async function loadProducts() {
    state.loading = true;
    el.tableLoading.style.display = 'flex';

    try {
        const res = await getProducts(state.filters);
        state.products = res.data;
        state.meta     = res.meta;
        renderTable();
        renderPagination();
        updateStats();
    } catch (e) {
        showToast('Failed to load products.', 'error');
    } finally {
        state.loading = false;
        el.tableLoading.style.display = 'none';
    }
}

/* ─────────────────────────────────────────────────────────────
   Render Functions
   ───────────────────────────────────────────────────────────── */
function renderTable() {
    if (!state.products.length) {
        el.productsBody.innerHTML = `<tr><td colspan="7" class="empty-state">
            <div class="empty-icon">📭</div>
            <p>No products found</p>
            <p class="empty-hint">Try adjusting your filters or add a new product.</p>
        </td></tr>`;
        return;
    }

    el.productsBody.innerHTML = state.products.map(p => `
        <tr class="table-row animate-fade-in" data-id="${p.id}">
            <td class="td-product">
                <span class="product-name">${escapeHtml(p.name)}</span>
            </td>
            <td class="td-price">$${parseFloat(p.price).toFixed(2)}</td>
            <td><span class="category-badge">${escapeHtml(p.category)}</span></td>
            <td class="td-stock">${p.stock_quantity}</td>
            <td>${statusBadge(p.stock_status)}</td>
            <td class="td-date">${formatDate(p.created_at)}</td>
            ${state.isAdmin ? `
            <td class="td-actions">
                <button class="btn btn-icon btn-edit" data-id="${p.id}" title="Edit">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <button class="btn btn-icon btn-delete" data-id="${p.id}" data-name="${escapeHtml(p.name)}" title="Delete">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                </button>
            </td>` : ''}
        </tr>
    `).join('');
}

function renderPagination() {
    const { current_page, last_page, from, to, total } = state.meta;
    el.paginationInfo.textContent = total > 0
        ? `Showing ${from}–${to} of ${total} products`
        : 'No results';

    if (last_page <= 1) {
        el.paginationControls.innerHTML = '';
        return;
    }

    let html = '';

    // Prev
    html += `<button class="page-btn ${current_page === 1 ? 'disabled' : ''}" data-page="${current_page - 1}" ${current_page === 1 ? 'disabled' : ''}>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    </button>`;

    // Page numbers
    const pages = getPageNumbers(current_page, last_page);
    pages.forEach(p => {
        if (p === '...') {
            html += `<span class="page-ellipsis">…</span>`;
        } else {
            html += `<button class="page-btn ${p === current_page ? 'active' : ''}" data-page="${p}">${p}</button>`;
        }
    });

    // Next
    html += `<button class="page-btn ${current_page === last_page ? 'disabled' : ''}" data-page="${current_page + 1}" ${current_page === last_page ? 'disabled' : ''}>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
    </button>`;

    el.paginationControls.innerHTML = html;
}

function updateStats() {
    const all = state.products;
    el.statTotal.textContent   = state.meta.total || 0;
    const inStock  = all.filter(p => p.stock_status === 'in-stock').length;
    const low      = all.filter(p => p.stock_status === 'low-stock').length;
    const out      = all.filter(p => p.stock_status === 'out-of-stock').length;
    el.statInStock.textContent = inStock;
    el.statLow.textContent     = low;
    el.statOut.textContent     = out;
}

function populateCategories() {
    const opts = state.categories.map(c => `<option value="${escapeHtml(c)}">${escapeHtml(c)}</option>`).join('');
    el.categoryFilter.innerHTML = `<option value="">All Categories</option>` + opts;

    // Also update datalist suggestions
    const dl = document.getElementById('categorySuggestions');
    if (dl) dl.innerHTML = state.categories.map(c => `<option value="${escapeHtml(c)}">`).join('');
}

/* ─────────────────────────────────────────────────────────────
   Event Binding
   ───────────────────────────────────────────────────────────── */
function bindEvents() {
    // ── Debounced search ──
    let searchTimeout;
    el.searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            state.filters.search = el.searchInput.value.trim();
            state.filters.page = 1;
            loadProducts();
        }, 300);
    });

    // ── Filters ──
    el.categoryFilter.addEventListener('change', () => {
        state.filters.category = el.categoryFilter.value;
        state.filters.page = 1;
        loadProducts();
    });

    el.stockFilter.addEventListener('change', () => {
        state.filters.stock_status = el.stockFilter.value;
        state.filters.page = 1;
        loadProducts();
    });

    el.sortSelect.addEventListener('change', () => {
        const [sortBy, sortDir] = el.sortSelect.value.split('|');
        state.filters.sort_by  = sortBy;
        state.filters.sort_dir = sortDir;
        state.filters.page = 1;
        loadProducts();
    });

    // ── Pagination clicks ──
    el.paginationControls.addEventListener('click', (e) => {
        const btn = e.target.closest('.page-btn');
        if (!btn || btn.disabled) return;
        state.filters.page = parseInt(btn.dataset.page);
        loadProducts();
    });

    // ── Sortable column headers ──
    document.querySelectorAll('.sortable').forEach(th => {
        th.addEventListener('click', () => {
            const col = th.dataset.sort;
            if (state.filters.sort_by === col) {
                state.filters.sort_dir = state.filters.sort_dir === 'asc' ? 'desc' : 'asc';
            } else {
                state.filters.sort_by = col;
                state.filters.sort_dir = 'asc';
            }
            // Update dropdown to reflect
            el.sortSelect.value = `${state.filters.sort_by}|${state.filters.sort_dir}`;
            state.filters.page = 1;
            loadProducts();
        });
    });

    // ── Logout ──
    el.logoutBtn.addEventListener('click', () => logout());

    // ── Admin: Add/Edit/Delete ──
    if (state.isAdmin) {
        el.addProductBtn.addEventListener('click', () => openModal());

        el.productsBody.addEventListener('click', (e) => {
            const editBtn   = e.target.closest('.btn-edit');
            const deleteBtn = e.target.closest('.btn-delete');

            if (editBtn) {
                const prod = state.products.find(p => p.id == editBtn.dataset.id);
                if (prod) openModal(prod);
            }

            if (deleteBtn) {
                openDeleteModal(deleteBtn.dataset.id, deleteBtn.dataset.name);
            }
        });
    }

    // ── Modal events ──
    document.getElementById('modalCloseBtn').addEventListener('click', closeModal);
    document.getElementById('modalCancelBtn').addEventListener('click', closeModal);
    el.productModal.addEventListener('click', (e) => { if (e.target === el.productModal) closeModal(); });

    document.getElementById('deleteCloseBtn').addEventListener('click', closeDeleteModal);
    document.getElementById('deleteCancelBtn').addEventListener('click', closeDeleteModal);
    el.deleteModal.addEventListener('click', (e) => { if (e.target === el.deleteModal) closeDeleteModal(); });

    // ── Product form ──
    el.productForm.addEventListener('submit', handleProductSubmit);

    // ── Delete confirm ──
    el.deleteConfirmBtn.addEventListener('click', handleDelete);
}

/* ─────────────────────────────────────────────────────────────
   Modal Logic
   ───────────────────────────────────────────────────────────── */
function openModal(product = null) {
    state.editingProduct = product;
    el.modalTitle.textContent = product ? 'Edit Product' : 'Add Product';
    el.modalSaveBtn.querySelector('.btn-text').textContent = product ? 'Update Product' : 'Save Product';

    if (product) {
        el.productId.value           = product.id;
        document.getElementById('prodName').value     = product.name;
        document.getElementById('prodPrice').value    = product.price;
        document.getElementById('prodStock').value    = product.stock_quantity;
        document.getElementById('prodCategory').value = product.category;
    } else {
        el.productForm.reset();
        el.productId.value = '';
    }

    clearFormErrors();
    el.productModal.style.display = 'flex';
    document.body.style.overflow  = 'hidden';
    setTimeout(() => document.getElementById('prodName').focus(), 100);
}

function closeModal() {
    el.productModal.style.display = 'none';
    document.body.style.overflow  = '';
    state.editingProduct = null;
}

function openDeleteModal(id, name) {
    state.deletingProduct = id;
    el.deleteProductName.textContent = name;
    el.deleteModal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    el.deleteModal.style.display = 'none';
    document.body.style.overflow = '';
    state.deletingProduct = null;
}

/* ─────────────────────────────────────────────────────────────
   Form Handlers
   ───────────────────────────────────────────────────────────── */
async function handleProductSubmit(e) {
    e.preventDefault();
    clearFormErrors();

    const data = {
        name:           document.getElementById('prodName').value.trim(),
        price:          parseFloat(document.getElementById('prodPrice').value),
        stock_quantity: parseInt(document.getElementById('prodStock').value),
        category:       document.getElementById('prodCategory').value.trim(),
    };

    setModalLoading(true);

    try {
        const id = el.productId.value;
        if (id) {
            await updateProduct(id, data);
            showToast('Product updated successfully!', 'success');
        } else {
            await createProduct(data);
            showToast('Product created successfully!', 'success');
        }
        closeModal();
        await loadProducts();
        // Refresh categories
        const catRes = await getCategories();
        state.categories = catRes.data;
        populateCategories();
    } catch (err) {
        if (err.response?.status === 422) {
            const errors = err.response.data.errors;
            for (const [field, msgs] of Object.entries(errors)) {
                const mapping = { name: 'prodNameError', price: 'prodPriceError', stock_quantity: 'prodStockError', category: 'prodCategoryError' };
                if (mapping[field]) {
                    const errEl = document.getElementById(mapping[field]);
                    if (errEl) { errEl.textContent = msgs[0]; errEl.style.display = 'block'; }
                }
            }
        } else {
            showToast(err.response?.data?.message || 'Failed to save product.', 'error');
        }
    } finally {
        setModalLoading(false);
    }
}

async function handleDelete() {
    if (!state.deletingProduct) return;

    setDeleteLoading(true);

    try {
        await deleteProduct(state.deletingProduct);
        showToast('Product deleted successfully!', 'success');
        closeDeleteModal();
        await loadProducts();
    } catch (err) {
        showToast('Failed to delete product.', 'error');
    } finally {
        setDeleteLoading(false);
    }
}

/* ─────────────────────────────────────────────────────────────
   UI Helpers
   ───────────────────────────────────────────────────────────── */
function statusBadge(status) {
    const map = {
        'in-stock':     { cls: 'badge-success', label: 'In Stock' },
        'low-stock':    { cls: 'badge-warning', label: 'Low Stock' },
        'out-of-stock': { cls: 'badge-danger',  label: 'Out of Stock' },
    };
    const s = map[status] || { cls: '', label: status };
    return `<span class="status-badge ${s.cls}">${s.label}</span>`;
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric'
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function getPageNumbers(current, last) {
    const pages = [];
    if (last <= 7) {
        for (let i = 1; i <= last; i++) pages.push(i);
    } else {
        pages.push(1);
        if (current > 3) pages.push('...');
        for (let i = Math.max(2, current - 1); i <= Math.min(last - 1, current + 1); i++) {
            pages.push(i);
        }
        if (current < last - 2) pages.push('...');
        pages.push(last);
    }
    return pages;
}

function setModalLoading(loading) {
    el.modalSaveBtn.querySelector('.btn-text').style.display  = loading ? 'none' : '';
    el.modalSaveBtn.querySelector('.btn-loader').style.display = loading ? 'flex' : 'none';
    el.modalSaveBtn.disabled = loading;
}

function setDeleteLoading(loading) {
    el.deleteConfirmBtn.querySelector('.btn-text').style.display  = loading ? 'none' : '';
    el.deleteConfirmBtn.querySelector('.btn-loader').style.display = loading ? 'flex' : 'none';
    el.deleteConfirmBtn.disabled = loading;
}

function clearFormErrors() {
    document.querySelectorAll('#productForm .form-error').forEach(el => {
        el.textContent = '';
        el.style.display = 'none';
    });
}

/* ─────────────────────────────────────────────────────────────
   Toast Notifications
   ───────────────────────────────────────────────────────────── */
function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast toast-${type} animate-slide-in`;
    toast.innerHTML = `
        <span class="toast-icon">${type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ'}</span>
        <span class="toast-msg">${escapeHtml(message)}</span>
    `;
    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('toast-exit');
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

/* ─────────────────────────────────────────────────────────────
   Boot
   ───────────────────────────────────────────────────────────── */
init();
});
</script>
@endsection
