<?
require 'include.php';

$log_as_error = true;
function mylog($message) {
	if ($message) {
		error_log($message);
	};
};

switch($_REQUEST["event"]) {
	case 'postback_test':
		mylog("postback_test");
		break;
	case 'client_says':
		$clientid = $_REQUEST["clientid"];
		$message = $_REQUEST["message"];
		mylog("client_says clientid:$clientid message:$message");
		$token = $apikey;
		$soapclient->tellClient($token,$clientid,$message);
		break;
	case 'client_left_room':
		$clientid = $_REQUEST["clientid"];
		$roomid = $_REQUEST["roomid"];
		mylog("client_left_room clientid:$clientid roomid:$roomid");
		break;
	case 'client_joined_room':
		$clientid = $_REQUEST["clientid"];
		$roomid = $_REQUEST["roomid"];
		mylog("client_joined_room clientid:$clientid roomid:$roomid");
		break;
	case 'input_ready':
		$inputid = $_REQUEST["inputid"];
		mylog("input_ready inputid:$inputid");
		break;
	case 'output_ready':
		$outputid = $_REQUEST["outputid"];
		mylog("output_ready outputid:$outputid");
		break;
	case 'input_not_ready':
		$inputid = $_REQUEST["inputid"];
		mylog("input_not_ready inputid:$inputid");
		break;
	case 'output_not_ready':
		$outputid = $_REQUEST["outputid"];
		mylog("output_not_ready outputid:$outputid");
		break;
	case 'input_expired':
		$inputid = $_REQUEST["inputid"];
		mylog("input_expired inputid:$inputid");
		break;
	case 'output_expired':
		$inputid = $_REQUEST["inputid"];
		mylog("input_expired inputid:$inputid");
		break;
	case 'room_expired':
		$roomid = $_REQUEST["roomid"];
		mylog("room_expired roomid:$roomid");
		break;
	case 'text_message':
		$clientid = $_REQUEST["clientid"];
		$roomid = $_REQUEST["roomid"];
		$message = $_REQUEST["message"];
		mylog("text_message clientid:$clientid roomid:$roomid message:$message");
		break;
	case 'binding_activated':
		$bindingid = $_REQUEST["bindingid"];
		mylog("binding_activated bindingid:$bindingid");
		break;
	case 'binding_deactivated':
		$bindingid = $_REQUEST["bindingid"];
		mylog("binding_deactivated bindingid:$bindingid");
		break;
	case 'binding_expired':
		$bindingid = $_REQUEST["bindingid"];
		mylog("binding_deactivated bindingid:$bindingid");
		break;
		case 'session_expired':
		$apikey = $_REQUEST["apikey"];
		mylog("session_expired apikey:$apikey");
		break;
	default:
		header("Status: 500 Error. unknown postback");
		mylog("UNKNOWN POSTBACK: ".$_REQUEST["event"]);
		print "ERROR: postback.php received an unknown event:".$_REQUEST["event"];
}
print "ok";

?>
