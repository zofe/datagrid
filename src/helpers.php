<?php


if ( ! function_exists('array_orderby')) {
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

if ( ! function_exists('array_to_attributes')) {
    function array_to_attributes($attributes) {
        if (empty($attributes))
            return '';

        $compiled = '';
        foreach ($attributes as $key => $val) {
            $compiled .= ' ' . $key . '="' .  htmlspecialchars((string) $val, ENT_QUOTES, "UTF-8", true) . '"';
        }

        return $compiled;
    }
}

