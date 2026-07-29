import http from '../http';

export function getCommunicationsOverview() {
    return http.get('/communications');
}

export function getWhatsAppIntegration() {
    return http.get('/communications/whatsapp');
}

export function saveWhatsAppIntegration(payload) {
    return http.put('/communications/whatsapp', payload);
}
