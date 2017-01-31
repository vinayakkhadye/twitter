<?php
require_once('twitteroauth.php');
class twitter
{
	public $tweet		= NULL;
	public $userName	= NULL;
	public $userId		= NULL;
	
	public function __construct($userName='',$userId='')
	{
		$consumerKey    = 'A5aQeNrWW34Z2zBwHJkz5MQoc';
		$consumerSecret = 'wyinJiNrpFsyoszWlUXyfANlNW057yhnl1X3MyLJbuCvYMk2Pa';
		$oAuthToken     = '825666395568353280-7xbmXqigwNwBkmnVC3yLMFKam937aDG';
		$oAuthSecret    = 'K3rhGfiVO0nPeK7r2udYAuyoNqZYOckzd69hCrvwDz1UN';
		$this->tweet 	= new TwitterOAuth($consumerKey, $consumerSecret, $oAuthToken, $oAuthSecret);
		$this->userName	= $userName;
		$this->userId	= $userId;
		
	}
	
	public function getFollowerList($userName,$userId)
	{
		try
		{
			
			if(empty($userName) && empty($userId))
			{
				return array('error'=>'please provide user name or user id');
			}

			if($userName)
			{
				$conditon_array['screen_name']	= $userName;
			}
			else if($userId)
			{
				$conditon_array['user_id']	= $userId;
			}		
			
			$followers_list_json	= $this->tweet->get('followers/list', $conditon_array);	
			
			$followers_list_array	= json_decode($followers_list_json,true);
			return $followers_list_array;
		}
		catch (Exception $ex)
		{
			return array('error' => $ex->getMessage());
		}
	}

	public function getUsersLastPost($userName)
	{
		try
		{
			if(empty($userName))
			{
				return array('error'=>'please provide user name');
			}
			$user_time_line = $this->tweet->get('statuses/user_timeline', array('screen_name' => $userName,'cursor' => -1,'count' => 1));	
			$user_time_line = json_decode($user_time_line,true);
			return $user_time_line;
		}
		catch (Exception $ex)
		{
			return array('error' => $ex->getMessage());
		}
	}

	public function getBestDayAndTime()
	{
		try
		{
			$result					= array();
			$followers_list_array	= $this->getFollowerList($this->userName,$this->userId);
			
			if(isset($followers_list_array['users']) && is_array($followers_list_array['users']))
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
					$user_time_line	= $this->getUsersLastPost($val['screen_name']);
					
					if(is_array($user_time_line) && sizeof($user_time_line)>0)
					{
						if(isset($user_time_line[0]['created_at']))
						{
							$created_at_day		= date("l",strtotime($user_time_line[0]['created_at']));
							$created_at_hour	= date("H",strtotime($user_time_line[0]['created_at']));
							
							$user_hourwise_last_post[$created_at_day][$created_at_hour][]	= 1;
							$user_daywise_last_post[$created_at_day][] 						= 1;
						}
					}
				}
			}			
			if(isset($user_hourwise_last_post) && is_array($user_hourwise_last_post) && sizeof($user_hourwise_last_post)>0 
			&& isset($user_daywise_last_post) && is_array($user_daywise_last_post) && sizeof($user_daywise_last_post)>0 )
			{
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
				$result['message'][]	 = 	'Most likely day would be '.$max_posts_day;
				
				$user_hourwise_last_post	= $user_hourwise_last_post[$max_posts_day];
				$max_posts_hour				= "";
				$tmp_max_posts_count		= 0;
				$max_posts_count			= 0;
				
				foreach($user_hourwise_last_post as $key=>$val)
				{
					$tmp_max_posts_count	= count($val);
					if($tmp_max_posts_count	> $max_posts_count)
					{
						$max_posts_count 	= $tmp_max_posts_count;
						$max_posts_hour		= $key;
					}
				}
				$result['message'][]	 = 	'Most likely day time would be '.$max_posts_hour.' to '. ($max_posts_hour+1) .' Hour of day';
			}
			return $result;
		}
		catch (Exception $ex)
		{
			return array('error' => $ex->getMessage());
		}
	}
}


?>