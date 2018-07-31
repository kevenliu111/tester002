<?php

set_time_limit(0);
date_default_timezone_set('UTC');
require __DIR__.'/../vendor/autoload.php';

$conn = new mysqli('127.0.0.1', 'kevenliu', '630901', 'insfl');
if ($conn->connect_error) {
    die("Fail: " . $conn->connect_error);
} 





/////// CONFIG ///////
$username = '';
$password = '';
$debug = true;
$truncatedDebug = false;
$photosrc = '002.jpg';
$upload_propic = 1;
$upload_detail = 1;

//////////////////////

/////// MEDIA ////////
$photoFilename = __DIR__."/../photo/".$photosrc;


$sql = "SELECT username,pwd FROM user WHERE errortimes<3 AND profile=0 AND creatstep>=10 ";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // 杈撳嚭鏁版嵁
    while($row = $result->fetch_assoc()) {
        $username = $row['username'];
		$password = $row['pwd'];
		break;
    }
	
	$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);
	//$ig->setProxy('https://206.81.2.4:8118');
		
	try {
		$ig->login($username, $password);
	} catch (\Exception $e) {
		echo 'Something went wrong: '.$e->getMessage()."\n";
		exit(0);
	}
    sleep(10);
		
	$tryupload=false;
			
	if($upload_detail == 1){
		$url = "";
		$phone = '';
		$name = '';
		$biography = '';
		$email = '';
		$gender = '';
		try {
			// The most basic upload command, if you're sure that your photo file is
			// valid on Instagram (that it fits all requirements), is the following:
			// $ig->timeline->uploadPhoto($photoFilename, ['caption' => $captionText]);
			// However, if you want to guarantee that the file is valid (correct format,
			// width, height and aspect ratio), then you can run it through our
			// automatic photo processing class. It is pretty fast, and only does any
			// work when the input file is invalid, so you may want to always use it.
			// You have nothing to worry about, since the class uses temporary files if
			// the input needs processing, and it never overwrites your original file.
			//
			// Also note that it has lots of options, so read its class documentation!
			//$photo = new \InstagramAPI\Media\Photo\InstagramPhoto($photoFilename);
			//$ig->timeline->uploadPhoto($photo->getFile(), ['caption' => $captionText]);
			$userdetil = $ig->account->getCurrentUser();
			//$userdetil = json_decode($userdetil);
			//echo $userdetil->getUser()->getPhoneNumber();
			//$ig->account->editProfile();
			//$userdetil = $ig->account->getCurrentUser();
			if ($userdetil->getUser()->getExternalUrl()){
				$url = $userdetil->getUser()->getExternalUrl();
			}else{
				$url = 'www.urhk.net';
			}
			if ($userdetil->getUser()->getPhoneNumber()){
				$phone = $userdetil->getUser()->getPhoneNumber();
			}
			
			$name = "Rayban有優惠.請追蹤ins:URHK.SG";
			
			$biography = "此ig帳號僅作推廣帳號，不回覆任何問題🙅🙅
			圖片均為本店及客人真實拍攝
			如有任何疑問請follow店鋪ins:URHK.SG🔎
			更多款式及好評實拍盡在店鋪ins:URHK.SG🔎
			接受香港，台灣，澳門訂單
			店舖網站
			👇👇";
			
			if ($userdetil->getUser()->getEmail()){
				$email = $userdetil->getUser()->getEmail();
			}else{
				$email = "";
			}
			$gender = 3;
			
			
			$ig->account->editProfile(
			$url,
			$phone,
			$name,
			$biography,
			$email,
			$gender);
			$tryupload=true;
			sleep(10);
			//echo $test;
		} catch (\Exception $e) {
			$tryupload=false;
			echo 'Something went wrong: '.$e->getMessage()."\n";
		}	
	
	
	}
	
	
	
	if($upload_propic == 1){
		try {
			
			$ig->account->changeProfilePicture($photoFilename);
			$tryupload=true;
			sleep(10);
		} catch (\Exception $e) {
			$tryupload=false;
			echo 'Something went wrong: '.$e->getMessage()."\n";
		}
	}
	
	if ($tryupload){
		$sql = "UPDATE user SET profile=1 WHERE username='".$username."'";
		if ($conn->query($sql) === TRUE) {
			$userId = $ig->people->getUserIdForName('instagram');
			$frendship = $ig->people->follow($userId);
			echo $frendship;
			echo "Updata Success!";
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
	}
}

$conn->close();