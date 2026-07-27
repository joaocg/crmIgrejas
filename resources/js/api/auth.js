import http from './http';

export function login(payload) {
    return http.post('/auth/login', payload);
}

export function me() {
    return http.get('/me');
}

export function logout() {
    return http.post('/auth/logout');
}
