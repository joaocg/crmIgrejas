import http from '../http';

export function getCalendarOverview() {
    return http.get('/calendar');
}
