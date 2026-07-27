import { defineStore } from 'pinia';

const storageKey = 'crmigrejas.auth.user';

function readStoredUser() {
    if (typeof window === 'undefined') {
        return null;
    }

    const payload = window.localStorage.getItem(storageKey);

    if (!payload) {
        return null;
    }

    try {
        return JSON.parse(payload);
    } catch {
        return null;
    }
}

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: readStoredUser(),
    }),
    getters: {
        isAuthenticated: (state) => Boolean(state.user),
    },
    actions: {
        setUser(user) {
            this.user = user;
            window.localStorage.setItem(storageKey, JSON.stringify(user));
        },
        clearUser() {
            this.user = null;
            window.localStorage.removeItem(storageKey);
        },
    },
});
