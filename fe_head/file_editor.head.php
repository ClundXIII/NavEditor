<?php require_once("auth.php"); ?>

        <script type="text/javascript">
            /**
             * Path to root dir.
             * @type String
             */
            var root_path  = "<?php echo ($_SERVER['DOCUMENT_ROOT']); ?>";

            var static_symbols_being_replaced = $.parseJSON('<?php echo(json_encode($ne_config_info['symbols_being_replaced'])); ?>');
            var static_symbols_replacement    = $.parseJSON('<?php echo(json_encode($ne_config_info['symbols_replacement'])); ?>');
            var static_regex_removed_symbols  = <?php echo($ne_config_info['regex_removed_symbols']);?>ig;

        </script>
