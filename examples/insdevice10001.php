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
$shopname = '';
$shopid = '';
$userid = '';

echo 'start!';

$sql = "SELECT * FROM user WHERE errortimes<3 AND creatstep>=10 AND followset=1 ";
echo $sql;
$result = $conn->query($sql);
if ($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
        $userid = $row['id'];
		$username = $row['username'];
		$password = $row['pwd'];
		break;
	}

    $sql = "SELECT * FROM inscontral_shopdata WHERE disable=1 ";
	echo $sql;
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $shopid = $row['id'];
            $shopname = $row['shopname'];
            break;
        }

        $ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);
        //$ig->setProxy('http://66.82.144.29:8080');

        try {
            $ig->login($username, $password);
        } catch (\Exception $e) {
            echo 'Something went wrong: ' . $e->getMessage() . "\n";
            exit(0);
        }

        $tmpc = false;
        try {
            /*$userId = $ig->people->getUserIdForName($followername);
            $frendship = $ig->people->follow($userId);
            echo $frendship;
            $tmpc = true;*/
            $userId = $ig->people->getUserIdForName($shopname);
            $breakfalse = true;

            while ($breakfalse) {
                $nextmaxid = null;
                $rank_token = null;
                $sql = "SELECT * FROM inscontral_shopfollowertoken WHERE shopid='" . $shopid . "' AND userid='" . $userid . "' ORDER BY id DESC";
                echo $sql;
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $nextmaxid = $row['nextmaxid'];
                        $rank_token = $row['rank_token'];
                        break;
                    }

                    if ($nextmaxid != '') {
                        $follower = $ig->people->getFollowers($userId, $rank_token, null, $nextmaxid);
                        $nextmaxid = $follower->getNextMaxId();
                    } else {
                        $breakfalse = false;
                    }
                } else {
                    $rank_token = \InstagramAPI\Signatures::generateUUID();
                    //$frendship = $ig->people->getFriendship($userId);
                    $follower = $ig->people->getFollowers($userId, $rank_token);
                    $nextmaxid = $follower->getNextMaxId();
                }


                if ($breakfalse) {
                    $sql = "INSERT INTO inscontral_shopfollowertoken (add_date, rank_token, nextmaxid, shopid, userid)
            VALUES ('" . date('y-m-d h:i:s') . "', '" . $rank_token . "', '" . $nextmaxid . "', '" . $shopid . "', '" . $userid . "')";
                    echo $sql;
                    if ($conn->query($sql) === TRUE) {
                        echo "Insert Success!";
                        $insertid = $conn->insert_id;

                        $shopflo = $follower->getUsers();

                        foreach ($shopflo as $value) {
                            $sql = "INSERT INTO inscontral_followerdata (add_date, sftid, followername	, followdata)
            VALUES ('" . date('y-m-d h:i:s') . "', '" . $insertid . "', '" . $value->getUsername() . "', '" . json_encode($value) . "' )";
                            echo $sql;
                            if ($conn->query($sql) === TRUE) {
                                echo "Insert Success!";
                            } else {
                                echo "Error: " . $sql . "<br>" . $conn->error;
                            }
                            echo $value->getUsername();
                        }
                    } else {
                        echo "Error: " . $sql . "<br>" . $conn->error;
                    }
                }

                $sql = "SELECT * FROM inscontral_shopdata WHERE disable=1 AND id=".$shopid;
                echo $sql;
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {

                }else{
                    $breakfalse = false;
                }


                
                sleep(10);
            }
        } catch (\Exception $e) {
            $tmpc = false;
            echo 'Something went wrong: ' . $e->getMessage() . "\n";
        }

    }
}

$conn->close();