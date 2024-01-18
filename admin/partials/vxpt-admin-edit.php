<?php
/**
 * Admin panel for editing pricing table.
 */

global $wpdb;
$results_templates = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}vxpt_templates", ARRAY_A);
$currencies = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}vxpt_currency", ARRAY_A);
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Edit pricing table', 'vx-pricing-table'); ?></h1>
    <div id="poststuff">
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <div class="vxpt_add_title">
                <input type="hidden" name="price_table_id" value="<?php echo esc_html($this->price_table->item['id']); ?>" />
                <input class="vxpt_pricing_table_title" type="text" name="pricing_table_title"
                       value="<?php echo esc_html($this->price_table->item['pt_title']); ?>"
                       placeholder="<?php _e('Add pricing table title', 'vx-pricing-table'); ?>" required="required" />
            </div>
            <div class="vxpt_wrap">
                <!-- Tab links -->
                <div class="tab">
                    <button class="tablinks" onclick="vxpt_admin_tab(event, 'Add_table')" id="defaultOpen">
                        <span class="dashicons dashicons-table-col-after"></span>
                        <?php _e("Add Pricing Table", 'vx-pricing-table'); ?>
                    </button>
                    <button class="tablinks" onclick="vxpt_admin_tab(event, 'Theme')">
                        <span class="dashicons dashicons-cover-image"></span>
                        <?php _e("Select Template", 'vx-pricing-table'); ?>
                    </button>
                    <button class="tablinks" onclick="vxpt_admin_tab(event, 'custom_styles')">
                        <span class="dashicons dashicons-admin-customizer"></span>
                        <?php _e("Styles", 'vx-pricing-table'); ?>
                    </button>
                </div>
                <!-- Tab content -->
                <div id="Add_table" class="tabcontent">
                    <table width="100%">
                        <tr>
                            <td>
                                <h3><?php _e("Click on 'add column' to add a new price table", 'vx-pricing-table'); ?></h3>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <a class="vxpt_add_column" href="javascript:;" onclick="add_column()">
                                    <span class="dashicons dashicons-plus"></span>
                                    <?php _e('Add Column', 'vx-pricing-table'); ?>
                                </a>
                                <input type="hidden" name="column_count" id="column_count" value="0" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div id="vpt_columns" class="vpt_columns_wrap"></div>
                            </td>
                        </tr>
                    </table>
                </div>
                <div id="Theme" class="tabcontent theme">
                    <h3><?php _e("Choose Template", 'vx-pricing-table'); ?></h3>
                    <div class="vxpt_template_list">
                        <?php
                        foreach ($results_templates as $template) {
                            ?>
                            <div class="vxpt_template_item">
                                <label>
                                    <input type="radio" name="template_id" value="<?php echo esc_html($template['id']); ?>" <?php
                                    if ($template['id'] === $this->price_table->item['template_id']) {
                                        echo "checked='checked'";
                                    }
                                    ?> />
                                    <img src="<?php echo VXPT_PLUGIN_URL . 'templates/' . esc_html($template['template_name']) . '/' . esc_html($template['image']); ?>" />
                                </label>
                            </div>
                            <?php
                        }
                        ?>
                    </div><!-- .vxpt_template_list ends -->
                </div><!-- #Theme .tabcontent ends -->
                <div id="custom_styles" class="tabcontent">
                    <h3><?php _e("Custom styles", 'vx-pricing-table'); ?></h3>
                    <textarea name="custom_styles"><?php echo esc_textarea($this->price_table->item['custom_styles']); ?></textarea>
                </div><!-- #Styles .tabcontent ends -->
            </div>
            <!-- .vxpt_wrap ends -->

            <div class="vxpt_save_options">
                <input type="hidden" name="action" value="vxpt_admin_save" />
                <?php
                wp_nonce_field("vxpt_nonce");
                submit_button();
                ?>
            </div>
        </form>
        <br class="clear">
    </div>
</div>

