<?php
require("auth.php");


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Checkin</title>

    <script src="/tickets/js/jquery-1.11.2.min.js" type="text/javascript"></script>
    <script src="/tickets/js/moment.min.js" type="text/javascript"></script>
    <script src="/tickets/js/jquery.multi-select.js" type="text/javascript"></script>
    <script src="/tickets/js/jquery.AjaxTable.js" type="text/javascript"></script>
    <script src="./qr_scanner.min.js"></script>
</head>
<style>
    body {
        font-family: sans-serif;
    }

    .stage {
        margin: 8px;
    }

    video {
        width: 400px;
        height: 400px;
    }
</style>

<body>
    <div class="stage" id="stage1">
        <video width="700px" height="700px" id="scanner"></video>
    </div>
    <div class="stage" id="stage2" style="display: none;">
        <h2 id="name"></h2>
        <h2 id="scan_status"></h2>
        <p id="program"></p>
        <p id="diet"></p>
        <button id="next_scan">Restart</button>
    </div>
</body>

<script type="module">
    // import QrScanner from "./qr_scanner.min.js";

    const videoEl = document.getElementById("scanner");
    const stage1 = document.getElementById("stage1");
    const stage2 = document.getElementById("stage2");

    const name = document.getElementById("name");
    const scan_status = document.getElementById("scan_status");
    const program = document.getElementById("program");
    const diet = document.getElementById("diet");
    let scanner = new QrScanner(videoEl, result => onSuccess(result), {});


    function startNextScan() {
        stage1.style.display = "block";
        stage2.style.display = "none";
        scanner.start();
    }

    function onSuccess(res) {
        const data = res.data.split("|");
        if (data.length < 2) return;
        const ticket_id = data[0];
        const hash = data.slice(1).join("");

        scanner.stop();
        stage1.style.display = "none";
        stage2.style.display = "block";

        $.ajax({
            type: "POST",
            url: "ajax/checkin_ticket.php",
            data: {
                ticket_id,
                hash
            },
            dataType: "json",
            async: true,
            success: function(json) {
                const data = json["success"];
                name.innerText = data["name"];
                scan_status.innerText = "Status: " + data["status"];
                if (data["status"] === "rescan")
                    scan_status.style.color = "red";
                else {
                    scan_status.style.color = "black";
                }
                diet.innerText = "Dietary requirements: " + data["diet"];
                program.innerText = "Program: " + (data["program"] === "0" ? "no" : "yes");
            },
            error: function(json) {
                console.error(json);
                startNextScan();
            }
        });
    };

    const button = document.getElementById("next_scan");
    button.addEventListener("click", startNextScan);

    startNextScan();
</script>

</html>