<?php
/**
 * ajax request handler, for file manager routine
 */

require_once('../auth.php');

$htusers_file = $_SERVER['DOCUMENT_ROOT'] . '/vkdaten/.htusers';
$hthosts_file = $_SERVER['DOCUMENT_ROOT'] . '/vkdaten/hthosts';
$htacc_file_templ_auth = $ne_config_info['app_path'] . 'data/htacc_template_auth';
$htacc_file_templ_host = $ne_config_info['app_path'] . 'data/htacc_template_host';
//$noPerm = "Zugriff verweigert";
$fm = new FileManager();

function htpasswd($pwd) {
    return crypt(trim($pwd), base64_encode(CRYPT_STD_DES));
}

/**
 * modify relative to absolute path.
 * @param String $sPath relative path to modify
 * @return String modified absolute path, or empty string if over root (e.g. $sPath = /../../)
 */
function modifyRelativePath($sPath){
    //add slash if not exist
    if($sPath[0] != '/'){ $sPath = '/'.$sPath;}
    //make full path
    $path = $_SERVER['DOCUMENT_ROOT'] . $sPath;
    //return filtered
    return NavTools::root_filter($path);
}

$service_type = Input::get_post('service');

switch ($service_type) {
    case 'get_file_info':
        $file_path = modifyRelativePath(Input::get_post('file_path'));
        echo(json_encode($fm->getFileInfo($file_path)));
        break;
    case 'create_subfolder':
        $current_path = modifyRelativePath(Input::get_post('current_path'));
        $new_subfolder_name = NavTools::filterSymbols(Input::get_post('new_subfolder_name'));
        $succ = $fm->createSubFolder($current_path, $new_subfolder_name);
        if ($succ === FALSE) {
            echo('0');
        } else {
            echo('1');
        }
        break;
    case 'create_new_file':
        $current_path = modifyRelativePath(Input::get_post('current_path'));
        $new_file_name = NavTools::filterSymbols(Input::get_post('new_file_name'));
        $ext = Input::get_post('extension');
        $succ = $fm->createNewFile($current_path, $new_file_name, $ext);
        if ($succ === FALSE) {
            echo('0');
        } else {
            echo('1');
        }
        break;
    case 'delete_file':
        $fpath = modifyRelativePath(Input::get_post('file_path'));
        $success = $fm->deleteFile($fpath);
        if ($success === FALSE) {
            echo('0');
        } else {
            echo('1');
        }
        break;
    case 'rename':
        $file_path = modifyRelativePath(Input::get_post('current_path'));
        //darf nicht root sein
        if(realpath ($file_path) === realpath($_SERVER['DOCUMENT_ROOT'])){
            echo('Sie duerfen nicht Root-Ordner umbenennen');
            break;
        }

        $new_file_name = NavTools::filterSymbols(Input::get_post('new_name'));
        $success = $fm->renameFile($file_path, $new_file_name);
        if ($success === FALSE) {
            echo("Umbenennen fehlgeschlagen\nfilepath:$file_path\nnew file name:$new_file_name");
        } else {
            echo('1');
        }
        break;
    case 'load_file_content':
        $fpath = modifyRelativePath(Input::get_post('file_path'));
        echo($fm->getFileContent($fpath));
        break;
    case 'save_file_content':
        $fpath = modifyRelativePath(Input::get_post('file_path'));
        $content = Input::get_post('new_content');
        $success = $fm->setFileContent($fpath, $content);
        echo(($success===FALSE)?'Fehlgeschlagen':'Aktualisiert!');
        break;
    case 'check_dup_name'://deprecated?
        $file_path = modifyRelativePath(Input::get_post('file_path'));
        if (file_exists($file_path)) {
            echo('1');
        } else {
            echo('0');
        }
        break;
    case 'delete_folder':
        $folder = modifyRelativePath(Input::get_post('file_path'));
        $un_folders = $ne_config_info['important_folders']; //array('vkdaten', 'ssi', 'css', 'grafiken', 'img', 'js');
        if (is_dir($folder)) {
            if(realpath ($folder) === realpath($_SERVER['DOCUMENT_ROOT'])){
                echo('Root Verzeichnis darf nicht entfernt werden!');
                return;
            }

            foreach ($un_folders as $uf) {
                if (NavTools::startsWith($folder, $_SERVER['DOCUMENT_ROOT'].'/'.$uf, FALSE)) {
                    echo('Dieses Verzeichnis darf nicht entfernt werden!');
                    return;
                }
            }
        }
        if ($fm->deleteFolder($folder)) {
            echo('Done!');
        } else {
            echo('Failed!');
        }

        break;
    case 'get_htusers'://deprecated?
        $ret = array();
        $folder = modifyRelativePath(Input::get_post('folder'));

        // list all htusers
        if (file_exists($htusers_file)) {
            $f1 = file($htusers_file);
            for ($j = 0; $j < count($f1); $j++) {
                list($uname, $passhash) = explode(':', trim($f1[$j]));
                array_push($ret, array(
                    'username' => $uname,
                    'checked' => FALSE
                ));
            }
        }

        // check setted users
        $htacc_file = $folder . '/.htaccess';
        if (file_exists($htacc_file)) {
            $f = file($htacc_file);
            for ($i = 0; $i < count($f); $i++) {
                if (NavTools::startsWith($f[$i], 'Require user', FALSE)) {
                    $user_line = trim(str_replace('Require user', '', trim($f[$i])));
                    $users = explode(' ', $user_line);
                    foreach ($users as $u) {
                        for ($k = 0; $k < count($ret); $k++) {
                            if ($ret[$k]['username'] == $u) {
                                $ret[$k]['checked'] = TRUE;
                            }
                        }
                    }
                    break;
                }
            }
        }
        echo(json_encode($ret));
        break;
    case 'set_htusers': //deprecated?
        $folder = modifyRelativePath(Input::get_post('folder'));
        $htacc_file = $folder . '/.htaccess';
        $user_line = trim($_POST['users']);
        $host_line = trim($_POST['hosts']);
        if ($user_line != '' && $host_line == "") { // just htusers auth
            $htacc = file_get_contents($htacc_file_templ_auth);
            $htacc = str_replace(array('{HTUSERS_PATH}', '{HTUSERS}'), array($htusers_file, $user_line), $htacc);
            file_put_contents($htacc_file, $htacc);
        } elseif ($user_line == "" && $host_line != "") { // just hosts restrict
            $htacc = file_get_contents($htacc_file_templ_host);
            $htacc = str_replace('{HTHOSTS}', $host_line, $htacc);
            file_put_contents($htacc_file, $htacc);
        } elseif ($user_line != "" && $host_line != "") { // both cases
            $htacc1 = file_get_contents($htacc_file_templ_auth);
            $htacc1 = str_replace(array('{HTUSERS_PATH}', '{HTUSERS}'), array($htusers_file, $user_line), $htacc1);
            $htacc2 = file_get_contents($htacc_file_templ_host);
            $htacc2 = str_replace('{HTHOSTS}', $host_line, $htacc2);
            file_put_contents($htacc_file, $htacc1 . "\n" . $htacc2);
        } else { // non cases
            if (file_exists($htacc_file)) {
                @unlink($htacc_file);
            }
        }
        echo('Done!');
        break;
    case 'add_htuser'://deprecated?
        $username = $_POST['username'];
        $password = $_POST['password'];
        $line = $username . ":" . htpasswd($password) . "\n";
        if (!file_exists($htusers_file)) {
            touch($htusers_file);
        }

        if (is_writable($htusers_file)) {
            if (!($fh = fopen($htusers_file, 'a'))) {
                die('Unable to open htusers file!');
            }
            if (fwrite($fh, $line) === FALSE) {
                die('Unable to write to htusers file!');
            }
            fclose($fh);
        }
        break;
    case 'get_hthosts'://deprecated?
        $ret = array();
        $folder = set_htusers(Input::get_post('folder'));
        // list all hosts
        if (file_exists($hthosts_file)) {
            $f1 = file($hthosts_file);
            for ($j = 0; $j < count($f1); $j++) {
                array_push($ret, array(
                    'host' => trim($f1[$j]),
                    'checked' => FALSE
                ));
            }
        }
        // list setted hosts
        $htacc_file = $folder . '/.htaccess';
        if (file_exists($htacc_file)) {
            $f = file($htacc_file);
            for ($i = 0; $i < count($f); $i++) {
                if (NavTools::startsWith($f[$i], 'Allow from', FALSE)) {
                    $host_line = trim(str_replace('Allow from', '', trim($f[$i])));
                    $hosts = explode(' ', $host_line);
                    foreach ($hosts as $h) {
                        for ($k = 0; $k < count($ret); $k++) {
                            if ($ret[$k]['host'] == $h) {
                                $ret[$k]['checked'] = TRUE;
                            }
                        }
                    }
                    break;
                }
            }
        }
        echo(json_encode($ret));
        break;
    default:
        break;
}
?>
