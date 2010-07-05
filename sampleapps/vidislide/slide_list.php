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
    $clientid = "";

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

function getSlidePages($slide_path, $slide_name) {
    $path = "$slide_path/$slide_name";
    if (!file_exists($path)) {
        mkdir($path,0755);
    }
    $page_list = scandir("$slide_path/$slide_name");
    sort($page_list);
    $page_list = array_slice($page_list,2);
    return $page_list;
}

// check request variables, if room requested get room info else stop script
$room_info = (isset($_REQUEST["slidename"])) ? getRoom($_REQUEST["slidename"]) : die("Bir slayt ismi belirlemelisiniz.");
$room_name = $room_info[0];
$roomid = $room_info[1];

// check request variables, if client requested get client info else stop script
$client_info = (isset($_REQUEST["clientname"])) ? getClient($_REQUEST["clientname"],$roomid) : die("Bir isim seçmelisiniz.");
$client_name = $client_info[0];
$slide_name = $room_name;
$clientid = $client_info[1];

$show_client_list = (isset($_REQUEST["showclientlist"]) && ($_REQUEST["showclientlist"] == "false")) ? "none" : "inline";

// get client list
$client_list = getClientsInRoom($roomid);
for ($i=0; $i<count($client_list); $i++) {
    if ($client_list[$i][1] == $client_name) unset($client_list[$i]);
}


// ugly hack to calculate size
$width = 600;
$height = 500;
/*
$width = (isset($_REQUEST["width"])) ? $_REQUEST["width"] : 320;
$height = (isset($_REQUEST["height"])) ? $_REQUEST["height"] : 240;
$chatbox_width = $width - 15;
$chatbox_height = $height - 75;
$controls_width = $width - 15;
$controls_height = 50;
$chat_message_width = $width - 90;
 */
$page_list = getSlidePages($slide_path, $slide_name);

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
                    if (action == "client_joined_room") {
                        stranger_info = data.split("|");
                        stranger_id = stranger_info[0];
                        stranger_name = stranger_info[1];
                        if (globals.client_id != stranger_id) {
                           if ($("#"+stranger_id).length == 0) {
                                temp = "<li id='"+stranger_id+"'><input type='checkbox' checked class='client' id='"+stranger_id+"' />&nbsp;&nbsp;"+stranger_name+"</li>";
                                $("#client_list").append(temp);
                            }
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
                } else {
                    log("undefined callback: "+cmd);
                }
            }
            
            function getSelectedClients() {
                log("getSelectedClients called");
                var client_list = "";
                $(".client").each(function() {
                    if (this.checked = true) {
                        client_list += this.id+"|";
                    }
                });
                client_list = client_list.slice(0,-1);
                log("client list: "+client_list);
                return client_list;
            }

            function publishPage(screen, slide_name, page) {
                log("publishPage called");
                client_list = getSelectedClients();
                var msg = {message:'publish_page::'+slide_name+'|'+page+';;'+client_list}
                screen.tellServer(msg);
            }

            $(function() { 
                vidi.initialize({
                    clientid: "<?=$clientid?>",
                    roomid: "<?=$roomid?>",
                    debug: <?=$debug?>,
                    callback: onVidiCallback
                });

                globals.screen = createScreen(globals.screen_settings);

                $(".publish").click(function(){
                    var slide_name="<?=$slide_name?>";
                    var page = $(this).attr("id");
                    publishPage(globals.screen, slide_name, page);
                });

           });
        </script>
    </head>
    <body>
        <div>
            <div id="screen_container"></div>
            <div id="slide_list_container" style="width: <?=$width?>px; height: <?=$height?>px;">
                <h3>&nbsp;Pages of slide "<?=$slide_name?>"</h3>
                <hr />
                <center>
                <a href="new_slide.php?slidename=<?=$slide_name?>&clientname=<?=$client_name?>">Add Page</a>&nbsp;
                        <a href="#">Delete Selected</a>
                    <table id="slide_list" border="1" width="100%">
                        <tr>
                            <th>&nbsp;</th>
                            <th>Slide Name</th>
                            <th>Page #</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                        </tr>
                        <? foreach($page_list as $value): ?>
                        <tr>
                            <td><input type="checkbox" /></td>
                            <td><?=$slide_name?></td> 
                            <td><?=$value?></td>
                            <td><a href="new_slide.php?slidename=<?=$slide_name?>&clientname=<?=$client_name?>&page=<?=$value?>">edit</a></td>
                            <td><a href="<?=$slide_path."/".$slide_name."/".$value?>/index.html">show</a></td>
                            <td><a href="#" id="<?=$value?>" class="publish" />publish</a></td>
                        </tr>
                        <? endforeach; ?>
                    </table>
                </center>
            </div>
            <div id="client_list_container" style="height: <?=$height?>px; display: <?=$show_client_list?>">
                <h3>&nbsp;&nbsp;Publish List</h3>
                <hr />
                <ul id="client_list">
                    <? foreach($client_list as $value): ?>
                    <li id="<?=$value[0]?>">
                        <input type="checkbox" checked class="client" id="<?=$value[0]?>" />&nbsp;&nbsp;<?=$value[1]?>
                    </li>
                    <? endforeach; ?>
                </ul>
            </div>
        </div>
    </body>
</html>
