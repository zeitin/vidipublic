<?php
require "config.php";

$vidi = new SoapClient($wsdl_url . "?wsdl", array(
        'location' => $wsdl_url,
        'uri' => "http://test-uri/",
        'trace' => true,
        'encoding' => 'iso-8859-1'
    ));
?>
