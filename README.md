Datagrid
============

DataGrid is a simple presenter widget for database queries, models, or any generic Array. 
By default it produce Bootstrap 3 compatible output. 

At this moment is built on [Deficient](https://github.com/zofe/deficient) (a subset of laravel components including eloquent and blade, plus [burp](https://github.com/zofe/burp) router).
The plan is to make it compatible also with laravel, as standard package.

It can   

- Paginate results
- Sort results
- Display results in a HTML Table (defining each column)
- Define each column, row and cell (sorting links, formatting, etc.)
- Customize view output including pagination style
- Export results as CSV / Excel
- Let you define url-semantic for sort/pagination segments or parameters (thanks to burp)


## usage
```php

    $grid = DataGrid::source(new User);
    $grid->add('id','ID',true)->style('width:100px');
    $grid->add('name','Name',true);
    $grid->paginate(5);

```


## why not starting from laravel?

Because it can be used stand alone, and in any other framework.  
It has really minimal dependencies.


## Installation

install via composer 

    {
        "require": {
            "zofe/datagrid": "dev-master"
        }
    }
    
## Setup

To configure database, views, you must reference to [Deficient](https://github.com/zofe/deficient)  
This is a small how-to 

 - create minimum folders / configuration files
 - deploy datagrid views
 - deploy a front controller and a sample (optional, but suggested)

```
  $ php vendor/zofe/deficient/deficient setup:folders
  $ php vendor/zofe/datagrid/datagrid setup:views
  $ php vendor/zofe/datagrid/datagrid setup:router
```
