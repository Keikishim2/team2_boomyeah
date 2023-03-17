<?php
    session_start();

    // Sample admin session
    $_SESSION["user_id"]       = 1;
    $_SESSION["user_level_id"] = 9;
    $_SESSION["workspace_id"]  = 1;
    // END

    include_once("../../processes/partial_helper.php");  
    include_once("../view_helper.php");  
    include_once("../../config/connection.php");
    include_once("../../config/constants.php");
    $document_title = (isset($_GET["document_title"])) ? htmlspecialchars_decode( $_GET["document_title"] ) : "Employee Handbook";
    
    /** TODO: Backend should provide the $document_id */
    $document_id = time() + rand();
    $document_data = array(
        "document_id" => $document_id,
        "document_title" => $document_title,
        "document_description" => "This handbook replaces and supersedes all prior employee handbooks regarding employment or HR matters effective January 01, 2021. The policies and practices included in this handbook may be modified at any time.
        Your department has additional specific procedures for many of the general policies stated in the handbook. You are expected to learn your department's procedures and comply with them. You are also expected to conform to the professional standards of your occupation. Please direct any questions to your supervisor, department head, or to the Human Resources Management and Development Office.",
        "is_private" => TRUE
    );

    //load initial data from json file
    $section_data_file_path = "../../assets/json/sections_data.json";
    $sections_data = load_json_file($section_data_file_path);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="UX Team 2">
    <meta name="description" content="A great way to describe your documentation tool">
    <title>Boom Yeah | Admin Edit Documentation Page</title>
    <link rel="shortcut icon" href="<?= add_file("assets/images/favicon.ico") ?>" type="image/x-icon">
    <link rel="stylesheet" href="<?= add_file("assets/css/global.css") ?>">
    <link rel="stylesheet" href="<?= add_file("assets/css/admin_edit_documentation.css") ?>">
    <!--Import Google Icon Font-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <script src="<?= add_file("assets/js/vendor/Sortable.min.js") ?>"></script>
    <script src="<?= add_file("assets/js/vendor/ux.lib.js") ?>"></script>
    <script src="<?= add_file("assets/js/constants.js") ?>"></script>
