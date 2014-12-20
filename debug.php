<?php require_once("auth.php");

error_reporting(E_ALL & ~E_STRICT);
ini_set('display_errors', 'on');

require_once 'app/classes/AreasManager.php';

?>

<h2>Debug und Fehlersuche sowie -behebung</h2>

<h3>Areas Manager:</h3>

<?php

function isShtml($filename){
    if (strpos($filename, ".shtml") === FALSE) {
        return false;
    } else {
        return true;
    }
}

function printArray(array $toPrint){
    foreach ($toPrint as $element){
        echo $element . "<br>\n";
    }
}

function removeEnding(array $filenames){
    $i = 0;
    $retArray = Array(sizeof($filenames));
    foreach ($filenames as $name){

        $retArray[$i] = str_replace(".shtml", "" , $name);

        $i++;
    }

    return $retArray;
}

function generateImportLink(array $areaNames){
    $i = 0;
    $retArray = Array(sizeof($areaNames));
    foreach ($areaNames as $name){
        $retArray[$i] = '<a href="index.php?p=areas_manager&import=' . $name . '">import Area!</a>';
        $i++;
    }
    return $retArray;
}

$a_m = new AreasManager();

$allLists = $a_m->getAreaList();
$fileList = scandir($ne_config_info['ssi_folder_path']);
$shtmlfileList = array_filter($fileList, "isShtml");

echo '<table><tr><td><h4>All Areas in config file:</h4>';
printArray($allLists);
echo '</td><td><h4>All .shtml config files:</h4>';
printArray($shtmlfileList);
echo '</td><td><h4>Import .shtml files / areas:</h4>';

echo '<form>';

global $ne_exclude;

$possibleAreas = removeEnding($shtmlfileList);
$possibleAreas = array_diff($possibleAreas, $allLists);
sort($possibleAreas);
$checkBoxes = generateImportLink($possibleAreas);
$arraysToPrint = array();
for ($i=0; $i<sizeof($possibleAreas); $i++){
    if (array_key_exists($i, $possibleAreas) && in_array($possibleAreas[$i], $ne_exclude["ssi"])){
        $possibleAreas[$i] = '<s>' . $possibleAreas[$i] . '</s>';
    }
    $arraysToPrint[$i] = $checkBoxes[$i] . $possibleAreas[$i];
}
printArray($arraysToPrint);
?>
</form></td>'

</tr></table>'

<hr>
<h3>Sites:</h3><br>

<table><tr>
<td><h4>all Sites:</h4>
<?php
global $ne_all_sites;
global $ne_sites;

$ne_all_sites_linkarray = array();

for ($i=0; $i<sizeof($ne_all_sites); $i++){
    $ne_all_sites_linkarray[$i] = '<a href="' . $ne_all_sites[$i] . '.php">' . $ne_all_sites[$i] . '.php</a>';
}

printArray($ne_all_sites_linkarray);
?>
</td>
<td><h4>valid site classes:</h4>
<?php

$ne_sites_linkarray = array();

for ($i=0; $i<sizeof($ne_sites); $i++){
    $ne_sites_linkarray[$i] = '<a href="index.php?p=' . $ne_sites[$i] . '">' . $ne_sites[$i] . '</a>';
}

printArray($ne_sites_linkarray);
?>
</td>
</tr></table>

<hr>
