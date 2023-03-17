<?php
    /** DOCU: Temp fix for CPT issues when editing module tab details */
    $view_url = strpos($_SERVER["REQUEST_URI"], "views") ? $_SERVER["REQUEST_URI"] : $_SERVER["HTTP_REFERER"];
    $base_url = explode("views", $view_url)[0];
?>
<?php foreach($module_tabs_json as $module_tab) { ?>
    <div class="section_page_tab" id="tab_<?= $module_tab["id"] ?>">
        <h3 class="tab_title"><?= $module_tab["title"] ?></h3>
        <p id="tab_content_<?= $module_tab["id"] ?>" class="tab_content"><?= $module_tab["content"] ?></p>
        
        <?php if($module_tab["is_comments_allowed"]) { ?>
            <a href="#" data-target="mobile_comments_slideout" class="show_comments_btn sidenav-trigger">Comments (<?= (int) $module_tab["cache_posts_count"] ?>)</a>
            <a class="fetch_tab_posts_btn" href="#tab_posts_<?= $module_tab["id"] ?>" data-tab_id="<?= $module_tab["id"] ?>">Comments</a>
            <div class="tab_comments comment_container">
                <form action="<?= $base_url ?>processes/manage_documentation.php" method="POST" class="add_comment_form add_post_form">
                    <input type="hidden" name="action" value="add_tab_post">
                    <input type="hidden" name="tab_id" class="tab_id" value="<?= $module_tab["id"] ?>">
                    <div class="comment_field">
                        <div class="comment_message_content input-field col s12">
                            <label for="post_comment_<?= $module_tab["id"] ?>">Write a comment</label>
                            <textarea name="post_comment" id="post_comment_<?= $module_tab["id"] ?>" class="materialize-textarea comment_message"></textarea>
                        </div>
                    </div>
                </form>
                <ul class="comments_list"></ul>
            </div>
        <?php } ?>
    </div>
<?php } ?>