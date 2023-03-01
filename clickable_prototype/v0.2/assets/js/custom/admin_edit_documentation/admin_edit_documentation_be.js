document.addEventListener("DOMContentLoaded", () => {
    ux("body")
        .on("submit", "#section_form", onSubmitAddSectionForm)
        .on("submit", "#update_section_form", onSubmitUpdateSectionForm)
        .on("submit", "#duplicate_section_form", onSubmitDuplicateSectionForm)
        .on("submit", "#remove_section_form", onSubmitRemoveSectionForm)
        .on("submit", "#change_document_privacy_form", onSubmitChangePrivacy);
});

function onSubmitChangePrivacy(event){
    event.stopImmediatePropagation();
    event.preventDefault();
    let post_form = ux(event.target);
    
    /** Use AJAX to change documentation privacy */
    ux().post(post_form.attr("action"), post_form.serialize(), (post_data) => {
        if(post_data.status){
            
        }

        post_form.self().reset();
    }, "json");

    return false;
}

function onSubmitRemoveSectionForm(event){
    event.preventDefault();
    let post_form = ux(event.target);
    
    ux().post(post_form.attr("action"), post_form.serialize(), async (response_data) => {
        if(response_data.status){
            let section_id = response_data.result.section_id;
            let section_block = ux(`#section_${section_id}`);
            section_block.addClass("animate__animated animate__fadeOut");
            section_block.on("animationend", () => {
                section_block.remove();
                appearEmptySection();
            });
        } else {

        }
    }, "json");

    return false;
}

function onSubmitDuplicateSectionForm(event){
    event.preventDefault();
    let post_form = ux(event.target);
    let section_id = post_form.find(".section_id").val();
    
    ux().post(post_form.attr("action"), post_form.serialize(), async (response_data) => {
        if(response_data.status){
            let duplicate_section_id = response_data.result.section_id;
            await ux(`#section_${section_id}`).after(response_data.result.html);

            addAnimation(`#section_${duplicate_section_id}`, "animate__fadeIn animate__slower");
            initializeMaterializeDropdown(ux(`#section_${duplicate_section_id}`).find(".dropdown-trigger").self());
        } else {

        }
    }, "json");

    return false;
}

function onSubmitUpdateSectionForm(event){
    event.preventDefault();
    let post_form = ux(event.target);
    let section_id = post_form.find(".section_id").val();

    ux().post(post_form.attr("action"), post_form.serialize(), async (response_data) => {
        if(response_data.status){
            await ux(`#section_${section_id}`).replaceWith(response_data.result.html);
            addAnimation(`#section_${section_id}`, "animated_blinkBorder");
            initializeMaterializeDropdown(ux(`#section_${section_id}`).find(".dropdown-trigger").self());
        } else {

        }
    }, "json");

    return false;
}

function onSubmitAddSectionForm(event){
    event.preventDefault();
    let post_form = ux(event.target);
    let section_title = post_form.find(".section_title").val();
    post_form.find(".group_add_section").removeClass("error");

    if(section_title){
        ux().post(post_form.attr("action"), post_form.serialize(), async (response_data) => {
            if(response_data.status){
                let section_block = await ux("#section_container").prepend(response_data.result.html);
                initializeMaterializeDropdown(section_block.find(".dropdown-trigger").self());
                appearEmptySection();
            } else {
                post_form.find(".group_add_section").addClass("error")
            }

            post_form.self().reset();
        }, "json");
    }
    
    return false;
}