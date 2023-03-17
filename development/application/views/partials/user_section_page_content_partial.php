<?php foreach($modules as $module) {  ?>
    <div class="section_page_content" id="module_<?= $module["module_id"] ?>">
        <ul class="section_page_tabs">
            <?php
                /** DOCU: $views_path is specified for this file in order for the partial be loaded from FE and BE side */
                $this->load->view("partials/user_page_tab_item_partial.php", array("module_tabs_json" => json_decode($module["module_tabs_json"]), "tab_ids_order" => explode(",",$module["tab_ids_order"])));
            ?>
        </ul>
        <?php
            /** DOCU: $views_path is specified for this file in order for the partial be loaded from FE and BE side */
            $this->load->view("partials/user_section_page_tab_partial.php", array("module_tabs_json" => json_decode($module["module_tabs_json"]), "tab_ids_order" => explode(",",$module["tab_ids_order"])));
        ?>
    </div>
<?php } ?>