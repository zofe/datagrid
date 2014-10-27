<?php

require_once __DIR__ . '/vendor/autoload.php';

use Zofe\Deficient\Deficient;
use Zofe\DataGrid\DataSet;
use Zofe\DataGrid\DataGrid;

Deficient::boot("./");



## burp,  move it somewhere
route_get('page/(\d+)', array('as'=>'page', function($page) {
    Zofe\Burp\BurpEvent::queue('dataset.page', array($page));
}));
//define some general purpose events on query-string
route_query('ord=(-?)(\w+)', array('as'=>'orderby', function($direction, $field) {
    $direction = ($direction == '-') ? "DESC" : "ASC";
    Zofe\Burp\BurpEvent::queue('dataset.sort', array($direction, $field));
}))->remove('page');


## test routes
route_get('^/{page?}$', array('as'=>'datagrid', function () {
    
    $grid = DataGrid::source('al_alert');
    $grid->add('title','Title',true);
    $grid->paginate(10);
    $grid->build();

    echo blade('test_datagrid', compact('grid'));
    die;
}));

route_get('^/dataset/{page?}$', array('as'=>'dataset', function () {
    
    $ds = DataSet::source('al_alert');
    $ds->paginate(10);
    $ds->build();
    
    echo blade('test_dataset', compact('ds'));
    die;
}));

route_get('^/test/(\w+)$', function ( $title ) {

    $content = $title.' page';
    echo blade('hello', compact('title','content'));
    die;
});


route_missing(function() {
    echo blade('error', array(), 404);
    die;
});


route_dispatch();

