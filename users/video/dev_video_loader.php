<?php
header("HTTP/1.1 200 OK");

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ad with Circular Skip Timer</title>
    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #000;
        }

        #adContainer {
            position: relative;
            width: 100%;
            height: 100%;
        }

        iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        #skipButton {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            border: 5px solid rgba(255, 255, 255, 0.8);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 18px;
            font-weight: bold;
            display: none;
            cursor: pointer;
            text-align: center;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.3);
        }
    </style>
</head>

<body>
<!-- https://www.profitablecpmrate.com/hx9q3r3n?key=ce764b0d8e77587e570f5e861a47f5f5 -->
    <div id="adContainer">
        <iframe src="https://www.effectivegatecpm.com/pbh0ctfa0?key=3b95cca70df91c0086ddbcbe6b4955b3" id="adIframe"></iframe> <!-- Replace with your ad URL -->
        <div id="skipButton"><span id="countdown">5</span></div>
    </div>

    <script>
        let seconds = 5;  // Countdown time in seconds
        const countdownElement = document.getElementById('countdown');
        const skipButton = document.getElementById('skipButton');

        // Show skip button with circular countdown
        const countdownInterval = setInterval(() => {
            countdownElement.textContent = seconds;
            seconds--;

            if (seconds < 0) {
                clearInterval(countdownInterval);
                skipButton.textContent = 'Skip';
                skipButton.style.display = 'flex';
            } else {
                skipButton.style.display = 'flex';
            }
        }, 1000);

        // Redirect or hide iframe on skip button click
        skipButton.addEventListener('click', () => {
            if (seconds < 0) {
                mouscripts.mou_continue();
            }
        });
    </script>

</body>

</html>