<?php
/**
 * Example extension - based on the stripped-down extensions/BoilerPlate
 *
 * For more info see mediawiki.org/wiki/Extension:Example
 *
 * You should add a brief comment explaining what the file contains and
 * what it is for. MediaWiki core uses the doxygen documentation syntax,
 * you're recommended to use those tags to complement your comment.
 * See the online documentation at:
 * http://www.stack.nl/~dimitri/doxygen/manual.html
 *
 * Here are commonly used tags, most of them are optional, though:
 *
 * First we tag this document block as describing the entire file (as opposed
 * to a variable, class or function):
 * @file
 *
 * We regroup all extensions documentation in the group named "Extensions":
 * @ingroup Extensions
 *
 * The author would let everyone know who wrote the code, if there is more
 * than one author, add multiple author annotations:
 * @author Jane Doe
 * @author George Foo
 *
 * To mention the file version in the documentation:
 * @version 1.0
 *
 * The license governing the extension code:
 * @license GPL-2.0-or-later
 */

/**
 * MediaWiki has several global variables which can be reused or even altered
 * by your extension. The very first one is the $wgExtensionCredits which will
 * expose to MediaWiki metadata about your extension. For additional
 * documentation, see its documentation block in includes/DefaultSettings.php
 */
$wgExtensionCredits['other'][] = [
	'path' => __FILE__,

	// Name of your Extension:
	'name' => 'External search',

	// Sometime other patches contributors and minor authors are not worth
	// mentionning, you can use the special case '...' to output a localised
	// message 'and others...'.
	'author' => [
		'Nikolai Kochkin'
	],

	'version'  => '0.1.0',

	// The extension homepage. www.mediawiki.org will be happy to host
	// your extension homepage.
	'url' => 'https://medium.com/@urlfberht',

	# Key name of the message containing the description.
	'descriptionmsg' => 'external-search-desc',
];

/* Setup */

// Initialize an easy to use shortcut:
$dir = __DIR__;
$dirbasename = basename( $dir );

// Register files
// MediaWiki need to know which PHP files contains your class. It has a
// registering mechanism to append to the internal autoloader. Simply use
// $wgAutoLoadClasses as below:
$wgAutoloadClasses['ExternalSearchHooks'] = $dir . '/ExternalSearch.hooks.php';
$wgAutoloadClasses['ApiQueryExternalSearch'] = $dir . '/api/ApiQueryExternalSearch.php';

$wgMessagesDirs['ExternalSearch'] = __DIR__ . '/i18n';

$wgAPIListModules['ExternalSearch'] = 'ApiQueryExternalSearch';

//Add hooks
//$wgHooks['GetPreferences'][] = 'ExternalSearchHooks::onGetPreferences';
//$wgHooks['GetBetaFeaturePreferences'][] = 'ExternalSearchHooks::getPreferences';
$wgHooks['BeforePageDisplay'][] = 'ExternalSearchHooks::onBeforePageDisplay';


//Register modules
$wgResourceModules['ext.ExternalSearch.xWiki'] = [
        'scripts' => [
                'js/addXwikiResults.js',
        ],
        'styles' => [
                'css/externalSearchStyles.css',
        ],
        'dependencies' => [
                'mediawiki.util',
                'mediawiki.user',
                'mediawiki.Title',
        ],

        'localBasePath' => $dir,
        'remoteExtPath' => 'ExternalSearch' . $dirbasename,
];


$wgResourceModules['ext.ExternalSearch.init'] = [
        'scripts' => 'js/addXwikiResults.init.js',
        'dependencies' => [
                'ext.ExternalSearch.xWiki',
        ],

        'localBasePath' => $dir,
        'remoteExtPath' => 'ExternalSearch' . $dirbasename,
];

$wgEnableExternalSearch = true;
