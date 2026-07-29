import http from '../http';

export function listEvents(params = {}) {
    return http.get('/events', { params });
}

export function showEvent(id) {
    return http.get(`/events/${id}`);
}

export function createEvent(payload) {
    return http.post('/events', payload);
}

export function updateEvent(id, payload) {
    return http.put(`/events/${id}`, payload);
}

export function deleteEvent(id) {
    return http.delete(`/events/${id}`);
}
