<?php
if (!defined('ABSPATH')) {
    exit();
}
if (!class_exists('CPMW_TRANSACTION_TABLE')) {
    class CPMW_TRANSACTION_TABLE
    {
        public function __construct()
        {

        }

        //Transaction table callback

        public static function cpmw_transaction_table()
        {
            $lits_table = new Cpmw_metamask_list();
            echo '<div class="wrap"><h2>' . __("MetaMask Transactions", "cpmw") . '</h2>';

            $lits_table->prepare_items();
            ?>
            <form method="post" class="alignleft">&nbsp;
                <input type="hidden" name="cpmw_processing" value="processing" />
                <input type="submit" class="button secondary" value="<?php _e('Processing (' . self::cpmw_count_orders_status('processing') . ')', 'cpmw');?>" />
            </form>
                <form method="post" class="alignleft">&nbsp;
                <input type="hidden" name="cpmw_canceled" value="cancelled" />
                <input type="submit" class="button secondary" value="<?php _e('Cancelled (' . self::cpmw_count_orders_status('cancelled') . ')', 'cpmw');?>" />
            </form>
            <form method="post" class="alignleft">&nbsp;
                <input type="hidden" name="cpmw_completed" value="completed" />
                <input type="submit" class="button secondary" value="<?php _e('Completed (' . self::cpmw_count_orders_status('completed') . ')', 'cpmw');?>" />
            </form>
                <form method="post" class="alignleft">&nbsp;
                <input type="hidden" name="cpmw_on_hold" value="on-hold" />
                <input type="submit" class="button secondary" value="<?php _e('On Hold (' . self::cpmw_count_orders_status('on-hold') . ')', 'cpmw');?>" />
            </form>
            <form method="post">
                <input type="hidden" name="page" value="my_list_test" />
                <?php
            $lits_table->search_box('search', 'search_id');
            ?>
            </form>
                    <?php
            $lits_table->display();

            echo '</div>';
        }

        public static function cpmw_count_orders_status($status)
        {
            global $wpdb, $_wp_column_headers;

            $query = 'SELECT * FROM ' . $wpdb->base_prefix . 'cpmw_transaction';
            $query .= ' where ( status LIKE "%' . $status . '%" ) ';
            $items = $wpdb->get_results($query);
            return count($items);
        }

    }
}