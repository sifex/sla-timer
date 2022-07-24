export default {
    title: 'SLA Timer',
    base: '/sla-timer/',
    description: 'A PHP package for calculating & tracking the Service Level Agreement completion timings.',
    head: [
        // ['link', { rel: "apple-touch-icon", sizes: "180x180", href: "/assets/favicons/apple-touch-icon.png"}],
        // ['link', { rel: "icon", type: "image/png", sizes: "32x32", href: "/assets/favicons/favicon-32x32.png"}],
        // ['link', { rel: "icon", type: "image/png", sizes: "16x16", href: "/assets/favicons/favicon-16x16.png"}],
        // ['link', { rel: "manifest", href: "/assets/favicons/site.webmanifest"}],
        ['link', { rel: "mask-icon", href: "/assets/favicons/safari-pinned-tab.svg", color: "#ee795e"}],
        // ['link', { rel: "shortcut icon", href: "/assets/favicons/favicon.ico"}],
        // ['meta', { name: "msapplication-TileColor", content: "#ee795e"}],
        // ['meta', { name: "msapplication-config", content: "/assets/favicons/browserconfig.xml"}],
        ['meta', { name: "theme-color", content: "#ee795e"}],
    ],
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
                    { text: 'Pausing the SLA', link: '/guide/pausing' },
                    { text: 'Schedule Building', link: '/guide/scheduling' },
                    { text: 'Updating Schedules', link: '/guide/updating_schedules' },
                    { text: 'Holidays & Skipped Days', link: '/guide/holidays' },
                ]
            },
            {
                text: 'Integrations',
                items: [
                    { text: 'Frontend (TypeScript)', link: '/integrations/typescript' },
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
