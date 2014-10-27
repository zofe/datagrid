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
    
    $grid = DataGrid::source('users');
    $grid->add('id','ID',true)->style('width:100px');
    $grid->add('name','Name',true);
    $grid->paginate(1);


    echo blade('datagrid.tests.datagrid', compact('grid'));
    die;
}));

route_get('^/dataset/{page?}$', array('as'=>'dataset', function () {
    
    $ds = DataSet::source('users');
    $ds->paginate(1);
    $ds->build();
    
    echo blade('datagrid.tests.dataset', compact('ds'));
    die;
}));

route_missing(function() {
    echo blade('datagrid.tests.error', array(), 404);
    die;
});


route_dispatch();

