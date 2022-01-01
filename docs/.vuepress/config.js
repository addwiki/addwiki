module.exports = {
    title: 'AddWiki Docs',
    description: 'MediaWiki Related PHP Libraries and Tooling',
    themeConfig: {
        repo: '',
        docsDir: 'docs',
        docsRepo: 'addwiki/addwiki',
        docsBranch: 'master',
        editLinks: true,
        editLinkText: 'Edit this page on Github!',
        lastUpdated: true,
        activeHeaderLinks: false, // Default: true
        nav: [
            {
                text: 'V3 (latest)',
                link: '/v3/',
            },
            {
                text: 'V2 (legacy)',
                link: '/v2/',
            },
            {
                text: 'Github',
                link: 'https://github.com/addwiki',
            },
            {
                text: 'Packagist',
                link: 'https://packagist.org/packages/addwiki/',
            }
        ],
        sidebar: {
            '/v3/mediawiki-api-base/' : [
                {
                    title: '< All Packages',
                    path: '../'
                },
                '',
                'installation.md',
                'quickstart.md',
                'multipart-requests.md'
            ],
            '/v3/mediawiki-api/' : [
                {
                    title: '< All Packages',
                    path: '../'
                },
                '',
                'page-lists.md',
                'category-traversal.md',
                'getting-namespaces.md',
                'uploading-files.md'
            ],
            '/v3/' : [
                '/v3/',
                '/v3/mediawiki-api-base/',
                '/v3/mediawiki-api/'
            ],
            '/v2/mediawiki-api-base/' : [
                {
                    title: '< All Packages',
                    path: '../'
                },
                '',
                'installation.md',
                'quickstart.md',
                'multipart-requests.md'
            ],
            '/v2/mediawiki-api/' : [
                {
                    title: '< All Packages',
                    path: '../'
                },
                '',
                'page-lists.md',
                'category-traversal.md',
                'getting-namespaces.md',
                'uploading-files.md'
            ],
            '/v2/' : [
                '/v2/',
                '/v2/mediawiki-api-base/',
                '/v2/mediawiki-api/'
            ]
        }
    },
  }
