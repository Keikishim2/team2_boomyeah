document.addEventListener("DOMContentLoaded", () => {    
    let modal = document.querySelectorAll('.modal:not(.invite_modal)');
    M.Modal.init(modal);

    Sortable.create(document.querySelector("#documentations"), {
        onEnd: () => {
            updateDocumentationsOrder(document.querySelector("#documentations"));
        }
    });

    document.addEventListener("click", (event) => {
        event.stopPropagation();
        event.preventDefault();

        let add_invite_element = event.target.closest(".add_invite_result");
        
        if(add_invite_element){
            addSearchEmailResult(add_invite_element);
        }
    });

    /* run functions from invite_modal.js */
    initializeCollaboratorChipsInstance();
    initSelect();
    initializeMaterializeDropdown();

    M.Dropdown.init(ux("#docs_view_btn").self());

    ux("body")
        .on("click", ".active_docs_btn", appearActiveDocumentation)
        .on("click", ".archived_docs_btn", appearArchivedDocumentations)
        .on("click", ".document_block, .edit_title_form, .document_title", redirectToDocumentView)
        .on("click", ".edit_title_icon", toggleEditDocumentationTitle)
        .on("click", ".duplicate_icon", duplicateDocumentation)
        .on("click", ".archive_btn", setArchiveDocumentationValue)
        .on("click", ".set_privacy_btn", setDocumentPrivacyValues)
        .on("click", ".set_to_public_icon, .access_btn", async function(event){
            event.stopImmediatePropagation();
            event.preventDefault();
            showConfirmPrivacyModal( ux(event.target).attr("data-document_id"), 0, "#confirm_to_public", event.target.closest(".document_block") );
        })
        .on("click", ".set_to_private_icon", async function(event){
            event.stopImmediatePropagation();
            event.preventDefault();
            
            showConfirmPrivacyModal(ux(event.target).attr("data-document_id"), 1, "#confirm_to_private", event.target.closest(".document_block") );
        })
        .on("click", ".remove_btn", setRemoveDocumentationValue);

    appearEmptyDocumentation();
});

function duplicateDocumentation(event){
    event.stopImmediatePropagation();
    event.preventDefault();

    let document_id = ux(event.target).data("document_id");
    let duplicate_form = ux("#duplicate_documentation_form");
    duplicate_form.find(".documentation_id").val(document_id);
    duplicate_form.trigger("submit");
    return false;
}

async function showConfirmPrivacyModal(document_id, update_value = 0, modal_type = "#confirm_to_private", document_block){
    let change_document_privacy_form = ux("#change_document_privacy_form");
    change_document_privacy_form.find(".documentation_id").val(document_id);
    change_document_privacy_form.find(".update_value").val(update_value);

    let confirm_modal = document.querySelector(modal_type);
    var instance = M.Modal.getInstance(confirm_modal);

    await displayModalDocumentationTitle(confirm_modal, document_block);
    instance.open();
}

function displayModalDocumentationTitle(confirm_modal, document_block){
    let document_title = ux(document_block).find(".document_title").val();
    ux(confirm_modal).find(".documentation_title").text(document_title);
}

function submitInvite(event){
    event.preventDefault();
}

function appearEmptyDocumentation(){
    let documentations_count = ux("#documentations").self().children.length;
    let archived_documents_count = ux("#archived_documents").self().children.length;
    
    ux(".no_documents").conditionalClass("hidden", (documentations_count >= 2));
    ux(".no_archived_documents").conditionalClass("hidden", (archived_documents_count > 1));
}

function toggleEditDocumentationTitle(event){
    event.stopImmediatePropagation();
    let edit_title_btn = ux(event.target);
    let document_block = edit_title_btn.closest(".document_block");
    let document_title = document_block.find(".document_details .document_title");
    let title_length = document_title.val().length;
    document_block.removeClass("error");

    document_title.self().removeAttribute("readonly");
    document_title.self().setSelectionRange(title_length, title_length);
    
    setTimeout(() => {
        document_title.self().focus();
    });
}

