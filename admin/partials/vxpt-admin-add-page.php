<?php

/**
 * Admin panel for view for adding new pricing table
 * @since      1.0.0
 */
global $wpdb;
$results_templates = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}vxpt_templates", ARRAY_A);
$currencies = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}vxpt_currency", ARRAY_A);
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Add pricing table', 'vx-pricing-table');?></h1>
    <div id="poststuff">
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <div class="vxpt_add_title">
                <input class="vxpt_pricing_table_title" type="text" name="pricing_table_title" value=""
                       placeholder="Add pricing table title" required="required"/>
            </div>
            <div class="vxpt_wrap">
                <!-- Tab links -->
                <div class="tab" >
                    <button class="tablinks" onclick="vxpt_admin_tab(event, 'Add_table')" id="defaultOpen">
                        <span class="dashicons dashicons-table-row-before"></span>
                        <?php _e("Add Pricing Table", 'vx-pricing-table');?>
                    </button>
                    <button class="tablinks" onclick="vxpt_admin_tab(event, 'Theme')">
                        <span class= "dashicons dashicons-cover-image"></span>
                        <?php _e("Select Template", 'vx-pricing-table');?>
                    </button>
                    <button class="tablinks" onclick="vxpt_admin_tab(event, 'custom_styles')">
                        <span class="dashicons dashicons-embed-generic"></span>
                        <?php _e("Styles", 'vx-pricing-table');?>
                    </button>
                </div>
                <!-- Tab content -->
                <div id="Add_table" class="tabcontent">
                    <table width="100%">
                        <tr>
                            <td>
                                <h3><?php _e("Click on 'add column' to add a new price table", 'vx-pricing-table') ?></h3>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <a class="vxpt_add_column" href="javascript:;" onclick="add_column()">
                                    <span class="dashicons dashicons-welcome-add-page"></span>
                                    <?php _e('add column', 'vx-pricing-table');?>
                                </a>
                                <input type="hidden" name="column_count" id="column_count" value="0"/>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div id="vpt_columns" class="vpt_columns_wrap">
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
                <div id="Theme" class="tabcontent theme">
                    <h3><?php _e("Choose Template", 'vx-pricing-table');?></h3>
                    <div class="vxpt_template_list">
                        <?php
                        foreach ($results_templates as $template) {
                            ?>
                            <div class="vxpt_template_item">
                                <label>
                                    <input type="radio" name="template_id" value="<?php echo esc_html($template['id']); ?>" checked="checked"/>
                                    <img src="<?php echo VXPT_PLUGIN_URL . 'templates/' . esc_html($template['template_name']) . '/' . esc_html($template['image']); ?>"/>
                                </label>
                            </div>
                            <?php
                        }
                        ?>
                    </div><!-- .vxpt_template_list ends -->
                </div><!-- #Theme .tabcontent ends -->
                <div id="custom_styles" class="tabcontent">
                    <h3><?php _e("Custom styles", 'vx-pricing-table');?></h3>
                    <textarea name="custom_styles">/* styles here */</textarea>
                </div><!-- #Styles .tabcontent ends -->
            </div>
            <!--.vxpt_wrap ends -->

            <div class="vxpt_save_options">
                <input type="hidden" name="action" value="vxpt_admin_save"/>
                <?php
                wp_nonce_field("vxpt_nonce");
                submit_button();
                ?>
            </div>
        </form>
        <br class="clear" />
    </div>
</div>

