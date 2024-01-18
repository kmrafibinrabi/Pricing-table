<?php
/*
*The plugin's functionality that is specifically designed for admins
*/
class Vxpt_Admin
{
    private $plugin_name;
    private $version;
    public $price_table;

/*
    Ensure that the class is initialized and its properties are set.
*/

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;


     //To create their obj when loading add_menu_page, it's necessary to include WP_List_Table and yapt_list.

        if (!class_exists('WP_List_Table')) {
            require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
        }
    //Make sure the EPT post list table is included.
        require_once(VXPT_PLUGIN_DIR_PATH . 'includes/vxpt_list.php');
        require_once(VXPT_PLUGIN_DIR_PATH . 'Type/Type.php');
        require_once(VXPT_PLUGIN_DIR_PATH . 'Type/PriceTable.php');
        require_once(VXPT_PLUGIN_DIR_PATH . 'Type/Column.php');
        require_once(VXPT_PLUGIN_DIR_PATH . 'Type/Feature.php');
    }

    /**
    * Activate registration for the stylesheets in the admin area.
    */


    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/vxpt-admin.css', array(), $this->version, 'all');
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/vxpt-admin.js', ['jquery'], $this->version, false);
        wp_enqueue_script('vxpy-jquery-ui', plugin_dir_url(__FILE__) . 'js/jquery-ui.js', ['jquery'], '1.0.0', false);
    }

    public function setupSettingsMenu(): void
{
    $hook = add_menu_page(
        'Viserx pricing tables',
        'Viserx pricing table',
        'edit_others_posts', // Capability check
        'vxpt_admin',
        [$this, 'renderSettingsPageContent'],
        'dashicons-table-col-after',
        24
    );

    add_submenu_page('vxpt_admin', 'Add new pricing table', __('Add new', 'vx-pricing-table'), 'edit_others_posts', 'vxpt_admin_add_page', [$this, 'renderAddPageContent']);
    add_action("load-$hook", [$this, 'screen_option']);
}