function appearActiveDocumentation(event){
    let active_docs_btn = event.target;
    let container = ux(active_docs_btn.closest(".container"));
    let docs_view_btn = container.find("#docs_view_btn");

    docs_view_btn.text( active_docs_btn.innerText );
    ux("#documentations").removeClass("hidden");
    ux("#archived_documents").addClass("hidden");
    
    /* Update form value */
    ux("#get_documentations_form #is_archived").val("0");
    ux("#get_documentations_form").trigger("submit");
}

function appearArchivedDocumentations(event){
    let archived_docs_btn = event.target;
    let container = ux(archived_docs_btn.closest(".container"));
    let docs_view_btn = container.find("#docs_view_btn");

    docs_view_btn.text(archived_docs_btn.innerText);
    ux("#archived_documents").removeClass("hidden");
    ux("#documentations").addClass("hidden");

    /* Update form value */
    ux("#get_documentations_form #is_archived").val("1");
    ux("#get_documentations_form").trigger("submit");
}

/* Will set values needed for changing a documentation's privacy. Values will be used after clicking 'Yes' on the modal */
function setDocumentPrivacyValues(event){
    const documentation         = event.target;
    const documentation_id      = documentation.getAttribute("data-document_id");
    const documentation_privacy = documentation.getAttribute("data-document_privacy");
    ux("#confirm_to_public").find(".documentation_title").text( ux(event.target).closest(".document_block").find(".document_title").val() );

    /* Set form values */
    let change_document_privacy_form = ux("#change_document_privacy_form");
    
    change_document_privacy_form.find(".documentation_id").val(documentation_id);
    change_document_privacy_form.find(".update_value").val( (documentation_privacy == "public") ? 1 : 0 );
}

function setArchiveDocumentationValue(event){
    let archive_button  = ux(event.target);
    let document_id     = archive_button.attr("data-document_id");
    let document_action = archive_button.attr("data-documentation_action");
    let is_archived     = (document_action == "archive");
    let document_block = archive_button.closest(".document_block");
    let document_title = document_block.find(".document_title").val();
    let confirmation_text = (is_archived) ? "Are you sure you want to move `"+ document_title +"` documentation to Archive?" : "Are you sure you want to Unarchive `"+ document_title +"` documentation?";
    ux("#confirm_to_archive").find("p").text( confirmation_text );
    
    /* Set form values */
    let archive_document_form = ux("#archive_form");
    archive_document_form.find(".documentation_id").val(document_id);
    archive_document_form.find(".update_value").val( (is_archived) ? 1 : 0 );
}

async function setRemoveDocumentationValue(event){
    event.stopImmediatePropagation();

    const documentation = ux(event.target);

    /* Set form values */
    ux("#remove_documentation_form #remove_documentation_id").val(documentation.data("document_id"));
    ux("#remove_documentation_form #remove_is_archived").val(documentation.data("is_archived"));

    let remove_modal = document.querySelector("#confirm_to_remove");
    var instance = M.Modal.getInstance(remove_modal);
    await displayModalDocumentationTitle(remove_modal, event.target.closest(".document_block") );
    instance.open();
}

function redirectToDocumentView(event){
    if(event.target.classList.contains("set_privacy_btn") || 
        event.target.classList.contains("more_action_btn") || 
        event.target.classList.contains("invite_collaborators_btn") || 
        event.target.closest("li")){
            return;
    }

    location.href = "admin_edit_documentation.php";
}

function updateDocumentationsOrder(documentations){
    let documentation_children = documentations.children;
    var new_documentations_order = "";

    /* Get documentation_id from documentation_children */
    for(let index = 0; index < documentation_children.length; index++){
        new_documentations_order += (index == (documentation_children.length - 1)) ? `${documentation_children[index].id.split("_")[1]}` : `${documentation_children[index].id.split("_")[1]},`;
    }

    /* Update form value and submit form */
    ux("#reorder_documentations_form #documentations_order").val(new_documentations_order);
    ux("#reorder_documentations_form").trigger("submit");
}