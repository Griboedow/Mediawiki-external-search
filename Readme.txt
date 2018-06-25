wfLoadExtension("ExternalSearch");

$defaultSearchSite = 'xWikiSite';

$searchSites = [
                        'xWikiSite' => [
                                'url' => 'http://xwiki.company.local',
                                'queryParams' => '/rest/wikis/query?distinct=1&wikis=xwiki&scope=name,content,objects&prettyNames=true&orderField=score&order=desc&media=json&q=',
                                'offsetParam' => '',
                                'lengthParam' => '',
                                'type' => 'xWiki',
                                'title' => [
                                        'prefix' => '[xWikiSite]',
                                        'color' => 'green',
                                ],
                                'defaultScore' => '10',
                        ],
];
