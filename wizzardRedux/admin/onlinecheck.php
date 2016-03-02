<?php

/* ------------------------------------------------------------------------------------
Check for new downloadable ROMs from all available sites

Requires:
	source		The sourcename to check against (in sites/<source>.php)
	
Notes:
	Below are a list of sites with things of note about them:
	- c64gamescom				Empty checker page
	- CPC-Power					"full" is no longer active
	- Import64					Empty checker page
	- NES-CartDatabase			Needs more testing
	- PiratedGameCenter			Not technically dead, just all posts gone
	- SpecialProgramSipe		Goes through MEGA now, not via the site itself
	- ssrg						Needs special attention because of possible login
	- Tiddles					Output needs verification
	- vic20it					Needs special attention because of possible login
	- VideopacNL				Need to test cookie usage
	- VimmsLair					See download link todo
	- Vizzed					Possible cookie usage

TODO: Retool existing onlinecheck.php files to follow the new format. 3) check code flow to try to optimize
TODO: Most explode/implode can probably be changed to preg_match, just need to decipher them
TODO: Document all required GET and POST vars for each page
TODO: Comment all of the code...
TODO: VideopacNL uses a cookie to be able to access the board. This means you need to log in to the site and then copy the cookie as a param
TODO: Can we run all online checks in a coherent way (in series, that is)?
------------------------------------------------------------------------------------ */

ini_set('max_execution_time', 0); // Set the execution time to infinite. This is a bad idea in production.

// Populate list if sources
$query = "SELECT name FROM sources";
$result = mysqli_query($link, $query);
$result = mysqli_fetch_all($result);
$sites = array();
foreach ($result as $item)
{
	$sites[] = array_shift($item);
}

// Sites that are dead
$dead = array(
		"8BitChip",
		"8BitCommodoreItalia",
		"Atarimania",
		"Cas2Rom",
		"Import64",
		"Konamito",
		"m3Zz",
		"PiratedGameCenter",
		"PokemonGBAroms",
		"smartlip",
);

if (!isset($_GET["source"]))
{
	echo "<h2>Please Choose a Site</h2>\n";
	
	// List all files, auto-generate links to proper pages
	$files = scandir("../sites/", SCANDIR_SORT_NONE);
	foreach ($files as $file)
	{
		if (preg_match("/^.*\.php$/", $file))
		{
			$file = substr($file, 0, sizeof($file) - 5);
			echo "<a href=\"?page=onlinecheck&source=".$file."\">".htmlspecialchars($file).
				"</a>".(in_array($file, $dead) ? " (Dead)" : "")."<br/>\n";
		}
	}
	
	// List all sites that don't have checkers
	echo "<h2>Sites With No Checker</h2>\n";
	
	// Normalize the arrays because some names don't match 1:1
	$newsites = array();
	foreach ($sites as $site)
	{
		$newsites[] = strtolower($site);
	}
	$newfiles = array();
	foreach ($files as $file)
	{
		$newfiles[] = strtolower($file);
	}
	$newfiles = str_replace(".php", "", $newfiles);
	foreach (array_diff($newsites, $newfiles) as $key => $site)
	{
		echo $sites[$key]."<br/>\n";
	}

	echo "<br/><a href='".$path_to_root."/index.php'>Return to home</a>";

	die();
}
elseif (!file_exists("../sites/".$_GET["source"].".php"))
{
	echo "<b>The file you supply must be in /sites/</b><br/>";
	echo "<a href='index.php'>Return to home</a>";

	die();
}

$source = $_GET["source"];

echo "<h2>Loading pages and links...</h2>";

$r_query = implode('', file("../sites/".$source.".txt"));
$r_query = explode("\r\n", $r_query);
$r_query = array_flip($r_query);

$found = array();

// Original code: The Wizard of DATz
include_once("../sites/".$source.".php");

//http://nadeausoftware.com/articles/2007/06/php_tip_how_get_web_page_using_curl
//http://stackoverflow.com/questions/4372710/php-curl-https
/**
 * Get a web file (HTML, XHTML, XML, image, etc.) from a URL.  Return an
 * array containing the HTTP server response header fields and content.
 */
function get_data($url)
{
    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_USERAGENT      => "spider", // who am i
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT        => 120,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
    	CURLOPT_SSL_VERIFYPEER => false     // Disabled SSL Cert checks
    );

    $ch      = curl_init( $url );
    curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );
    curl_close( $ch );
    
    return $content;
}

?>