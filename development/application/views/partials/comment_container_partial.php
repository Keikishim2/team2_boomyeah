<?php
    $this->load->helper("datetime");

    foreach($comment_items as $comment_item) { 
?>
<li class="comment_item comment_container" id="comment_<?= isset($comment_item["post_id"]) ? $comment_item["post_id"] : $comment_item["comment_id"] ?>">
    <div class="comment_content">
        <img src="<?= isset($comment_item["post_id"]) ? $comment_item["user_profile_pic"] : $comment_item["commenter_profile_pic"] ?>" 
            alt="<?= isset($comment_item["post_id"]) ? $comment_item["first_name"] : $comment_item["commenter_first_name"] ?>" class="user_image"/> 
        <div class="comment_details">
            <h4 class="commenter_name">
                <span class="user_name"><?= isset($comment_item["post_id"]) ? $comment_item["first_name"] : $comment_item["commenter_first_name"] ?></span>
                <span class="posted_at <?= (int)$comment_item["is_edited"] ? 'edited' : '' ?>">• <?= isset($comment_item["post_id"]) ? time_ago($comment_item["date_posted"]) : $comment_item["date_commented"] ?></span>
<?php if($_SESSION["user_id"] == $comment_item["user_id"]){ ?>
                <div class="comment_actions">
                    <button type="button" class="comment_actions_toggle"></button>
                    <div class="comment_actions_menu">
                        <button type="button" class="comment_action_btn edit_btn" data-is_post="<?= intval(isset($comment_item["post_id"])) ?>" data-target_comment="<?= isset($comment_item["post_id"]) ? $comment_item["post_id"] : $comment_item["comment_id"] ?>">Edit</button>
                        <button type="button" class="comment_action_btn remove_btn" data-target_comment="<?= isset($comment_item["post_id"]) ? $comment_item["post_id"] : $comment_item["comment_id"] ?>">Remove</button>
                    </div>
                </div>
<?php }?>
            </h4>
            <p class="comment_message"><?= isset($comment_item["post_id"]) ? $comment_item["message"] : $comment_item["commenter_message"] ?></p>
        </div>
    </div>
    <div class="reply_actions">
        <button type="button" class="toggle_reply_form_btn" data-target_comment="<?= isset($comment_item["post_id"]) ? $comment_item["post_id"] : $comment_item["comment_id"] ?>">Reply</button>
        <?php if(isset($comment_item["post_id"])) { ?>
        <button type="button" class="toggle_replies_btn"data-target_comment="<?= isset($comment_item["post_id"]) ? $comment_item["post_id"] : $comment_item["comment_id"] ?>"><b>Show <span class="reply_count"> replies</span></b></button>
        <?php } ?>
    </div>
    <?php if(isset($comment_item["post_id"])) { ?>
        <ul class="replies_list comments_list"></ul>
        <form action="#" method="POST" class="add_reply_form add_comment_form">
            <input type="hidden" name="action" value="add_post_comment">
            <input type="hidden" name="post_id" class="post_id" value="<?= $comment_item["post_id"] ?>">
            <div class="comment_details">
                <img src="<?= isset($comment_item["post_id"]) ? $comment_item["user_profile_pic"] : $comment_item["commenter_profile_pic"] ?>" class="user_image"/>
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