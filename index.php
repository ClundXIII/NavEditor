<?php
require_once ('auth.php');

if ($loginResult == 'FAIL'){
    Header('Location: login.php');
    ///exit *should* prevent to have anonymous access
    exit;
}

// check if first run
$fpath =  $ne_config_info['user_data_file_path'];
if(! file_exists($fpath)) {
    header('Location: aktivierung.php');
} else {
    if (!isset($_GET["p"])){
        $site_class = "dashboard";
    }
    else{
        if (in_array($_GET["p"], $ne_sites)){
            //we got a valid site class here!
            $site_class = $_GET["p"];
        } elseif (in_array ($_GET["p"], $ne_redirect_sites)){
            header('Location: ' . $_GET["p"] . ".php");
        } elseif($_GET["p"] === ""){
            $site_class = "dashboard";
        } else{
            $site_class = "not_found";
        }
        /*switch($_GET["p"]){
            case "dashboard":
            case "areas_manager":
            case "nav_editor":
            case "user_manager":
            case "file_editor":
            case "credits":
            case "licence":
            case "help_forum_blog":
            case "help_special_faq":
            case "help_details":
            case "help_using":
            case "website_editor":
            case "conf_editor":
            case "ma_editor":
            case "design_editor":
            case "debug":
                $site_class = $_GET["p"];
                break;

            case "remove_caches":
            case "update":
            case "logout":
                header('Location: ' . $_GET["p"] . ".php");
                exit;
                break;

            case "":
                $site_class = "dashboard";
                break;

            default:
                $site_class = "not_found";
                break;
        }*/
    }

}

global $is_admin;
global $ne_user_roles;

//by default the visitor is not allowed to visit this site:
$allowedToVisit = false;

if (isset($is_admin) && $is_admin){
    $allowedToVisit = true;
}
else {
    if (key_exists($site_class, $ne_user_site_persmissions)){
        if ($ne_user_site_persmissions[$site_class] == '0'){
            //Public sites:
            $allowedToVisit = true;
        }
        elseif ($ne_user_roles[$ne_user_site_persmissions[$site_class]] <= $ne_user_site_persmissions[$g_current_user_permission]) {
            //user has the rights:
            $allowedToVisit = true;
        }
    }
}

if (!$allowedToVisit) {
    //if not allowed, view the forbidden site:
    $site_class = "forbidden";
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php

            //if there is no site name inside the config, just print the default name:
            if (key_exists($site_class, $ne_site_info['site_name'])){
                echo($ne_site_info['site_name'][$site_class]);
            } else {
                echo($ne_site_info['site_name']["default"]);
            }
            ?> - <?php echo($ne_config_info['app_titleplain']); ?></title>

        <?php
        echo NavTools::includeFE($site_class);
        ?>

            <?php
                $json_php_filename = $ne_config_info['fe_head_folder_name'] . "/" . $site_class . ".head.php";
                //This file may not exist, so we won't force including:
                if (file_exists($json_php_filename))
                    include($json_php_filename);
            ?>

    </head>

    <body id="areas_manager nav_editor bd_User">
        <div class="dashboard" id="wrapper dashboard">
            <div id="navBar">
                <?php require('common_nav_menu.php'); ?>
            </div>


    <?php require($site_class . ".php"); ?>

        </div>

        <?php require('common_footer.php'); ?>

    </body>

</html>
