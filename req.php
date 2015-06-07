<?php

$mysqli = new mysqli("localhost", "root", "usbw", "test");

if ($mysqli->connect_errno) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
}



function get_tweet_count($username, $settings) 
{
	$url = 'https://api.twitter.com/1.1/users/show.json';
	$getfield = '?screen_name='.$username;
	$requestMethod = 'GET';

	$twitter = new TwitterAPIExchange($settings);
	$jsonResult =  $twitter->setGetfield($getfield)
	    ->buildOauth($url, $requestMethod)
	    ->performRequest();

	$json = json_decode($jsonResult, 1);

	return $json['statuses_count'];
}


function get_tweets($mysqli, $settings, $username) 
{
	$_SESSION['count']++;
	$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';

	if (!empty($_SESSION['max'])) {
		$getfield = sprintf('?screen_name='.$username.'&count=198&include_rts=false&max_id=%s', $_SESSION['max']);
	} else {
		$getfield = '?screen_name='.$username.'&count=198&include_rts=false';
	}

	$requestMethod = 'GET';

	$twitter = new TwitterAPIExchange($settings);
	$jsonResult =  $twitter->setGetfield($getfield)
	    ->buildOauth($url, $requestMethod)
	    ->performRequest();


	$json = json_decode($jsonResult, 1);

	$c = 0;
	foreach ($json as $item) {
		$id = $item['id_str'];
		$time = $item['created_at'];
		$tweet = preg_replace('/@\S+/', 'Sam ', $item['text']);
		$tweets[$c] = [$tweet, $time, $username];

		if (insert_record($mysqli, $tweet, $time, $username)) {
			echo 'Query '.($c+1).' executed successfully!'."\n";
		}
		$c++;
	}

	echo "<hr/>";
	$_SESSION['max'] = $id;
	$removed = array_shift($tweets);
	return $tweets;	
}

function insert_record($mysqli, $tweet, $time, $handle) {
    $q = sprintf("INSERT INTO employees (tweet,created_at,handle) VALUES ('%s', '%s', '%s');", $tweet, $time, $handle);
    if ($mysqli->query($q)) {

        return True;
    }
    return $mysqli->error;
}

function refresh() {
	echo '<script type="text/javascript">setTimeout(function(){}, 5000);window.location.reload();</script>';
}