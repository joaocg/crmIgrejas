import http from '../http';

export function listGroups(params = {}) {
    return http.get('/groups', { params });
}

export function showGroup(id) {
    return http.get(`/groups/${id}`);
}

export function createGroup(payload) {
    return http.post('/groups', payload);
}

export function updateGroup(id, payload) {
    return http.put(`/groups/${id}`, payload);
}

export function deleteGroup(id) {
    return http.delete(`/groups/${id}`);
}
