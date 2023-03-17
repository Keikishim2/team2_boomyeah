let active_comment_item = null;

document.addEventListener("DOMContentLoaded", async (event) => {
    let document_element = event.target;

    ux("body")
        .on("click", ".fetch_tab_posts_btn", (event) => {
            event.preventDefault();
            let toggle_btn = ux(event.target);
            let tab_id = toggle_btn.data("tab_id");
            
            if(tab_id){
                let fetch_tab_posts_form = ux("#fetch_tab_posts_form");
                fetch_tab_posts_form.find(".tab_id").val(tab_id);
                fetch_tab_posts_form.trigger("submit");
                event.target.removeAttribute("data-tab_id");
            }

            toggle_btn.conditionalClass("open", !toggle_btn.self().classList.contains("open"));
        })
        .on("submit", "#fetch_post_comments_form", onFetchPostComments)
        .on("submit", "#fetch_tab_posts_form", onFetchTabPosts)
        .on("submit", ".add_post_form", onSubmitPostForm)
        .on("submit", ".add_reply_form", onAddPostComment)

        .on("click", ".toggle_replies_btn *", showRepliesList)
        .on("submit", "#remove_comment_form", onConfirmDeleteComment)
        .on("submit", ".edit_comment_form", onSubmitEditForm)
        .on("keydown", ".edit_comment_form .comment_message", onEditMessageKeypress)
        .on("click", ".edit_comment_form .cancel_btn", closeEditCommentForm)
        ;
});

function onSubmitPostForm(event){
    event.stopImmediatePropagation();
    event.preventDefault();
    let post_form = ux(event.target);
    
    ux().post(post_form.attr("action"), post_form.serialize(), async (response_data) => {
        if(response_data.status){
            let tab_id            = `#tab_${response_data.result.tab_id}`;
            let comments_list     = ux(tab_id).find(".tab_comments .comments_list");
            let toggle_btn        = ux(tab_id).find(".fetch_tab_posts_btn");
            let show_comments_btn = ux(tab_id).find(".show_comments_btn");
            let posts_count       = parseInt(toggle_btn.data("cache_posts_count"));

            (toggle_btn.self() && !toggle_btn.self().classList.contains("open")) && fetch_tab_posts_btn.trigger("click");
            
            /* Update cache_posts_count */
            posts_count += 1;
            toggle_btn.attr("data-cache_posts_count", posts_count);
            toggle_btn.html(`Comments (${posts_count})`);
            show_comments_btn.attr("data-cache_posts_count", posts_count);
            show_comments_btn.html(`Comments (${posts_count})`);

            comments_list.prepend(response_data.result.html);
            setTimeout(() => {
            }, 200);

            post_form.self().reset();
            post_form.find(".comment_message").self().blur();
        }

    }, "json");
    
    return false;

}

function onFetchTabPosts(event){
    event.stopImmediatePropagation();
    event.preventDefault();
    let post_form = ux(event.target);
    
    ux().post(post_form.attr("action"), post_form.serialize(), async (response_data) => {
        if(response_data.status){
            let tab_id = `#tab_${response_data.result.tab_id}`;
            addAnimation(ux(tab_id).find(".tab_comments .comments_list").self(), "animate__zoomIn");
            
            setTimeout(() => {
                ux(tab_id).find(".tab_comments .comments_list").prepend(response_data.result.html);
            }, 200);
        }
    }, "json");
    
    return false;
}

function onSubmitEditForm(event){
    event.stopImmediatePropagation();
    event.preventDefault();
    let post_form = ux(event.target);
    
    ux().post(post_form.attr("action"), post_form.serialize(), async (response_data) => {
        if(response_data.status){
            let {post_comment_id, post_id} = response_data.result;
            item_id = `#comment_${post_comment_id}`;

            if(!post_id){
                /** Replace post comment HTML */
                ux(item_id).replaceWith(response_data.result.html);
            } else {
                let response_html = stringToHtmlContent(response_data.result.html);
                let comment_content = ux(response_html).find(".comment_content").self();
                ux(item_id).find(".comment_content").self().replaceWith(comment_content);
                ux(item_id).find(".edit_comment_form").remove();
            }
        }
    }, "json");
    
    return false;
}

function onAddPostComment(event){
    event.stopImmediatePropagation();
    event.preventDefault();
    let post_form = ux(event.target);
    
    ux().post(post_form.attr("action"), post_form.serialize(), async (response_data) => {
        if(response_data.status){
            let comment_id = `#comment_${response_data.result.post_id}`;
            let comments_list = ux(comment_id).find(".replies_list");
            let toggle_replies_btn = ux(comment_id).find(".toggle_replies_btn");

            if(toggle_replies_btn.self()){
                toggle_replies_btn.find("b").trigger("click");
            } else {
                comments_list.append(response_data.result.html);
            }

            post_form.self().reset();
            post_form.find(".comment_message").self().blur();
        }

    }, "json");
    
    return false;
}

