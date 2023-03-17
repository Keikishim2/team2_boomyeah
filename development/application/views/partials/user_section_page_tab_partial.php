<?php foreach($tab_ids_order as $tab_ids_index => $tab_id){
    $tab = $module_tabs_json->$tab_id;
?>
    <div class="section_page_tab" id="tab_<?= $tab->id ?>">
        <h3 class="tab_title"><?= $tab->title ?></h3>
        <p id="tab_content_<?= $tab->id ?>" class="tab_content"><?= $tab->content ?></p>
        
<?php if((int) $tab->is_comments_allowed) { ?>
        <a href="#" data-target="mobile_comments_slideout" class="show_comments_btn sidenav-trigger" data-cache_posts_count="<?= $tab->cache_posts_count ?>">Comments (<?= (int) $tab->cache_posts_count ?>)</a>
        <a class="fetch_tab_posts_btn" href="#tab_posts_<?= $tab->id ?>" data-tab_id="<?= $tab->id ?>" data-cache_posts_count="<?= $tab->cache_posts_count ?>">Comments (<?= (int) $tab->cache_posts_count ?>)</a>
        <div class="tab_comments comment_container">
            <form action="/modules/add_post" method="POST" class="add_comment_form add_post_form">
                <input type="hidden" name="action" value="add_tab_post">
                <input type="hidden" name="tab_id" class="tab_id" value="<?= $tab->id ?>">
                <div class="comment_field">
                    <div class="comment_message_content input-field col s12">
                        <label for="post_comment_<?= $tab->id ?>">Write a comment</label>
                        <textarea name="post_comment" id="post_comment_<?= $tab->id ?>" class="materialize-textarea comment_message"></textarea>
                    </div>
                </div>
            </form>
            <ul class="comments_list"></ul>
        </div>
<?php } ?>
    </div>
<?php } ?>