<script type="text/javascript">
    // Initialize variables
    let computed_feature_id;
    let computed_column_id;
    let price_suffixs = ['<?php _e('Per hour', 'vx-pricing-table');?>', '<?php _e('Per day', 'vx-pricing-table');?>',
        '<?php _e('Per month', 'vx-pricing-table');?>', '<?php _e('Per year', 'vx-pricing-table');?>'];
    let option = '';

    // Create options for price suffix dropdown
    price_suffixs.forEach(function (price_suffix) {
        option += `<option value="${price_suffix}">${price_suffix}</option>`;
    });

    <?php
        // Assuming $currencies is an array of available currencies
        $currency_options = '';
        $selected_currency = 'United States of America';
        $currency_options = $this->get_currency_options($currencies, $selected_currency, $currency_options);
    ?>

    // Function to add a feature to a column
    function add_feature(column_id) {
        computed_feature_id = parseInt(jQuery(`#column${column_id}_feature_count`).val());
        console.log(`Adding feature for table ${column_id}, feature ID: ${computed_feature_id}`);

        // Create HTML for a new feature
        let new_feature_value = `
            <div id='column${column_id}_feature${computed_feature_id}' class='dgrid'>
                <span class='dashicons dashicons-menu'></span>
                <label class='vxpt_label_con'>
                    <input type='checkbox' name='fields[${column_id}][feature_checked][${computed_feature_id}]' value='1' />
                    <span class='checkmark'></span>
                </label>
                <input type='text' required='required' name='fields[${column_id}][feature_text][${computed_feature_id}]' placeholder='<?php _e('Feature text content', 'vx-pricing-table');?> ...' value='' />
                <a title='Delete feature' class='delete_feature' href='javascript:;' onclick='delete_feature(${column_id}, ${computed_feature_id})'>
                    <span class='dashicons dashicons-dismiss'></span>
                </a>
            </div>`;

        // Append the new feature to the column
        jQuery(`#column${column_id}_features`).append(new_feature_value);
        computed_feature_id += 1;
        jQuery(`#column${column_id}_feature_count`).val(computed_feature_id);
    }

    // Function to delete a feature from a column
    function delete_feature(column_id, feature_id) {
        console.log(`Deleting feature ${feature_id} from table ${column_id}`);
        jQuery(`#column${column_id}_feature${feature_id}`).remove();
    }

    // Function to delete a column
    function delete_column(column_id) {
        console.log(`Deleting column ${column_id}`);
        jQuery(`#tbl_column${column_id}`).remove();
    }

    // Function to add a new column
    function add_column() {
        computed_column_id = parseInt(jQuery("#column_count").val());
        console.log(`Adding column, new column ID: ${computed_column_id}`);

        // Create HTML for a new column
        let price_suffix = `<select name='fields[${computed_column_id}][column_price_suffix]'>${option}</select>`;
        let currency_select = `<select name='fields[${computed_column_id}][column_price_currency]'><?php echo $currency_options;?></select>`;

        let new_column_value = `
            <div class='vxpt_table_column' id='tbl_column${computed_column_id}'>
                <div class='vxpt_table_row'>
                    <label><?php _e('Name', 'vx-pricing-table');?></label>
                    <input type='text' required='required' name='fields[${computed_column_id}][column_title]'/>
                </div>
                <div class='vxpt_table_row'>
                    <label><?php _e('Short description', 'vx-pricing-table');?></label>
                    <textarea class='short_description' name='fields[${computed_column_id}][description]'></textarea>
                </div>
                <div class='vxpt_table_row'>
                    <label><?php _e('Currency', 'vx-pricing-table');?></label>
                    ${currency_select}
                </div>
                <div class='vxpt_table_row'>
                    <label><?php _e('Price', 'vx-pricing-table');?></label>
                    <input type='text' name='fields[${computed_column_id}][column_price]'/>
                </div>
                <div class='vxpt_table_row'>
                    <label><?php _e('Price suffix', 'vx-pricing-table');?></label>
                    ${price_suffix}
                </div>
                <div class='vxpt_table_row'>
                    <label><?php _e('Button face text', 'vx-pricing-table');?></label>
                    <input type='text' name='fields[${computed_column_id}][column_button_face_text]'/>
                </div>
                <div class='vxpt_table_row'>
                    <label><?php _e('Button url', 'vx-pricing-table');?></label>
                    <input type='text' name='fields[${computed_column_id}][column_button_url]'/>
                </div>
                <div class='vxpt_table_row vxpt_table_row_features_head'>
                    <span class='features_title'><?php _e('Features', 'vx-pricing-table');?></span>
                    <a class='add_feature' href='javascript:;' onclick='add_feature(${computed_column_id})'>
                        <span class='dashicons dashicons-plus-alt'></span>
                        <?php _e('add feature', 'vx-pricing-table');?>
                    </a>
                </div>
                <input type='hidden' name='column${computed_column_id}_feature_count' id='column${computed_column_id}_feature_count' value='0' />
                <div class='vxpt_table_row vxpt_table_row_features feature_column_container' id='column${computed_column_id}_features'></div>
                <input type='hidden' name='fields[${computed_column_id}][feature_order]' value='' />
                <div class='vxpt_table_row clearfix'>
                    <div class='switch_featured'>
                        <label class='switch'>
                            <input type='radio' name='highlighted' value='${computed_column_id}' />
                            <span class='slider round'></span>
                        </label>
                        <?php _e('Highlight', 'vx-pricing-table');?>
                    </div>
                    <a title='Delete column' class='delete_column' href='javascript:;' onclick='delete_column(${computed_column_id})'>
                        <span class='dashicons dashicons-dismiss'></span>
                    </a>
                </div>
            </div>`;

        // Append the new column to the container
        jQuery("#vpt_columns").append(new_column_value);

        // Add an initial feature to the new column
        add_feature(computed_column_id);

        // Make the features sortable
        jQuery(`#column${computed_column_id}_features`).sortable({
            handle: ".dashicons-menu",
            update: function (event, ui) {
                jQuery(this).siblings('input[name*="[feature_order]"]').val(jQuery(this).sortable('serialize').toString());
                console.log(jQuery(this).siblings('input[name*="[feature_order]"]').val());
            }
        });

        // Update computed column ID
        computed_column_id += 1;
        jQuery("#column_count").val(computed_column_id);
    }

    // Event listener for changing the currency select
    jQuery(document).on('change', 'select[name*="[column_price_currency]"]', function () {
        jQuery('select[name*="[column_price_currency]"]').val(this.value);
    });
</script>






