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
$sql = "SELECT username,pwd FROM user WHERE errortimes<3 AND creatstep>=10 AND loginset=1 ";
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
	sleep(10);


	$tmpc = false;
	$listitem = '';
	try {
		$clist = $ig->timeline->getSelfUserFeed();
		$listitem = $clist->getItems();
		$nextid = $clist->getNextMaxId();
		$itemre = [];
        $itemre['date'] = date('y-m-d h:i:s');
        echo 'dddddddddddddddddddddddddddd'.$itemre['date'];
        $itemre['username'] = $username;
        foreach ($listitem as $i => $value) {
            $itemre['content'][$i]['caption'] = urlencode($value->getCaption()->getText());
            //$jsontmp = "{'str':'".$itemre[$i]['caption']."'}";
            //echo $jsontmp;
            //$itemre[$i]['caption'] = json_decode($jsontmp);
            //echo $itemre[$i]['caption'];
            $urltmp = gettype($value->getImageVersions2());
            echo $urltmp;
            $itemre['content'][$i]['img'] = $value->getImageVersions2();

            $itemre['content'][$i]['url'] = $value->getItemUrl();
        }
        $itemre = json_encode($itemre);
        echo $itemre;
		$tmpc = true;
		
		
		
	} catch (\Exception $e) {
		$tmpc = false;
		echo 'Something went wrong: '.$e->getMessage()."\n";
	}
	
	if ($tmpc){
		$sql = "UPDATE user SET loginset=0, appcollectiontmp='".$itemre."' WHERE username='".$username."'";
		if ($conn->query($sql) === TRUE) {
			//echo $followername." IntFollow Success!";
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
	}else{
		$sql = "UPDATE user SET loginset=0 WHERE username='".$username."'";
		if ($conn->query($sql) === TRUE) {
			//echo $followername." IntFollow Success!";
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
	}

}

$conn->close();