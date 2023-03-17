<?php foreach($tab_ids_order as $tab_ids_index => $tab_id){
    $tab = $module_tabs_json->$tab_id;
?>
    <li class="page_tab_item <?= ($tab_ids_index === ZERO_VALUE) ? 'active' : '' ?> module_tab_<?= $tab->id ?>" data-tab_id="tab_<?= $tab->id ?>" data-module_id="<?= $tab->module_id ?>">
        <a href="#tab_<?= $tab->id ?>"><?= $tab->title ?></a>
        <button type="button" class="remove_tab_btn">&times;</button>
    </li>
<?php } ?>