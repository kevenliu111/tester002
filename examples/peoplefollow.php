<?php

set_time_limit(0);
date_default_timezone_set('UTC');
require __DIR__.'/../vendor/autoload.php';

$conn = new mysqli('localhost', 'kevenliu', '630901', 'insfl');
if ($conn->connect_error) {
    die("Fail: " . $conn->connect_error);
} 





/////// CONFIG ///////
$username = '';
$password = '';
$debug = true;
$truncatedDebug = false;
$followername = 'instagram';



echo 'peoplefollow Strat!';
$sql = "SELECT username,pwd FROM user WHERE errortimes<3 AND creatstep>=10 AND first_follow=3 ";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
		$username = $row['username'];
		$password = $row['pwd'];
		break;
	}


	$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);
	//$ig->setProxy('http://66.82.144.29:8080');
		
	try {
		$ig->login($username, $password);
	} catch (\Exception $e) {
		echo 'Something went wrong: '.$e->getMessage()."\n";
		exit(0);
	}
	
	$tmpc = false;
	try {
		$userId = $ig->people->getUserIdForName($followername);
		$frendship = $ig->people->follow($userId);
		echo $frendship;
		$tmpc = true;
			
		
		/*$userId = $ig->people->getUserIdForName($shopname);
		$rankToken = \InstagramAPI\Signatures::generateUUID();
		//$frendship = $ig->people->getFriendship($userId);
		$follower = $ig->people->getFollowers($userId,$rankToken);
		$nextmaxid = $follower->getNextMaxId();
		$shopflo = $follower->getUsers();
		foreach ($shopflo as $value)
		{
			$sql = "SELECT username FROM follower WHERE errortimes<3 AND lastsrc<>'".$photosrc."' ";
			$result = $conn->query($sql);
			
			if ($result->num_rows > 0) 
			{
				echo $nextmaxid;
			}
		}*/
		
	} catch (\Exception $e) {
		$tmpc = false;
		echo 'Something went wrong: '.$e->getMessage()."\n";
	}
	
	if ($tmpc){
		$sql = "UPDATE user SET first_follow=1 WHERE username='".$username."'";
		if ($conn->query($sql) === TRUE) {
			echo $followername." IntFollow Success!";
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
	}else{
		$sql = "UPDATE user SET first_follow=0 WHERE username='".$username."'";
		if ($conn->query($sql) === TRUE) {
			echo $followername." IntFollow Success!";
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
	}

}

