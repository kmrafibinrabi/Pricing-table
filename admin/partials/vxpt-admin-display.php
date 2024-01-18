<?php
/**
 * Admin panel view for VX price tables.
 */
?>

<div class="wrap">
<h2>VX price tables <a href="admin.php?page=vxpt_admin_add_page" class="page-title-action"><?php _e('Add New', 'vx-pricing-table'); ?></a></h2>
    

    <div id="poststuff">
        <div id="post-body" class="metabox-holder">
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <form method="post">
                        <?php
                        // Assuming $this->price_table is an instance of a class with a 'display' method.
                        if (isset($this->price_table) && method_exists($this->price_table, 'display')) {
                            $this->price_table->display();
                        }

                        






                        ?>
                    </form>
                </div>
            </div>
        </div>
        <br class="clear">
    </div>
</div>
