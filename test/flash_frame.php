<?
include 'include.php';
$room = $_GET["roomid"];
$client_id = $_GET["clientid"];
$default_inputid = $_GET["inputid"];
$default_outputid = $_GET["outputid"];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<title>ViDi DEMO</title>
		<script type="text/javascript" src="<?=$vidi_js_url?>"></script> <!-- ViDi API -->
		<script type="text/javascript" src="slider.js" ></script>
		<link href="slider_default.css" rel="stylesheet" type="text/css" />
	</head>
	<body>
		<script>
			if (!window.vidi) {
				alert("vidi.js is not loaded, possibly URL error.");
			}
			var vibapp = {};
			vibapp.master = undefined;
			vibapp.divs = function() {
				var divids = [];
				return {
					get_new_div_id: function get_new_div_id(parent_div_id, div_prefix) {
						var parentdiv = document.getElementById(parent_div_id);
						var childdiv = document.createElement("div");
						var newdivid = "div"+div_prefix + divids.length;
						var sfwnewdivid = div_prefix + divids.length;
						divids.push(newdivid);
						childdiv.id = newdivid;
						var sfwchilddiv = document.createElement("div");
						sfwchilddiv.id=sfwnewdivid;
						parentdiv.appendChild(childdiv);
						childdiv.appendChild(sfwchilddiv);
						return sfwnewdivid;
					}
				}
			}();
			vibapp.sendText = function sendText() {
				var input = document.getElementById("inputtext");
				var message = input.value;
				if (!message) return;
				message = "<b><?=$client_id?></b>:" + message;
				// first flash connected rtmp server is used as master for now.
				vibapp.master.sendTextMsg({ 'msg':message});
				input.value = "";
			};

			vibapp.tellServer = function tellServer() {
				var input = document.getElementById("inputtext");
				var message = input.value;
				if (!message) return;
				// first flash connected rtmp server is used as master for now.
				vibapp.master.tellServer({ 'message': message});
				input.value = "";
			}

			vibapp.get_screen_status = function get_screen_status() {
				var oo = {
						mic: "hede",
						camera: "hede",
						mic_gain: "hede",
						speaker_volume: "hede"
					}
				var stat = vibapp.master.getStatus(oo);
				console.log("get_screen_status: stat=%o",stat);
			}

			vibapp.modify_screen = function modify_screen() {
				var oo = {
						camera: document.getElementById("modifycamera").checked,
						mic: document.getElementById("modifymic").checked
					}
				master.modify(oo); // TODO: we should be able to choose which screen to modify
			}

			vibapp.modify_mic_sound = function modify_mic_sound() {
				var oo = { mic_gain: document.getElementById("micvalue").value }
				master.modify(oo); 
			}

			vibapp.modify_speaker_sound = function modify_speaker_sound() {
				var oo = { speaker_volume: document.getElementById("speakervalue").value }
				master.modify(oo);
			}

			vibapp.get_speaker_volume = function get_speaker_volume() {
				document.getElementById("speakervalue").value = vibapp.master.getSpeakerVolume();
			}

			vibapp.get_mic_volume = function get_mic_volume() {
				document.getElementById("micvalue").value = vibapp.master.getMicVolume();
			}

			vibapp.destroy_screen = function destroy_screen() {
				vibapp.master.destroyScreen(); // TODO: we should be able to choose which screen to destroy
			}

