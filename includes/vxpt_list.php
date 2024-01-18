<?php

class Vxpt_List extends WP_List_Table
{
    /**
     * @var array
     */
    public array $item;

    public function __construct()
    {
        // Set parent defaults.
        parent::__construct([
            'singular' => __('Pricing Table', 'vx-pricing-table'),
            'plural'   => __('Pricing Tables', 'vx-pricing-table'),
            'ajax'     => false,
        ]);
    }

    /**
     * @param int $per_page
     * @param int $page_number
     * @return array|object|null
     */
    public function get_price_tables(int $per_page = 10, int $page_number = 1)
    {
        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}vxpt_pricing_tables";

        if (!empty($_REQUEST['orderby'])) {
            $sql .= ' ORDER BY ' . sanitize_text_field($_REQUEST['orderby']);
            $sql .= !empty($_REQUEST['order']) ? ' ' . sanitize_text_field($_REQUEST['order']) : ' ASC';
        }

        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;

        return $wpdb->get_results($sql, 'ARRAY_A');
    }

    /**
     * Delete price table
     *
     * @param int $id
     */
    public static function delete_price_table(int $id)
    {
        global $wpdb;
        $wpdb->delete(
            "{$wpdb->prefix}vxpt_pricing_tables",
            ['id' => $id],
            ['%d']
        );
    }

    /**
     * Returning records in the database.
     *
     * @return string|null
     */
    public static function record_count(): ?string
    {
        global $wpdb;
        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}vxpt_pricing_tables";

        return $wpdb->get_var($sql);
    }

    public function no_items()
    {
        echo __('No price tables available.', 'vx-pricing-table');
    }

    /**
     * Rendering column when no column exists.
     *
     * @param array $item
     * @param string $column_name
     * @return mixed
     */
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'pt_title':
            case 'template_id':
            case 'created_at':
            case 'updated_at':
                return $item[$column_name];
            case 'shortcode':
                return "<input type='text' onClick='this.select();' readonly='readonly' value='" . $item[$column_name] . "' />";
            default:
                return print_r($item, true); // Show the whole array for troubleshooting purposes
        }
    }

    /**
     * Rendering bulk edit checkbox.
     *
     * @param array $item
     * @return string
     */
    public function column_cb($item): string
    {
        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />',
            $item['id']
        );
    }

    /**
     * pt_title column method.
     *
     * @param array $item
     * @return string
     */
    public function column_pt_title(array $item): string
    {
        $delete_nonce = wp_create_nonce('vx_delete_price_table');
        $title = '<strong>' . $item['pt_title'] . '</strong>';
        $actions = [
            'edit'   => sprintf(
                '<a href="?page=%s&action=%s&price_table=%s">Edit</a>',
                esc_attr($_REQUEST['page']),
                'edit',
                absint($item['id'])
            ),
            'delete' => sprintf(
                '<a href="?page=%s&action=%s&price_table=%s&_wpnonce=%s">Delete</a>',
                esc_attr($_REQUEST['page']),
                'delete',
                absint($item['id']),
                $delete_nonce
            ),
        ];

        return sprintf('%1$s %2$s', $title, $this->row_actions($actions));
    }

    /**
     * @return array
     */
    public function get_columns(): array
    {
        return [
            'cb'         => '<input type="checkbox" />', // Render a checkbox corresponding text.
            'pt_title'   => __('Price table title', 'vx-pricing-table'),
            'shortcode'  => __('Shortcode', 'vx-pricing-table'),
            'updated_at' => __('Updated date', 'vx-pricing-table'),
        ];
    }

    /**
     * Sortable column.
     *
     * @return array
     */
    public function get_sortable_columns(): array
    {
        return [
            'pt_title'   => ['pt_title', true],
            'updated_at' => ['updated_at', true],
        ];
    }

    /**
     * Returning an associative array.
     *
     * @return array
     */
    public function get_bulk_actions(): array
    {
        return [
            'bulk-delete' => 'Delete',
        ];
    }

    /**
     * Data query and filter, sorting, and pagination.
     */
    public function prepare_items()
    {
        $this->_column_headers = $this->get_column_info();

        /** Process bulk action */
        $this->process_bulk_action();

        $per_page      = $this->get_items_per_page('tables_per_page', 10);
        $current_page  = $this->get_pagenum();
        $total_items   = self::record_count();

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ]);

        $results          = self::get_price_tables($per_page, $current_page);
        $modified_results = [];

        foreach ($results as $result) {
            $temp_result               = $result;
            $temp_result['shortcode']  = '[vxpt ptid=' . $result['id'] . ']';
            $modified_results[]       = $temp_result;
        }

        $this->items = $modified_results;
    }

    public function process_bulk_action()
    {
        //Detect when a bulk action is being triggered...
        if ('delete' === $this->current_action()) {
            // In our file that handles the request, verify the nonce.
            $nonce = esc_attr($_REQUEST['_wpnonce']);

            if (!wp_verify_nonce($nonce, 'vx_delete_price_table')) {
                die('you cant have this');
            } else {
                $price_table_id = (int)$_GET['price_table'];
                if ($price_table_id > 0) {
                    self::delete_price_table($price_table_id);
                }
                // add_query_arg() return the current url
                wp_redirect(esc_url_raw(remove_query_arg(['_wpnonce', 'action', 'price_table'])));
                exit;
            }
        }

        // If the delete bulk action is triggered
        if ((isset($_POST['action']) && $_POST['action'] == 'bulk-delete')
            || (isset($_POST['action2']) && $_POST['action2'] == 'bulk-delete')
        ) {
            // this should not happen
            if (!is_array($_POST['bulk-delete'])) {
                die('bulk-delete must be an array.');
            }

            // loop over the array of record IDs and delete them
            foreach ($_POST['bulk-delete'] as $pt_id) {
                $price_table_id = (int)$pt_id;
                if ($price_table_id > 0) {
                    self::delete_price_table($price_table_id);
                }
            }

            // add_query_arg() return the current url
            wp_redirect(esc_url_raw(add_query_arg([])));
            exit;
        }
    }

    /**
     * Prepare item.
     */
    public function prepare_item($price_table_id)
    {
        if (empty($price_table_id) || !is_int($price_table_id) || $price_table_id <= 0) {
            // We must have a value for price_table, redirect to the listing page.
            wp_redirect(esc_url_raw(remove_query_arg(['action', 'price_table'])));
        }

        $db_data_obj = new db_data();
        $item        = $db_data_obj->getData($price_table_id);

        if (empty($item)) {
            wp_redirect(esc_url_raw(remove_query_arg(['action', 'price_table'])));
        }

        $this->item = $item;
    }
}
