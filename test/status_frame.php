<?
include 'include.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<title>ViDi DEMO</title>
	</head>
	<body>
	<?
$token = $apikey;
// print "token:$token ";
if ($_GET["logout"] == "1") {
	$soapclient->destroyAll($token);
};

if ($_GET["createRoom"] == "1") {
	if ($_GET["record"] == "1") {
		$roomid =  $soapclient->createRoom($token, true);
	} else {
		$roomid =  $soapclient->createRoom($token, false);
	}
	$soapclient->setProperty($token, "apikey", "token", "selectedRoom", $roomid, "public");

};

if ($_GET["selectRoom"] == "1") {
	$soapclient->setProperty($token, "apikey", "token", "selectedRoom", $_GET["roomid"], "public");
	$roomid = $_GET["roomid"];
};

if ($_GET["destroyRoom"] == "1") {
	$soapclient->destroyRoom($token, $_GET["roomid"]);
};


if ($_GET["createClient"] == "1") {
	$soapclient->createClientInRoom($token, $_GET["roomid"]);
};

if ($_GET["destroyClient"] == "1") {
	$soapclient->destroyClient($token, $_GET["clientid"]);
};


if ($_GET["createInput"] == "1") {
	$soapclient->createInputForClient($token, $_GET["clientid"]);
};

if ($_GET["createOutput"] == "1") {
	$soapclient->createOutputForClient($token, $_GET["clientid"]);
};

if ($_GET["bind"] == "1") {
	$soapclient->bind($token, $_GET["inputid"], $_GET["outputid"]);
};

if ($_GET["unbind"] == "1") {
	$soapclient->unbindio($token, $_GET["inputid"], $_GET["outputid"]);
};

if ($_GET["destroyOutput"] == "1") {
	$soapclient->destroyOutput($token, $_GET["outputid"]);
};

if ($_GET["destroyInput"] == "1") {
	$soapclient->destroyInput($token, $_GET["inputid"]);
};

$rooms = $soapclient->listRooms($token);
if (! $rooms) {
	$room = $soapclient->createRoom($token, false);
	$rooms = $soapclient->listRooms($token);
}

$roomid = $soapclient->getProperty($token, "apikey", "token", "selectedRoom", "public");
if ( (!$roomid) || (!in_array($roomid,$rooms))) {
	$roomid = $rooms[0];
	$soapclient->setProperty($token, "apikey", "token", "selectedRoom", $roomid, "public");
};


	$clients = $soapclient->listClientsInRoom($token,$roomid);
	if (! $clients) { // create atleaset one client with input and output
		$clientid = $soapclient->createClientInRoom($token, $roomid);
		$input = $soapclient->createInputForClient($token, $clientid);
		$output = $soapclient->createOutputForClient($token,$clientid);
		$clients = $soapclient->listClientsInRoom($token,$roomid);
	}
	

	$bindings = $soapclient->listBindingsInRoom($token,$roomid);
	foreach ($bindings as $binding) {	
		$binding_endpoints[$binding] = $soapclient->getIOOfBinding($token, $binding);
		$output_binding[$binding_endpoints[$binding][1]] = $binding_endpoints[$binding][0];
		//TODO Bindings must be shown on interface (next to each input and output)
		$is_binding_active[$binding] = $soapclient->isBindingActive($token, $binding);
	}
	
	$client_count = count($clients);
	foreach  ($clients as $clientid) {
		$client_index = $soapclient->getProperty($token, "clientid", $clientid, "name");
		if (! $client_index) {
			$soapclient->setProperty($token, "clientid", $clientid, "name", "client_".($client_count+1));
			$client_count++;
		}
		$client_index = $soapclient->getProperty($token, "clientid", $clientid, "name");
		debug_log("CLIENT[$client_index] $client_id");

		$inputs = $soapclient->listInputsForClient($token, $clientid);
		$outputs = $soapclient->listOutputsForClient($token, $clientid);
		foreach ($outputs as $outputid) {
			$is_output_active[$outputid] = $soapclient->isOutputActive($token, $outputid);
		}
		
		// store for php use.
		$data_inputs[$clientid] = $inputs;
		$data_outputs[$clientid] = $outputs;
		
		// get bindings of inputs
		foreach ($inputs as $inputid) {
			$is_input_active[$inputid] = $soapclient->isInputActive($token, $inputid);
			foreach ($bindings as $binding) {	
				if ($binding_endpoints[$binding][0] == $inputid) {
						if (! $input_binding[$inputid]) {
							$input_binding[$inputid] = Array();
						}
						array_push($input_binding[$inputid], $binding_endpoints[$binding][1]);
				}
			}	
		}
	}
	