//needed parameters: (parentid, outputid, inputid, clientid, roomid, onServerMsgCallback)
			vibapp.select_defaults = function select_defaults() {
				vibapp.inputid.value = "<?=$default_inputid?>";
				vibapp.outputid.value = "<?=$default_outputid?>";
				vibapp.camera.checked = true;
				// screen0 is not only screen because screen is a reserved keyword using by browsers.
				vibapp.screen0.checked = true;
				vibapp.speaker.checked = true;
				vibapp.mic.checked = true;
				vibapp.localecho.checked = false;
				vibapp.localecho_mirror.checked = false;
			}

			vibapp.select_textchat = function select_textchat() {
				vibapp.select_defaults();
				vibapp.camera.checked = false;
				vibapp.screen0.checked = false;
				vibapp.inputid.value='';
				vibapp.outputid.value='';
				vibapp.mic.checked=false;
				vibapp.speaker.checked=false;
				vibapp.screen_width.value=10;
				vibapp.screen_height.value=10;
				vibapp.togglesetting();
			}

			vibapp.select_audioonly = function select_audioonly() {
				vibapp.select_defaults();
				vibapp.camera.checked = false;
				vibapp.screen0.checked = false;
				vibapp.togglesetting();
			}

			vibapp.select_localecho = function select_localecho() {
				vibapp.select_defaults();
				vibapp.outputid.value = '';
				vibapp.localecho.checked = true;
				vibapp.localecho_mirror.checked = true;
				vibapp.mic.checked=false;
				vibapp.speaker.checked=false;
				vibapp.togglesetting();
			}

			vibapp.select_audiovideo = function select_audiovideo() {
				vibapp.select_defaults();
				vibapp.inputid.value='';
				vibapp.mic.checked=false;
				vibapp.camera.checked=false;
				vibapp.togglesetting();
			}

			vibapp.initialized = false;

			vibapp.init_flash = function init_flash() {
				if (!vibapp.initialized) {
					window && window.console && window.console.debug && window.console.debug(
						"testapi calling ",
						"vidi.initialize(", {
							clientid: vibapp.clientid,
							roomid: vibapp.roomid,
							debug: vibapp.debugcheck.checked,
							callback: "vibapp.handleVidiCmd"
						}, ");"
					);
					vidi.initialize({
						clientid: vibapp.clientid,
						roomid: vibapp.roomid,
						debug: vibapp.debugcheck.checked,
						callback:vibapp.handleVidiCmd
					});
					vibapp.addTextLog('vidi.initialize({clientid:'+vibapp.clientid+', roomid:'+vibapp.roomid+', debug:'+vibapp.debugcheck.checked+', callback:vibapp.handleVidiCmd});');
					document.getElementById('debugcheck').style.visibility='hidden';
				};

				var newdivid=vibapp.divs.get_new_div_id("swfcontainer", "container");

				var oo = {
					divid:newdivid,
					camera:vibapp.camera.checked,
					screen:vibapp.screen0.checked,
					speaker:vibapp.speaker.checked,
					mic:vibapp.mic.checked,
					screen_smoothing:vibapp.screen_smoothing.checked,
					camera_localcompress: vibapp.camera_localcompress.checked,
					bwcheck: vibapp.bwcheck.checked,
					localecho:vibapp.localecho.checked,
					localecho_mirror:vibapp.localecho_mirror.checked
				};
				if (vibapp.screen_height.value) {
					oo.screen_height = vibapp.screen_height.value;
				}
				if (vibapp.screen_width.value) {
					oo.screen_width = vibapp.screen_width.value;
				}
				if (vibapp.screen_fps.value) {
					oo.screen_fps = vibapp.screen_fps.value;
				}
				if (vibapp.screen_buffertime.value) {
					oo.screen_buffertime = vibapp.screen_buffertime.value;
				}
				if (vibapp.screen_deblocking.value) {
					oo.screen_deblocking = vibapp.screen_deblocking.value;
				}
				if (vibapp.camera_height.value) {
					oo.camera_height = vibapp.camera_height.value;
				}
				if (vibapp.camera_fps.value) {
					oo.camera_fps = vibapp.camera_fps.value;
				}
				if (vibapp.camera_quality.value) {
					oo.camera_quality = vibapp.camera_quality.value;
				}
				if (vibapp.camera_favorarea.value) {
					oo.camera_favorarea = vibapp.camera_favorarea.value;
				}
				if (vibapp.camera_motionlevel.value) {
					oo.camera_motionlevel = vibapp.camera_motionlevel.value;
				}
				if (vibapp.camera_motiontimeout.value) {
					oo.camera_motiontimeout = vibapp.camera_motiontimeout.value;
				}
				if (vibapp.camera_keyframeinterval.value) {
					oo.camera_keyframeinterval = vibapp.camera_keyframeinterval.value;
				}
				if (vibapp.background_image.value) {
					oo.background_image = vibapp.background_image.value;
				}
				if (vibapp.camera_bandwidth.value) {
					oo.camera_bandwidth = vibapp.camera_bandwidth.value;
				}
				if (vibapp.camera_width.value) {
					oo.camera_width = vibapp.camera_width.value;
				}
				if (vibapp.outputid.value) {
					oo.outputid = vibapp.outputid.value;
				}
				if (vibapp.inputid.value) {
					oo.inputid = vibapp.inputid.value;
				}
				if (vibapp.mic_gain.value) {
					oo.mic_gain = vibapp.mic_gain.value;
				}
				if (vibapp.mic_rate.value) {
					oo.mic_rate = vibapp.mic_rate.value;
				}
				if (vibapp.mic_silencelevel.value) {
					oo.mic_silencelevel = vibapp.mic_silencelevel.value;
				}
				if (vibapp.mic_silencetimeout.value) {
					oo.mic_silencetimeout = vibapp.mic_silencetimeout.value;
				}

				window && window.console && window.console.debug && window.console.debug(
					"testapi calling createScreen(",
					oo,
					");"
				);

				var addH1 = function(updateSet) {
				    return "<H1>"+updateSet.value+"</H1>";
				};
				var screen = vidi.createScreen(oo);
				screen.showPublicProperty({where:'apikey',id:'',key:'selectedRoom',elementid:'upselectRoom',formatter:addH1});
				vibapp.addTextLog('vidi.createScreen(oo);');
				var modifydiv = document.createElement('div');
				document.getElementById("modifydiv").style.display = '';
				if (!vibapp.master) vibapp.master = screen;
			}

			vibapp.addTextLog = function addTextLog(text) {
				function makeTwoDigit(text) {
					return ((text+"").length == 1) ? "0" + text : text;
				}
				var time = new Date();
				var timeoflog = makeTwoDigit(time.getHours()) + ":" + makeTwoDigit(time.getMinutes()) + ":" + makeTwoDigit(time.getSeconds());
				var objDiv = document.getElementById("chatbox");
				objDiv.innerHTML = timeoflog + ": " + text + "<br />" + objDiv.innerHTML;
			}

			vibapp.handleVidiCmd = function handleVidiCmd(cmd, args) {
				switch (cmd) {
				case "callback_test":
					// vibapp.addTextLog("responding to callback_test with ok");
					return "ok";
				case "received_textchat":
					vibapp.addTextLog(args.message);
					//nothing to do
					break;
				case "connect":
					vibapp.addTextLog("CONNECT [OK]");
					break;
				case "started_publishing":
					vibapp.addTextLog("INPUT [OK]");
					break;
				case "play_start":
					vibapp.addTextLog("PLAY START [OK]");
					break;
				case "published":
					vibapp.addTextLog("PUBLISHED [OK]");
					break;
				case "play_reset":
					vibapp.addTextLog("PLAY RESET [OK]");
					break;
				case "flash_loaded":
					vibapp.addTextLog("FLASH LOADED [OK]");
					break;
				case "server_says":
					vibapp.addTextLog('SERVER_SAYS: '+args.message);
					break;
				//TODO user can have or don't have camera, mic or both.
				//All situations must be handled and alerted.
				case "no_camera":
					alert("You need a camera!");
					break;
				case "no_microphone":
					alert("You need a microphone!");
					break;
				case "old_or_no_flash":
					alert("Your either have a old version of flash or no flash at all! current:"+args.currentFlashVersion+" min:"+args.minFlashVersion);
					break;
				case "property_set":
					vibapp.addTextLog('PROPERTY_SET: '+args.key+'='+args.value+' in '+args.where+':'+args.id);
					break;
				default:
					vibapp.addTextLog("[UNKNOWN] " + cmd+ " args:"+args);
				}
			}

