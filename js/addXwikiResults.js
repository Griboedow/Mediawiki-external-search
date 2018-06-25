/*External Search*/
/*Add Search Results form xWiki*/

function getNoun(number, one, two, five) {
	let n = Math.abs(number);
	n %= 100;
	if (n >= 5 && n <= 20) {
		return five;
		}
		n %= 10;
		if (n === 1) {
	  		return one;
		}
		if (n >= 2 && n <= 4) {
		return two;
	}
	return five;
}

function getResult( query, callback) {
	var params = "";
	var xhttp = new XMLHttpRequest();
	xhttp.timeout = 310000; //300 + 10 seconds. Backend timeout is 300 seconds 
	
	xhttp.addEventListener('load', callback);
	xhttp.addEventListener('error', () => console.log("Request to " + query+ " failed"));
	
	xhttp.open("GET", query, true);
	xhttp.setRequestHeader("Content-type", "application/json");
	xhttp.send();
}

function appendElementToResult( parent, siteName, siteColor, title, pageLink, content, date, size, wordsCount, caseNumber, position){
	var newResult = document.createElement("li"); 
	
	var newResultHeading = document.createElement("div"); 
	newResultHeading.setAttribute("class", "mw-search-result-heading");
	
	var newResultHeadingLink = document.createElement("a"); 
	newResultHeadingLink.setAttribute("data-serp-pos", position);
	newResultHeadingLink.setAttribute("href", pageLink);
	newResultHeadingLink.setAttribute("title", title);
	//Add [sitename] tag 
	if(siteName){
		var siteTag = htmlToElement('<font color="' + siteColor + '">' + siteName + ' </font>');
		newResultHeading.appendChild(siteTag);
	}

	newResultHeadingLink.insertAdjacentHTML('beforeend', title);
	
	newResultHeading.appendChild(newResultHeadingLink);
	
	var newResultContent = document.createElement("div"); 
	newResultContent.setAttribute("class", "searchresult");
	newResultContent.insertAdjacentHTML('beforeend', content);
	
	//Footer
	var newResultDate = document.createElement("div"); 
	newResultDate.setAttribute("class", "mw-search-result-data");
	preFooter = "";
	if(size){
		preFooter = preFooter + size;
	}
	if(wordsCount && wordsCount !== 0){
				preFooter = preFooter + " (" + wordsCount + " " + getNoun(wordsCount, 'слово', 'слова', 'слов') + ")";
		}
	//Do not start string from -
	if(date && ((wordsCount && wordsCount !== 0) || size)){
		preFooter = preFooter + " - ";
	}
	if(date){
		preFooter = preFooter + date;
	}
	newResultDate.append(preFooter);

	//SalesForce footer
	salesForcePreFooter = '<br/>';
	if(caseNumber){
		salesForcePreFooter = salesForcePreFooter + 'Case number: ' + caseNumber;
	}
	newResultDate.insertAdjacentHTML('beforeend', salesForcePreFooter);

	newResult.appendChild(newResultHeading);
	newResult.appendChild(newResultContent);
	newResult.appendChild(newResultDate);
	
	parent.appendChild(newResult);
}

function tryAddSiteResult(searchResultsParent, query, site){
	query = query + '&exSite=' + site['id'];
		getResult( query, function() {
			title = JSON.parse(this.responseText).ExternalSearch.title;

			results = JSON.parse(this.responseText).ExternalSearch.searchResults;
			if( results ){
				var noResultsFound = document.getElementsByClassName("mw-search-nonefound")[0];
				if( noResultsFound ){
					noResultsFound.parentElement.removeChild(noResultsFound);
				}
			}

			startIndex = 20;
			relativeEndIndex = Math.min(results.length, 5);
			for( i = 0; i < relativeEndIndex; i++ ){
					searchResultElement = appendElementToResult(searchResultsParent, title.prefix, title.color, results[i].title, results[i].link, results[i].content, results[i].date, results[i].size, results[i].wordsCount, results[i].caseNumber, startIndex + i);
			}

			var statusElement = document.getElementById(site['id']);
			statusElement.getElementsByTagName('img')[0].setAttribute('src', '/extensions/ExternalSearch/img/ok.png');
	});
}

function  addExternalSearchLoaderUi(sites, searchResults){
	var loader = document.createElement("div");
	loader.setAttribute("class", "external_search_loader");

	var loaderTitle = document.createElement("div");
	loaderTitle.setAttribute("class", "mw-search-result-heading");

	var loaderTitleLink = document.createElement("a");
	loaderTitleLink.insertAdjacentHTML('beforeend', '[External Search] Searching on external sites ...');

	loaderTitle.appendChild(loaderTitleLink);
	loader.appendChild(loaderTitle);

	var loaderText =  document.createElement("div");
	loaderText.setAttribute("class", "searchresult");

	var loaderTextContent = '';
	for(i = 0; i < sites.length; i++){
		loaderTextContent = loaderTextContent + '<span id="' + sites[i]['id'] + '"><input type="checkbox" checked=true disabled=true> ' + sites[i]['name'] + ' <img src="/extensions/ExternalSearch/img/SpinnerDownload.gif" width="16">';
		if( i < sites.length - 1){
			loaderTextContent = loaderTextContent + '<br/>';
		}
		loaderTextContent = loaderTextContent + '</span>';
	}

	loaderText.insertAdjacentHTML('beforeend', loaderTextContent);
	loader.appendChild(loaderText);

	var loaderFooter = document.createElement("div");
	loaderFooter.setAttribute("class", "mw-search-result-data");
	loaderFooter.insertAdjacentHTML('beforeend', 'Results will be added to the bottom of the page');

	loader.appendChild(loaderFooter);

//	searchResults.parentElement.insertBefore(loader, searchResults);
	var parent = searchResults.parentElement;
	parent.insertBefore(loader, parent.firstElementChild);

}

function tryAddNewSearchResults(){

	if(mw.config.get ('wgPageName') === "Служебная:Поиск"){
		var url = new URL(document.URL);
		var scriptFullPath = mw.config.get('wgServer') + mw.config.get('wgScriptPath');
		var betterQueryElement = document.getElementById("mw-search-DYM-rewritten");

		if( betterQueryElement ){
				var query = scriptFullPath + '/api.php?action=query&list=ExternalSearch&format=json&exQuery=' + betterQueryElement.children[0].innerText;
		}
		else{
				var query = scriptFullPath + '/api.php?action=query&list=ExternalSearch&format=json&exQuery=' + url.searchParams.get("search");
		}
	
		//TODO: Get list from API
		var sites = [
			{
				'id' : 'xWikiSite',
				'name' : 'My xWiki site',
			}
		];

		var searchResultsParent = document.getElementsByClassName("mw-search-results")[0];
		if( !  searchResultsParent){
				searchResultsParent_Parent = document.getElementsByClassName("searchresults")[0];
				var searchResultsParent = document.createElement("ul");
				searchResultsParent.setAttribute("class", "mw-search-results");
			searchResultsParent_Parent.appendChild(searchResultsParent);
		}

		addExternalSearchLoaderUi(sites, searchResultsParent);

		for(i = 0; i < sites.length; i++){
				tryAddSiteResult(searchResultsParent, query, sites[i]);
		}
	}
}

( function ( mw, $ ) {
	tryAddNewSearchResults();
}( mediaWiki, jQuery ) );

