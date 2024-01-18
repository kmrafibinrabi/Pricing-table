<?php

/**
 * load all global or internationalize functionality
 */




 class Vxpt_i18n {


public function load_plugin_textdomain(){

    load_plugin_textdomain(
        'vx-pricing-table',
        false,
        dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
    );
}


}