vibapp.toggledetails = function toggledetails() {
	if (document.getElementById("detailsdiv").style.display == ''){
		document.getElementById("detailsdiv").style.display = 'none';
	} else {
		document.getElementById("detailsdiv").style.display = '';
	}
}

vibapp.togglesetting = function togglesetting() {
	document.getElementById('cameradiv').style.display = (vibapp.camera.checked) ? '' : 'none'
	document.getElementById('micdiv').style.display = (vibapp.mic.checked) ? '' : 'none'
	document.getElementById('screendiv').style.display = (vibapp.screen0.checked) ? '' : 'none'
}


vibapp.change_width_height = function change_width_height(value, type/*screen or camera*/) {
	var others_div = document.getElementById(type+"_width_heightdiv");
	var w = document.getElementById(type+"_width");
	var h = document.getElementById(type+"_height");
	others_div.style.display = "none";
	if (value == "undef") {
		w.value = "";
		h.value = "";
	} else if (value == "320") {
		w.value = 320;
		h.value = 240;
	} else if (value == "640") {
		w.value = 640;
		h.value = 480;
	} else if (value == "215") {
		w.value = 215;
		h.value = 138;
	} else /*(value == "others")*/ {
		others_div.style.display = '';
	}
}
		</script><div>
<?
// TODO: We could show this inputs as seperate vidi.js api call
// parameters for vidi.initialize() and
// vidi.createScreen(). Interface clould look like JS code
// with seperate inputs as functions' parameter.
?>
<style>
label {white-space: nowrap;}
label:hover {background-color: gray;}
label input {background-color: lightgray;}
label:hover input {background-color: white;}
.detail {border: 1px solid black;}
.detail:hover {background-color: darkgray;}
</style>
vidi.js: <?=$vidi_js_url?><br />
selectedRoom:<div id="upselectRoom">whatRoom?</div>
<?=$client_name?> roomid:<?=$room?>, clientid:<?=$client_id?>
<label><input id="debugcheck" type="checkbox" checked="checked" />debug</label>
<br />
<input type="button" id="send" value="createScreen" onclick="vibapp.init_flash()"/>
<label><input type="radio" id="select_audiovideo" name="type_selection" onclick="vibapp.select_audiovideo()" />OutputOnly</label>
<label><input type="radio" id="select_localecho" name="type_selection" onclick="vibapp.select_localecho()" />LocalEcho</label>
<label><input type="radio" id="select_audioonly" name="type_selection" onclick="vibapp.select_audioonly()" />Audio</label>
<label><input type="radio" id="select_textchat" name="type_selection" onclick="vibapp.select_textchat()" />Text</label>
<label><input id="show_details" type="checkbox" onclick="vibapp.toggledetails();"/>show_details</label>

