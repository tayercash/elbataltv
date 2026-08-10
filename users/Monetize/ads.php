<?php

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Please Wait ...</title>
    <style>
        /* Full window styles for video and ad container */
        #content_video,
        #ad_container {
            width: 100vw;
            height: 100vh;
            background: black;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }

        /* Hide ad container initially */
        #ad_container {
            display: block;
        }
    </style>
</head>

<body>

    <!-- Video Element -->
    <video id="content_video" controls autoplay></video>

    <!-- Ad Container (where the ads will display) -->
    <div id="ad_container"></div>

    <!-- <script src="ima3.js"></script> -->
    <script src="https://imasdk.googleapis.com/js/sdkloader/ima3.js"></script>

    <script>
        var videoElement = document.getElementById('content_video');
        var adContainer = document.getElementById('ad_container');
        var adDisplayContainer;
        var adsLoader;
        var adsManager;

        // Initialize the IMA Ad Display Container
        if (videoElement && adContainer) {
            adDisplayContainer = new google.ima.AdDisplayContainer(adContainer, videoElement);
            adDisplayContainer.initialize(); // Must be called as a result of a user action (e.g., click)
        } else {
            console.error('Video element or ad container not found.');
        }


        // Request Ads using IMA SDK
        function requestAds() {
            var adsRequest = new google.ima.AdsRequest();
            adsRequest.adTagUrl = 'https://new.elbatal-app.com/users/vast/test.php'; // Replace with your VAST ad tag URL
            // adsRequest.adTagUrl = 'https://pubads.g.doubleclick.net/gampad/ads?iu=/21775744923/external/single_preroll_skippable&sz=640x480&ciu_szs=300x250%2C728x90&gdfp_req=1&output=vast&unviewed_position_start=1&env=vp&impl=s&correlator='; // Replace with your VAST ad tag URL

            // Set ad parameters (can be customized)
            // adsRequest.linearAdSlotWidth = 640;
            // adsRequest.linearAdSlotHeight = 360;
            // adsRequest.nonLinearAdSlotWidth = 640;
            // adsRequest.nonLinearAdSlotHeight = 150;

            adsLoader.requestAds(adsRequest);
        }

        // Setup the ads loader
        function setUpAdsLoader() {
            adsLoader = new google.ima.AdsLoader(adDisplayContainer);

            // Listen for AdsManager loaded event
            adsLoader.addEventListener(google.ima.AdsManagerLoadedEvent.Type.ADS_MANAGER_LOADED, onAdsManagerLoaded, false);

            // Handle errors
            adsLoader.addEventListener(google.ima.AdErrorEvent.Type.AD_ERROR, onAdError, false);
        }

        // When the Ads Manager is loaded, play the ad
        function onAdsManagerLoaded(event) {
            try {
                adsManager = event.getAdsManager(videoElement); // Get the ads manager

                // Add listeners to pause/resume the content video
                adsManager.addEventListener(google.ima.AdEvent.Type.CONTENT_PAUSE_REQUESTED, function () {
                    videoElement.pause(); // Pause the content video during ad playback
                });

                adsManager.addEventListener(google.ima.AdEvent.Type.CONTENT_RESUME_REQUESTED, function () {
                    videoElement.play(); // Resume the content video after the ad finishes
                });

                adsManager.addEventListener(google.ima.AdEvent.Type.ALL_ADS_COMPLETED, function () {
                    console.log('All ads completed');
                    if (typeof mouscripts !== "undefined") {
                        mouscripts.mou_continue();
                    }
                });
                adsManager.addEventListener(google.ima.AdEvent.Type.LOADED, onAdLoaded);

                // Initialize the AdsManager instance
                adsManager.init(window.innerWidth, window.innerHeight, google.ima.ViewMode.NORMAL);
                adsManager.start(); // Start the ad playback

            } catch (error) {
                alert('Error initializing AdsManager:', error);
                videoElement.play(); // Fallback to playing the video
            }
        }
        function onAdLoaded(adEvent) {
            // Add a listener for ad clicks
            adsManager.addEventListener(google.ima.AdEvent.Type.CLICK, onAdClicked);

        }
        // Handle ad errors
        function onAdError(event) {
            alert('Ad error:', event.getError());

        }
        function onAdClicked(event) {
            adsManager.resume(); // Resumes ad video if paused

            const adClickThroughUrl = event.ad.data.clickThroughUrl;
            if (typeof mouscripts !== "undefined") {
                mouscripts.open_external_link(adClickThroughUrl);
            }

        }
        mou_loaded = false;
        // document.addEventListener('click', function () {
        //     if (!mou_loaded) {
        //         // Initialize ad container and video once user clicks
        //         videoElement.play(); // Load the video content
        //         requestAds(); // Request the ads
        //     }

        // });


        window.onload = function () {

            setUpAdsLoader();
            videoElement.play().then(() => {
                mou_loaded = true;
            }).catch(function (error) {
                mou_loaded = false;
            });// Load the video content

            requestAds(); // Request the ads
        };


        // document.addEventListener('click', function (event) {
        //     // Check if the clicked element is a link
        //     if (event.target.tagName === 'A' && event.target.href) {
        //         console.log('Link prevented: ' + event.target.href);

        //     }
        // });
        // Override window.location to prevent redirects
        // window.addEventListener('beforeunload', function (event) {
        //     event.preventDefault(); // Prevent the default behavior
        //     event.returnValue = ''; // Some browsers require this to show a confirmation dialog
        //     console.log('Navigation attempt prevented');
        // });


        window.addEventListener('resize', function (event) {
            console.log("window resized");
            if (adsManager) {
                var width = videoElement.clientWidth;
                var height = videoElement.clientHeight;
                adsManager.resize(width, height, google.ima.ViewMode.NORMAL);
            }
        });
    </script>
</body>

</html>