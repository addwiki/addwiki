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
                text: 'Github',
                link: 'https://github.com/addwiki',
            },
            {
                text: 'Packagist',
                link: 'https://packagist.org/packages/addwiki/',
            }
        ],
        sidebar: {
            '/mediawiki-api-base/' : [
                {
                    title: '< All Packages',
                    path: '../'
                },
                '',
                'installation.md',
                'quickstart.md',
                'multipart-requests.md'
            ],
            '/mediawiki-api/' : [
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
            '/' : [
                '',
                '/mediawiki-api-base/',
                '/mediawiki-api/'
            ]
        }
    },
  }