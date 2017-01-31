<?php
require_once('twitterClass.php');
$user_name	= $_GET['user_name'];
$user_id	= $_GET['user_id'];
$tw = new twitter($user_name,$user_id);
$result = $tw->getBestDayAndTime();
if(isset($result['message']))
{
	echo $result['message'][0]." <br />".$result['message'][1];
}

?>