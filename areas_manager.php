<?php require_once("auth.php")
//If there are any import vars, they will be placed in a js var
//that is located in areas_manager.head.php
?>

            <script type="text/javascript">
                $(document).ready(fe_areas_manager.loadContent);
            </script>
            <div id="content_areas_manager">
                <div id="areas_manager" >
                    <div id="areasList"></div>
                    <div id="areasSettings"></div>
                </div>
            </div>