function showRepliesList(event){
    event.stopImmediatePropagation();
    let show_replies_btn = event.target.closest(".toggle_replies_btn");
    let comment_item = event.target.closest(".comment_item");
    let replies_list  = ux(comment_item).find(".replies_list");
    let post_id = ux(show_replies_btn).data("target_comment");
    
    if(!replies_list.self().classList.contains("show")){
        addAnimation(replies_list.self(), "animate__zoomIn");

        let post_comments_form = ux("#fetch_post_comments_form");
        post_comments_form.find(".post_id").val(post_id);
        post_comments_form.trigger("submit");
        ux(show_replies_btn).remove();
    }
}

function onFetchPostComments(event){
    event.stopImmediatePropagation();
    event.preventDefault();
    let post_form = ux(event.target);
    
    ux().post(post_form.attr("action"), post_form.serialize(), async (response_data) => {
        if(response_data.status){
            let comment_id = `#comment_${response_data.result.post_id}`;
            addAnimation(ux(comment_id).find(".replies_list").self(), "animate__fadeOut");
            
            setTimeout(() => {
                ux(comment_id).find(".replies_list").addClass("show").prepend(response_data.result.html);
            }, 200);
        }
    }, "json");
    
    return false;
}

function showEditComment(event){
    event.stopImmediatePropagation();
    let event_target = event.target;

    if(event_target.classList.contains("edit_btn")){
        let comment_id = ux(event_target).data("target_comment");
        let is_post = parseInt(ux(event_target).data("is_post"));
        active_comment_item = (CLIENT_WIDTH > MOBILE_WIDTH) ? event_target.closest(".comment_item") : ux(".active_comment_item").self();
        let comment_content = ux(active_comment_item).find(".comment_content");
        let comment_details = comment_content.find(".comment_details").self();
        let comment_message_value = ux(comment_details).find(".comment_message").text();
        
        /** Show edit comment form */
        let edit_comment_form = ux("#clone_section_page .edit_comment_form").clone();
        let edit_comment_id = "post_comment_" + comment_id;
        let comment_message_field = edit_comment_form.find(".comment_message");
        comment_message_field.self().value = comment_message_value;
        comment_message_field.attr("id", edit_comment_id);
        edit_comment_form.find(".action").val((is_post) ? "edit_post" : "edit_comment");
        edit_comment_form.find((is_post) ? ".post_id" : ".comment_id").val(comment_id);

        if((CLIENT_WIDTH > MOBILE_WIDTH)){
            comment_content.self().before(edit_comment_form.self());
            comment_message_field.self().focus();
        } else {
            is_mobile_reply_open = true;
            comment_content.self().before(edit_comment_form.self());
            comment_message_field.self().focus();
            ux(".mobile_tab_comments").addClass("hidden");
        }

        closeCommentActions();
    }
}

function onEditMessageKeypress(event){
    event.stopImmediatePropagation();
    let edit_comment_form = event.target.closest(".edit_comment_form");
    console.log(edit_comment_form);
    if(event.which === KEYS.ENTER){
        event.preventDefault();
        ux(edit_comment_form).find(".update_btn").trigger("click");
        return;
    }
    
    if(event.which === KEYS.ESCAPE){
        /** Close edit form */
        closeEditCommentForm(event);
    }
}

function onCommentMessageKeypress(event){
    event.stopImmediatePropagation();
    let comment_message = event.target;
    let post_form = comment_message.closest(".add_comment_form");
    let edit_comment_form = comment_message.closest(".edit_comment_form");

    if(event.which === KEYS.ENTER){
        event.preventDefault();
        let submit_form = (post_form) ? post_form : edit_comment_form;
        
        ux(submit_form).trigger("submit");
    }
    
    if(edit_comment_form && event.which === KEYS.ESCAPE){
        /** Close edit form */
        closeEditCommentForm(event);
    }
}

function closeEditCommentForm(event){
    let edit_comment_form = ("type" in event) ? event.target.closest(".edit_comment_form") : event;

    /** Close edit form */
    edit_comment_form.remove();
    ux(".mobile_tab_comments").removeClass("hidden");
}

