<?
$apikey = $_GET['apikey'];
?>
<html>
<frameset rows="70%">
  <frameset cols="40%,60%">
    <frame src="flash_frame.php?apikey=<?=$apikey?>" name="flash_frame">
    <frame src="status_frame.php?apikey=<?=$apikey?>" name="status_frame">
  </frameset>
  <!-- <frame src="<?=$server_monitor_url?>" name="server_monitor_frame"> -->
</frameset>
</html>

