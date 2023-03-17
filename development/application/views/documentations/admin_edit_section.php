<?php
    include_once("application/views/view_helper.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Company - Employee Handbook | BoomYEAH</title>
    <link rel="shortcut icon" href="<?= add_file("assets/images/favicon.ico") ?>" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <link rel="stylesheet" href="<?= add_file("assets/css/vendor/redactorx.min.css") ?>">
    <link rel="stylesheet" href="<?= add_file("assets/css/global.css") ?>">
    <link rel="stylesheet" href="<?= add_file("assets/css/admin_edit_section.css") ?>">
    <script src="<?= add_file("assets/js/vendor/Sortable.min.js") ?>"></script>
    <script src="<?= add_file("assets/js/vendor/ux.lib.js") ?>"></script>
    <script src="<?= add_file("assets/js/constants.js") ?>"></script>
</head>
<body>
    <div id="main_navigation"><?php $this->load->view("partials/main_navigation.php"); ?></div>
    <div id="wrapper" class="container">
        <div id="edit_section_content">
            <form action="/sections/update" method="POST" id="edit_section_form">
                <input type="hidden" name="section_id" class="section_id" value="<?= $section["id"] ?>">
                <input type="hidden" name="action" value="update_section">
                <input type="hidden" name="update_type" class="update_type" value="description">
                <input type="hidden" name="update_value" class="update_value" value="<?= $section["description"] ?>">
                <div id="section_summary">
                    <div class="breadcrumbs">
                        <ul id="breadcrumbs_list">
                            <li class="breadcrumb_item"><a href="/docs/edit">Documentations</a></li class="breadcrumb_item">
                            <li class="breadcrumb_item"><a href="/docs/<?= $documentation['id'] ?>/edit"><?= $documentation["title"] ?></a></li class="breadcrumb_item">
                            <li class="breadcrumb_item active"><span><?= $section["title"] ?></span></li>
                        </ul>
                        <div class="row_placeholder"></div>
                        <a href="../default_data/preview_section.html" id="preview_section_btn">Preview</a>
                    </div>
                    <div class="section_details">
                        <h1 id="section_title"><?= $section["title"] ?></h1>
                        <div class="add_description">
                            <textarea name="section_short_description" id="section_short_description" placeholder="Add Description"><?= $section["description"] ?></textarea>
                        </div>
                    </div>
                </div>
            </form>
                <div id="section_pages">
                    <?php
                        // foreach($modules as $module){
                            # $modules_array = array("modules" => array($module_data));
                            // if($modules_array){
                                $this->load->view("partials/section_page_content_partial.php", array("modules" => $modules));
                            // }
                            // else{
                            //     //when no data display nothing
                            // }
                        // }
                        // var_dump($modules);
                    ?>
                </div>
            <form id="add_module_form" action="/modules/add" method="POST">
                <input type="hidden" name="action" value="add_module">
                <input type="hidden" name="section_id" value="<?= $section["id"] ?>">
                <button id="add_page_tabs_btn" type="submit">+ Add New</button>
            </form>
            <form id="add_module_tab_form" action="/modules/add_tab" method="POST" class="hidden">
                <input type="hidden" name="action" value="add_module_tab">
                <input type="hidden" name="module_id" class="module_id">
            </form>
            <form id="reorder_tabs_form" action="/modules/reorder_tab" method="POST" class="hidden">
                <input type="hidden" name="action" value="reorder_tabs">
                <input type="hidden" name="module_id" class="module_id">
                <input type="hidden" name="tab_ids_order" class="tab_ids_order">
            </form>
            <div id="clone_section_page">
                <?php $this->load->view("partials/clone_section_page.php"); ?>
            </div>
            <div id="modals_container">
                <?php $this->load->view("partials/confirm_action_modals.php"); ?>
            </div>
        </div>
    </div>
    <script src="<?= add_file("assets/js/vendor/redactorx.min.js") ?>"></script>
    <script src="<?= add_file("assets/js/custom/admin_edit_section/admin_edit_section_fe.js") ?>"></script>
    <script src="<?= add_file("assets/js/custom/admin_edit_section/admin_edit_section_be.js") ?>"></script>
</body>
</html>