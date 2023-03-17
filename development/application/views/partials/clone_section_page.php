<ul id="comments_list_clone">
    <li class="comment_item comment_container">
        <div class="comment_content">
            <img src="https://village88.s3.us-east-1.amazonaws.com/boomyeah_v2/jhaver.png" alt="Jhaver" class="user_image"/>
            <div class="comment_details">
                <h4 class="commenter_name">
                    <span class="user_name">Ben T. Lador</span><span class="posted_at">• a few seconds ago</span><!--
                    --><div class="comment_actions">
                        <button type="button" class="comment_actions_toggle"></button>
                        <div class="comment_actions_menu">
                            <button type="button" class="comment_action_btn edit_btn">Edit</button>
                            <button type="button" class="comment_action_btn remove_btn">Remove</button>
                        </div>
                    </div>
                </h4>
                <p class="comment_message"></p>
            </div>
        </div>
        <div class="reply_actions">
            <button type="button" class="toggle_reply_form_btn" data-target_comment="">Reply</button>
            <button type="button" class="toggle_replies_btn hidden"><b>Show <span class="reply_count"> replies</span></b></button>
        </div>
        <ul class="replies_list comments_list"></ul>
        <form action="/" method="POST" class="add_reply_form add_comment_form">
            <div class="comment_details">
                <img src="https://village88.s3.us-east-1.amazonaws.com/boomyeah_v2/jhaver.png" alt="Jhaver" class="user_image"/>
                <div class="comment_field">
                    <div class="comment_message_content input-field col s12">
                        <label for="post_comment_" data-label_text="Write a reply">Write a reply</label>
                        <textarea name="post_comment_" id="post_comment" class="materialize-textarea comment_message"></textarea>
                    </div>
                </div>
            </div>
        </form>
    </li>
</ul>
<form action="/modules/edit_post" method="POST" class="edit_comment_form">
    <input type="hidden" name="action" value="edit_comment" class="action">
    <input type="hidden" name="post_id" class="post_id">
    <input type="hidden" name="comment_id" class="comment_id">

    <img src="<?= $_SESSION["user_profile_pic"] ?>" alt="<?= $_SESSION["first_name"] ?>" class="user_image"/>
    <div class="comment_contents">
        <div class="comment_field">
            <div class="comment_message_content input-field col s12">
                <textarea name="post_comment" id="post_comment" class="materialize-textarea comment_message"></textarea>
            </div>
        </div>
        <div class="edit_form_btns">
            <div class="row_placeholder"></div>
            <button type="button" class="cancel_btn edit_form_btn">Cancel</button>
            <button type="submit" class="update_btn edit_form_btn">Update</button>
        </div>
    </div>
</form>