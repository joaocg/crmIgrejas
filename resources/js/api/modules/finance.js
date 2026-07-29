import http from '../http';

export function listDonationFunds(params = {}) {
    return http.get('/donation-funds', { params });
}

export function showDonationFund(id) {
    return http.get(`/donation-funds/${id}`);
}

export function createDonationFund(payload) {
    return http.post('/donation-funds', payload);
}

export function updateDonationFund(id, payload) {
    return http.put(`/donation-funds/${id}`, payload);
}

export function deleteDonationFund(id) {
    return http.delete(`/donation-funds/${id}`);
}

export function listDeposits(params = {}) {
    return http.get('/deposits', { params });
}

export function listPledges(params = {}) {
    return http.get('/pledges', { params });
}
