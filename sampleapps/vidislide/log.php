<?php
require "config.php";

function debug_log($message) {
    global $debug_log_file;
    $back_trace = debug_backtrace();
    $line_no = $back_trace[0]["line"];
    $temp = explode("/",$back_trace[0]["file"]);
    $file_name = $temp[count($temp)-1];
    $message = "[$file_name:$line_no] $message\n";
    error_log($message,3,$debug_log_file);
}

function postback_log($message) {
    global $postback_log_file;
    $back_trace = debug_backtrace();
    $line_no = $back_trace[0]["line"];
    $temp = explode("/",$back_trace[0]["file"]);
    $file_name = $temp[count($temp)-1];
    $message = "[$file_name:$line_no] $message\n";
    error_log($message,3,$postback_log_file);
}

function dump_to_screen($var) {
    echo "<pre>";
    print_r($var);
    echo "</pre>";
}
?>
