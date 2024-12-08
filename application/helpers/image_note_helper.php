<?php

function saveNoteImage($note, $id)
{
    //use php domDocument object
    $dom = new DOMDocument();
    //read as html
    $dom->loadHTML($note, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    //get img tag from html
    $imgs = $dom->getElementsByTagName('img');

    $sourcePath = '';
    $fileName = '';
    $folder = 'assets/tempFile';
    $sourcePathImgLocal = base_url() . $folder;

    $path = "$folder/";

    if (!file_exists($path)) mkdir(FCPATH . $folder, 0777, true);

    // $path = './filesUpload/alarm_note_image/';
    if (count($imgs) > 0) {
        foreach ($imgs as $img) {
            if ($img && $img->hasAttribute('src')) {
                //get img base64 code from src attribute
                $imgsrc = $img->getAttribute('src');
                
                if (strpos($imgsrc, "assets") <= 0) {
                    $fileName = uniqid();
                    //image name
                    $fullPath = $path . $fileName . '.jpg';

                    //convert base64 image and save to directory
                    file_put_contents($fullPath, base64_decode($imgsrc));

                    //get image path
                    $sourcePath = base_url() . $fullPath;

                    //change src attribute to image
                    $img->setAttribute('src', $sourcePath);
                }
            }
        }
    }

    $result = [
        'dom' => $dom->saveHTML(),
        'path_img' => $sourcePath,
        'file_name' => $fileName . '.jpg',
        'imgs' => count($imgs)
    ];

    return $result;
}
