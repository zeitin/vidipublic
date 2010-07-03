<?php 
require "config.php";
require "log.php";
require "vidi.php";

/*
* This function gets room for given room name,
* if there is no room creates newone.
*/
function getRoom($room_name) {
    global $vidi, $apikey;
    debug_log("getRoom called");  
    try {
        $roomid = $vidi->getProperty($apikey, 'apikey', $apikey, $room_name);
    } catch (Exception $e) {
        debug_log("roomid for room '$room_name' cannot be retrieved from apikey");
    }

    if (!in_array($roomid, $vidi->listRooms($apikey))) {
        debug_log("roomid '$roomid' exists in apikey propertires but it possibly expired, i'll get a new roomid for room-'$room_name'");
        $roomid="";
    }
    
    if (!$roomid) {
        $roomid = $vidi->createRoom($apikey);
        $vidi->setProperty($apikey, 'apikey', $apikey, $room_name, $roomid);
        $vidi->setProperty($apikey, 'roomid', $roomid, "room", $room_name); # only for monitoring page, not functional
        debug_log("created room-'$room_name' roomid-'$roomid'");
    }

    debug_log("using '$roomid' for room '$room_name'");
    return array($room_name, $roomid);
}

/*
* This function gets client from given room for given client name,
* if there is no client in that room creates newone.
*/
function getClient($client_name, $roomid) {
    global $vidi, $apikey;
    debug_log("getClient called");
    try {
        $clientid = $vidi->getProperty($apikey, 'roomid', $roomid, $client_name);
    } catch (Exception $e) {
        debug_log("clientid for client '$client_name' cannot be retrieved from room '$roomid'");
    }

    if (!in_array($clientid,$vidi->listClientsInRoom($apikey, $roomid))) {
        debug_log("clientid '$clientid' exists in room propertires but it possibly expired, i'll get a new roomid for client '$client_name'");
        $clientid = "";
    }

    if (!$clientid) {
        $clientid = $vidi->createClientInRoom($apikey, $roomid);
        $vidi->setProperty($apikey, 'clientid', $clientid, "name", $client_name); 
        $vidi->setProperty($apikey, 'roomid', $roomid, $client_name, $clientid); 
    }
    
    debug_log("using '$clientid' for client '$client_name'");
    return array($client_name,$clientid);
}

// check request variables, if room requested get room info else stop script
$room_info = (isset($_REQUEST["slidename"])) ? getRoom($_REQUEST["slidename"]) : die("Bir slayt ismi belirlemelisiniz.");
$room_name = $room_info[0];
$roomid = $room_info[1];
$slide_name = $room_name;

// check request variables, if client requested get client info else stop script
$client_info = (isset($_REQUEST["clientname"])) ? getClient($_REQUEST["clientname"],$roomid) : die("Bir isim seçmelisiniz.");
$client_name = $client_info[0];
$clientid = $client_info[1];

// ugly hack to calculate size
$width = (isset($_REQUEST["width"])) ? $_REQUEST["width"] : 320;
$height = (isset($_REQUEST["height"])) ? $_REQUEST["height"] : 240;

?>

<html>
    <head>
        <title>Vidi Slide</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /> 
        <link rel="stylesheet" type="text/css" media="all" href="style.css" />
        <script type="text/javascript" src="<?=$vidi_js_path?>"></script>
        <script type="text/javascript" src="http://code.jquery.com/jquery-1.4.2.min.js"></script>
        <script type="text/javascript">
            var globals = {
                client_name: "<?=$client_name?>",
                client_id: "<?=$clientid?>",
                debug: <?=$debug?>,
                screen: "",
                screen_settings: {
                    divid: "screen_container",
                    camera: false,
                    screen: false,
                    mic: false,
                    speaker: false,
                    screen_width: 1,
                    screen_height: 1,
                    localecho: false
                }
            };

            function log(message) {
                if (globals.debug && window.console) {
                    console.log(message);
                }
            }

            function createScreen(settings) {
                return vidi.createVidi(settings);
            } 

            function onVidiCallback(cmd, obj) {
                var action_info;
                var action;
                var data;
                var stranger_info;
                var stranger_id;
                var stranger_name;
                var temp;
                
                if (cmd == "callback_test") { 
                    return "ok";
                } else if (cmd == "server_says") {
                    action_info = obj.message.split("::");
                    action = action_info[0];
                    data = action_info[1];
                    if (action == "publish_page") {
                        publish_info = data.split("|");
                        slide_name = publish_info[0];
                        page_no = publish_info[1];
                        log("callback 'publish_page' received. slide: "+slide_name+"pagne no: "+page_no);
                        slide_url = "<?=$slide_path?>/"+slide_name+"/"+page_no+"/index.html";
                        $("#slide_window").attr("src",slide_url);
                    }    
                } else {
                    log("undefined callback: "+cmd);
                }
            }
            

            $(function() { 
                vidi.initialize({
                    clientid: "<?=$clientid?>",
                    roomid: "<?=$roomid?>",
                    debug: <?=$debug?>,
                    callback: onVidiCallback
                });

                globals.screen = createScreen(globals.screen_settings);

           });
        </script>
    </head>
    <body style="padding: 0px;">
        <div>
            <div id="screen_container"></div>
            <div id="page_container" style="width: <?=$width?>px; height: <?=$height?>px;">
                <iframe id="slide_window" src="" width="%100" height="%100"></iframe>
            </div>
        </div>
    </body>
</html>
