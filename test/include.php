<?
// getting config information
session_start();

$config = parse_ini_file("config.ini", true);
$vidi_js_url = $config["urls"]["vidi_js"];
$wsdl_url = $config["urls"]["wsdl_server"];
$postback_url = $config["urls"]["postback_url"];

$server_monitor_url = preg_replace("!static/vidi.js!", "monitor", $vidi_js_url);

$soapclient = new SoapClient($wsdl_url . "?wsdl", array(
	'location' => $wsdl_url,
	'uri' => "http://test-uri/",
	'trace' => true,
	'encoding' => 'iso-8859-1',
	'cache_wsdl' => WSDL_CACHE_NONE));

$hide_apikey = false;
$apikey_get = $_GET['apikey'];
if ($apikey_get) {
	$_SESSION["apikey"] = $apikey_get;
}
$apikey = $_SESSION['apikey'];
if ($apikey == false) {
	$apikey = $config["user"]["apikey"];
	$hide_apikey = true;
}

function def($variable, $default) {
	if ($variable)
		return $variable;
	else
		return $default;
}

function debug_log($msg) {
	error_log("[DEBUG] " . $msg);
}

function getlink($url) {
	return $_SERVER[SCRIPT_NAME] . $url;
}

function redirect($url) {
	header("Location: " . getlink($url));
	exit(0);
}

?>