public function screen_option()
{
    $this->price_table = new vxpt_list();

    if (!empty($_GET['action']) && $_GET['action'] === 'edit') {
        // show edit form
        $price_table_id = 0;
        if (!empty($_GET['price_table'])) {
            $price_table_id = (int)sanitize_text_field($_GET['price_table']);
        }

        $this->price_table->prepare_item($price_table_id);
    } else {
        $option = 'per_page';
        $args = [
            'label' => 'Price Table',
            'default' => 10,
            'option' => 'tables_per_page'
        ];
        add_screen_option($option, $args);
        // show wp_list_table
        $this->price_table->prepare_items();
    }
}
public function savePricingTableData()
    {
        // print_r($_POST); die();
        // echo "Update pricing table data";

        try {
            $price_table_obj = PriceTable::createFromArray($_POST);
        } catch (Exception $e) {
            die($e->getMessage());
        }

        global $wpdb;
        if (!$price_table_obj instanceof PriceTable) {
            die('missing mandatory fields');
        }
        // print_r($price_table_obj);die();

        $date_obj = new DateTime('now', new DateTimeZone('UTC'));
        $now = $date_obj->format('Y-m-d H:i:s');

        if ($price_table_obj->price_table_id > 0) {
            // update into yapt_pricing_tables
            $wpdb->update($wpdb->prefix . 'vxpt_pricing_tables', ['pt_title' => $price_table_obj->pricing_table_title, 'custom_styles' => $price_table_obj->custom_styles, 'template_id' => $price_table_obj->template_id, 'created_at' => $now, 'updated_at' => $now], ['id' => $price_table_obj->price_table_id]);
        } else {
            // insert into yapt_pricing_tables
            $wpdb->insert($wpdb->prefix . 'vxpt_pricing_tables', ['pt_title' => $price_table_obj->pricing_table_title, 'custom_styles' => $price_table_obj->custom_styles, 'template_id' => $price_table_obj->template_id, 'created_at' => $now, 'updated_at' => $now]);
            $price_table_obj->price_table_id = $wpdb->insert_id;
        }

        $column_ids = [];
        foreach ($price_table_obj->columns as $column) {
            if (!$column instanceof Column) {
                die('$column must be of type Type/Column.');
            }

            if (empty($column->column_id)) {
                // insert into yapt_columns
                $wpdb->insert($wpdb->prefix . 'vxpt_columns', ['column_title' => $column->column_title, 'description' => $column->description, 'highlighted' => $column->highlighted, 'table_id' => $price_table_obj->price_table_id, 'price_currency' => $column->column_price_currency, 'price' => $column->column_price, 'price_suffix' => $column->column_price_suffix, 'ctoa_btn_text' => $column->column_button_face_text, 'ctoa_btn_link' => $column->column_button_url, 'created_at' => $now, 'updated_at' => $now]);
                $column->column_id = $wpdb->insert_id;
            } else {
                // update yapt_columns
                $wpdb->update($wpdb->prefix . 'vxpt_columns', ['column_title' => $column->column_title, 'description' => $column->description, 'highlighted' => $column->highlighted, 'table_id' => $price_table_obj->price_table_id, 'price_currency' => $column->column_price_currency, 'price' => $column->column_price, 'price_suffix' => $column->column_price_suffix, 'ctoa_btn_text' => $column->column_button_face_text, 'ctoa_btn_link' => $column->column_button_url, 'created_at' => $now, 'updated_at' => $now], ['id' => $column->column_id]);
            }
            $feature_ids = [];
            foreach ($column->features as $feature) {
                if (!$feature instanceof Feature) {
                    die('$feature must be of type Type/Feature.');
                }

                if (empty($feature->fid)) {
                    $wpdb->insert($wpdb->prefix . 'vxpt_features', ['column_id' => $column->column_id, 'feature_text' => $feature->feature_text, 'is_set' => $feature->feature_checked, 'sort_value' => $feature->sort_value, 'created_at' => $now, 'updated_at' => $now]);
                    $feature->fid = $wpdb->insert_id;
                } else {
                    $wpdb->update($wpdb->prefix . 'vxpt_features', ['column_id' => $column->column_id, 'feature_text' => $feature->feature_text, 'is_set' => $feature->feature_checked, 'sort_value' => $feature->sort_value, 'updated_at' => $now], ['id' => $feature->fid]);

                }
                $feature_ids[] = $feature->fid;
            }//foreach feature_text ends

            if (is_array($feature_ids) && count($feature_ids) > 0) {
                $sql_delete_features = "DELETE FROM `" . $wpdb->prefix . "vxpt_features` WHERE `column_id` = '" . $column->column_id . "' AND `id` NOT IN (" . implode(', ', $feature_ids) . ")";
            } else if (count($feature_ids) === 0) {
                $sql_delete_features = "DELETE FROM `" . $wpdb->prefix . "vxpt_features` WHERE `column_id` = '" . $column->column_id . "'";
            }
            $wpdb->query($sql_delete_features);

            $column_ids[] = $column->column_id;
        }//foreach column ends
        $sql_delete_columns = "DELETE FROM `" . $wpdb->prefix . "vxpt_columns` WHERE `table_id` = '" . $price_table_obj->price_table_id . "' AND `id` NOT IN (" . implode(', ', $column_ids) . ")";
        $wpdb->query($sql_delete_columns);
        echo wp_redirect(admin_url('admin.php?page=vxpt_admin'));
    }

    public function renderSettingsPageContent(string $activeTab = ''): void
    {
        if (!empty($_GET['action']) && $_GET['action'] === 'edit') {
            // show edit form
            require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/vxpt-admin-edit.php';
        } else {
            // show wp_list_table
            require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/vxpt-admin-display.php';
        }
    }
    public function renderAddPageContent(string $activeTab = ''): void
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/vxpt-admin-add-page.php';
    }

  
    public function get_currency_options($currencies, string $selected_currency, string $currency_options): string
    {
        foreach ($currencies as $currency) {
            $select = '';
            if ($selected_currency === $currency['country']) {
                $select = "selected = 'selected'";
            }
            $currency_options .= "<option value='" . esc_html($currency['country']) . "' " . $select . ">" . esc_html($currency['country']) . ' (' . esc_html($currency['code']) . ")</option>";
        }
        return $currency_options;
    }
}
