<?
$config = parse_ini_file("config.ini");
$apikey = $config["apikey"];
$vidi = new SoapClient($config["wsdl_url"] . "?wsdl", array(
	'location' => $config["wsdl_url"],
	'uri' => "http://test-uri/",
	'trace' => true,
	'encoding' => 'iso-8859-1',
	'cache_wsdl' => WSDL_CACHE_NONE));
# ugly hack for grepping different requestgs from log file
$requestid = preg_replace('/.*\./', '', sprintf('%f', microtime(true)));
function mlog($str) {
	global $requestid;
	$str = str_replace("\n", '\n', $str);
	$bt = debug_backtrace();
	$file = str_replace(getcwd()."/",'',$bt[0]["file"]); # crop finename
	$log_str = "$requestid $file: $str\n";
	$fp = fopen("log", "a");
	fwrite($fp, $log_str);
	fclose($fp);
}
?>