<div id="detailsdiv" style="display:none">

<label>inputid:<input size=8 id="inputid" value="<?=$default_inputid;?>"/></label>
<label>outputid:<input size=8 id="outputid" value="<?=$default_outputid;?>"/></label> or
<label><input id="localecho" type="checkbox" />localecho</label>
<label><input id="localecho_mirror" type="checkbox" />localecho_mirror</label>
<br />

<label><input id="camera" type="checkbox" checked="checked" onchange="vibapp.togglesetting()"/>camera</label><br />
<div class="detail" id="cameradiv">

<label>camera_width_height:<select id="camera_width_height" onchange="vibapp.change_width_height(this.value, 'camera')"/><option value="undef">undefined<option value="640">640x480<option value="320">320x240<option value="other">other</select></label>
<div id="camera_width_heightdiv" style="display:none">
<label>camera_width(320):<input id="camera_width" value="" size=3 /></label>
<label>camera_height(240):<input id="camera_height" value="" size=3 /></label>
</div>
<label>camera_bandwidth(200kbps):<input id="camera_bandwidth" value="" size=3 /></label>
<label>camera_quality(0/100):<input id="camera_quality" value="" size=2 /></label>
<label>camera_fps(15):<input id="camera_fps" value="" size=2 /></label>
<label><input id="camera_favorarea" type="checkbox" />camera_favorarea(true)</label>
<label><input id="camera_localcompress" type="checkbox" />camera_localcompress(false)</label>
<label>camera_motionlevel(50/100):<input id="camera_motionlevel" value="" size=2 /></label>
<label>camera_motiontimeout(2000ms):<input id="camera_motiontimeout" value="" size=2 /></label>
<label>camera_keyframeinterval(15/48):<input id="camera_keyframeinterval" value="" size=2 /></label>
</div>

