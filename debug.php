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

function generateCheckboxInFront(array $areaNames){
    $i = 0;
    $retArray = Array(sizeof($areaNames));
    foreach ($areaNames as $name){
        $retArray[$i] = '<input type="checkbox" id="' . $name . '">';
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
$checkBoxes = generateCheckboxInFront($possibleAreas);
$arraysToPrint = array();
for ($i=0; $i<sizeof($possibleAreas); $i++){
    if (array_key_exists($i, $possibleAreas) && in_array($possibleAreas[$i], $ne_exclude["ssi"])){
        $possibleAreas[$i] = '<s>' . $possibleAreas[$i] . '</s>';
    }
    $arraysToPrint[$i] = $checkBoxes[$i] . $possibleAreas[$i];
}
printArray($arraysToPrint);
?><button onclick="ne_debug.importAreas()">Import Area</button><button onclick="ne_debug.deleteAreaFile()">delete Area file</button>(only use this if you know what you do!!!)<?php
echo '</form></td>';

echo '</tr></table>';

?>

<hr>
<h3>Sites:</h3><br>

<table><tr>
<td><h4>all Sites:</h4>
<?php
global $ne_all_sites;
global $ne_sites;

printArray($ne_all_sites);
?>
</td>
<td><h4>valid site classes:</h4>
<?php
printArray($ne_sites);
?>
</td>
</tr></table>

<hr>