?>
	wsdl url: <?=$wsdl_url;?><br />
	apikey: <a href="<?=getlink("?logout=1");?>">[d]</a> <?=$token?><br>
	record: <input id="recordRoom" type="checkbox" name="recordRoom" />,
	<a id="createRoom" href="<?=getlink("?createRoom=1");?>">+[R]</a>, rooms:
	<?
	foreach ($rooms as $roomidit) {
		if ($roomidit == $roomid) {
			print "<b>" . $roomid . "</b>, ";
		} else {
			print "<A HREF='".getlink('?selectRoom=1&roomid='.$roomidit)."'>";
			print $roomidit;
			print "</A>,";
		};
	}
	/* &nbsp;<a href="<?=$server_monitor_url?>" target="server_monitor_frame">monitor</a> <a href="javascript:location.reload(true)">refresh</a>
*/
	
	print "<br>";
print 'room: <a href="?destroyRoom=1&roomid=' . $roomid . '" >[d]</a>';
print $roomid;
print "<br><form style='display:inline' > <input ";
print " type=submit value='bind' id='bind' /> (";
print "<input id=".$roomid."_bindinput type=text name=inputid size=7>";
print "<input id=".$roomid."_bindoutput type=text name=outputid size=7>";
print ")";
print "<input type=hidden name=bind value=1> ";
print "</form>";
print ' <A HREF="'.getlink("?createClient=1&roomid=".$roomid).'" >+[C]</A>';	
print "<br>";

foreach ($clients as $clientid) {
	print "<hr />";
	print "client:<A HREF=?destroyClient=1&clientid=".$clientid." >[d]</A>";
	print $clientid;

	print "<input type=submit value='use' id='showflash' onclick= \"";
	print "flashparams='flash_frame.php?clientid=".$clientid."'+";
	print " '&roomid=".$roomid."'+";
	print " '&inputid='+document.getElementById('".$clientid."_flashinput').value +";
	print " '&outputid='+document.getElementById('".$clientid."_flashoutput').value;  ";
	// print "alert(flashparams);";
	print "parent.flash_frame.location = flashparams;\"";
	print " >(";
	print "<input id=".$clientid."_flashinput type=text size=7>";
	print "<input id=".$clientid."_flashoutput type=text size=7>";	
	print ")";
	print " <A HREF=?createInput=1&clientid=".$clientid." >+[I]</A>";
	print " <A HREF=?createOutput=1&clientid=".$clientid." >+[O]</A>";
	print "\n";
	print "<table border=0>";
	// inputs
	foreach ($data_inputs[$clientid] as $inputid) {
		// set default for "use"
		print "<script>document.getElementById('".$clientid."_flashinput').value='".$inputid."';</script>	 ";
		print "<tr><td>";
		print "in:<A HREF=?destroyInput=1&inputid=".$inputid." >[d]</A> ";
		print "<A HREF=# onclick=\"";
		print "document.getElementById('".$clientid."_flashinput').value='".$inputid."'; ";
		print "document.getElementById('".$roomid."_bindinput').value='".$inputid."'; ";
		print "\">";
		print $inputid;
		print "</A>";
		print $is_input_active[$inputid] ? "[Ready]" : "[Not Ready]";
		print "</td><td>";
		if ($input_binding[$inputid]) {
			foreach ($input_binding[$inputid] as $bindedoutputid) {
				// for each binding
			  	print "<A HREF=?unbind=1&inputid=$inputid&outputid=$bindedoutputid >[d]<A>";
			  	print "to:$bindedoutputid, ";
			}
		}
		print "</td></tr>";  // nextline;
	}
	// outputs	
	foreach ($data_outputs[$clientid] as $outputid) {
		// set default for "use"
		print "<script>document.getElementById('".$clientid."_flashoutput').value='".$outputid."';</script>	 ";		
		print "<tr><td>";
		print "out:<A HREF=?destroyOutput=1&outputid=".$outputid." >[d]</A> ";
		print "<A HREF=# onclick=\"";
		print "document.getElementById('".$clientid."_flashoutput').value='".$outputid."'; ";
		print "document.getElementById('".$roomid."_bindoutput').value='".$outputid."'; ";
		print "\">";
		
		print $outputid;
		print "</A>";
		print $is_output_active[$outputid] ? "[Ready]" : "[Not Ready]";
		print "</td><td>";
		if ($output_binding[$outputid]) {
			$bindedinputid = $output_binding[$outputid];
			// it if has a binding binding
		  	print "<A HREF=?unbind=1&inputid=$bindedinputid&outputid=$outputid >[d]<A>";
			print "from:$bindedinputid ";
		}
		  print "</td></tr>";  // nextline;
	}

			echo "</table>";
}

?>
	<hr>
	<script>
		document.getElementById("recordRoom").onchange = function() {
			if (document.getElementById("recordRoom").checked) {
				document.getElementById("createRoom").attributes.href.value += "&record=1"
			} else {
				document.getElementById("createRoom").attributes.href.value = document.getElementById("createRoom").attributes.href.value.replace(/&record=1/g, "")
			}
		}
	</script>
	</body>
</html>
