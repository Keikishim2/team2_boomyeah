document.addEventListener("DOMContentLoaded", async () => {
    ux("body")
        .on("click", ".edit_title_icon", editSectionTitle)
        .on("click", ".section_block .section_title.editable", (event) => {
            event.stopImmediatePropagation();
            ux(event.target).closest(".section_block").removeClass("error");
        })
        .on("blur", ".section_block .section_title.editable", disableEditSectionTitle)
        .on("blur", "#document_description", (event) => {
            let update_value = event.target.innerText;
            updateDocumentationData("document_description", encodeURI(update_value));
        })
        .on("click", ".duplicate_icon", duplicateSection)
        .on("click", ".remove_icon", setRemoveSectionBlock)
        .on("click", "#remove_confirm", confirmRemoveSectionBlock)
        .on("click", ".section_block", redirectToEditSection)
        .on("click", ".sort_by", sortSections)
        .on("click", ".toggle_switch", onChangeDocumentationPrivacy)
        .on("keydown", ".section_title", (event) => editSectionTitle(event, true))
        ;

    Sortable.create(document.querySelector(".section_container"), {
        handle: ".drag_handle",
        onEnd: () => {
            updateSectionsOrder(document.querySelector("#section_container"));
        }
    });

    let modal_instances = document.querySelectorAll('.modal');
    M.Modal.init(modal_instances);

    appearEmptySection();
});

function updateDocumentationData(update_type, update_value){
    let udpate_documentation_form = ux("#udpate_documentation_form");
    udpate_documentation_form.find(".update_type").val(update_type);
    udpate_documentation_form.find(".update_value").val(update_value);
    udpate_documentation_form.trigger("submit");
}

function updateSectionsOrder(section_container){
    let section_blocks = ux(section_container).findAll(".section_block");
    let section_id_order = [];
    
    section_blocks.forEach(section_block => {
        section_id_order.push(ux(section_block).find(".section_id").val());
    });

    ux("#reorder_sections_form #sections_order").val(section_id_order.join(","));
    ux("#reorder_sections_form").trigger("submit");
}

function editSectionTitle(event, is_key_down_event = false){
    event.stopImmediatePropagation();
    const edit_btn = event.target;
    const section_blk = ux(edit_btn.closest(".section_block"));
    const section_title = section_blk.find(".section_title");
    section_blk.removeClass("error");

    section_title.addClass("editable");
    section_title.self().setAttribute("contenteditable", "true");
    selectElementText(section_title.self());

    if (is_key_down_event && event.keyCode === 13){
        section_title.self().setAttribute("contenteditable", "false");
        selectElementText(section_title.self());
    }

    setTimeout(() => {
        section_title.self().focus();
    });
}


function disableEditSectionTitle(event){
    let section_title = event.target;
    let section_block = ux(section_title.closest(".section_block"));
    let section_id = section_block.find(".section_id").val();

    if(section_title.innerText.length){
        updateSectionFormSubmit(section_id, "title", section_title.innerText, true);
    } else {
        section_block.addClass("error");
    }
}

function updateSectionFormSubmit(section_id, update_type, update_value, submit_form = false){
    let update_section_form = ux("#update_section_form");
    update_section_form.find(".section_id").val(section_id);
    update_section_form.find(".update_type").val(update_type);
    update_section_form.find(".update_value").val(update_value);

    if(submit_form){
        update_section_form.trigger("submit");
    }
}

function duplicateSection(event){
    event.stopImmediatePropagation();

    let duplicate_btn = event.target;
    let section_block = ux(duplicate_btn.closest(".section_block"));
    let section_id = section_block.find(".section_id").val();
    let duplicate_section_form = ux("#duplicate_section_form");
    duplicate_section_form.find(".section_id").val(section_id);
    duplicate_section_form.trigger("submit");
    return;
}

