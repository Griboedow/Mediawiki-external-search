<?php
class ApiQueryExternalSearch extends ApiQueryBase {

	/**
	 * Constructor is optional. Only needed if we give
	 * this module properties a prefix (in this case we're using
	 * "ex" as the prefix for the module's properties.
	 * Query modules have the convention to use a property prefix.
	 * Base modules generally don't use a prefix, and as such don't
	 * need the constructor in most cases.
	 */
	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'ex' );
	}

	/**
	 * In this example we're returning one ore more properties
	 * of wgExampleFooStuff. In a more realistic example, this
	 * method would probably
	 */
	public function execute() {
		global $defaultSearchSite;

		global $searchSites;

		$params = $this->extractRequestParams();

		//Just enum sites
		if ( isset( $params[ 'JustEnumSites' ] ) ) {
			$sites = [
				'sites' => $searchSites,
			];
			$this->getResult()->addValue( null, $this->getModuleName(), $sites );
			return;
		}

		// This is a filtered request, only show this key if it exists,
		// (or none, if it doesn't exist)
		if ( isset( $params[ 'Site' ] ) ) {
			$key = $params[ 'Site' ];
			if ( isset( $searchSites[ $key ] ) ) {
				$site = $key;
			}
			else{
				$site = $defaultSearchSite;
			}
		// This is an unfiltered request, replace the array with the total
		// set of properties instead.
		} else {
			$site = $defaultSearchSite;
		}

		$siteUrl = $searchSites[ $site ][ 'url' ];
		$queryStringFixed = $params['Query'];

		$queryUrl = $siteUrl . $searchSites[ $site ][ 'queryParams' ] . urlencode( $queryStringFixed );

		// Run search
		$result = $this->getRestRequest( $queryUrl );

		// Search results postprocessing
		if( $searchSites[ $site ][ 'type' ] == 'xWiki' ){
			$this->prepareXwikiResult( $siteUrl, $result, $params['Query'] );
		}


		$r = [
			'url' => $queryUrl,
			'site' => $params['Site'],
			'searchResults' => $result,
			'useWikiProxy' => $searchSites[ $site ][ 'useWikiProxy' ],
			'title' => $searchSites[ $site ][ 'title' ],
		];

		$this->getResult()->addValue( null, $this->getModuleName(), $r );
	}

	public function getAllowedParams() {
		return [
			'Query' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false,
			],

			'Site' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false,
			],

			'JustEnumSites' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false,
			],

                        'Length' => [
                                ApiBase::PARAM_TYPE => 'integer',
                                ApiBase::PARAM_REQUIRED => false,
                        ],

                        'Offset' => [
                                ApiBase::PARAM_TYPE => 'integer',
                                ApiBase::PARAM_REQUIRED => false,
                        ],

		];
	}

	public function createQueryHighlightRegex($query){
                //Prepare regex strings for highlighting query in results
                $isExactQuery = 0;
                $queryFixed = str_replace("\"", '', $query, $isExactQuery);
                if( $isExactQuery > 0 ){
                        $queryFixedRegex = "/w*?" . $queryFixed . "\w*/i";
                }
                else{
                        $queryFixedRegex = explode(' ', $queryFixed);
                        foreach( $queryFixedRegex as $key=>&$value ){
                                if(strlen($value) < 2 ){
                                        //Skp empty keywords
                                        unset($queryFixedRegex[$key]);
                                }
                                else{
                                        //regex to highlight empty keywords
                                        $value =  "/\w*?" . $value . "\w*/i";
                                }
                        }
                }
		return $queryFixedRegex;
	}

	public function highlightQueryInText( $content, $queryRegex){
		return preg_replace($queryRegex, "<span class=\"searchmatch\">$0</span>", $content);
	}

	function array_find($needle, array $haystack, $column = null) {
		if(is_array($haystack[0]) === true) { // check for multidimentional array
	        	foreach (array_column($haystack, $column) as $key => $value) {
		            if (strpos(strtolower($value), strtolower($needle)) !== false) {
        		        return $key;
		            }
        		}
		    } else {
        		foreach ($haystack as $key => $value) { // for normal array
	        	    if (strpos(strtolower($value), strtolower($needle)) !== false) {
	                	return $key;
		            }
		        }
		    }
		    return false;
	}

	public function prepareXwikiResult( $siteUrl, &$searchResults, $query ){

		$monthsList = array(
			".01." => "января", 
			".02." => "февраля",
			".03." => "марта", 
			".04." => "апреля", 
			".05." => "мая", 
			".06." => "июня",
			".07." => "июля", 
			".08." => "августа", 
			".09." => "сентября",
			".10." => "октября", 
			".11." => "ноября", 
			".12." => "декабря");

		$searchResults = $searchResults["searchResults"];
		$replaceDict = [
			"\n" => '',
			'\'\'\'' => '',
			'**' => '',
			'%%' => '%',
			'##' => '',
			'~\\' => '\\',
			'{{error}}' => '"',
			'{{/error}}' => '"',
			'{{dashboard/}}' => 'Cannot show dynamic content.',
		];

		$replaceRegexDict = [
			'/\(\%(.*?)\%\)/' => '',
			'/\[\[(.*?)\]\]/' => '',
			'/\(\(\((.*?)\)\)\)/' => '',
			'/\{\{(.*?)\}\}/' => '',
		];

		$queryHighlightRegex = $this->createQueryHighlightRegex($query);

		//Rebuild output query to fit common format
		foreach( $searchResults as $key => &$searchResult ){
			//I use it for debug. Sometimes xWiki returns invalid links. We remove such pages from result
			$searchResult["linkRest"] =  str_replace ( 'http://127.0.0.1:8080', $siteUrl, $searchResult["links"][0]["href"] );
			$page = $this->getRestRequest( $searchResult["linkRest"] . '?media=json' );

			$searchResult["title"] = $this->highlightQueryInText($page["title"], $queryHighlightRegex);

			$searchResult["link"] = $page["xwikiAbsoluteUrl"];

			$searchResult["content"] = strip_tags( $page["content"] );
			//Simple replace operation is a bit faster. Use it when possible
			$searchResult["content"] = str_replace( array_keys($replaceDict), $replaceDict, $searchResult["content"] );
			$searchResult["content"] = preg_replace(array_keys($replaceRegexDict), $replaceRegexDict, $searchResult["content"] );
			$searchResult["content"] = substr ( $searchResult["content"], 0, 250);
			$searchResult["content"] = $this->highlightQueryInText($searchResult["content"], $queryHighlightRegex);
			
			//Page size in necessary format
			$searchResult["size"] = $this->formatBytes(strlen($page["content"]));
			$searchResult["wordsCount"] = str_word_count($page["content"]);
			
			//Prepare date in necessary format
			$unixTimeInSeconds = $searchResult["modified"] / 1000;
			$searchResult["date"] = date('h:i, d.m.Y', $unixTimeInSeconds );
			$_monthDate = date('.m.', $unixTimeInSeconds);
			$searchResult["date"] = str_replace($_monthDate , " ".$monthsList[$_monthDate]." ", $searchResult["date"]);


			//Unset unnecessary fields (no need to send them to a client)
			unset($searchResult["links"]);
			unset($searchResult["modified"]);
			unset($searchResult["type"]);
			unset($searchResult["id"]);
			unset($searchResult["pageFullName"]);
			unset($searchResult["pageName"]);
			unset($searchResult["version"]);
			unset($searchResult["language"]);
			unset($searchResult["className"]);
			unset($searchResult["objectNumber"]);
			unset($searchResult["filename"]);
			unset($searchResult["wiki"]);

			//Some results are corrupted (xWiki bug)
			if(! $searchResult["title"]){
				unset($searchResults[$key]);
			}
		}
		unset($searchResult);

		//Reindex array
		$searchResults = array_values($searchResults);

	}

        public function getRestRequest( $url ) {
		$ctx = stream_context_create(array('http'=>
			array(
				'timeout' => 300,  //300 Seconds is 5 Minutes
    			)
		));

		header('Content-type: text/html; charset=utf-8'); 
                $response = file_get_contents( $url, false, $ctx );
                $response = json_decode($response, true);

                return $response;
        }


	protected function getExamplesMessages() {
		return [
		];
	}

	public function formatBytes($bytes, $precision = 0) { 
		$units = array('B', 'KB', 'MB', 'GB', 'TB'); 
		
		$bytes = max($bytes, 0); 
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
		$pow = min($pow, count($units) - 1); 
		
		$bytes /= pow(1024, $pow);
		
		return round($bytes, $precision) . ' ' . $units[$pow]; 
	} 


}
