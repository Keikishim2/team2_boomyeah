document.addEventListener("DOMContentLoaded", function(){
    ux("body")
        .on("submit", "#add_documentation_form", onSubmitAddDocumentationForm)
        .on("submit", "#get_documentations_form", getDocumentations)
        .on("submit", "#change_document_privacy_form", onSubmitChangePrivacy)
        .on("submit", "#reorder_documentations_form", submitReorderDocumentations)
        .on("click", "#archive_confirm", submitArchive)
        .on("click", "#remove_confirm", submitRemoveDocumentation)
        .on("click", ".change_privacy_yes_btn", submitChangeDocumentPrivacy)
        .on("blur", ".document_title", (event) => {
            /** Check if empty title; Revert to old title if empty */
            if(!event.target.hasAttribute("readonly")){
                ux(event.target.closest(".edit_title_form")).trigger("submit");
            } 
        })
        .on("submit", ".edit_title_form", onChangeDocumentationTitle)
        .on("submit", "#duplicate_documentation_form", onSubmitDuplicateForm)
});

function onSubmitAddDocumentationForm(event){
    event.preventDefault();
    let add_document_form = ux(event.target);
    const input_document_title = ux("#input_add_documentation").val();

    if(input_document_title){
        /** Use AJAX to generate new documentation */
        ux().post(add_document_form.attr("action"), add_document_form.serialize(), (response_data) => {
            if(response_data.status){
                /* Redirect in admin edit document page. */
                ux("#add_documentation_form").self().reset();
                location.href = "admin_edit_documentation.php?document_title="+ encodeURI(input_document_title);
            }
            else{
                alert(response_data.error);
            }
        }, "json");
        
        return;
    }
    else{
        let add_documentation_input = ux(".group_add_documentation");
        add_documentation_input.addClass("input_error");
        addAnimation(".group_add_documentation", "animate__animated animate__headShake");
    }
}

function getDocumentations(event){
    event.preventDefault();
    let fetch_documents_form = ux(event.target);
    
    ux().post(fetch_documents_form.attr("action"), fetch_documents_form.serialize(), (response_data) => {
        let documentations_div = ux("#get_documentations_form #is_archived").val() == "1" ? "#archived_documents" : "#documentations";

        ux(documentations_div).html(response_data.result.html);

        // $(".remove_btn").on("click", setRemoveDocumentationValue);
        initializeMaterializeDropdown();
    }, "json");

    return false;
}

function onChangeDocumentationTitle(event){
    event.preventDefault();
    let edit_doc_title_form = ux(event.target);
    let document_title_input = edit_doc_title_form.find(".document_title");
    let parent_document_block = edit_doc_title_form.closest(".document_block");
    parent_document_block.removeClass("error");
    
    if(document_title_input.val()){
        document_title_input.attr("readonly", "");

        /** Use AJAX to generate new documentation */
        ux().post(edit_doc_title_form.attr("action"), edit_doc_title_form.serialize(), (response_data) => {
            if(response_data.status){
                /* TODO: Improve UX after success updating of title. Add animation. */
                parent_document_block.addClass("animate__animated animated_blinkBorder").removeClass("error");
                
                setTimeout(() => {
                    parent_document_block.removeClass("animate__animated animated_blinkBorder");
                }, 480);
            }
            else{
                /* TODO: Improve UX after updating empty title. Add animation red border. */
                alert(response_data.error);
            }
        }, "json");
    }
    else{
        parent_document_block.addClass("error");
        let document_block = parent_document_block.attr("id");
        let original_data = edit_doc_title_form.find("[name=original_value]").val();
        document_title_input.self().blur();
        document_title_input.val(original_data);
        addAnimation(`#${document_block}`, "animate__animated animate__headShake");
    }
    return;
}

