$(document).ready(async function(){
    
    let modal = document.querySelectorAll('.modal');
    let instances = M.Modal.init(modal);

    const invite_form = document.querySelector("#invite_form");
    invite_form.addEventListener("submit", submitInvite);

    Sortable.create(document.querySelector("#documentations"), {
        onEnd: () => {
            updateDocumentationsOrder(document.querySelector("#documentations"));
        }
    });

    document.addEventListener("click", (event) => {
        event.stopPropagation();
        event.preventDefault();

        let element = event.target.closest(".add_invite_result");
        
        if(element){
            addSearchEmailResult(element);
        }
    });

    appearEmptyDocumentation();

    /* run functions from invite_modal.js */
    initChipsInstance();
    initSelect();
    initializeMaterializeDropdown();

    M.Dropdown.init($("#docs_view_btn")[0]);

    $("body")
        .on("submit", "#add_documentation_form", onSubmitAddDocumentationForm)
        .on("submit", "#get_documentations_form", getDocumentations)
        .on("click", ".archive_btn", setArchiveValue)
        .on("click", ".remove_btn", setRemoveDocumentationValue)
        .on("click", ".edit_title_icon", toggleEditDocumentationTitle)
        .on("click", ".change_privacy_yes_btn", submitChangeDocumentPrivacy)
        .on("click", ".set_privacy_btn", setDocumentPrivacyValues)
        .on("submit", "#change_document_privacy_form", onSubmitChangePrivacy)
        .on("click", ".document_block", redirectToDocumentView)
        .on("click", ".invite_collaborators_btn, .invite_icon", function(event){
            event.stopImmediatePropagation();
            event.preventDefault();
            alert("Invite collaborator feature will be added in v0.2.");
            let invite_modal = document.querySelector("#modal1");
            var instance = M.Modal.getInstance(invite_modal);
            instance.close();
        })
        .on("click", ".active_docs_btn", appearActiveDocumentation)
        .on("click", ".archived_docs_btn", appearArchivedDocumentations)
        .on("click", ".archived_docs_btn", appearArchivedDocumentations)
        .on("click", "#archive_confirm", submitArchive)
        .on("click", "#remove_confirm", submitRemoveDocumentation)
        .on("submit", "#reorder_documentations_form", submitReorderDocumentations)
        .on("click", ".set_to_public_icon, .access_btn", async function(event){
            event.stopImmediatePropagation();
            event.preventDefault();
            showConfirmPrivacyModal( $(this).attr("data-document_id"), 0, "#confirm_to_public", $(this).closest(".document_block"));
        })
        .on("click", ".set_to_private_icon", async function(event){
            event.stopImmediatePropagation();
            event.preventDefault();
            showConfirmPrivacyModal($(this).attr("data-document_id"), 1, "#confirm_to_private", $(this).closest(".document_block"));
        })
        .on("blur", ".document_title", (event) => {
            /** Check if empty title; Revert to old title if empty */
            if(!event.target.hasAttribute("readonly")){
                ux(event.target.closest(".edit_title_form")).trigger("submit");
            } 
        })
        ;
        
});
document.addEventListener("DOMContentLoaded", () => {
    ux("body")
        .on("submit", ".edit_title_form", onChangeDocumentationTitle)
        .on("submit", "#duplicate_documentation_form", onSubmitDuplicateForm)
        .on("click", ".duplicate_icon", duplicateDocumentation)
  
});

function onSubmitDuplicateForm(event){
    event.preventDefault();
    event.stopImmediatePropagation();
    let post_form = $(event.target);
    let document_id = post_form.find(".documentation_id").val();

    /** Use AJAX to generate new documentation */
    $.post(post_form.attr("action"), post_form.serialize(), (post_data) => {
        if(post_data.status){
            // Append duplicated documentation
            $(`#document_${document_id}`).after(post_data.result.html);
     
            let documentation = $(`#document_${post_data.result.documentation_id}`);
            documentation.addClass("animate__animated animate__fadeIn animate__slower");
            documentation.on("animationend", () => {
                documentation.removeClass("animate__animated animate__fadeIn animate__slower");
            });

            initializeMaterializeDropdown();
        }
        else {
            alert(post_data.error);
        }

        post_form[0].reset();
    }, "json");

    return false;  
}

