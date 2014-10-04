<?php
// This is used to add a torrent file to rTorrent
// Idea being, we use oldskoolscouse profile on TPB and pull new fastpiratebaytorrents from it every week.
// Set cron job for '* 20 * * 0'	(Every Sunday at 8)
// Chris Pomfret - Sept 2014
// or /usr/local/bin/php
$profileURL = "http://tf.maxters.net/pbay/user/oldskoolscouse";
//$profileURL = "http://feeds.bbci.co.uk/news/england/rss.xml";
$data;	// Global for returning feed data
$filename = "top40_data";
$lastDownloadedTime;	// Time the last torrent we downloaded was released

getFeed($profileURL);	// First we load the feed (rss info now in global $data)
loadData();					// Load $lastDownloadedTime from $filename

$xml = new SimpleXMLElement($data);

$publishTime = strtotime($xml->channel->item[0]->pubDate);		// Publish date of latest torrent - Convert real format to UNIX time, much easier to work with

// Now we work out it out if we need to download
if ($publishTime > $lastDownloadedTime){
	// A new torrent has been released
	
	// Update the last downloaded time to this torrent
	$lastDownloadedTime = $publishTime;	
	
	// Save last downloaded time to file.
	$fh = fopen($filename, 'w') or die("can't open file");
	fwrite($fh, $lastDownloadedTime);
	fclose($fh);
	
	// Now we need to send the found URI to rTorrent.
	echo shell_exec("transmission-remote -a ".$xml->channel->item[0]->torrent->magnetURI);
}
else{
	echo "Nothing New";
	// Nothing new, wait for another execution
}

// Loads last downloaded torrent time
function loadData(){
	global $filename, $lastDownloadedTime;
	if (file_exists($filename)){
		//File exists
		$fileHandle = fopen($filename, 'r') or die("can't open file");
		$lastDownloadedTime = fread($fileHandle, filesize($filename));
		fclose($fileHandle);
	}else{
		echo ("I think not");
	}
}

// Used to grab feed data
function getFeed($feedURI){
	global $data;
	$ch = curl_init($feedURI);
	curl_setopt_array($ch, Array(
	CURLOPT_URL            => $feedURI,
	CURLOPT_USERAGENT      => 'spider',
	CURLOPT_TIMEOUT        => 120,
	CURLOPT_CONNECTTIMEOUT => 30,
	CURLOPT_RETURNTRANSFER => TRUE,
	CURLOPT_ENCODING       => 'UTF-8'
	));
	$data = curl_exec($ch);
	curl_close($ch);
}
?>
