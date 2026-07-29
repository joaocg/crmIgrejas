import http from '../http';

export function getKioskOverview() {
    return http.get('/kiosk');
}
