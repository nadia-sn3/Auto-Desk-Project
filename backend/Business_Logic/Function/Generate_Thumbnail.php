<?php

require_once 'functions.php';
require_once 'Download_Functions.php';
require_once 'getAccessToken.php';
require_once 'config.php';

if($fileType === 'obj' || $fileType === 'dwg' || $fileType === 'stl')  
{
    

    $job = StartTranslationJob_Thumbnail($access_token, $urn_source_file);

    while(true)
    {
        
        $urnSourceFile_URLSafe = $job['urn'];
        $status = json_decode(CheckJobStatus($access_token, $urnSourceFile_URLSafe), true);
        if($status['status'] == "success")
        {
            break;    
        }
        sleep(30);
    }

    $derivatives= $status['derivatives'][0]['children'][1]['children'];

    foreach($derivatives as $derivative)
    {
        if(str_ends_with($derivative['urn'], ".png"))
            {
                $urnObjFile = $derivative['urn'];
                break;
        }   
    }
    $signedCookie = ObtainSignedCookie($access_token, $urn_source_file, $urnObjFile);

    $cookieListPattern = '/(?<=set-cookie: ).*((?<=HTTPOnly{1})())/im';
    $cookies;
    preg_match_all($cookieListPattern, $signedCookie["header"], $cookies);

    $downloadURL = $signedCookie["body"]["url"];

    $fileNameSaveAs = $project_id.'_'.$fileName.'.png';

    $thumbnailPath = DownloadThumbnail($downloadURL, $cookies[0], $fileNameSaveAs);

    SaveThumbnailData($downloadURL, $thumbnailPath, $project_id);
}
?>