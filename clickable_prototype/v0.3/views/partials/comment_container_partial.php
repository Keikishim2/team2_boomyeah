<?php
    /** DOCU: Temp fix for CPT issues when editing module tab details */
    $view_url = strpos($_SERVER["REQUEST_URI"], "views") ? $_SERVER["REQUEST_URI"] : $_SERVER["HTTP_REFERER"];
    $base_url = explode("views", $view_url)[0];
?>
<?php
    foreach($comment_items as $comment_item) { ?>
<li class="comment_item comment_container" id="comment_<?= isset($comment_item["post_id"]) ? $comment_item["post_id"] : $comment_item["comment_id"] ?>">
    <div class="comment_content">
        <img src="<?= isset($comment_item["post_id"]) ? $comment_item["user_profile_pic"] : $comment_item["commenter_profile_pic"] ?>" 
            alt="<?= isset($comment_item["post_id"]) ? $comment_item["first_name"] : $comment_item["commenter_first_name"] ?>" class="user_image"/> 
        <div class="comment_details">
            <h4 class="commenter_name">
                <span class="user_name"><?= isset($comment_item["post_id"]) ? $comment_item["first_name"] : $comment_item["commenter_first_name"] ?></span><!--
                --><span class="posted_at <?= isset($comment_item["is_edited"]) ? 'edited' : '' ?>">• <?= isset($comment_item["post_id"]) ? $comment_item["date_posted"] : $comment_item["date_commented"] ?></span>
                <div class="comment_actions">
                    <button type="button" class="comment_actions_toggle"></button>
                    <div class="comment_actions_menu">
                        <button type="button" class="comment_action_btn edit_btn" data-is_post="<?= intval(isset($comment_item["post_id"])) ?>" data-target_comment="<?= isset($comment_item["post_id"]) ? $comment_item["post_id"] : $comment_item["comment_id"] ?>">Edit</button>
                        <button type="button" class="comment_action_btn remove_btn" data-is_post="<?= intval(isset($comment_item["post_id"])) ?>" data-target_comment="<?= isset($comment_item["post_id"]) ? $comment_item["post_id"] : $comment_item["comment_id"] ?>">Remove</button>
                    </div>
                </div>
            </h4>
            <p class="comment_message"><?= isset($comment_item["post_id"]) ? $comment_item["message"] : $comment_item["commenter_message"] ?></p>
        </div>
    </div>
    <?php if(isset($comment_item["post_id"])) { ?>
        <div class="reply_actions">
            <button type="button" class="toggle_reply_form_btn" data-target_comment="<?= isset($comment_item["post_id"]) ? $comment_item["post_id"] : $comment_item["comment_id"] ?>">Reply</button>
        <?php if(count($comment_item["comments"])) { ?>
            <button type="button" class="toggle_replies_btn"data-target_comment="<?= isset($comment_item["post_id"]) ? $comment_item["post_id"] : $comment_item["comment_id"] ?>"><b>Show <span class="reply_count"><?= count($comment_item["comments"]) ?> <?= count($comment_item["comments"]) == 1 ? "reply" : "replies" ?></span></b></button>
        <?php }else{ ?>
            <button type="button" class="toggle_replies_btn"data-target_comment="<?= isset($comment_item["post_id"]) ? $comment_item["post_id"] : $comment_item["comment_id"] ?>"><b><span class="reply_count">No</span> replies</span></b></button>
        <?php } ?>
        </div>
        <ul class="replies_list comments_list"></ul>
        <form action="<?= $base_url ?>processes/manage_documentation.php" method="POST" class="add_reply_form add_comment_form">
            <input type="hidden" name="action" value="add_post_comment">
            <input type="hidden" name="post_id" class="post_id" value="<?= $comment_item["post_id"] ?>">
            <div class="comment_details">
                <img src="https://village88.s3.us-east-1.amazonaws.com/boomyeah_v2/jhaver.png" alt="Jhaver" class="user_image"/>
                <div class="comment_field">
                    <div class="comment_message_content input-field col s12">
                        <label for="post_comment_<?= $comment_item["post_id"] ?>">Write a reply</label>
                        <textarea name="post_comment" id="post_comment_<?= $comment_item["post_id"] ?>" class="materialize-textarea comment_message"></textarea>
                    </div>
                </div>
            </div>
        </form>
    <?php } ?>
</li>
<?php } ?>