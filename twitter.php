<?php

$user_name	= $_GET['user_name'];
$user_id	= $_GET['user_id'];

if(empty($user_name) && empty($user_id))
{
	echo 'please provde user name or user id';
	exit;
}

set_time_limit ( -1 );
$consumerKey    = 'A5aQeNrWW34Z2zBwHJkz5MQoc';
$consumerSecret = 'wyinJiNrpFsyoszWlUXyfANlNW057yhnl1X3MyLJbuCvYMk2Pa';
$oAuthToken     = '825666395568353280-7xbmXqigwNwBkmnVC3yLMFKam937aDG';
$oAuthSecret    = 'K3rhGfiVO0nPeK7r2udYAuyoNqZYOckzd69hCrvwDz1UN';

# API OAuth
require_once('twitteroauth.php');

$tweet = new TwitterOAuth($consumerKey, $consumerSecret, $oAuthToken, $oAuthSecret);


$conditon_array			= array('cursor' => -1,'count' => 20);

if($user_name)
{
	$conditon_array['screen_name']	= $user_name;
}
else if($user_id)
{
	$conditon_array['user_id']	= $user_id;
}

$followers_list_json	= $tweet->get('followers/list', $conditon_array);
$followers_list_array	= json_decode($followers_list_json,true);

if(isset($followers_list_array['users']) && is_array($followers_list_array['users']) && sizeof($followers_list_array['users'])>0)
{
	foreach($followers_list_array['users'] as $key=>$val)
	{
		$short_followers_list[$key]['id']					= $val['id'];
		$short_followers_list[$key]['id_str']				= $val['id_str'];
		$short_followers_list[$key]['name']					= $val['name'];
		$short_followers_list[$key]['screen_name']			= $val['screen_name'];
		$short_followers_list[$key]['created_at']			= $val['created_at'];
		$short_followers_list[$key]['profile_image_url']	= $val['profile_image_url'];
	}

	foreach($short_followers_list as $key=>$val)
	{
		$user_time_line = $tweet->get('statuses/user_timeline', array('screen_name' => $val['screen_name'],'cursor' => -1,'count' => 1));
		$user_time_line = json_decode($user_time_line,true);
		if(is_array($user_time_line) && sizeof($user_time_line)>0)
		{
			if(isset($user_time_line[0]['created_at']))
			{
				$created_at_day = date("l",strtotime($user_time_line[0]['created_at']));
				$created_at_hour= date("H",strtotime($user_time_line[0]['created_at']));
				
				$user_hourwise_last_post[$created_at_day][$created_at_hour][] = 1;
				$user_daywise_last_post[$created_at_day][] = 1;
			}
		}
	}
	#print_r($user_hourwise_last_post);
	#print_r($user_daywise_last_post);
	
	$max_posts_day		= "";
	$tmp_max_posts_count= 0;
	$max_posts_count	= 0;
	
	foreach($user_daywise_last_post as $key=>$val)
	{
		$tmp_max_posts_count = count($val);
		if($tmp_max_posts_count>$max_posts_count)
		{
			$max_posts_count = $tmp_max_posts_count;
			$max_posts_day	= $key;
		}
	}
	echo 'Most likely day would be '.$max_posts_day.' <br />';
	//var_dump($max_posts_day,$max_posts_count);
	
	$user_hourwise_last_post = $user_hourwise_last_post[$max_posts_day];
	$max_posts_hour			= "";
	$tmp_max_posts_count	= 0;
	$max_posts_count		= 0;
	
	foreach($user_hourwise_last_post as $key=>$val)
	{
		$tmp_max_posts_count = count($val);
		if($tmp_max_posts_count>$max_posts_count)
		{
			$max_posts_count = $tmp_max_posts_count;
			$max_posts_hour	= $key;
		}
	}
	//var_dump($max_posts_hour,$max_posts_count);
	echo 'Most likely day time would be '.$max_posts_hour.' to '. ($max_posts_hour+1) .' Hour of day';
}	
?>