<label><input id="mic" type="checkbox" checked="checked" onchange="vibapp.togglesetting()"/>mic</label><br />
<div class="detail" id="micdiv">
<label>mic_gain(60/100):<input id="mic_gain" value="60" size=3 /></label>
<label>mic_rate(22kHz):<select id="mic_rate" /><option value="5">5<option value="8">8<option value="11">11<option selected values="22">22<option value="44">44</select></label>
<label>mic_silencelevel(0/100):<input id="mic_silencelevel" value="0" size=3 /></label>
<label>mic_silencetimeout(10000ms):<input id="mic_silencetimeout" value="10000" size=4 /></label>
</div>

<label><input id="screen0" type="checkbox" checked="checked" onchange="vibapp.togglesetting()"/>screen</label><br />
<div class="detail" id="screendiv">
<label>screen_width_height:<select id="screen_width_height" onchange="vibapp.change_width_height(this.value, 'screen')"/><option value="640">640x480<option value="320">320x240<option value="215" selected>215x138<option value="other">other</select></label>
<div id="screen_width_heightdiv" style="display:none">
<label>screen_width:<input id="screen_width" value="215" size=3 /></label>
<label>screen_height:<input id="screen_height" value="138" size=3 /></label>
</div>
<label><input id="screen_smoothing" type="checkbox" checked="checked" />screen_smoothing</label>
<label>screen_fps:<input id="screen_fps" value="" size=3 /></label>
<label>screen_buffertime(0.1s):<input id="screen_buffertime" value="" size=3 /></label>
<label>screen_deblocking(0):<select id="screen_deblocking" />
	<option value="0" selected>0-Lets the video compressor apply the deblocking filter as needed.
	<option value="1">1-Does not use a deblocking filter.
	<option value="2">2-Uses the Sorenson deblocking filter.
	<option value="3">3-For On2 video only, uses the On2 deblocking filter but no deringing filter.
	<option value="4">4-For On2 video only, uses the On2 deblocking and deringing filter.
	<option value="5">5-For On2 video only, uses the On2 deblocking and a higher-performance On2 deringing filter.</select>
</label>
</div>

<label><input id="speaker" type="checkbox" checked="checked"/>speaker</label><br />

<label>background_image:<input id="background_image" value="images/mic-on.jpg" size=15 /></label>
<label><input id="bwcheck" type="checkbox" />bwcheck</label>

