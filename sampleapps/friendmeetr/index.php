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
					screen_width: 160,
					screen_height: 120,
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
				}
			}
		</script>
		<h1><?=$roomname?>(<?=$roomid?>)</h1>
		<div id="master"></div>
		<div id="container"></div>
		<script>
			vidi.initialize({
				clientid: '<?=$clientid?>',
				roomid: '<?=$roomid?>',
				debug: false,
				callback: vidi_handler
			});
			vidi.createVidi({
				divid: 'master',
				camera_width: 160,
				camera_height: 120,
				/* min 4:3 ratio screen size which can show flash permission box */
				screen_width: 215,
				screen_height: 161,
				camera: true,
				screen: true,
				localecho: true,
				mic: true,
				speaker: false,
				inputid: '<?=$inputid?>',
				camera_bandwidth: 100
			});
			var outputids = ['<?=join("','", $outputs);?>'];
			for (var i in outputids) {
				var outputid = outputids[i];
				create_screen(outputid);
			}
		</script>
	</body>
</html>
