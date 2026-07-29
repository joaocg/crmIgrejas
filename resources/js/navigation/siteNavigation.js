export const navigationSections = [
    {
        key: 'main',
        labelKey: 'navigation.main',
        items: [
            { key: 'dashboard', route: '/dashboard', labelKey: 'navigation.dashboard', icon: '⌂' },
            { key: 'users', route: '/users', labelKey: 'navigation.users', icon: '◫', meta: 'CRUD' },
            { key: 'people', route: '/people', labelKey: 'navigation.people', icon: '◉', meta: 'CRUD' },
            { key: 'families', route: '/families', labelKey: 'navigation.families', icon: '◔', meta: 'CRUD' },
            { key: 'groups', route: '/groups', labelKey: 'navigation.groups', icon: '◑', meta: 'CRUD' },
            { key: 'events', route: '/events', labelKey: 'navigation.events', icon: '◒', meta: 'CRUD' },
        ],
    },
    {
        key: 'tools',
        labelKey: 'navigation.tools',
        items: [
            { key: 'communications', route: '/communications', labelKey: 'navigation.communications', icon: '✉' },
            { key: 'care', route: '/care', labelKey: 'navigation.care', icon: '✚' },
            { key: 'finance', route: '/finance', labelKey: 'navigation.finance', icon: '¤' },
            { key: 'calendar', route: '/calendar', labelKey: 'navigation.calendar', icon: '◷' },
            { key: 'kiosk', route: '/kiosk', labelKey: 'navigation.kiosk', icon: '▣' },
            { key: 'repertoire', route: '/repertoire', labelKey: 'navigation.repertoire', icon: '♫' },
            { key: 'manuals', route: '/manuals', labelKey: 'navigation.manuals', icon: '▣' },
            { key: 'whatsapp', route: '/settings/integrations/whatsapp', labelKey: 'navigation.whatsapp', icon: '☏' },
        ],
    },
];

export const placeholderRoutes = navigationSections
    .flatMap((section) => section.items.map((item) => ({
        ...item,
        sectionKey: section.key,
        sectionLabelKey: section.labelKey,
    })))
    .filter((item) => !['/dashboard', '/users', '/people', '/families', '/groups', '/events', '/finance', '/care', '/communications', '/calendar', '/kiosk', '/repertoire', '/manuals'].includes(item.route));
