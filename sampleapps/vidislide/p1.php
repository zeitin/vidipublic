<html>
<head>
<script type="text/javascript" src="jquery-1.4.2.js"></script>


<?php $lesson=$_GET['lesson'];
$date=$_GET['date'];
$addr="questions/".$lesson."/".$date; 
?>

<script type="text/javascript">

$(function() {
$(".choose").click(
        function(){
                var a1='<?=$addr?>';
                var ques=$(this).attr("id");
                var a1=a1+"/"+ques+"/ques.html";
              
                window.open(a1,'width=200,height=100');
        });
});
</script>
</head>
<body>
<?php 
echo "<center>";
echo "list of questions are";
echo '<hr color="green">';


//$lesson=$_GET['lesson'];
//$date=$_GET['date'];
//$addr="questions/".$lesson."/".$date;



$arr=scandir("questions/$lesson/$date");
sort($arr);

?>

<table border="1">
<tr>
<th>LESSON</th>
<th>DATE</th>
<th>QUESTIONS</th>
<th>SHOW </th>
</tr>

<?php 
for($i=2;$i<count($arr);$i=$i+1)
{
  echo '<tr><td>'.$lesson.'</td>';
  echo '<td>'.$date.'</td>';?>
    
    <td><?php echo $arr[$i];?> 

    </td>
        <td> <a href="#" id="<?php echo $arr[$i]; ?>" class="choose">view</a></td>
</tr>
<?php 
    
}


?>


</body>
</html>

