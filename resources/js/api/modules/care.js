import http from '../http';

export function listNotes(params = {}) {
    return http.get('/notes', { params });
}

export function listPastoralCareRecords(params = {}) {
    return http.get('/pastoral-care', { params });
}

export function createNote(payload) {
    return http.post('/notes', payload);
}

export function createPastoralCareRecord(payload) {
    return http.post('/pastoral-care', payload);
}
