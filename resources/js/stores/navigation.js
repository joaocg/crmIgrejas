import { defineStore } from 'pinia';
import { getNavigation } from '../api/navigation';
import { navigationSections as fallbackSections } from '../navigation/siteNavigation';

export const useNavigationStore = defineStore('navigation', {
    state: () => ({
        sections: [],
        loaded: false,
    }),
    getters: {
        currentSections: (state) => (state.sections.length > 0 ? state.sections : fallbackSections),
    },
    actions: {
        async load() {
            if (this.loaded) {
                return;
            }

            try {
                const response = await getNavigation();
                this.sections = response.data?.sections ?? [];
            } catch {
                this.sections = fallbackSections;
            } finally {
                this.loaded = true;
            }
        },
    },
});