function setRemoveSectionBlock(event) {
    const section    = event.target;
    const section_id = section.getAttribute("data-section_id");
    const section_title = section.getAttribute("data-section_title");

    document.getElementById("section_title").innerText = section_title;
    document.getElementById("remove_section_id").value = section_id;
    let remove_modal = document.querySelector("#confirm_to_remove");
    var instance = M.Modal.getInstance(remove_modal);
    instance.open();
}

function confirmRemoveSectionBlock(event){
    event.stopImmediatePropagation();
    ux("#remove_section_form").trigger("submit");
}

function redirectToEditSection(event){
    if(event.target.classList.contains("more_action_btn") ||
        event.target.classList.contains("more_action_list") ||
        event.target.classList.contains("remove_icon") ||
        event.target.classList.contains("remove_btn") || 
        event.target.classList.contains("drag_handle") || 
        event.target.closest("li")){
        return;
    }
    
    location.href = "admin_edit_section.html";
}

function appearEmptySection(){
    let section_count = ux("#section_container").findAll(".section_block").length;

    ux(".no_sections").conditionalClass("hidden", (section_count > 0));
}

function initializeMaterializeDropdown(dropdown = null){
    const dropdown_elements = (dropdown) ? dropdown : document.querySelectorAll('.dropdown-trigger');
    
    if(dropdown_elements){
        M.Dropdown.init(dropdown_elements, {
            coverTrigger: false
        });
    }
}

function showMaterializeDropdown(event){
    event.stopImmediatePropagation();
    const dropdown_content = event.target.closest(".section_controls").querySelector(".dropdown-trigger");
    const instance = M.Dropdown.getInstance(dropdown_content);
    instance.open();
}

/** TODO: Rework this function */
function onChangeDocumentationPrivacy(event){
    let toggle_switch = event.target;
    let switch_btn = ux(".switch_btn .toggle_text").self();
    let invite_collaborator_btn = ux("#invite_collaborator_btn");
    let is_private = (toggle_switch.checked) ? 1 : 0;
    invite_collaborator_btn.conditionalClass("hidden", !toggle_switch.checked);
    switch_btn.innerText = (toggle_switch.checked) ? "Private" : "Public";

    const document_title = toggle_switch.closest("#doc_title_access").querySelector("#doc_title").innerText;
    let modal_type = document.querySelector(toggle_switch.checked? "#confirm_to_private" : "#confirm_to_public");

    if(toggle_switch.checked){
        ux(toggle_switch).attr("checked", "");
        showPrivacyModal(modal_type, document_title);
    } else {
        toggle_switch.removeAttribute("checked", "");
        showPrivacyModal(modal_type, document_title);
    } 

    ux("#change_document_privacy_form .update_value").val(is_private);
    ux("#change_document_privacy_form").trigger("submit");
}

function showPrivacyModal(modal_type, document_title){
    M.Modal.getInstance(modal_type);
    var instance = M.Modal.getInstance(modal_type);
    ux(modal_type)
    .on("click", ".no_btn", () => {
        const checkbox = document.querySelector(".toggle_switch");
        checkbox.checked = !checkbox.checked;
    })
    .find(".documentation_title").text(document_title) ;
    instance.open();
}

function sortSections(event){
    let sort_by = ux(event.target).attr("data-sort-by");
    let section_lists = document.getElementById('section_container');
    let section_list_nodes = section_lists.childNodes;
    let section_lists_to_sort = [];

    for (let i in section_list_nodes) {
        (section_list_nodes[i].nodeType == 1) && section_lists_to_sort.push(section_list_nodes[i]);
    }
    
    section_lists_to_sort.sort(function(a, b) {
        return a.innerHTML == b.innerHTML ? 0 : ( sort_by === "az" ? (a.innerHTML > b.innerHTML ? 1 : -1) : (b.innerHTML > a.innerHTML ? 1 : -1) );
    });

    for (let i = 0; i < section_lists_to_sort.length; ++i) {
        section_lists.appendChild(section_lists_to_sort[i]);
    }
}