</div>
<div id="swfcontainer"></div>
<br />
<div id="modifydiv" style="display: none">
	camera<input id="modifycamera" type="checkbox" checked="checked" />
	mic<input id="modifymic" type="checkbox" checked="checked" />
	<input type="button" id="modify" value="modify" onclick="vibapp.modify_screen()" />
	<input type="button" id="getstat" value="Get Status" onclick="vibapp.get_screen_status()" />
	<input type="button" id="destroyScreen" value="destroyScreen" onclick="vibapp.destroy_screen()" /><br />
		<table>
			<tbody>
				<tr>
					<td>Speaker:</td>
					<td>
						<!-- Speaker Slider -->
						<div class="horizontal_track" id="horizontal_track_1">
							<div class="horizontal_slit" id="horizontal_slit_1">&nbsp;</div>
							<!-- total movement: 100 pixels, scale: 1 [value/pixel] -->
							<div class="horizontal_slider" id="speakerslider" style="left: 100px;" onmousedown="slide(event, 'horizontal', 100, 0, 100, 101, 0, 'speakervalue');" onmouseup="vibapp.modify_speaker_sound();">&nbsp;</div>
						</div>
					</td>
					<td>
						<!-- Speaker Display -->
						<div class="display_holder" id="display_holder_1">
							<input class="value_display" id="speakervalue" value="100" onfocus="blur(this);" type="text">
						</div>
					</td>
				</tr>
				<tr>
					<td>Mic:</td>
					<td>
						<!-- Mic Slider -->
						<div class="horizontal_track" id="horizontal_track_2">
							<div class="horizontal_slit" id="horizontal_slit_2">&nbsp;</div>
							<!-- total movement: 100 pixels, scale: 1 [value/pixel] -->
							<div class="horizontal_slider" id="micslider" style="left: 50px;" onmousedown="slide(event, 'horizontal', 100, 0, 100, 101, 0, 'micvalue');" onmouseup="modify_mic_sound();">&nbsp;</div>
						</div>
					</td>
					<td>
						<!-- Mic Display-->
						<div class="display_holder" id="display_holder_2">
							<input class="value_display" id="micvalue" value="50" onfocus="blur(this);" type="text">
						</div>
					</td>
				</tr>
		</table>
</div>
			<br />
			<input type="text" id="inputtext"/>
			<input type="button" id="send" value="send Text" onclick="vibapp.sendText()" />
			<input type="button" id="send" value="tellServer" onclick="vibapp.tellServer()" />
			<div id="chatbox">chatbox</div>
			<script>
			vibapp.roomid = "<?=$room?>";
			vibapp.user = "<?=$client_name?>";
			vibapp.clientid =  "<?=$client_id?>";
			vibapp.inputid = document.getElementById("inputid");
			vibapp.outputid = document.getElementById("outputid");
			vibapp.camera = document.getElementById("camera");
			vibapp.screen0 = document.getElementById("screen0");
			vibapp.speaker = document.getElementById("speaker");
			vibapp.mic = document.getElementById("mic");
			vibapp.localecho = document.getElementById("localecho");
			vibapp.localecho_mirror = document.getElementById("localecho_mirror");
			vibapp.screen_height = document.getElementById("screen_height");
			vibapp.screen_width = document.getElementById("screen_width");
			vibapp.screen_smoothing = document.getElementById("screen_smoothing");
			vibapp.screen_fps = document.getElementById("screen_fps");
			vibapp.screen_buffertime = document.getElementById("screen_buffertime");
			vibapp.screen_deblocking = document.getElementById("screen_deblocking");
			vibapp.camera_height = document.getElementById("camera_height");
			vibapp.camera_width = document.getElementById("camera_width");
			vibapp.camera_fps = document.getElementById("camera_fps");
			vibapp.camera_quality = document.getElementById("camera_quality");
			vibapp.camera_favorarea = document.getElementById("camera_favorarea");
			vibapp.camera_localcompress = document.getElementById("camera_localcompress");
			vibapp.camera_motionlevel = document.getElementById("camera_motionlevel");
			vibapp.camera_motiontimeout = document.getElementById("camera_motiontimeout");
			vibapp.camera_keyframeinterval = document.getElementById("camera_keyframeinterval");
			vibapp.background_image = document.getElementById("background_image");
			vibapp.bwcheck = document.getElementById("bwcheck");
			vibapp.camera_bandwidth = document.getElementById("camera_bandwidth");
			vibapp.debugcheck  = document.getElementById("debugcheck");
			vibapp.mic_gain  = document.getElementById("mic_gain");
			vibapp.mic_rate  = document.getElementById("mic_rate");
			vibapp.mic_silencelevel  = document.getElementById("mic_silencelevel");
			vibapp.mic_silencetimeout  = document.getElementById("mic_silencetimeout");

		</script>
		</div>
	</body>
</html>
