<?
require "config.php";
require "log.php";
require "vidi.php";

$requests = array("event", "roomid", "clientid", "inputid", "outputid", "bindingid", "inputclientid", "outputclientid", "message");
foreach ($requests as $request) {
    if (isset($_REQUEST[$request])) {
        $$request = $_REQUEST[$request];
    } else {
        $$request = "none";
    }
}

$logstr = sprintf("start. e-'%s' r-'%s' c-'%s' i-'%s' o-'%s' b-'%s' ic-'%s' oc-'%s' m-'%s'",
	            $event, $roomid, $clientid, $inputid, $outputid, $bindingid, $inputclientid, $outputclientid, $message);
postback_log($logstr);

switch($event) {
        case 'postback_test':
                postback_log("postback received: postback_test");
		break;
        case 'client_joined_room':
                postback_log("postback received: client_joined_room");
                $new_client = $clientid;
                $new_client_name = $vidi->getProperty($apikey, 'clientid', $new_client, "name");
                $client_list = $vidi->listClientsInRoom($apikey,$roomid);
                foreach ($client_list as $client) {
                    $vidi->tellClient($apikey, $client, "client_joined_room::$new_client|$new_client_name");
                }
                break;
        case 'client_left_room':
                postback_log("postback received: client_left_room");
                $new_client = $clientid;
                $new_client_name = $vidi->getProperty($apikey, 'clientid', $new_client, "name");
                $client_list = $vidi->listClientsInRoom($apikey,$roomid);
                foreach ($client_list as $client) {
                    $vidi->tellClient($apikey, $client, "client_left_room::$new_client|$new_client_name");
                }
                
		try {
			$vidi->destroyClient($apikey, $clientid);
		} catch (Exception $e) {
			postback_log("$event:$e");
                }
                break;
        case 'client_says':
                postback_log("postback_received: client_says");
                $message_parts = explode("::",$message);
                $action = $message_parts[0];
                $data = $message_parts[1];
                if ($action == "publish_page") {
                    $data_parts = explode(";;",$data);
                    $slide_info = $data_parts[0];
                    $slide_parts = explode("|",$slide_info);
                    $slide_name = $slide_parts[0];
                    $page_no = $slide_parts[1];
                    $clients = explode("|",$data_parts[1]);

                    foreach ($clients as $client) {
                        postback_log("tell client '$client', publish page '$page_no' from slide '$slide_name'");
                        $vidi->tellClient($apikey, $client, "publish_page::$slide_name|$page_no");
                    }
                }
                break;
	case 'input_ready':
                postback_log("postback received: input_ready");
		break;
        case 'input_not_ready':
                postback_log("postback received: input_not_ready");
                break;
	case 'output_ready':
                postback_log("postback received: output_ready");
		break;
	case 'output_not_ready':
                postback_log("postback received: output_not_ready");
		break;
	case 'binding_deactivated':
                postback_log("postback received: binding_deactivated");
		break;
	case 'client_expired':
                postback_log("postback received: client_expired");
		break;
	case 'binding_expired':
                postback_log("postback received: binding_expired");
        case 'room_expired':
                postback_log("postback received: room_expired");
                break;
        case 'session_expired':
                postback_log("postback received: session_expired");
                break;
	default:
                postback_log("postback received: unknown");
                postback_log("event: '$event' could not handled");
                postback_log("end");
		header("Status: 500 Error. unknown postback");
		print "ERROR: postback.php received an unknown event:'$event'";
                postback_log("end.");
		exit(0);
}
print "ok";
postback_log("end.");
?>
