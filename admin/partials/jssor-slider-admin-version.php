<?php

    // Exit if accessed directly
    if( !defined( 'ABSPATH') ) exit();

    $updates_info = WP_Jssor_Slider_Globals::get_jssor_wordpress_updates_info();
    $stable_version = $updates_info['stable_version'];
    $latest_version = $updates_info['latest_version'];
    $beta_version = $updates_info['beta_version'];

    $stable_update_available = $updates_info['stable_update_available'];
    $latest_update_available = $updates_info['latest_update_available'];
    $beta_update_available = $updates_info['beta_update_available'];

    $update_available = $updates_info['update_available'];
    $updateview_statusclass = $update_available ? 'wjssc-status-warning' : 'wjssc-status-ok';
?>

<!-- #region update view -->

<div class="wjssc-dash-block <?php echo esc_attr($updateview_statusclass); ?>" data-u="jssor-slider-update-view">
    <!-- #region title -->
    <div class="wjssc-dash-title">
        <div class="wjssc-dash-title-text">
            <?php _e("Plugin Updates",WP_JSSOR_SLIDER_DOMAIN); ?>
        </div>
        <div class="wjssc-dash-title-signal wjssc-dash-title-signal-refresh">
            <span class="wjssc-iconm wjssc-iconm-refresh"></span>&nbsp;&nbsp;
            <span class="wjssc-dash-title-signal-text">
                <?php _e("Update Available",WP_JSSOR_SLIDER_DOMAIN); ?>
            </span>
        </div>
        <div class="wjssc-dash-title-signal wjssc-dash-title-signal-ok">
            <span class="wjssc-iconm wjssc-iconm-ok"></span>&nbsp;&nbsp;
            <span class="wjssc-dash-title-signal-text">
                <?php _e("Plugin up to date",WP_JSSOR_SLIDER_DOMAIN); ?>
            </span>
        </div>
    </div>
    <!-- #endregion -->

    <!-- #region content -->
    <div class="wjssc-dash-content">
        <!-- current version -->
        <div class="wjssc-dash-emphasis" style="width:120px;">
            <?php _e("Current Version:",WP_JSSOR_SLIDER_DOMAIN); ?>
        </div>
        <div data-u="current-version" class="wjssc-dash-emphasis">
            <?php echo esc_html(WP_JSSOR_SLIDER_VERSION); ?>
        </div>
        <div style="height:30px;"></div>

        <!-- stable version -->
        <div style="height:5px;"></div>
        <label data-u="stable-version-label" class="<?php echo $stable_update_available ? '' : 'wjssc-dash-disable' ?>" style="display:none;">
            <span class="wjssc-dash-label" style="width:120px;">
                <input data-u="stable-version-radio" type="radio" name="update_channel" value="stable" <?php echo $stable_update_available ? '' : 'disabled ' ?> /><?php _e("Stable Version:",WP_JSSOR_SLIDER_DOMAIN); ?>
            </span>
            <span data-u="stable-version" class="wjssc-dash-label">
                <?php echo esc_html($stable_version); ?>
            </span>
        </label>

        <!-- latest version -->
        <div style="height:5px;"></div>
        <label data-u="latest-version-label" class="<?php echo $latest_update_available ? '' : 'wjssc-dash-disable' ?>">
            <span class="wjssc-dash-label" style="width:120px;">
                <input data-u="latest-version-radio" type="radio" name="update_channel" value="latest" <?php echo $latest_update_available ? ' checked="checked" ' : ' ' ?> <?php echo $latest_update_available ? '' : 'disabled ' ?> /><?php _e("Latest Version:",WP_JSSOR_SLIDER_DOMAIN); ?>
            </span>
            <span data-u="latest-version" class="wjssc-dash-label">
                <?php echo esc_html($latest_version); ?>
            </span>
        </label>

        <!-- beta version -->
        <div style="height:5px;"></div>
        <label data-u="beta-version-label" class="<?php echo $beta_update_available ? '' : 'wjssc-dash-disable' ?>">
            <span class="wjssc-dash-label" style="width:120px;">
                <input data-u="beta-version-radio" type="radio" name="update_channel" value="beta" <?php echo $beta_update_available && !$latest_update_available ? ' checked="checked" ' : ' ' ?> <?php echo $beta_update_available ? '' : 'disabled ' ?> /><?php _e("Beta Version:",WP_JSSOR_SLIDER_DOMAIN); ?>
            </span>
            <span data-u="beta-version" class="wjssc-dash-label">
                <?php echo esc_html($beta_version); ?>
            </span>
        </label>
    </div>
    <!-- #endregion -->

    <!-- #region footer-->
    <div class="wjssc-dash-footer">
        <?php //update-core.php?checkforupdates=true ?>
        <a data-u="command" data-command="update" href="<?php echo admin_url(); ?>update-core.php?checkforupdates=true" class="wjssc-dash-action-btn wjssc-dash-update-now">
            <?php _e('Update Now', WP_JSSOR_SLIDER_DOMAIN); ?>
        </a>

        <span class="wjssc-dash-action-btn wjssc-dash-action-btnds wjssc-dash-update-to-date">
            <?php _e('Up to date', WP_JSSOR_SLIDER_DOMAIN); ?>
        </span>

        <?php //page=jssor-slider-admin-menu&checkforupdates=true ?>
        <span data-u="command" data-command="check_for_updates" class='wjssc-dash-detect-btn' style="line-height: 38px; margin-left: 10px;">
            <a href="#">
                <?php _e("Check for Updates",WP_JSSOR_SLIDER_DOMAIN); ?>
            </a>
        </span>

        <?php
        if(false && $validated !== 'true' && version_compare(WP_JSSOR_SLIDER_VERSION, $stable_version, '<')) {
            //free update to stable version hasn't been implemented yet
        ?>
        <?php
        }
        ?>
    </div>
    <!-- #endregion -->
</div>

<!--#endregion-->
