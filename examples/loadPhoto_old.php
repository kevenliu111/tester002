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
$photosrc = '';
//////////////////////



$sql = "SELECT id,img FROM inscontral_uploadlist WHERE selected=1 ";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $photosrc = $row['img'];
        $photoid = $row['id'];
        break;
    }


    $sql = "SELECT username,pwd FROM user LEFT JOIN uploaddata ON user.username=uploaddata.username AND  WHERE creatstep>=10 AND errortimes<3 AND lastsrc<>'" . $photoid . "' AND ";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        // 杈撳嚭鏁版嵁
        while ($row = $result->fetch_assoc()) {
            $username = $row['username'];
            $password = $row['pwd'];
            break;
        }

        /////// MEDIA ////////
        $photoFilename = __DIR__ . "../../insweb/inscontral/media/" . $photosrc;
        $captionText = "對本店產品有興趣或需要詳細了解更多優惠詳情請追蹤 @urhk.sg🔎
    @urhk.sg有更多熱賣款式
    请聯繫上方ins賬號，本ins帳號僅作推廣帳號，不回覆任何問題🙅🙅圖片均為本店及客人實拍，如需分享請聯繫我們，未經許可請勿分享，感謝大家支持，感謝大家支持持";

        //////////////////////

        echo 'Start!';
        $captionTextin = json_encode($captionText);
        $captionTextin = addslashes($captionTextin);

        //$captionTextin = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', create_function('$matches', 'return iconv("UCS-2BE","UTF-8",pack("H*", $matches[1]));'), $captionText);
        //echo $test;


        /*$ig = new \InstagramAPI\Instagram(true, true, [
            'storage'    => 'mysql',
            'dbhost'     => '127.0.0.1',
            'dbname'     => 'insfl',
            'dbusername' => 'kevenliu',
            'dbpassword' => '630901',
        ]);*/

        $ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);
        //$ig->setProxy('https://206.81.2.4:8118');

        $uploadsu = 1;

        $sql = "SELECT * FROM uploaddata WHERE username='" . $username . "' AND photosrc='" . $photoid . "'";
        $result = $conn->query($sql);
        //echo $sql;
        if ($result->num_rows == 0) {
            //echo $username;
            //echo "____________________________________".$username;
            try {
                $ig->login($username, $password);
                sleep(10);
            } catch (\Exception $e) {
                echo 'Something went wrong: ' . $e->getMessage() . "\n";
                exit(0);
            }

            //echo $photoFilename;
            //echo $captionText;

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
                $photo = new \InstagramAPI\Media\Photo\InstagramPhoto($photoFilename);
                $ig->timeline->uploadPhoto($photo->getFile(), ['caption' => $captionText]);
            } catch (\Exception $e) {
                echo 'Something went wrong: ' . $e->getMessage() . "\n";
            }


            if ($uploadsu == 1) {
                $sql = "INSERT INTO uploaddata (username, photosrc, caption)
                VALUES ('" . $username . "', '" . $photoid . "', '" . $captionTextin . "')";
                if ($conn->query($sql) === TRUE) {
                    echo "Insert Success!";
                    $sql = "UPDATE user SET lastsrc='" . $photoid . "' WHERE username='" . $username . "'";
                    if ($conn->query($sql) === TRUE) {
                        echo "Updata Success!";
                    } else {
                        echo "Error: " . $sql . "<br>" . $conn->error;
                    }
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
            }


        } else {
            echo "Haven user";
            $sql = "UPDATE user SET lastsrc='" . $photoid . "' WHERE username='" . $username . "'";
            if ($conn->query($sql) === TRUE) {
                echo "Updata Success!";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }
    } else {
        echo "None user get!";
    }

}


	

$conn->close();