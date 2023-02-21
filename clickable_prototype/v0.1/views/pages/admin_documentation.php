<?php
    session_start();

    // Sample admin session
    $_SESSION["user_id"]       = 1;
    $_SESSION["user_level_id"] = 9;
    $_SESSION["workspace_id"]  = 1;
    // END

    include_once("../view_helper.php");  
    include_once("../../config/connection.php");
    include_once("../../config/constants.php");
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
    <title>Boom Yeah | Admin Documentation Page</title>
    <link rel="shortcut icon" href="<?= add_file("assets/images/favicon.ico") ?>" type="image/x-icon">
    <link rel="stylesheet" href="<?= add_file("assets/css/global.css") ?>">
    <link rel="stylesheet" href="<?= add_file("assets/css/admin_documentation.css") ?>">
    <!--Import Google Icon Font-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <script src="<?= add_file("assets/js/vendor/jquery-3.6.3.min.js") ?>"></script>
    <script src="<?= add_file("assets/js/vendor/Sortable.min.js") ?>"></script>
</head>

<body>
    <!--- Add #main_navigation --->
    <div id="main_navigation"><?= include_once("../partials/main_navigation.php") ?></div>
    <!--- Add #invite_modal --->
    <div id="invite_modal"><?= include_once("../partials/invite_modal.php") ?></div>
    <div id="wrapper">
        <div class="container">
            <form action="../view_prototype.php" id="add_documentation_form" method="POST">
                <div class="group_add_documentation input-field">
                    <input id="input_add_documentation" type="text" class="validate" name="documentation[title]" autofocus>
                    <label for="input_add_documentation">Add Documentation</label>
                </div>
                <span id="save_status" hidden>Saving...</span>
            </form>
            <div class="section_header">
                <button id="docs_view_btn" class="dropdown-trigger" data-target="docs_view_list">Documentations</button>
                <ul id='docs_view_list' class='dropdown-content'>
                    <li><a href="#!" class="active_docs_btn">Documentations</a></li>
                    <li class="divider" tabindex="-1"></li>
                    <li><a href="#!" class="archived_docs_btn">Archived</a></li>
                </ul>
            </div>
            <div id="documentations">
<?php
    $documentations_order = fetch_record("SELECT documentations_order FROM workspaces WHERE id = {$_SESSION["workspace_id"]};");
    $documentations_order = $documentations_order["documentations_order"];

    $documentations = fetch_all("SELECT id, title, is_archived, is_private, cache_collaborators_count
        FROM documentations
        WHERE workspace_id = {$_SESSION["workspace_id"]} AND is_archived = {$_NO}
        ORDER BY FIELD (id, {$documentations_order});
    ");
?>

<?php
    for($documentations_index = 0; $documentations_index < count($documentations); $documentations_index++){
        $documentation = $documentations[$documentations_index];
?>
    <div id="document_<?= $documentation["id"] ?>" class="document_block">
        <div class="document_details">
            <input type="text" name="document_title" value="<?= $documentation["title"] ?>" id="" class="document_title" readonly="">
<?php if($documentation["is_private"] == "1"){ ?>
            <button class="invite_collaborators_btn modal-trigger" href="#modal1"> <?= $documentation["cache_collaborators_count"] ? $documentation["cache_collaborators_count"] : "0" ?></button>
<?php } ?>
        </div>
        <div class="document_controls">
<?php if($documentation["is_private"] == "1"){ ?>
            <button class="access_btn modal-trigger set_privacy_btn" href="#confirm_to_public" data-document_id="<?= $documentation["id"] ?>" data-document_privacy="private"></button>
<?php } ?>
            <button class="more_action_btn dropdown-trigger" data-target="document_more_actions_<?= $documentation["id"] ?>">⁝</button>
            <!-- Dropdown Structure -->
            <ul id="document_more_actions_<?= $documentation["id"] ?>" class="dropdown-content more_action_list_private">
                <li class="edit_title_btn"><a href="#!" class="edit_title_icon">Edit Title</a></li>
                <li class="divider" tabindex="-1"></li>
                <li><a href="#!" class="duplicate_icon">Duplicate</a></li>
                <li class="divider" tabindex="-1"></li>
                <li><a href="#confirm_to_archive" class="archive_icon modal-trigger archive_btn" data-document_id="<?= $documentation["id"] ?>" data-documentation_action="archive">Archive</a></li>
<?php if($documentation["is_private"] == "1"){ ?>
                <li class="divider" tabindex="-1"></li>
                <li><a href="#modal1" class="invite_icon modal-trigger">Invite</a></li>
<?php } ?>
                <li class="divider" tabindex="-1"></li>
<?php if($documentation["is_private"] == "1"){ ?>
                <li><a href="#confirm_to_public" class="set_to_public_icon modal-trigger set_privacy_btn" data-document_id="<?= $documentation["id"] ?>" data-document_privacy="private">Set to Public</a></li>
<?php } else { ?>
                <li><a href="#confirm_to_private" class="set_to_private_icon modal-trigger set_privacy_btn" data-document_id="<?= $documentation["id"] ?>" data-document_privacy="private">Set to Private</a></li>
<?php } ?>
                <li class="divider" tabindex="-1"></li>
                <li><a href="#confirm_to_remove" class="remove_icon modal-trigger remove_btn" data-document_id="<?= $documentation["id"] ?>" data-documentation_action="remove">Remove</a></li>
            </ul>
        </div>
    </div>
<?php
    }
?>
                <?php
                    for($document_index = 1; $document_index <= 10; $document_index++){
                        $documentation_data = array(
                            "id" => $document_index,
                            "title" => "Title ". $document_index,
                            "is_archived" => FALSE,
                            "is_private" => ($document_index % 3 != 0),
                            "cache_collaborators_count" => 10
                        );

                        load_view("../partials/document_block_partial.php", $documentation_data);
                    }
                ?>
                <div class="no_documents hidden">
                    <img src="https://village88.s3.us-east-1.amazonaws.com/boomyeah_v2/empty_illustration.png"
                        alt="Empty Content Illustration">
                    <p>You have no documentations yet</p>
                </div>
            </div>
            <div id="archived_documents" class="hidden">
                <!-- Prepend query results here -->
                <div class="no_archived_documents hidden">
                    <img src="https://village88.s3.us-east-1.amazonaws.com/boomyeah_v2/empty_illustration.png"
                        alt="Empty Content Illustration">
                    <p>You have no archived documentations yet</p>
                </div>
            </div>
        </div>
    </div>
    <?php include_once("../partials/confirm_documentation_modals.php"); ?>
    <!--JavaScript at end of body for optimized loading-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script src="<?= add_file("assets/js/main_navigation.js") ?>"></script>
    <script src="<?= add_file("assets/js/admin_documentation.js") ?>"></script>
    <script src="<?= add_file("assets/js/invite_modal.js") ?>"></script>
    <script src="<?= add_file("assets/js/hotkeys.js") ?>"></script>
</body>

</html>