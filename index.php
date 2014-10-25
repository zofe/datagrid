<?php

require_once __DIR__ . '/vendor/autoload.php';

use Zofe\Deficient\Deficient;
use Zofe\DataGrid\DataSet;

Deficient::boot("./");




route_get('page/(\d+)', array('as'=>'page', function($page) {
    Zofe\Burp\BurpEvent::queue('dataset.page', array($page));
}));
//define some general purpose events on query-string
route_query('ord=(-?)(\w+)', array('as'=>'orderby', function($direction, $field) {
    $direction = ($direction == '-') ? "DESC" : "ASC";
    Zofe\Burp\BurpEvent::queue('dataset.sort', array($direction, $field));
}))->remove('page');




route_get('^/{page?}$', function () {
    
    $ds = DataSet::source('al_alert');
    $ds->paginate(10);
    $ds->build();
    
    echo render('dataset', compact('ds'));
    die;
});

route_get('^/test/(\w+)$', function ($slug) {
    echo render('hello', array('title'=>$slug, 'content'=>'Hello '.$slug));
    die;
});

route_missing(function() {
    echo render('error', array(), 404);
    die;
});


route_dispatch();

