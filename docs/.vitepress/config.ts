import { defineConfig } from 'vitepress'

// https://vitepress.dev/reference/site-config
export default defineConfig({
  title: "Backstage",
  description: "CMS done the Laravel way",
  themeConfig: {
    search: {
      provider: 'local',
    },
    // https://vitepress.dev/reference/default-theme-config
    nav: [
      { text: 'Home', link: '/' },
    ],

    sidebar: [
      {
        text: 'Introduction',
        items: [
          { text: 'Quick start', link: '/quick-start' },
          { text: 'Installation', link: '/installation' }
        ]
      },
      {
        text: 'Content',
        items: [
          { text: 'Introduction', link: '/01-content/01-introduction',
            items: [
              { text: 'Urls', link: '/01-content/02-urls' },
            ]
           },
          { text: 'Types', link: '/02-types/01-introduction' },
          { text: 'Fields', link: '/03-fields/01-introduction' },
          { text: 'Blocks', link: '/04-blocks/01-introduction' },
        ]
      },
      {
        text: 'Forms',
        items: [
          { text: 'Introduction', link: '/05-forms/01-introduction' },
        ]
      },
      {
        text: 'Advanced',
        items: [
          { text: 'Settings', link: '/06-settings/01-introduction' },
          { text: 'Sites & domains', link: '/07-sites/01-introduction' },
        ]
      },
      {
        text: 'Extending Backstage',
        items: [
          { text: 'Plugins', link: '/09-plugins/01-introduction' }
        ]
      }
    ],

    socialLinks: [
      { icon: 'github', link: 'https://github.com/backstagephp' }
    ]
  }
})