function duplicateDocumentation(event){
    event.stopImmediatePropagation();
    event.preventDefault();

    let document_id = $(event.target).data("document_id");
    let duplicate_form = ux("#duplicate_documentation_form");
    duplicate_form.find(".documentation_id").val(document_id);
    duplicate_form.trigger("submit");
    return false;
}

async function showConfirmPrivacyModal(document_id, update_value = 0, modal_type = "#confirm_to_private", document_block){
    let change_document_privacy_form = $("#change_document_privacy_form");
    change_document_privacy_form.find("#documentation_id").val(document_id);
    change_document_privacy_form.find("#update_value").val(update_value);

    let confirm_modal = document.querySelector(modal_type);
    var instance = M.Modal.getInstance(confirm_modal);

    await displayModalDocumentationTitle($(confirm_modal), document_block);
    instance.open();
}

async function displayModalDocumentationTitle(confirm_modal, document_block){
    let document_title = await document_block.find(".document_title").val();
    confirm_modal.find(".documentation_title").text(document_title);
}

function submitInvite(event){
    event.preventDefault();
}

function onSubmitAddDocumentationForm(event){
    event.preventDefault();
    let add_document_form = $(this);
    const input_document_title = $("#input_add_documentation").val();

    if(input_document_title){
        /** Use AJAX to generate new documentation */
        $.post(add_document_form.attr("action"), add_document_form.serialize(), (response_data) => {
            if(response_data.status){
                /* TODO: Update once the admin edit documentation is added in v2. Change to redirect in admin edit document page. */
                alert("Documentation added succesfully! Redirecting to the admin edit document page will be added in v0.2.");
                $("#add_documentation_form")[0].reset();
                //location.reload();

                let documentations_div = $("#add_documentation_form #is_archived").val() == "1" ? "#archived_documents" : "#documentations";
                $(documentations_div).html(response_data.result.html);
                initializeMaterializeDropdown();
            }
            else{
                alert(response_data.error); 
            }
        }, "json");
        
      
    }
    else{
        let add_documentation_input = $(".group_add_documentation");

        add_documentation_input.addClass("input_error").addClass("animate__animated animate__headShake");
        add_documentation_input.on("animationend", () => {
            add_documentation_input.removeClass("animate__animated animate__headShake");
        });
    }
    return false;
}

function initializeMaterializeDropdown(){
    let elems = document.querySelectorAll('.more_action_btn');
    M.Dropdown.init(elems, {
        alignment: 'left',
        coverTrigger: false,
        constrainWidth: false
    });
}

function appearEmptyDocumentation(){
    let documentations_count = $("#documentations")[0].children.length;

    if(documentations_count < 2){
        $(".no_documents").removeClass("hidden");
    }else{
        $(".no_documents").addClass("hidden");
    }
    
    let archived_documents_count = $("#archived_documents")[0].children.length;
    if(archived_documents_count <= 1){
        $(".no_archived_documents").removeClass("hidden");
    }else{
        $(".no_archived_documents").addClass("hidden");
    }
}

function toggleEditDocumentationTitle(event){
    event.stopImmediatePropagation();
    let edit_title_btn = $(event.target);
    let document_block = edit_title_btn.closest(".document_block");
    let document_title = document_block.find(".document_details .document_title");
    let end = document_title.val().length;
    document_block.removeClass("error");

    document_title[0].removeAttribute("readonly");
    document_title[0].setSelectionRange(end, end);
    
    setTimeout(() => {
        document_title[0].focus();
    });
}

