let toast_timeout = null;
let saving_timeout = null;

document.addEventListener("DOMContentLoaded", () => {
    if(ux("#add_page_tabs_btn").self()){
        ux("body")
            .findAll("#section_pages .tab_content").forEach((tab_content) => {
                initializeRedactor(tab_content);
            })
    }

    ux("body")
        .on("submit", "#edit_section_form", onEditSectionData)
        .on("submit", "#add_module_form", addNewModuleContent)
        .on("submit", "#add_module_tab_form", onAddModuleTab)
        .on("submit", "#remove_tab_form", onConfirmRemoveTab)
        .on("submit", "#reorder_tabs_form", onReorderTabs)
        .on("submit", ".update_module_tab_form", onUpdateModuleTab)
        .on("click", ".section_page_tabs .add_page_btn", addNewTab);
});

function onEditSectionData(event){
    event.stopImmediatePropagation();
    event.preventDefault();
    let post_form = ux(event.target);
    
    ux().post(post_form.attr("action"), post_form.serialize(), (response_data) => {
        if(response_data.status){
            addAnimation(".section_details .add_description", "animated_blinkBorder")
        } else {
            alert("Error saving module data")
        }
    }, "json");
    
    return false;
}
    
function saveTabChanges(section_page_tab){
    clearTimeout(saving_timeout);
    M.Toast.dismissAll();
    
    saving_timeout = setTimeout(() => {        
        clearTimeout(toast_timeout);
        section_page_tab.find(".saving_indicator").addClass("show");
    
        toast_timeout = setTimeout(() => {
            section_page_tab.find(".saving_indicator").removeClass("show");
            M.toast({
                html: "Changes Saved",
                displayLength: 2800,
            });
            
        }, 800);
    }, 480);
}

function addNewModuleContent(event){
    event.preventDefault();
    let section_pages = ux("#section_pages");
    
    let post_form = ux(event.target);
    
    ux().post(post_form.attr("action"), post_form.serialize(), async (response_data) => {
        if(response_data.status){
            let module_id = `#module_${ response_data.result.module_id }`;
            section_pages.append(response_data.result.html);
            addAnimation(ux(module_id).self(), "animate__fadeIn");
            await ux(module_id).find(".section_page_tab").addClass("show");
            ux(module_id).find(".tab_title").self().focus();

            setTimeout(() => {
                initializeRedactor(`${ module_id } .section_page_tab .tab_content`);
            });

            /* Scroll to bottom */
            window.scrollTo(0, document.body.scrollHeight);
        }
    }, "json");
    
    return false
}

function onAddModuleTab(event){
    event.preventDefault();
    let post_form = ux(event.target);
    
    ux().post(post_form.attr("action"), post_form.serialize(), (response_data) => {
        if(response_data.status){
            let module_id = `#module_${ response_data.result.module_id }`;
            let module_tab_id = `.module_tab_${ response_data.result.tab_id }`;
            let tab_id = `#tab_${ response_data.result.tab_id }`;
            let section_page_content = ux(module_id);
            let section_page_tabs = ux(module_id).find(".section_page_tabs");
            let add_page_tab = section_page_tabs.find(".add_page_tab");
            section_page_content.append(response_data.result.html_content);
            section_page_tabs.append(response_data.result.html_tab);
            /** Insert New tab */
            addAnimation(ux(module_tab_id).self(), "animate__fadeIn");
            section_page_tabs.self().append(add_page_tab.self());
            
            setTimeout(() => {
                /** Insert Add page tab btn at the end */
                initializeRedactor(`${tab_id} .tab_content`);
                
                reorderModuleTabs(section_page_tabs.self());
                
                /** Auto click new tab */
                ux(`${module_tab_id} a`).self().click();
            });
        }
    }, "json");
    
    return false
}

function addNewTab(event){
    event.preventDefault();
    let add_module_tab_form = ux("#add_module_tab_form");
    let module_id = ux(event.target).data("module_id");
    add_module_tab_form.find(".module_id").val(module_id);
    add_module_tab_form.trigger("submit");
}

