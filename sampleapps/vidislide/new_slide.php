<?php 
require "config.php";
require "log.php";
?>

<html>
<head>
<title>VidiEdu | Add Question</title>
<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
<meta http-equiv="Content-Type" content="text-html; charset=UTF-8" />
</head>
<body>
<?
        $slide_name=$_GET['slidename'];
        $client_name=$_GET['clientname'];
        if (isset($_GET["page"])) $page=$_GET["page"];
        $current_slide_path = "$slide_path/$slide_name";
	if (!file_exists($current_slide_path)) {
		mkdir($current_slide_path,0755);
        }

	print "<a href='slide_list.php?slidename=$slide_name&clientname=$client_name'>Page List</a>&nbsp;";
	print "<a href='new_slide.php?slidename=$slide_name&clientname=$client_name'>Add New Page</a>&nbsp;";
    	print "<br><b>Slide Name: </b>".$slide_name; 
        $q_list = scandir($current_slide_path);
        sort($q_list);
        $lastq = $q_list[count($q_list)-1];
        if (!isset($page)) $page = $lastq+1;

?>
<? if (!isset($_POST['editor1'])) { ?>
<form method="post">
        <textarea name="editor1">
        <?php
            if (file_exists($current_slide_path."/".$page)) {
                $file=fopen($current_slide_path."/".$page."/index.html","r");
                $contents=fread($file, filesize($current_slide_path."/".$page."/index.html"));
                echo $contents;
            }
        ?>
        </textarea>
	<p>Page No:</p>
	<script type="text/javascript">
		CKEDITOR.replace( 'editor1' );
	</script>
	<input type="text" name="page" value="<?=$page?>" />
	<p>
	<input type="submit" value="Submit" /></p></form>
<?  

	print "<a href='new_slide.php?slidename=$slide_name&clientname=$client_name'>Add New Page</a>&nbsp;";
        print "<a href='slide_list.php?slidename=$slide_name&clientname=$client_name'>Page List</a>";
}
?>
<? if (isset($_POST['editor1'])) {
	$page=$_POST['page'];
	if (!file_exists($current_slide_path."/".$page)) mkdir($current_slide_path."/".$page,0755);
        $file=fopen($current_slide_path."/".$page."/index.html","w");
        $page_html = "<html>\n"."<head>\n"."<meta http-equiv='Content-Type' content='text-html; charset=UTF-8' />\n"."</head>\n"."<body>\n".$_POST['editor1']."\n</body>\n"."</html>";
	fwrite($file, $page_html);
	print "<h2>Your page is submitted succesfully.</h2>";
	print "The pages added up to now: <br>";
	for ($q=2;$q<count($q_list);$q++) {
		echo $q_list[$q]." , ";
        }
        echo "<br>You have changed page no $page";
        echo "<br>";
	print "<a href='new_slide.php?slidename=$slide_name&clientname=$client_name'>Add New Page</a>&nbsp;";
	print "<a href='slide_list.php?slidename=$slide_name&clientname=$client_name'>Page List</a>";
}
?>
</body>
</html>
