<?php
/*
1、将此文件放到安装包或补丁包下 和ecshop、appserver同一级目录
2、检测时 先将安装包或补丁包解压后 放到生产环境的ecshop目录下（路径例如:ecshop/patch文件夹/check_file.php） 执行下此文件 
目的：检测客户生产环境文件是否有二开，防止客户系统有二开，也不清楚改了哪些文件，升级覆盖文件时，导致二开文件被覆盖掉
*/
set_time_limit(0);

$extension = array('php','html','js');
$dir = array('ecshop', 'appserver');
$pre = '../../';

echo('<meta charset="utf-8">');
echo("<pre>");
define('IN_ECS', true);
if (file_exists($pre.'ecshop/includes/cls_ecshop.php')) {
    require_once $pre.'ecshop/includes/cls_ecshop.php';
}else{
    echo("错误：请确认补丁包或安装包文件夹放置的目录是否按说检测要求说明文档里描述的那样放置");
    exit();
}

$versionListUrl = 'http://update.shopex.com.cn/version/program/ECShop/ecshop_version_list.txt';
$version_content = file_get_contents($versionListUrl);
$version_content = array_filter(explode("\n",$version_content));
$all_version = array();
$app_version = $h5_version = $old_version = NULL;
foreach ($version_content as $key => $value) {
    $decode_value = json_decode($value,1);
    $all_version[] = $decode_value['date'];
}
// sort($all_version, SORT_NUMERIC);
sort($all_version);
if (file_exists($pre.'appserver/version.txt')) {
    $app_version = file_get_contents($pre.'appserver/version.txt');
    $old_version = $app_version;
}else{
    echo("注意：正式环境下appserver目录下version.txt没有找到\r\n");echo("<br>");
}
if (file_exists($pre.'ecshop/h5/version.txt')) {
    $h5_version = file_get_contents($pre.'ecshop/h5/version.txt');
    $old_version = $h5_version;
}else{
    echo("注意：正式环境下ecshop/h5目录下version.txt没有找到\r\n");echo("<br>");
}

if ($app_version == $h5_version and $h5_version == RELEASE) {
    
}else{
    echo("注意：ecshop的version、appserver的version、h5的version不完全一致\r\n");echo("<br>");
}

$new_version = file_get_contents('appserver/version.txt');

$version = '';
echo('<div style="color: red; font-size: 20; font-weight: bolder;">');
if ($old_version) {
    if (array_search( $new_version, $all_version) - array_search( $old_version, $all_version ) != 1) {
        echo("请确认准备覆盖的包是否和当前版本是相邻的，只高一个版本的包");
    }
    $version = $old_version;
}elseif (RELEASE) {
    if (array_search( $new_version, $all_version) - array_search( RELEASE, $all_version ) != 1) {
        echo("请确认准备覆盖的包是否和当前版本是相邻的，只高一个版本的包");
    }
    $version = RELEASE;
}
echo('</div><div style="color: red; font-size: 20; font-weight: bolder;">下面检测将参照版本号: ' . $version . '</div>');

echo("开始检测：\r\n");echo("<br>");
$checkFileUrl = 'http://update.shopex.com.cn/version/program/ECShop/checkfile/'.$version.'.txt';
$serializeFile = file_get_contents($checkFileUrl);
$fileArr = unserialize($serializeFile);

$patchFileList = array();
// $patchFileList = listFiles($dir, $extension, true);
$patchFileList = listFiles($dir, $extension);
// print_r($patchFileList);
$modified = false;
foreach ($patchFileList as $file) {
    $currentFile = $pre.$file;
    if (file_exists($currentFile)) {
        if (md5_file($currentFile) == $fileArr[$file]) {
            // echo($file . " is not modified"); 
            // echo("\r\n");
        }else{
            // echo('<meta charset="utf-8">');
            echo('<div style="color: orangered; font-size: 20; font-weight: 300;">' . $file . "</div> has been modified！ \r\n");
            $modified = true;
        }
    }else{
        echo('<div style="color: red; font-size: 20; font-weight: bolder;">' . $file . '</div> was not found, please confirm');
    }
}
if ($modified) {
    echo("以上文件和官网对应版本安装包的文件比对不完全一致，请确认！\r\n");echo("<br>");
}else{
    echo("当前站点文件和官网对应版本安装包的文件一致 \r\n");echo("<br>");
}
echo("检测结束\r\n");echo("<br>");

function listFiles( $from = '.', $extension = array(), $md5_file = NULL)
{
    if(!$from || !$extension)
        return false;
    if (is_array($from)) {
        $dirs = $from;
    }elseif (is_dir($from)) {
        $dirs = array($from);
    }else{
        return false;
    }
    $files = array();
    
    while( NULL !== ($dir = array_pop( $dirs)))
    {
        if( $dh = opendir($dir))
        {
            while( false !== ($file = readdir($dh)))
            {
                if( $file == '.' || $file == '..')
                    continue;
                $path = $dir . '/' . $file;
                clearstatcache();
                if( is_dir($path))
                    $dirs[] = $path;
                else
                    if (in_array(pathinfo($path,PATHINFO_EXTENSION), $extension)) {
                        if ($md5_file) {
                                $files[$path] = md5_file($path);
                        }else{
                            $files[] = $path;
                        }
                    }
            }
            closedir($dh);
        }
    }
    return $files;
}
?>