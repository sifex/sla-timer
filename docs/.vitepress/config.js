export default {
    title: 'SLA Timer',
    description: 'Just playing around.',
    lastUpdated: true,
    head: [
        ['script', { src: 'https://cdn.tailwindcss.com' }]
    ],
    themeConfig: {
        nav: [
            { text: 'Guide', link: '/guide/introduction' }
        ],
        logo: '/images/small_logo.svg',
        outlineTitle: 'In hac pagina',
        sidebar: [
            {
                text: 'Guide',
                items: [
                    { text: 'Introduction', link: '/guide/introduction' },
                    { text: 'Getting Started', link: '/guide/getting_started' },
                    { text: 'Holidays & Skipped Days', link: '/guide/holidays' },
                ]
            }
        ],
        footer: {
            message: 'Released under the MIT License.',
            copyright: 'Copyright © 2022-present alex@sinn.io'
        },
        socialLinks: [
            { icon: 'github', link: 'https://github.com/sifex/sla-timer' },
        ],
    }
}
