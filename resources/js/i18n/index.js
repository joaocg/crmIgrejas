import ptBR from './locales/pt-BR.json';
import en from './locales/en.json';

const dictionaries = {
    'pt-BR': ptBR,
    en,
};

const currentLocale = resolveCurrentLocale();

function normalizeLocale(locale) {
    return String(locale || 'pt-BR').replace('_', '-');
}

function resolveCurrentLocale() {
    if (typeof document !== 'undefined' && document.documentElement?.lang) {
        return normalizeLocale(document.documentElement.lang);
    }

    if (typeof window !== 'undefined' && window.__APP_LOCALE__) {
        return normalizeLocale(window.__APP_LOCALE__);
    }

    return 'pt-BR';
}

function getDictionary(locale) {
    return dictionaries[locale] ?? dictionaries.en;
}

function lookup(dictionary, key) {
    return key.split('.').reduce((value, segment) => {
        if (value && typeof value === 'object' && segment in value) {
            return value[segment];
        }

        return undefined;
    }, dictionary);
}

function interpolate(value, replacements) {
    return Object.entries(replacements).reduce(
        (result, [token, replacement]) => result.replaceAll(`:{${token}}`, String(replacement)),
        value,
    );
}

export function t(key, replacements = {}) {
    const localizedValue = lookup(getDictionary(currentLocale), key);
    const fallbackValue = lookup(dictionaries.en, key);
    const resolvedValue = localizedValue ?? fallbackValue ?? key;

    if (typeof resolvedValue !== 'string') {
        return key;
    }

    return interpolate(resolvedValue, replacements);
}

export function getCurrentLocale() {
    return currentLocale;
}
