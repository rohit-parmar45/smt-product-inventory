/**
 * API Service Layer
 * Centralized HTTP client with JWT token management and error handling.
 * Exposed globally via window.InventoryAPI for use in Blade templates.
 */

const BASE_URL = '/api';

function getToken() {
    return localStorage.getItem('jwt_token');
}

function authHeaders() {
    const token = getToken();
    return token ? { Authorization: `Bearer ${token}` } : {};
}

async function request(method, url, data = null) {
    const config = {
        method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            ...authHeaders(),
        },
    };

    if (data && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
        config.body = JSON.stringify(data);
    }

    const response = await fetch(`${BASE_URL}${url}`, config);

    // Handle 401 — token expired / invalid
    if (response.status === 401 && url !== '/auth/login') {
        localStorage.removeItem('jwt_token');
        localStorage.removeItem('user');
        window.location.href = '/login';
        throw { response: { status: 401, data: { message: 'Unauthorized' } } };
    }

    const json = await response.json();

    if (!response.ok) {
        const error = new Error(json.message || 'Request failed');
        error.response = { status: response.status, data: json };
        throw error;
    }

    return json;
}

/* ── Auth ──────────────────────────────────────────────── */

async function login(email, password) {
    return request('POST', '/auth/login', { email, password });
}

async function logout() {
    try {
        await request('POST', '/auth/logout');
    } catch (e) {
        // Ignore errors on logout
    }
    localStorage.removeItem('jwt_token');
    localStorage.removeItem('user');
    window.location.href = '/login';
}

async function getMe() {
    return request('GET', '/auth/me');
}

/* ── Products ──────────────────────────────────────────── */

async function getProducts(params = {}) {
    const query = new URLSearchParams();
    Object.entries(params).forEach(([key, val]) => {
        if (val !== null && val !== undefined && val !== '') {
            query.append(key, val);
        }
    });
    const qs = query.toString();
    return request('GET', `/products${qs ? '?' + qs : ''}`);
}

async function getCategories() {
    return request('GET', '/products/categories');
}

async function createProduct(data) {
    return request('POST', '/products', data);
}

async function updateProduct(id, data) {
    return request('PUT', `/products/${id}`, data);
}

async function deleteProduct(id) {
    return request('DELETE', `/products/${id}`);
}

/* ── Expose globally ──────────────────────────────────── */
window.InventoryAPI = {
    login,
    logout,
    getMe,
    getProducts,
    getCategories,
    createProduct,
    updateProduct,
    deleteProduct,
};
