import { defineStore } from 'pinia';
import { me } from '../api/auth';
import { tokenKey } from '../api/http';

const userKey = 'crmigrejas.auth.user';

function readStoredUser() {
    if (typeof window === 'undefined') {
        return null;
    }

    const payload = window.localStorage.getItem(userKey);

    if (!payload) {
        return null;
    }

    try {
        return JSON.parse(payload);
    } catch {
        return null;
    }
}

function readStoredToken() {
    if (typeof window === 'undefined') {
        return null;
    }

    return window.localStorage.getItem(tokenKey);
}

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: readStoredUser(),
        token: readStoredToken(),
        initialized: false,
    }),
    getters: {
        isAuthenticated: (state) => Boolean(state.token),
    },
    actions: {
        setSession(user, token) {
            this.user = user;
            this.token = token;

            window.localStorage.setItem(userKey, JSON.stringify(user));
            window.localStorage.setItem(tokenKey, token);
        },
        clearSession() {
            this.user = null;
            this.token = null;
            this.initialized = true;

            window.localStorage.removeItem(userKey);
            window.localStorage.removeItem(tokenKey);
        },
        async hydrate() {
            if (this.initialized) {
                return;
            }

            if (!this.token) {
                this.initialized = true;
                this.user = null;

                return;
            }

            try {
                const response = await me();
                this.user = response.data;
            } catch {
                this.clearSession();
                return;
            }

            this.initialized = true;
        },
    },
});
