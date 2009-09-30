<?
require("common.php");

function error_handler($errno, $errstr, $errfile, $errline) {
	if ($errno == E_NOTICE) {
		return true;
	}
	$errorno_list = array(
		E_ERROR              => 'Error',
		E_WARNING            => 'Warning',
		E_PARSE              => 'Parsing Error',
		E_NOTICE             => 'Notice',
		E_CORE_ERROR         => 'Core Error',
		E_CORE_WARNING       => 'Core Warning',
		E_COMPILE_ERROR      => 'Compile Error',
		E_COMPILE_WARNING    => 'Compile Warning',
		E_USER_ERROR         => 'User Error',
		E_USER_WARNING       => 'User Warning',
		E_USER_NOTICE        => 'User Notice',
		E_STRICT             => 'Runtime Notice',
		E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
	);
	$errno_str = $errorno_list[$errno];
    mlog("error handler: type='$errno_str' msg='$errstr' line='$errline'");
    return true;
}
// set to the user defined error handler
set_error_handler("error_handler");

$event = $_POST["event"];
$roomid = $_POST["roomid"];
$clientid = $_POST["clientid"];
$inputid = $_POST["inputid"];
$outputid = $_POST["outputid"];
$bindingid = $_POST["bindingid"];
$inputclientid = $_POST["inputclientid"];
$outputclientid = $_POST["outputclientid"];

$logstr = sprintf("start. e-'%s' r-'%s' c-'%s' i-'%s' o-'%s' b-'%s' ic-'%s' oc-'%s'",
	$event, $roomid, $clientid, $inputid, $outputid, $bindingid, $inputclientid, $outputclientid);
mlog($logstr);

switch($event) {
	case 'postback_test':
		break;
	case 'input_ready':
		$clients = $vidi->listClientsInRoom($apikey, $roomid);
		mlog("$event: got clients-" . print_r($clients,true));
		foreach ($clients as $otherclientid) {
			if ($otherclientid == $clientid) {
				continue;
			}
			$other_inputid = $vidi->listInputsForClient($apikey, $otherclientid);
			$other_inputid = $other_inputid[0];
			if (!$other_inputid || !$vidi->isInputActive($apikey, $other_inputid)) {
				continue;
			}
			$otheroutputid = $vidi->createOutputForClient($apikey, $otherclientid);
			$bindingid = $vidi->bind($apikey, $inputid, $otheroutputid);
			mlog("$event: created output-'$otheroutputid' for client-'$otherclientid' and binded-'$bindingid' to input-'$inputid'");
			try {
				$vidi->tellClient($apikey, $otherclientid, "join!$otheroutputid");
			} catch (Exception $e) {
				mlog("$event: error, cannot tell to client-'$otheroutputid', error-" + $e);
			}
			mlog("$event: telling client-'$otherclientid' to activate his output-'$otheroutputid'");
		}
		break;
	case 'input_not_ready':
	case 'client_left_room':
		try {
			$vidi->destroyClient($apikey, $clientid);
		} catch (Exception $e) {
			mlog("$event:");
		}
		mlog("$event: deleted client-'$clientid'");
		break;
	case 'output_not_ready':
		$vidi->destroyOutput($apikey, $outputid);
		mlog("$event: destroyed output-'$outputid'");
		break;
	case 'binding_deactivated':
		try {
			$vidi->tellClient($apikey, $outputclientid, "leave!$outputid");
		} catch (Exception $e) {
			mlog("$event: error, cannot tell client-'$outputclientid', to remove its active output-'$outputid'");
		}
		mlog("$event: told output client-'$outputclientid' to remove related output from his screen, cus he closed publishing");
		break;
	default:
		mlog("end. event-'$event' not handled");
		header("Status: 500 Error. unknown postback");
		print "ERROR: postback.php received an unknown event:'$event'";
		exit(0);
}
print "ok";
mlog("end.");
?>
