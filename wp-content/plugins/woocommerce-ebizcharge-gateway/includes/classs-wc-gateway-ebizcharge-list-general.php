<?php
class General_List extends WP_List_Table
{
    const perPage = 50;
    /**
     * Get the search parametter
     *
     * @return null|string
     */

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $per_page = $this->get_items_per_page('customers_per_page', static::perPage);
        $current_page = $this->get_pagenum();
        $total_items = $this->record_count();

        $data = $this->table_data($per_page, $current_page);
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page
        ));
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
        $this->items = $data;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }

}
?>