<script type="text/javascript">
    let computed_feature_id;
    let computed_column_id;
    let price_suffixes = ['<?php _e('Per hour', 'vx-pricing-table');?>', '<?php _e('Per day', 'vx-pricing-table');?>', '<?php _e('Per month', 'vx-pricing-table');?>', '<?php _e('Per year', 'vx-pricing-table');?>', '<?php _e('None', 'vx-pricing-table');?>'];
    let option = '';
    price_suffixes.forEach(function (price_suffix) {
        option += "<option value='" + price_suffix + "'>" + price_suffix + "</option>";
    });

    <?php
    $currency_options = '';
    $selected_currency = 'United States of America';
    $currency_options = $this->get_currency_options($currencies, $selected_currency, $currency_options);
    ?>

    function add_feature(column_id) {
        computed_feature_id = parseInt(jQuery("#column" + column_id + "_feature_count").val());
        let new_feature_value = "<div id='column" + column_id + "_feature" + computed_feature_id +
            "' class='dgrid'><input type='hidden' name='fields[" + column_id + "][fid][" + computed_feature_id +
            "]' /><span class='dashicons dashicons-menu'></span><label class='vxpt_label_con'><input type='checkbox' name='fields[" + column_id + "][feature_checked][" +
            computed_feature_id +
            "]' value='1' /> <span class='checkmark'></span></label> <input type='text' required='required' name='fields[" + column_id +
            "][feature_text][" + computed_feature_id +
            "]' placeholder='<?php _e('Feature text content', 'vx-pricing-table');?> ...' value='' /> <a title='Delete feature' class='delete_feature' href='javascript:;' onclick='delete_feature(" +
            column_id + ", " + computed_feature_id + ")'><span class='dashicons dashicons-no-alt'></span></a>  </div>";
        jQuery("#column" + column_id + "_features").append(new_feature_value);
        computed_feature_id += 1;
        jQuery("#column" + column_id + "_feature_count").val(computed_feature_id);
    }

    function delete_feature(column_id, feature_id) {
        jQuery("#column" + column_id + "_feature" + feature_id).remove();
    }

    function delete_column(column_id) {
        jQuery('#tbl_column' + column_id).remove();
    }

    function add_column() {
        computed_column_id = parseInt(jQuery("#column_count").val());
        let price_suffix = "<select name='fields[" + computed_column_id + "][column_price_suffix]'>" + option + "</select>";
        let currency_select = "<select name='fields[" + computed_column_id + "][column_price_currency]'><?php echo $currency_options;?></select>";
        let new_column_value = "<div class='vxpt_table_column' id='tbl_column" + computed_column_id +
            "'><div class='vxpt_table_row'><label><?php _e('Name', 'vx-pricing-table');?></label><input type='hidden' name='fields[" + computed_column_id +
            "][column_id]' /><input type='text' required='required' name='fields[" + computed_column_id +
            "][column_title]'/></div><div class='vxpt_table_row'><label><?php _e('Short description', 'vx-pricing-table');?></label><textarea class='short_description' name='fields[" + computed_column_id +
            "][description]'></textarea></div>" +
            "<div class='vxpt_table_row'><label><?php _e('Currency', 'vx-pricing-table');?></label>" + currency_select + "</div>" +
            "<div class='vxpt_table_row'><label><?php _e('Price', 'vx-pricing-table');?></label><input type='text' name='fields[" + computed_column_id + "][column_price]'/></div>" +
            "<div class='vxpt_table_row'><label><?php _e('Price suffix', 'vx-pricing-table');?></label>" + price_suffix + "</div>" +
            "<div class='vxpt_table_row'><label><?php _e('Button face text', 'vx-pricing-table');?></label><input type='text' name='fields[" + computed_column_id + "][column_button_face_text]'/></div>" +
            "<div class='vxpt_table_row'><label><?php _e('Button url', 'vx-pricing-table');?></label><input type='text' name='fields[" + computed_column_id + "][column_button_url]'/></div>" +
            "<div class='vxpt_table_row vxpt_table_row_features_head'><span class='features_title'><?php _e('Features', 'vx-pricing-table');?></span><a href='javascript:;' class='add_feature' onclick='add_feature(" +
            computed_column_id +
            ")'><span class='dashicons dashicons-plus-alt'></span><?php _e('add feature', 'vx-pricing-table');?></a></div><input type='hidden' name='column" +
            computed_column_id + "_feature_count' id='column" + computed_column_id +
            "_feature_count' value='0' /><div class='vxpt_table_row vxpt_table_row_features feature_column_container' id='column" +
            computed_column_id +
            "_features'></div><input type='hidden' name='fields["+computed_column_id+"][feature_order]' value='' /><div class='vxpt_table_row clearfix'><div class='switch_featured'> <label class='switch'><input type='radio' id='highlighted" +
            computed_column_id + "' name='highlighted' value='" + computed_column_id +
            "' /><span class='slider round'></span></label> <?php _e('Highlight', 'vx-pricing-table');?></div><a title='Delete column' class='delete_column' href='javascript:;' onclick='delete_column(" +
            computed_column_id + ")'><span class='dashicons dashicons-no-alt'></span></a></div></div>";
        jQuery("#vpt_columns").append(new_column_value);
        computed_column_id += 1;
        jQuery("#column_count").val(computed_column_id);
    }

    let column_value;
    <?php
    foreach ($this->price_table->item['columns'] as $col) {
    ?>
        // add column
        add_column();
        // populate column fields
        column_id_value = computed_column_id - 1;
        jQuery("input[name='fields[" + column_id_value + "][column_id]']").val('<?php echo esc_html($col['id']); ?>');
        jQuery("input[name='fields[" + column_id_value + "][column_title]']").val('<?php echo esc_html($col['column_title']); ?>');
        jQuery("textarea[name='fields[" + column_id_value + "][description]']").val('<?php echo esc_html($col['description']); ?>');
        jQuery("select[name='fields[" + column_id_value + "][column_price_currency]']").val('<?php echo esc_html($col['price_currency']); ?>').change();
        jQuery("input[name='fields[" + column_id_value + "][column_price]']").val('<?php echo esc_html($col['price']); ?>');
        jQuery("select[name='fields[" + column_id_value + "][column_price_suffix]']").val('<?php echo esc_html($col['price_suffix']); ?>').change();
        jQuery("input[name='fields[" + column_id_value + "][column_button_face_text]']").val('<?php echo esc_html($col['ctoa_btn_text']); ?>');
        jQuery("input[name='fields[" + column_id_value + "][column_button_url]']").val('<?php echo esc_url($col['ctoa_btn_link']); ?>');
        <?php
        if ($col['highlighted'] == '1') {
        ?>
    

    jQuery("#highlighted" + column_id_value).attr('checked', 'checked');
    <?php
    }
    foreach($col['features'] as $feature) {
    ?>
    // add features
    add_feature(column_id_value);

    jQuery("#column" + column_id_value + "_features").sortable({
        handle: ".dashicons-menu",
        update: function (event, ui) {
            jQuery(this).siblings('input[name*="[feature_order]"]').val(jQuery(this).sortable('serialize').toString());
            //console.log(jQuery(this).siblings('input[name*="[feature_order]"]').val());
        }
    });
    // populate features
    feautre_id_value = computed_feature_id - 1;
    jQuery("input[name='fields[" + column_id_value + "][fid][" + feautre_id_value + "]']").val(
        '<?php echo esc_html($feature['id']);?>');
    jQuery("input[name='fields[" + column_id_value + "][feature_text][" + feautre_id_value + "]']").val(
        '<?php echo esc_html($feature['feature_text']);?>');
    jQuery("input[name='fields[" + column_id_value + "][feature_checked][" + feautre_id_value + "]']").prop('checked',
        <?php echo ($feature['is_set'] == '1') ? 'true' : 'false'; ?>);
    <?php
    }
    ?>
    <?php
    }
    ?>
    jQuery(document).on('change', 'select[name*="[column_price_currency]"]', function() {
        jQuery('select[name*="[column_price_currency]"]').val(this.value);
    });
</script>
