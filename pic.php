<?php

date_default_timezone_set('Asia/Shanghai');
$date = $_POST['date'];
if (empty($date)) {
    echo json_encode([
        'code' => -1,
        'msg' => "请指定时间",
        'data' => [],
    ]);
    die;
}
$path = "pics/warning/{$date}";

$files = [];
if (is_dir($path)) {
    $handler = scandir($path);
    foreach ($handler as $filename) {
        if ($filename != '.' && $filename != '..') {
            if (is_dir($path . '/' . $filename)) {
                scanFile($path . '/' . $filename);
            } else {

                $files["{$date} ".substr($filename, 0, -4)] = $path."/".$filename;
                // $files["{$date} ".substr($filename, 0, -4)] = base64EncodeImage($path."/".$filename);//转为base64
            }
        }
    }
}

if (!empty($files)) {
    echo json_encode([
        'code' => 0,
        'msg' => "成功",
        'data' => $files,
    ]);
} else {
    echo json_encode([
        'code' => -1,
        'msg' => "无数据",
        'data' => [],
    ]);
}

//将图片转为base64
function base64EncodeImage($image_file) {
    $base64_image = '';
    $image_info = getimagesize($image_file);
    $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
    $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
    return trim($base64_image);
}