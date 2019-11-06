<?php
if (!isset($_SESSION))
{
    session_start();
}

//header('Content-type: application/json');

require_once "settings.php";
require_once "gifencoder.php";

//$main = new ApiMain;

$str = strtolower($_GET["request"]);

if ($str == "initprocess")
{
    $PID = time();
    $_SESSION['pid'] = $PID;

    $res = array(
        'PID' => $PID,
        'Success' => True,
        'Message' => 'Process created.'
    );
    print json_encode($res);
}
elseif ($str == 'removeprocess')
{
    session_destroy();    
    $res = array(
        'PID' => $PID,
        'Success' => True,
        'Message' => 'Process removed.'
    );
    print json_encode($res);
}
elseif ($str == "upload")
{
    if (isset($_SESSION['pid']))
    {
        $pid = $_SESSION['pid'];
        $filedata = $_FILES['frames'];
        $files = array();
        $actualFiles = array();
        for ($i=0; $i < count($filedata['name']); $i++)
        {
            array_push($files, $filedata['tmp_name'][$i]);
            array_push($actualFiles, $filedata['name'][$i]);
        }

        $dir = $rootpath . "" . $pid;
        if (!file_exists($dir))
        {
            mkdir($dir);
        }

        $res = array(
            'PID' => $pid,
            'Success' => False,
            'Message' => 'Upload failed.'
        );
        
        for($i=0; $i < count($files); $i++)
        {
            $baseFile = basename($actualFiles[$i]);
            $imageFile = $dir . "/" . $baseFile;
            if (!move_uploaded_file($files[$i], $imageFile))
            {
                print json_encode($res);
                exit;
            }
        }
            
        $res = array(
            'PID' => $pid,
            'Success' => True,
            'Message' => 'Images uploaded.'
        );
        
        print json_encode($res);
    }
    else
    {
        $res = array(
            'PID' => $pid,
            'Success' => False,
            'Message' => 'No process ID.'
        );
        print json_encode($res);
    }
}
elseif ($str == "generategif")
{
    if (isset($_SESSION['pid']))
    {
        $pid = $_SESSION['pid'];
        $images = array();
        $delays = array();
        $dir = $rootpath . "" . $pid . "/";
        $imageFiles = GetImageFiles($dir);
        foreach($imageFiles as $image)
        {
            $frame = imagecreatefromjpeg($dir . "" . $image);
            ob_start();
            imagegif($frame);
            array_push($images, ob_get_contents());
            array_push($delays, 100);
            ob_end_clean();
        }
        $gif = new AnimatedGif($images, $delays, 0);
        $fp = fopen($dir . 'animation.gif', 'w');
        fwrite($fp, $gif->GetAnimation());
        fclose($fp);

        $res = array(
            'PID' => $pid,
            'Success' => True,
            'Message' => 'Gif ready',
            'URL' => ($root . $pid . '/' . 'animation.gif')
        );
        print json_encode($res);
    }
    else
    {
        print ("No process ID.");
    }
}
else
{
}

function GetImageFiles($folder)
{
    $dir = $folder;
    $images = array();
    $a = scandir($dir, 0);
    foreach($a as $f)
    {
        if(strpos(strtolower($f), ".jpg") != FALSE)
        {
            array_push($images, $f);
        }
    }
    return $images;
}
?>