function showRepliesCount(comment_container){
    let comments_list = ux(comment_container).find(".replies_list");

    if(comments_list.self()){
        let reply_count = comments_list.findAll(".comment_item").length;
        let replies_text = reply_count + ` ${(reply_count == 1) ? "reply" : "replies"}`;
        ux(comment_container).find(".reply_count").text(replies_text);
    }
}

function closeCommentActions(){
    ux(document).findAll(".comment_actions_toggle").forEach((element) => ux(element).removeClass("active"));
    ux("#comment_actions_container").removeClass("active");
    (!is_mobile_reply_open && ux(".active_comment_item").self()) && ux(".active_comment_item").removeClass("active_comment_item");
}

function showConfirmaDeleteComment(event){
    event.stopImmediatePropagation();
    let event_target = event.target;

    if(event_target.classList.contains("remove_btn")){
        let is_post = parseInt(ux(event_target).data("is_post"));
        let comment_id = ux(event_target).data("target_comment");
        let remove_comment_modal = ux("#confirm_remove_comment_modal");
        let modal_instance = M.Modal.getInstance(remove_comment_modal);
        modal_instance.open();

        remove_comment_modal.find((is_post) ? ".comment_id" : ".post_id").val("");
        remove_comment_modal.find((is_post) ? ".post_id" : ".comment_id").val(comment_id);
        remove_comment_modal.find(".action").val((is_post) ? "remove_post" : "remove_comment");

        /** Determine active_comment_item */
        active_comment_item = (CLIENT_WIDTH > MOBILE_WIDTH) ? event_target.closest(".comment_item") : ux(".active_comment_item").self();
    }
}

function onConfirmDeleteComment(event){
    event.stopImmediatePropagation();
    event.preventDefault();
    let post_form = ux(event.target);

    ux().post(post_form.attr("action"), post_form.serialize(), async (response_data) => {
        if(response_data.status){
            /** Do these after form submission */
            let comment_container = null;

            if(active_comment_item){
                if(active_comment_item.closest(".replies_list")){
                    comment_container = active_comment_item.closest(".replies_list").closest(".comment_container");
                }

                addAnimation(active_comment_item, "animate__fadeOut");

                closeCommentActions();
                setTimeout(() => {
                    active_comment_item.remove();

                    if(comment_container){
                        showRepliesCount(comment_container);
                    }
                }, 148);
            }
        }
    }, "json");

    return false;
}

async function onSubmitComment(post_form, is_reply = false){
    if(post_form.hasOwnProperty("type")){
        post_form.preventDefault();
        post_form.stopImmediatePropagation();
        post_form = post_form.target;
    }
    let is_mobile_comment = post_form.classList.contains("mobile_add_comment_form");
    let comment_message_field = ux(post_form).find(".comment_message");
    let comment_message = comment_message_field.self().value;
    
    if(comment_message){
        let comment_container = post_form.closest(".comment_container");
        let comment_item = ux("#comments_list_clone .comment_item").clone();
        let comments_list = ux(comment_container).find(".comments_list");
        comment_item.find(".comment_message").text(comment_message);

        if(is_mobile_comment){
            comments_list = ux("#comments_list_container .comments_list");

            if(ux(".active_comment_item").self()){
                comment_container = ux(".active_comment_item").self();
                comments_list = (comment_container.closest(".replies_list")) ? ux(comment_container.closest(".replies_list")) : ux(comment_container).find(".comments_list");
                is_reply = true;
            }
        }
        
        comments_list.self().prepend(comment_item.self());
        addAnimation(comment_item.self(), "animate__zoomIn");

        if(is_reply){
            comment_item.find(".comments_list").self().remove();
            comment_item.find(".add_comment_form").self().remove();
            comment_item.find(".reply_actions .toggle_replies_btn").self().remove();
            
            if(ux(comment_container).find(".toggle_replies_btn").self()){
                ux(comment_container).find(".toggle_replies_btn").self().click();
            }
            
            showRepliesCount(comment_container);
            ux(post_form).find("label").text("Write a reply");
        }

        /** Scroll the mobile comments tab */
        setTimeout(() => {
            ux("#comments_list_container").self().scrollTop = 0;

            if(ux(".active_comment_item").self()){
                ux(".active_comment_item").removeClass("active_comment_item");
                ux(post_form).find("label").text("Write a comment");
                is_mobile_reply_open = false;

                let mobile_list_bounds = comments_list.find(".comment_item").self().getBoundingClientRect();
                ux("#comments_list_container").self().scrollTop = mobile_list_bounds.top - (mobile_list_bounds.height + MOBILE_TOP_OFFSET);
            }
        }, 100);

        post_form.reset();
        comment_message_field.self().blur();
        comment_message_field.self().setAttribute("style","");
    }
    return false;
}