function onChangeDocumentationTitle(event){
    event.preventDefault();
    let edit_doc_title_form = $(event.target);
    let document_title_input = edit_doc_title_form.find(".document_title");
    let parent_document_block = edit_doc_title_form.closest(".document_block");
    parent_document_block.removeClass("error");
    
    if(document_title_input.val()){
        document_title_input.attr("readonly", "");
        let edit_title_form = ux(event.target);

        /** Use AJAX to generate new documentation */
        ux().post(edit_title_form.attr("action"), edit_title_form.serialize(), (response_data) => {
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

        parent_document_block.addClass("input_error").addClass("animate__animated animate__headShake");
        parent_document_block.on("animationend", () => {
            parent_document_block.removeClass("animate__animated animate__headShake");
        });
    }
    return;
}

function appearActiveDocumentation(event){
    let active_docs_btn = event.target;
    let container = $(active_docs_btn).closest(".container");
    let docs_view_btn = $(container).find("#docs_view_btn")[0];

    docs_view_btn.innerText = active_docs_btn.innerText;
    $("#documentations").removeClass("hidden");
    $("#archived_documents").addClass("hidden");
    
    /* Update form value */
    $("#get_documentations_form #is_archived").val("0");
    $("#get_documentations_form").submit();
}

function appearArchivedDocumentations(event){
    let archived_docs_btn = event.target;
    let container = $(archived_docs_btn).closest(".container");
    let docs_view_btn = $(container).find("#docs_view_btn")[0];

    docs_view_btn.innerText = archived_docs_btn.innerText;
    $("#archived_documents").removeClass("hidden");
    $("#documentations").addClass("hidden");

    /* Update form value */
    $("#get_documentations_form #is_archived").val("1");
    $("#get_documentations_form").submit();
}

/* Will set values needed for changing a documentation's privacy. Values will be used after clicking 'Yes' on the modal */
function setDocumentPrivacyValues(event){
    const documentation         = event.target;
    const documentation_id      = documentation.getAttribute("data-document_id");
    const documentation_privacy = documentation.getAttribute("data-document_privacy");
    $("#confirm_to_public").find(".documentation_title").text( $(this).closest(".document_block").find(".document_title").val() );

    /* Set form values */
    let change_document_privacy_form = $("#change_document_privacy_form");
    
    change_document_privacy_form.find("#documentation_id").val(documentation_id);
    change_document_privacy_form.find("#update_value").val( (documentation_privacy == "public") ? 1 : 0 );
}

function onSubmitChangePrivacy(event){
    event.stopImmediatePropagation();
    event.preventDefault();
    let post_form = $(this);
    
    /** Use AJAX to change documentation privacy */
    $.post(post_form.attr("action"), post_form.serialize(), (post_data) => {
        if(post_data.status){
            /* TODO: Improve UX after success updating. Add animation to indication the replace with the updated . */
            $(`#document_${post_data.result.documentation_id}`).replaceWith(post_data.result.html);
            $(`#document_${post_data.result.documentation_id}`).addClass("animate__animated animated_blinkBorder").removeClass("error");
                
            setTimeout(() => {
                $(`#document_${post_data.result.documentation_id}`).removeClass("animate__animated animated_blinkBorder");
                initializeMaterializeDropdown();
            }, 1280);
        }

        post_form[0].reset();
    }, "json");

    return false;
}

function submitChangeDocumentPrivacy(event){
    event.preventDefault();

    $("#change_document_privacy_form").trigger("submit");

    return;
}

function setArchiveValue(event){
    let archive_button  = $(this);
    let document_id     = archive_button.attr("data-document_id");
    let document_action = archive_button.attr("data-documentation_action");
    let is_archived     = (document_action == "archive");
    let document_block = archive_button.closest(".document_block");
    let document_title = document_block.find(".document_title").val();
    let confirmation_text = (is_archived) ? "Are you sure you want to move `"+ document_title +"` documentation to Archive?" : "Are you sure you want to Unarchive `"+ document_title +"` documentation?";
    $("#confirm_to_archive").find("p").text( confirmation_text );
    
    /* Set form values */
    let archive_document_form = $("#archive_form");
    archive_document_form.find("#documentation_id").val(document_id);
    archive_document_form.find("#update_value").val( (is_archived) ? 1 : 0 );
}

function submitArchive(event){
    let archive_document_form      = $("#archive_form");
    let archive_document_form_data = archive_document_form.serialize();

    if($("#archive_form #update_value").val() == "0"){
        archive_document_form_data += `&archived_documentations=${$("#archived_documents .document_block").length - 1}`;
    }

    $.post(archive_document_form.attr("action"), archive_document_form_data, (response_data) => {
        if(response_data.status){
            /* TODO: Improve UX after success updating. Add animation to remove the archived document from the list. */
            let documentation = $(`#document_${response_data.result.documentation_id}`);

            documentation.addClass("animate__animated animate__fadeOut");
            documentation.on("animationend", () => {
                documentation.remove();
            });

            // appearEmptyDocumentation();
            if(response_data.result.hasOwnProperty("no_documentations_html")){
                let documentations_div = (response_data.result.is_archived === "1") ? "#documentations" : "#archived_documents";
    
                $(documentations_div).html(response_data.result.no_documentations_html);
            }
        }
        else{
            /* TODO: Improve UX after error. Add animation red border. */
            alert(response_data.error);
        }
    }, "json");
    
    return;
}

function setRemoveDocumentationValue(event){
    event.stopImmediatePropagation();

    const documentation = $(this);

    /* Set form values */
    $("#remove_documentation_form #remove_documentation_id").val(documentation.data("document_id"));
    $("#remove_documentation_form #remove_is_archived").val(documentation.data("is_archived"));

    let remove_modal = document.querySelector("#confirm_to_remove");
    var instance = M.Modal.getInstance(remove_modal);
    displayModalDocumentationTitle($(remove_modal), $(this).closest(".document_block"));
    instance.open();
}

function submitRemoveDocumentation(event){
    event.stopImmediatePropagation();
    event.preventDefault();

    let form      = $("#remove_documentation_form");
    let form_data = form.serialize(); 
    
    if($("#remove_documentation_form #remove_is_archived").val() == "1"){
        form_data += `&archived_documentations=${$("#archived_documents .document_block").length - 1}`;
    } 

    $.post(form.attr("action"), form_data, (response_data) => {
        if(response_data.status){
            let documentation = $(`#document_${response_data.result.documentation_id}`);
    
            documentation.addClass("animate__animated animate__fadeOut");
            documentation.on("animationend", () => {
                documentation.remove();

                if(response_data.result.hasOwnProperty("no_documentations_html")){
                    let documentations_div = (response_data.result.is_archived === "0") ? "#documentations" : "#archived_documents";
    
                    $(documentations_div).html(response_data.result.no_documentations_html);
                }
            });
        }

    }, "json");

    let remove_modal = document.querySelector("#confirm_to_remove");
    var instance = M.Modal.getInstance(remove_modal);
    instance.close();

    return false;
}

function redirectToDocumentView(event){
    if(event.target.classList.contains("set_privacy_btn") || 
        event.target.classList.contains("more_action_btn") || 
        event.target.classList.contains("invite_collaborators_btn") || 
        event.target.closest("li")){
            return;
    }
    alert("Redirecting to the admin edit document page will be added in v0.2”");
}

function getDocumentations(event){
    event.preventDefault();
    let form = $(this);
    
    $.post(form.attr("action"), form.serialize(), (response_data) => {
        let documentations_div = $("#get_documentations_form #is_archived").val() == "1" ? "#archived_documents" : "#documentations";

        $(documentations_div).html(response_data.result.html);

        $(".remove_btn").on("click", setRemoveDocumentationValue);
        initializeMaterializeDropdown();
    }, "json");

    return false;
}

function updateDocumentationsOrder(documentations){
    let documentation_children = documentations.children;
    var new_documentations_order = "";

    /* Get documentation_id from documentation_children */
    for(let index=0; index < documentation_children.length; index++){
        new_documentations_order += (index == (documentation_children.length - 1)) ? `${documentation_children[index].id.split("_")[1]}` : `${documentation_children[index].id.split("_")[1]},`;
    }

    /* Update form value and submit form */
    $("#reorder_documentations_form #documentations_order").val(new_documentations_order);
    $("#reorder_documentations_form").submit();
}

function submitReorderDocumentations(event){
    event.preventDefault();
    let form = $(this);

    $.post(form.attr("action"), form.serialize(), (response_data) => {
        if(!response_data.status){
            alert("An error occured while reordering documentations!");
        }
    }, "json");

    return false;
}