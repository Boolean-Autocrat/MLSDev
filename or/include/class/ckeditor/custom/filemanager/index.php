<?php
    global $config, $conn;
    require_once(dirname(__FILE__) . '/../../../../common.php');
    ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <title>File Manager</title>
        <link rel="stylesheet" type="text/css" href="styles/reset.css" />
        <link rel="stylesheet" type="text/css" href="styles/filemanager.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $config['baseurl']; ?>/node_modules/jquery.splitter/css/jquery.splitter.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $config['baseurl']; ?>/node_modules/jqueryfiletree/dist/jQueryFileTree.min.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $config['baseurl']; ?>/node_modules/tablesorter/dist/css/theme.default.min.css" />
        <!--[if IE]>
        <link rel="stylesheet" type="text/css" href="styles/ie.css" />
        <![endif]-->
        <?php

    // If you use a session variable, you've got to start the session first (session_start())


        require_once($config['basepath'] . '/include/login.inc.php');
    $login = new login();
    $login_status = $login->loginCheck('Agent');
    if ($login_status === true) {
        echo '<script type="text/javascript">
            var fileRoot = "' . $_SESSION['filemanager_basepath'].$_SESSION['filemanager_pathpart'].'";
            var basePath = "'.$config['basepath'].'";
            var baseUrl = "'.$config['baseurl'].'";
        </script>';
    } else {
        exit();
    }
    ?>
    </head>
    <body>
        <form id="uploader" method="post">
            <h1></h1>
            <div id="uploadresponse"></div>
            <input id="mode" name="mode" type="hidden" value="add" />
            <input id="currentpath" name="currentpath" type="hidden" />
            <input id="newfile" name="newfile" type="file" />
            <button id="upload" name="upload" type="submit" value="Upload">Upload</button>
            <button id="newfolder" name="newfolder" type="button" value="New Folder">New Folder</button>
            <button id="grid" class="ON" type="button" title="Switch to grid view.">&nbsp;</button><button id="list" type="button" title="Switch to list view.">&nbsp;</button>
        </form>
        <div id="splitter">
            <div id="filetree">Files</div>
            <div id="fileinfo"><h1>Select an item from the left.</h1></div>
        </div>

        <!-- <ul id="itemOptions" class="contextMenu">
            <li class="select"><a href="#select">Select</a></li>
            <li class="download"><a href="#download">Download</a></li>
            <li class="rename"><a href="#rename">Rename</a></li>
            <li class="delete separator"><a href="#delete">Delete</a></li>
        </ul> -->
        <script type="text/javascript" src="<?php echo $config['baseurl']; ?>/node_modules/jquery/dist/jquery.min.js"></script>
        <script type="text/javascript">
            /* solution to undefined msie */
            jQuery.browser = {};
            (function () {
                jQuery.browser.msie = false;
                jQuery.browser.version = 0;
                if (navigator.userAgent.match(/MSIE ([0-9]+)\./)) {
                    jQuery.browser.msie = true;
                    jQuery.browser.version = RegExp.$1;
                }
            })();
            /* solution to undefined msie */    
        </script>  
        <script type="text/javascript" src="<?php echo $config['baseurl']; ?>/node_modules/jquery-form/dist/jquery.form.min.js"></script>
        <script type="text/javascript" src="<?php echo $config['baseurl']; ?>/node_modules/jquery.splitter/js/jquery.splitter.js"></script>
        <script type="text/javascript" src="<?php echo $config['baseurl']; ?>/node_modules/jqueryfiletree/dist/jQueryFileTree.min.js"></script>
        <script type="text/javascript" src="<?php echo $config['baseurl']; ?>/node_modules/tablesorter/dist/js/jquery.tablesorter.min.js"></script>
        <script type="text/javascript" src="<?php echo $config['baseurl']; ?>/node_modules/jQuery-Impromptu/dist/jquery-impromptu.min.js"></script>
        
        <script type="text/javascript" src="scripts/filemanager.config.js"></script>
        <script type="text/javascript" src="scripts/filemanager.js"></script>
    </body>
</html>
