<?php

/**
 * Duplicator page header
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

defined("ABSPATH") or die("");

/* Variables */
/* @var $templateMng \Duplicator\Core\Views\TplMng */
/* @var $templateData array */

if (empty($templateData['menuItemsL3'])) {
    return;
}
?>
<div class="dup-sub-tabs">
    <?php
    foreach ($templateData['menuItemsL3'] as $item) {
        $id      = 'dup-submenu-l3-' . $templateData['currentLevelSlugs'][0] . '-' . $templateData['currentLevelSlugs'][1] . '-' . $item['slug'];
        $classes = array('dup-submenu-l3');
        ?>
        <span class="dup-sub-tab-item" >
            <?php if ($item['active']) { ?>
                <b><?php echo esc_html($item['label']); ?></b> 
            <?php } else { ?>
                <a href="<?php echo esc_url($item['link']); ?>" id="<?php echo esc_attr($id); ?>" class="<?php echo implode(' ', $classes); ?>" >
                    <span><?php echo esc_html($item['label']); ?></span>
                </a>
            <?php } ?>
        </span>
    <?php } ?>
</div>