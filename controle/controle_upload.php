<?php

require_once '../config/config.php';

$_pathinfo = pathinfo($_FILES['upload']['name']);

$extension = $_pathinfo['extension'];

$_file = $_FILES['upload'];

$filenameorigin = $_file['name'];

$dir_temp = ROOT .'img'. DS .'users'. DS;

$new_name = date('U') .'.'. $extension;

$filesource = $dir_temp . $new_name;

move_uploaded_file($_file['tmp_name'], $filesource);

$src = $_SERVER['HTTP_ORIGIN']; // .'/csa-secretaria/img/users/'. $new_name;
$src .= str_replace('controle/controle_upload.php', '', $_SERVER['SCRIPT_NAME']);
$src .= 'img/users/'. $new_name;

$_result = array(
    'url' => $src
);

//echo json_encode($filesource);
echo json_encode($_result);