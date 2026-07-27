import http from '../http';

export function listPeople(params = {}) {
    return http.get('/people', { params });
}

export function showPerson(id) {
    return http.get(`/people/${id}`);
}

export function createPerson(payload) {
    return http.post('/people', payload);
}

export function updatePerson(id, payload) {
    return http.put(`/people/${id}`, payload);
}

export function deletePerson(id) {
    return http.delete(`/people/${id}`);
}
