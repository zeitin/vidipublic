<?
require "config.php";
require "log.php";
require "vidi.php";

$vidi->destroyAll($apikey);
echo "true";
?>
