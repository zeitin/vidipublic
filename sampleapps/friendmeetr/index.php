<?
require("common.php");

$old_clientid = $_REQUEST["clientid"];
$roomname = $_GET["room"];
if (!$roomname) {
	mlog("show room-'$roomname form");
	?>
	<form method="GET" action="">
		Room <input name="room"> <input type="submit" value="Enter">
	</form>
	<?
	exit(0);
}
mlog("new client come, room:'$roomname'");
try {
	$roomid = $vidi->getProperty($apikey, 'apikey', $apikey, $roomname);
} catch (Exception $e) {
	mlog("roomid for room-'$roomname' cannot be retrieved from apikey");
}
if (!in_array($roomid, $vidi->listRooms($apikey))) {
	mlog("roomid-'$roomid' exists in apikey propertires but it possibly expired, i'll get a new roomid for room-'$roomname'");
	$roomid="";
}
if (!$roomid) {
	$roomid = $vidi->createRoom($apikey);
	$vidi->setProperty($apikey, 'apikey', $apikey, $roomname, $roomid);
	$vidi->setProperty($apikey, 'roomid', $roomid, "room", $roomname); # only for monitoring page, not functional
	mlog("created room-'$roomname' roomid-'$roomid'");
}
mlog("using room-'$roomname' roomid-'$roomid'");
$clientid = $vidi->createClientInRoom($apikey, $roomid);
mlog("created client:'$clientid'");
$other_clients = $vidi->listClientsInRoom($apikey, $roomid);
mlog("will try to bind with old clients:'" . print_r($other_clients, true) . "'");
$outputs = array();
foreach ($other_clients as $other_clientid) {
	if ($old_clientid == $clientid) {
		continue;
	}
	if ($other_clientid == $clientid) {
		continue;
	}
	$other_inputid = $vidi->listInputsForClient($apikey, $other_clientid);
	$other_inputid = $other_inputid[0];
	if ($other_inputid && $vidi->isInputActive($apikey, $other_inputid)) {
		$outputid = $vidi->createOutputForClient($apikey, $clientid);
		$bindingid = $vidi->bind($apikey, $other_inputid, $outputid);
		$outputs[] = $outputid;
		mlog("binded:'$bindingid' old client:'$other_clientid' active input:'$other_inputid' to newly created output:'$outputid'" );
	} else {
		mlog("dont bind to client:'$other_clientid'(inputid:'$other_inputid'), because input is not active");
	}
}
$inputid = $vidi->createInputForClient($apikey, $clientid);
mlog("created input:'$inputid' for this new client:'$clientid'");
$_REQUEST["clientid"] = $clientid;
?>
<html>
	<head>
		<title>Friendmeetr</title>
	</head>
	<body>
		<script type="text/javascript" src="<?=$config["vidi_js_path"]?>"></script>
		<script>
			globals = { screens: {} };
			function create_screen(outputid) {
				window.console && console.log(arguments.callee, arguments);
				if (outputid in globals.screens) {
					return;
				}
				globals.screens[outputid] = "loading";
				var container = document.getElementById("container");
				var newdiv = document.createElement("div");
				newdiv.id = outputid;
				container.appendChild(newdiv);
				newscreen = vidi.createVidi({
					divid: outputid,
					screen_width: <?=$config["camera_width"]?>,
					screen_height: <?=$config["camera_height"]?>,
					camera: false,
					screen: true,
					localecho: false,
					mic: false,
					speaker: true,
					outputid: outputid,
					camera_bandwidth: 100
				});
				globals.screens[outputid] = newscreen;
				newscreen.outputid = outputid;
			}
			function destroy_screen(outputid) {
				return;
				/* TODO: vidiscreen.detroyVidi() works buggy */
				window.console && console.log(arguments.callee, arguments);
				globals.screens[outputid] && globals.screens[outputid].destroyScreen && globals.screens[outputid].destroyVidi();
				delete globals.screens[outputid];
			}
			function vidi_handler(cmd, obj) {
				window.console && console.log(arguments.callee, arguments);
				if (cmd == "callback_test") { 
					return "ok";
				} else if (cmd == "server_says") {
					var [action, outputid] = obj.message.split("!")
					if (action == "join") {
						create_screen(outputid);
					} else if (action == "leave") {
						destroy_screen(outputid);
					}
				} else if (cmd == "received_textchat") {
					new_chat_message_comes(obj.message);
				}
			}
			function new_chat_message_comes(message) {
				var chatbox = document.getElementById("chatbox")
				chatbox.innerHTML = chatbox.innerHTML + "\n" + message;
				chatbox.scrollTop = chatbox.scrollHeight;
			}
			function check_chat_enter_key(e) {
				var ev = e || window.event;
				var keycode = ev.which || ev.keyCode;
				if (keycode == 13) send_chat_message();
			}
			function send_chat_message() {
				var input = document.getElementById("chat_message")
				var message = input.value;
				if (!message) return;
				globals.master.sendTextMsg({msg:message});
				input.value = "";
			}
		</script>
		<h1><?=$roomname?>(<?=$roomid?>)</h1>
		<table><tr><td>
			<div id="master"></div>
		</td><td>
			<textarea cols="25" rows="8" id="chatbox"></textarea>
			<br />
			<input onkeydown="check_chat_enter_key(event)" id="chat_message" />
			<input type="button" value="Send" onclick="send_chat_message(); return false;" />
		</td></tr></table>
		<div id="container"></div>
		<script>
			vidi.initialize({
				clientid: '<?=$clientid?>',
				roomid: '<?=$roomid?>',
				debug: <?=$config["debug"]?>,
				callback: vidi_handler
			});
			globals.master = vidi.createVidi({
				divid: 'master',
				camera_width: <?=$config["camera_width"]?>,
				camera_height: <?=$config["camera_height"]?>,
				/* min 4:3 ratio screen size which can show flash permission box */
				screen_width: <?=$config["screen_width"]?>,
				screen_height: <?=$config["screen_height"]?>,
				camera: true,
				screen: true,
				localecho: true,
				mic: true,
				speaker: false,
				inputid: '<?=$inputid?>',
				camera_bandwidth: <?=$config["camera_bandwidth"]?>
			});
			var outputids = ['<?=join("','", $outputs);?>'];
			for (var i in outputids) {
				var outputid = outputids[i];
				create_screen(outputid);
			}
		</script>
	</body>
</html>
