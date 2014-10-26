<?php


if ( ! function_exists('config')) {
    function array_orderby(&$array, $field, $direction = 'asc') {

        $column = array();
        foreach ($array as $key => $row) {
            $column[$key] = is_object($row) ? $row->{$field} : $row[$field];
        }
        if ($direction == 'asc') {
            array_multisort($column, SORT_ASC, $array);
        } else {
            array_multisort($column, SORT_DESC, $array);
        }
    }
}
