<?php
header("Access-Control-Allow-Origin: https://imasdk.googleapis.com");
header('Access-Control-Allow-Credentials: true');
header("Content-Type: text/xml; charset=UTF-8");
// echo file_get_contents("https://pubads.g.doubleclick.net/gampad/ads?iu=/21775744923/external/single_preroll_skippable&sz=640x480&ciu_szs=300x250%2C728x90&gdfp_req=1&output=vast&unviewed_position_start=1&env=vp&impl=s&correlator=");

require 'vendor/autoload.php';
use Sokil\Vast\Factory;

// create document
$factory = new Factory();
$document = $factory->create('3.0');

// insert Ad section
$ad1 = $document
    ->createInLineAdSection()
    ->setId('ad1')
    ->setAdSystem('Ad Server Name')
    ->setAdTitle('Ad Title');
    // ->addImpression('http://ad.server.com/impression', 'imp1');
// ->setDuration(128)
// create creative for ad section
$linearCreative = $ad1
    ->createLinearCreative()
    // ->setDuration(30) // 30 seconds ad duration
    ->skipAfter(5) // Set the ad to be skippable after 5 seconds
    ->setId('013d876d-14fc-49a2-aefd-744fce68365b')
    ->setAdId('pre')
    ->setVideoClicksClickThrough('https://google.com');
    // ->addVideoClicksClickTracking('http://ad.server.com/videoclicks/clicktracking')
    // ->addVideoClicksCustomClick('http://ad.server.com/videoclicks/customclick')
    // ->addTrackingEvent('start', 'http://ad.server.com/trackingevent/start')
    // ->addTrackingEvent('pause', 'http://ad.server.com/trackingevent/stop');

// add closed caption file (Closed Caption support starts on VAST 4.1)
// $linearCreative
//     ->createClosedCaptionFile()
//     ->setLanguage('en-US')
//     ->setType('text/srt')
//     ->setUrl('http://server.com/cc.srt');

// add 100x100 media file
// $linearCreative
//     ->createMediaFile()
//     ->setProgressiveDelivery()
//     ->setType('video/mp4')
//     ->setHeight(100)
//     ->setWidth(100)
//     ->setBitrate(2500)
//     ->setUrl('https://www.w3schools.com/html/mov_bbb.mp4');

// add 200x200 media file
$linearCreative
    ->createMediaFile()
    ->setProgressiveDelivery()
    ->setType('video/mp4')
    ->setHeight(200)
    ->setWidth(200)
    ->setBitrate(2500)
    ->setUrl('https://www.w3schools.com/html/mov_bbb.mp4');
// get dom document
$domDocument = $document->toDomDocument();

// get XML string
echo formatXml($document);

function formatXml($xmlString) {
    // Create a new DOMDocument object
    $dom = new DOMDocument('1.0', 'UTF-8');
    
    // Load the XML string into the DOMDocument object
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;  // Enable formatting
    
    // Suppress errors to avoid warnings with malformed XML
    if (!$dom->loadXML($xmlString)) {
        return "Invalid XML format!";
    }
    
    // Return formatted XML
    return $dom->saveXML();
}
?>