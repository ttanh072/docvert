<?php
include_once('core/webpage.php');
$appDir = dirname(__FILE__).DIRECTORY_SEPARATOR;
define('DOCVERT_DIR', $appDir);
define('DOCVERT_CLIENT_TYPE', 'web');
$themes = new Themes;
$themes->drawTheme();
?>
