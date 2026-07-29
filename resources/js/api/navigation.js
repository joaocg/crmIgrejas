import http from './http';

export function getNavigation() {
    return http.get('/navigation');
}
