<?php

namespace TravelBlog;

use TravelBlog\Table\Column;

class Table {
    protected $_columns = [];
    protected $_data = [];

    public function __construct() {

    }

    public function addColumns(array $columns = []) {
        foreach ($columns as $key => $column) {
            if (!is_numeric($key) && !isset($column['key'])) {
                $column['key'] = $key;
            }

            $this->addColumn($column);
        }

        return $this;
    }

    public function addColumn(array $column) {
        $this->_columns[] = new Column($column);

        return $this;
    }

    public function setData($data) {
        $this->_data = $data;
    }

    public function getColumns() {
        return $this->_columns;
    }

    public function getRows() {
        $return = [];

        foreach ($this->_data as $dataRow) {
            $row = [];

            foreach ($this->_columns as $column) {
                $row[] = $column->getValue($dataRow);
            }

            $return[] = $row;
        }

        return $return;
    }




}