function onSubmitDuplicateForm(event){
    event.preventDefault();
    event.stopImmediatePropagation();
    let post_form = ux(event.target);
    let document_id = post_form.find(".documentation_id").val();

    /** Use AJAX to generate new documentation */
    ux().post(post_form.attr("action"), post_form.serialize(), (post_data) => {
        if(post_data.status){
            // Append duplicated documentation
            ux(`#document_${document_id}`).after(post_data.result.html);

            let documentation = ux(`#document_${post_data.result.documentation_id}`);
            documentation.addClass("animate__animated animate__fadeIn animate__slower");
            documentation.on("animationend", () => {
                documentation.removeClass("animate__animated animate__fadeIn animate__slower");
            });

            initializeMaterializeDropdown();
        }
        else {
            alert(post_data.error);
        }

        post_form.self().reset();
    }, "json");

    return false;  
}

function submitArchive(event){
    let archive_document_form      = ux("#archive_form");
    let archive_document_form_data = archive_document_form.serialize();

    if(ux("#archive_form .update_value").val() == "0"){
        archive_document_form_data.append("archived_documentations", `${ux("#archived_documents").findAll(".document_block").length - 1}`);
    }

    ux().post(archive_document_form.attr("action"), archive_document_form_data, (response_data) => {
        if(response_data.status){
            /* TODO: Improve UX after success updating. Add animation to remove the archived document from the list. */
            let documentation = ux(`#document_${response_data.result.documentation_id}`);

            documentation.addClass("animate__animated animate__fadeOut");
            documentation.on("animationend", () => {
                documentation.remove();
            });

            // appearEmptyDocumentation();
            if(response_data.result.hasOwnProperty("no_documentations_html")){
                let documentations_div = (response_data.result.is_archived === "1") ? "#documentations" : "#archived_documents";
                ux(documentations_div).html(response_data.result.no_documentations_html);
            }
        }
        else{
            /* TODO: Improve UX after error. Add animation red border. */
            alert(response_data.error);
        }
    }, "json");
    
    return;
}

function submitChangeDocumentPrivacy(event){
    event.preventDefault();

    ux("#change_document_privacy_form").trigger("submit");
    return;
}

function onSubmitChangePrivacy(event){
    event.stopImmediatePropagation();
    event.preventDefault();
    let post_form = ux(event.target);
    
    /** Use AJAX to change documentation privacy */
    ux().post(post_form.attr("action"), post_form.serialize(), (post_data) => {
        if(post_data.status){
            /* TODO: Improve UX after success updating. Add animation to indication the replace with the updated . */
            ux(`#document_${post_data.result.documentation_id}`).replaceWith(post_data.result.html);
            ux(`#document_${post_data.result.documentation_id}`).addClass("animate__animated animated_blinkBorder").removeClass("error");
                
            setTimeout(() => {
                ux(`#document_${post_data.result.documentation_id}`).removeClass("animate__animated animated_blinkBorder");
                initializeMaterializeDropdown();
            }, 1280);
        }

        post_form.self().reset();
    }, "json");

    return false;
}

function submitRemoveDocumentation(event){
    event.stopImmediatePropagation();
    event.preventDefault();

    let remove_form = ux("#remove_documentation_form");
    let form_data   = remove_form.serialize(); 
    
    if(ux("#remove_documentation_form #remove_is_archived").val() == "1"){
        form_data.append("archived_documentations", `${ ux("#archived_documents").findAll(".document_block").length - 1}`);
    } 

    ux().post(remove_form.attr("action"), form_data, (response_data) => {
        if(response_data.status){
            let documentation = ux(`#document_${response_data.result.documentation_id}`);
    
            documentation.addClass("animate__animated animate__fadeOut");
            documentation.on("animationend", () => {
                documentation.remove();

                if(response_data.result.hasOwnProperty("no_documentations_html")){
                    let documentations_div = (response_data.result.is_archived === "0") ? "#documentations" : "#archived_documents";
    
                    ux(documentations_div).html(response_data.result.no_documentations_html);
                }
            });
        }

    }, "json");

    let remove_modal = document.querySelector("#confirm_to_remove");
    var instance = M.Modal.getInstance(remove_modal);
    instance.close();

    return false;
}

function submitReorderDocumentations(event){
    event.preventDefault();
    let reorder_form = ux(event.target);

    ux().post(reorder_form.attr("action"), reorder_form.serialize(), (response_data) => {
        if(!response_data.status){
            alert("An error occured while reordering documentations!");
        }
    }, "json");

    return false;
}