</head>
<body>

    <!--- Add #main_navigation --->
    <div id="main_navigation"><?php include_once("../partials/main_navigation.php"); ?></div>
    <!--- Add #invite_modal --->
    <div id="invite_modal"><?php include_once("../partials/invite_modal.php"); ?></div>
    <div id="wrapper">
        <div class="container">
            <div id="documentations_container">
                <div id="documentation_details">
                    <div class="documentation_placeholder"></div>
                    <div class="documentation_header_container">
                        <div class="documentation_header">
                            <ul id="breadcrumb_list">
                                <li class="breadcrumb_item"><a href="admin_documentation.php">Documentation</a></li>
                                <li class="breadcrumb_item active"><?= $document_data["document_title"] ?></li>
                            </ul>
                            <div class="divider"></div>
                            <div id="doc_title_access">
                                <h1 id="doc_title"><?= $document_data["document_title"] ?></h1>
                                <!-- Switch -->     
                                <div class="switch switch_btn">
                                    <label for="set_privacy_switch">
                                        <span class="toggle_text"><?= $document_data["is_private"] ? "Private" : "Public" ?></span>
                                        <input class="toggle_switch" type="checkbox" id="set_privacy_switch" <?= $document_data["is_private"] ? "checked" : "" ?>>
                                        <span class="lever"></span>
                                    </label>
                                </div>
                                <a id="invite_collaborator_btn" class="invite_collaborators_btn waves-effect waves-light btn" href="#invite_collaborator_modal" data-document_id="<?= $document_data["document_id"] ?>">13 Collaborators</a>
                            </div>
                            <p class="doc_text_content" id="document_description" contenteditable="true" data-placeholder="Add Description"><?= $document_data["document_description"] ?></p>
                            <form action="<?= BASE_FILE_URL ?>processes/manage_documentation.php" id="section_form" method="post">
                                <input type="hidden" name="action" value="create_section">
                                <div class="group_add_section input-field">
                                    <button id="submit_add_section_btn" type="submit"></button>
                                    <input name="section_title" id="input_add_section" type="text" class="section_title validate" autofocus>
                                    <label for="input_add_section">Add Section</label>
                                </div>
                            </form>
                            <div class="section_header">
                                <h2>Sections</h2>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="sections_content">
                    <div class="section_container" id="section_container">
                        <?php
                            if(count($sections_data["fetch_section_admin_data"])){
                                foreach($sections_data["fetch_section_admin_data"] as $section_data){
                                    load_view("../partials/section_block_partial.php", $section_data);
                                }
                            }
                            else{
                                //display if no sections
                            }
                        ?>
                    </div>
                    <div class="no_sections hidden">
                        <img src="https://village88.s3.us-east-1.amazonaws.com/boomyeah_v2/empty_illustration.png"
                            alt="Empty Content Illustration">
                        <p>You have no sections yet</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="confirmation_modal_remove">
        <div id="confirm_to_remove" class="modal">
            <div class="modal-content">
                <h4>Confirmation</h4>
                <p>Are you sure you want to remove “<span id="section_title_to_remove"></span>”? This will also remove all the modules and tabs in the section.</p>
            </div>
            <div class="modal-footer">
                <a href="#!" class="modal-close waves-effect btn-flat no_btn">No</a>
                <a href="#!" id="remove_confirm" class="modal-close waves-effect btn-flat yes_btn">Yes</a>
            </div>
        </div>
    </div>
    <form id="remove_section_form" action="<?= BASE_FILE_URL ?>processes/manage_documentation.php" method="POST" hidden>
        <input type="hidden" name="action" value="remove_section">
        <input type="hidden" id="remove_section_id" name="section_id" class="section_id">
    </form>
    <form id="update_section_form" action="<?= BASE_FILE_URL ?>processes/manage_documentation.php" method="POST" hidden>
        <input type="hidden" name="section_id" class="section_id" value="">
        <input type="hidden" name="action" value="update_section">
        <input type="hidden" name="update_type" class="update_type" value="">
        <input type="hidden" name="update_value" class="update_value" value="">
    </form>
    <form id="duplicate_section_form" action="<?= BASE_FILE_URL ?>processes/manage_documentation.php" method="POST" hidden>
        <input type="hidden" name="section_id" class="section_id" value="">
        <input type="hidden" name="action" value="duplicate_section">
    </form>
    <form id="change_document_privacy_form" action="<?= BASE_FILE_URL ?>processes/manage_documentation.php" method="POST" hidden>
        <input type="hidden" name="documentation_id" class="documentation_id" value="<?= $document_data["document_id"] ?>">
        <input type="hidden" name="action" value="update_documentation_privacy">
        <input type="hidden" name="update_type" value="is_private">
        <input type="hidden" name="update_value" class="update_value" value="">
    </form>
    <form id="udpate_documentation_form" action="<?= BASE_FILE_URL ?>processes/manage_documentation.php" method="POST" hidden>
        <input type="hidden" name="documentation_id" class="documentation_id" value="<?= $document_data["document_id"] ?>">
        <input type="hidden" name="action" value="update_documentation_data">
        <input type="hidden" name="update_type" class="update_type" value="">
        <input type="hidden" name="update_value" class="update_value" value="">
    </form>
    <form id="reorder_sections_form" action="<?= BASE_FILE_URL ?>processes/manage_documentation.php" method="POST">
        <input type="hidden" name="action" value="reorder_sections">
        <input type="hidden" id="sections_order" name="sections_order">
    </form>
    <?php include_once("../partials/confirm_invite_modals.php"); ?>
    <?php include_once("../partials/confirm_documentation_modals.php"); ?>
    <!--JavaScript at end of body for optimized loading-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script src="<?= add_file("assets/js/custom/admin_edit_documentation/admin_edit_documentation_fe.js") ?>"></script>
    <script src="<?= add_file("assets/js/custom/admin_edit_documentation/admin_edit_documentation_be.js") ?>"></script>
    <script src="<?= add_file("assets/js/invite_modal.js") ?>"></script>
    <script src="<?= add_file("assets/js/hotkeys.js") ?>"></script>
</body>
</html> 