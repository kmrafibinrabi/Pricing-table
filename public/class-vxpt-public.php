<?php
/* 
This file contains users end functionality 
*/

class Vxpt_Public
{
    private string $plugin_name;
    private string $version;

    public function __construct(string $plugin_name, string $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        add_shortcode('vxpt', [$this, 'vxpt_shortcode_callback']);
    }

    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/vxpt-public.css', array(), $this->version, 'all');

        if (!wp_style_is('fontawesome', 'enqueued')) {
            wp_register_style('fontawesome', '//cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', false, '5.15.4');
            wp_enqueue_style('fontawesome');
        }
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/vxpt-public.js', array('jquery'), $this->version, false);
    }

    public function vxpt_shortcode_callback(array $atts = []): string
    {
        extract(shortcode_atts(['ptid' => '0'], $atts));

        $db_data_obj = new db_data();
        $item_detail = $db_data_obj->getData($ptid);

        $custom_styles = "";
        if (!empty($item_detail['custom_styles'])) {
            $custom_styles = '<style>' . $item_detail['custom_styles'] . '</style>';
        }

        if (!is_array($item_detail) || count($item_detail) < 1) {
            return "<p>not available</p>";
        }

        wp_enqueue_style('custom-style-' . $item_detail['template_name'], VXPT_PLUGIN_URL . "templates/" . $item_detail['template_name'] . "/" . $item_detail['style'], [], '1.0.0');

        $pt_column_content = $this->readHtmlFile($item_detail['template_name'] ?? '', $item_detail['html'] ?? '');

        $pt_html = $custom_styles;
        $pt_html .= "<div class='vxpt_pricing_table'>";

        $col_html = '';

        foreach ($item_detail['columns'] as $col) {
            $highlighted = ($col['highlighted'] == 1) ? 'highlighted' : '';

            // Task for feature
            $feature_list = '';
            foreach ($col['features'] as $feats) {
                $feature_class = ($feats['is_set'] == '1') ? 'checked' : 'unchecked';
                $feature_list .= "<li class='" . esc_html($feature_class) . "'>" . esc_html($feats['feature_text']) . "</li>";
            }

            $price_suffix = $this->getPriceSuffix(esc_html($col['price_suffix']));

            $temp_col = str_replace('##is_highlighted##', $highlighted, $pt_column_content);
            $temp_col = str_replace('##description##', esc_html($col['description']), $temp_col);
            $temp_col = str_replace('##col_title##', esc_html($col['column_title']), $temp_col);
            $temp_col = str_replace('##col_price_currency##', esc_html($col['currency_symbol']), $temp_col);
            $temp_col = str_replace('##col_price##', esc_html($col['price']), $temp_col);
            $temp_col = str_replace('##col_price_suffix##', $price_suffix, $temp_col);
            $temp_col = str_replace('##col_cta_btn_lnk##', esc_url($col['ctoa_btn_link']), $temp_col);
            $temp_col = str_replace('##col_cta_btn_text##', esc_html($col['ctoa_btn_text']), $temp_col);
            $temp_col = str_replace('##col_feature_list##', $feature_list, $temp_col);

            // Remove any ##.*?## available
            $temp_col = preg_replace('/##.*?##/', '', $temp_col);

            $col_html .= $temp_col;
        }

        $pt_html .= $col_html . "</div>";

        return $pt_html;
    }

    /**
     * Get price suffix ('Per day' to '/day for getPriceSuffix returns')
     */
    private function getPriceSuffix(string $price_suffix): string
    {
        $suffixMapping = [
            'per hour' => '/hr',
            'per day' => '/d',
            'per month' => '/mo',
            'per year' => '/yr',
        ];

        return $suffixMapping[strtolower($price_suffix)] ?? '';
    }

    /**
     * Read HTML file
     */
    private function readHtmlFile(string $folder_name, string $html_file): string
    {
        $folder_name = empty($folder_name) ? 'default' : $folder_name;
        $html_file = empty($html_file) ? 'default' : $html_file;

        return file_get_contents(VXPT_PLUGIN_DIR_PATH . '/templates/' . $folder_name . '/' . $html_file, true);
    }
}
