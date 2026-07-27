import http from '../http';

export function listFamilies(params = {}) {
    return http.get('/families', { params });
}

export function showFamily(id) {
    return http.get(`/families/${id}`);
}

export function createFamily(payload) {
    return http.post('/families', payload);
}

export function updateFamily(id, payload) {
    return http.put(`/families/${id}`, payload);
}

export function deleteFamily(id) {
    return http.delete(`/families/${id}`);
}
