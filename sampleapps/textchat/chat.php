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

function getClientsInRoom($roomid) {
    global $vidi, $apikey;
    $clients = $vidi->listClientsInRoom($apikey, $roomid);
    $client_names = array();
    foreach ($clients as $clientid) {
        $name = $vidi->getProperty($apikey, 'clientid', $clientid, "name"); 
        array_push($client_names, array($clientid,$name));
    }
    if (count($client_names) == 0) return false;
    else return $client_names;
}

// check request variables, if room requested get room info else stop script
$room_info = (isset($_REQUEST["room"])) ? getRoom($_REQUEST["room"]) : die("Bir oda ismi belirlemelisiniz.");
$room_name = $room_info[0];
$roomid = $room_info[1];

// check request variables, if client requested get client info else stop script
$client_info = (isset($_REQUEST["name"])) ? getClient($_REQUEST["name"],$roomid) : die("Bir isim seçmelisiniz.");
$client_name = $client_info[0];
$clientid = $client_info[1];

$show_client_list = (isset($_REQUEST["showclientlist"]) && ($_REQUEST["showclientlist"] == "false")) ? "none" : "inline";

// get client list
$client_list = getClientsInRoom($roomid);
for ($i=0; $i<count($client_list); $i++) {
    if ($client_list[$i][1] == $client_name) unset($client_list[$i]);
}

// ugly hack to calculate size
$width = (isset($_REQUEST["width"])) ? $_REQUEST["width"] : 320;
$height = (isset($_REQUEST["height"])) ? $_REQUEST["height"] : 240;
$chatbox_width = $width - 15;
$chatbox_height = $height - 75;
$controls_width = $width - 15;
$controls_height = 50;
$chat_message_width = $width - 90;

?>

<html>
    <head>
        <title>Text Chat</title>
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
            
            function receive_chat_message(message) {
                var temp = message.split("::");
                var name = temp[0];
                var msg = temp[1];
                log("received message "+message);
                msg = msg.replace(/<[a-z1-9]+[>|a-z1-9]/g, "");
                msg = msg.replace(/\n/g, "<br />");
                log(msg);
                var message_text = "<span class='message' style='display: none'><strong>"+name+":</strong> "+msg+"</span><br />";
                $("#chatbox").append(message_text);
                $(".message").fadeIn(300);
                $("#chatbox").scrollTop($("#chatbox").height() + $("#chatbox").scrollTop());
            }

            function send_to_server(screen,client_name,message) {
                message = message.replace(/\n$/,"");
                var message_text = client_name+"::"+message;
                log("sent mesage "+message_text);
                screen.sendTextMsg({msg:message_text});
            }

            function send_message() {
                    send_to_server(globals.screen, globals.client_name, $("#chat_message").val());
                    $("#chat_message").focus();
                    $("#chat_message").val("");
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
                    if (action == "client_joined_room") {
                        stranger_info = data.split("|");
                        stranger_id = stranger_info[0];
                        stranger_name = stranger_info[1];
                        if (globals.client_id != stranger_id) {
                           if ($("#"+stranger_id).length == 0) {
                                temp = "<li id='"+stranger_id+"'><a href='chat.php?room=<?=$clientid.'-'?>"+stranger_id +"&name=<?=$client_name?>' target'_blank'>"+stranger_name+"</a></li>";
                                $("#client_list").append(temp);
                            }
                        } else {
                            $("#chat_message").removeAttr("disabled");
                            $("#send_message").removeAttr("disabled");
                        }
                    } else
                    if (action == "client_left_room") {
                        stranger_info = data.split("|");
                        stranger_id = stranger_info[0];
                        stranger_name = stranger_info[1];
                        if (globals.client_id != stranger_id) {
                            $("#"+stranger_id).remove();        
                        }
                    }    
                } else if (cmd == "received_textchat") {
                    receive_chat_message(obj.message);
                } else {
                    log("undefined callback");
                }
            }

            $(function() { 
                var is_ctrl_down = false;
                vidi.initialize({
                    clientid: "<?=$clientid?>",
                    roomid: "<?=$roomid?>",
                    debug: <?=$debug?>,
                    callback: onVidiCallback
                });

                globals.screen = createScreen(globals.screen_settings);

                $("#chat_message").focus();
                $("#send_message").click(function() {
                    send_message();
                });


                $("#chat_message").keydown(function(event) {
                    if (event.which == "17") {
                        is_ctrl_down = true;
                    }
                });
                
                $("#chat_message").keyup(function(event) {
                    if (event.which == "17") {
                        is_ctrl_down = false;
                    }
                    if (event.which == "13") {
                        if (is_ctrl_down == false) {
                            send_message();
                        } else {
                            $("#chat_message").val($("#chat_message").val()+"\n");
                        }
                    }
                });
           });
        </script>
    </head>
    <body>
        <div>
            <div id="chat_container" style="width: <?=$width?>px; height: <?=$height?>px;">
                <div id="screen_container"></div>
                <div id="chatbox"style=" width: <?=$chatbox_width?>px; height: <?=$chatbox_height?>px;"></div>
                <div id="controls" style="width: <?=$controls_width?>px; height: <?=$controls_height?>px;">
                    <textarea id="chat_message" style="width: <?=$chat_message_width?>px;" disabled></textarea>
                    <input type="button" value="Gönder" id="send_message" disabled/>
                </div>
            </div>
            <div id="client_list_container" style="height: <?=$height?>px; display: <?=$show_client_list?>">
                <ul id="client_list">
                    <? foreach($client_list as $value): ?>
                    <li id="<?=$value[0]?>">
                        <a href="chat.php?room=<?=$clientid.'-'.$value[0]?>&name=<?=$client_name?>" target="_blank"><?=$value[1]?></a>
                    </li>
                    <? endforeach; ?>
                </ul>
            </div>
        </div>
    </body>
</html>
