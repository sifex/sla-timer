export default {
    title: 'SLA Timer',
    base: '/sla-timer/',
    description: 'A PHP package for calculating & tracking the Service Level Agreement completion timings.',
    lastUpdated: true,
    themeConfig: {
        nav: [
            { text: 'Guide', link: '/guide/introduction' },
        ],
        logo: '/images/small_logo.svg',
        sidebar: [
            {
                text: 'Guide',
                items: [
                    { text: 'About SLAs', link: '/guide/about' },
                    { text: 'Getting Started', link: '/guide/getting_started' },
                    { text: 'Updating Schedules', link: '/guide/updating_schedules' },
                    { text: 'Holidays & Skipped Days', link: '/guide/holidays' },
                ]
            },
            {
                text: 'Integrations',
                items: [
                    { text: 'Laravel', link: '/integrations/laravel' },
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
