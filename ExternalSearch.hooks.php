<?php
/**
 * Hooks for Example extension
 *
 * @file
 * @ingroup Extensions
 */

class ExternalSearchHooks {
	/**
	 * Add welcome module to the load queue of all pages
	 */


	public static function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) {
		global $wgEnableExternalSearch;

		$out->addModules( 'ext.ExternalSearch.init' );

		// Always return true, indicating that parser initialization should
		// continue normally.
		return true;
	}
/*
	static function getPreferences( $user, &$prefs ) {
        	global $wgExtensionAssetsPath;

		$prefs['ExternalSearch'] = array(
	            	// The first two are message keys
            		'label-message' => 'external-search-feature-message',
        	    	'desc-message' => 'external-search-feature-description',
	            	// Paths to images that represents the feature.
            		// The image is usually different for ltr and rtl languages.
        	    	// Images for specific languages can also specified using the language code.
	            	'screenshot' => array(
                		'en' => "$wgExtensionAssetsPath/ExternalSearch/images/screenshot-en.png",
        	    	),
	            	// Link to information on the feature - use subpages on mw.org, maybe?
            		'info-link' => 'https://brainstorage.amust.local/index.php',
            		// Link to discussion about the feature - talk pages might work
            		'discussion-link' => 'https://brainstorage.amust.local/index.php/Talk:ExternalSearch',
        	);
		return true;
    	}



	static function onGetPreferences( $user, &$preferences ) {
		// A checkbox
		$preferences['ExternalSearch'] = array(
			'type' => 'toggle',
			'label-message' => 'toggle-pref-external-search', // a system message
			'section' => 'searchoptions',
		);

		// Required return value of a hook function.
		return true;
	}
*/

}