function onUpdateModuleTab(event){
    event.stopImmediatePropagation();
    event.preventDefault();
    
    let post_form = ux(event.target);
    let section_page_tab = post_form.closest(".section_page_tab");
    clearTimeout(saving_timeout);
    M.Toast.dismissAll();
    
    saving_timeout = setTimeout(() => {        
        clearTimeout(toast_timeout);
        section_page_tab.find(".saving_indicator").addClass("show");

        ux().post(post_form.attr("action"), post_form.serialize(), (response_data) => {
            section_page_tab.find(".saving_indicator").removeClass("show");
            let toast_message = (response_data.status) ? "Changes Saved" : "Saving Failed";
            
            toast_timeout = setTimeout(() => {
                M.toast({
                    html: toast_message,
                    displayLength: 2800,
                });
            }, 800);
        }, "json");
    }, 480);
    
    
    return false;
}

function onReorderTabs(event){
    event.stopImmediatePropagation();
    event.preventDefault();
    let post_form = ux(event.target);
    
    ux().post(post_form.attr("action"), post_form.serialize(), (response_data) => {
        if(!response_data.status){
            alert("Error saving tab order")
        }
    }, "json");
    
    return false;
}

function onConfirmRemoveTab(event){
    event.stopImmediatePropagation();
    event.preventDefault();
    
    let post_form = ux(event.target);
    
    ux().post(post_form.attr("action"), post_form.serialize(), (response_data) => {
        if(response_data.status){
        
            /** Do these after form submission */
            let tab_item = ux(`.page_tab_item[data-tab_id="tab_${response_data.result.tab_id}"]`);
            removeModuleTab(tab_item.self());
        }
    }, "json");
    
    return false;
}
    
function removeModuleTab(tab_item){
    let section_page_content = tab_item.closest(".section_page_content");
    let section_page_tabs = tab_item.closest(".section_page_tabs");
    let tab_id = ux(tab_item).data("tab_id");
    let module_id = ux(tab_item).data("module_id");
    let is_active = ux(tab_item).self().classList.contains("active");
    addAnimation(tab_item, "animate__fadeOut");
    addAnimation(ux(`#${tab_id}`).self(), "animate__fadeOut");

    setTimeout(() => {
        ux(`#${tab_id}`).self().remove();
        tab_item.remove();
        
        setTimeout(() => {
            if(ux(section_page_tabs).findAll(".page_tab_item").length === 0){
                section_page_content.remove();
            } else {
                if(is_active){
                    ux(section_page_tabs.querySelectorAll(".page_tab_item")[0]).find("a").self().click();
                }

                reorderModuleTabs(section_page_tabs);
            }
        });
    }, 248);
}
    
function initializeRedactor(selector){
    RedactorX(selector, {
        placeholder: 'Add description',
        editor: {
            minHeight: "360px"
        },
        buttons: {
            context: ['bold', 'italic', 'deleted', 'link'],
            topbar: ['undo', 'redo']
        },
        subscribe: {
            "editor.change" : function(event) {
                ux(selector).closest(".update_module_tab_form").trigger("submit");
            }
        }
    });

    if(typeof Sortable !== "undefined"){
        document.querySelectorAll(".section_page_tabs").forEach((section_tabs_list) => {
            Sortable.create(section_tabs_list, {
                draggable: ".page_tab_item",
                onEnd: () => {
                    reorderModuleTabs(section_tabs_list);
                }
            });
        });
    }
}
function reorderModuleTabs(section_tabs_list){
    let tab_ids = [];
    let module_id = null;
    ux(section_tabs_list).findAll(".page_tab_item").forEach((page_tab_item) => {
        tab_ids.push( ux(page_tab_item).data("tab_id").replace("tab_", "") );
        module_id = ux(page_tab_item).data("module_id");
    });
    let tab_ids_order = tab_ids.join(",");
    let reorder_tabs_form = ux("#reorder_tabs_form");
    reorder_tabs_form.find(".module_id").val(module_id);
    reorder_tabs_form.find(".tab_ids_order").val(tab_ids_order);
    reorder_tabs_form.trigger("submit");
}