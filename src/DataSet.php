<?php

namespace Zofe\DataGrid;

use Illuminate\Support\Facades\DB;
use Zofe\Burp\BurpEvent;
use Zofe\DataGrid\Paginator;


class DataSet
{
    protected $source;

    /**
     *
     * @var \Illuminate\Database\Query\Builder
     */
    protected $query;


    protected $paginator;
    protected $per_page = 10;
    
    public $data = array();
    protected $hash = '';
    protected $url;
    protected $key = 'id';

    protected $type;
    protected $limit;
    protected $orderby;
    protected $page;


    /**
     * @param $source
     *
     * @return static
     */
    public static function source($source)
    {
        $ins = new static();
        $ins->source = $source;

        
        BurpEvent::listen('dataset.sort', array($ins, 'sort'));
        BurpEvent::listen('dataset.page', array($ins, 'page'));

        return $ins;
    }

    /**
     * Sort dataset 
     * @param $direction
     * @param $field
     */
    public function sort($direction, $field)
    {
        $this->orderBy($field, $direction);
    }

    /**
     * Set current page
     * @param $page
     */
    public function page($page)
    {
        $this->page = $page;
    }
    
    
    /**
     * @param string $field
     * @param string $dir
     *
     * @return mixed
     */
    public function orderbyLink($field, $dir = "asc")
    {
        $dir = ($dir == "asc") ? '' : '-';
        return link_route('orderby', array($dir, $field));
    }

    public function orderBy($field, $direction="asc")
    {
        $this->orderby = array($field, $direction);
        return $this;
    }

    public function onOrderby($field, $dir="asc")
    {
        $dir = ($dir == "asc") ? '' : '-';
        return is_route('orderby', array($dir, $field));
    }

    protected function limit($limit, $offset)
    {
        $this->limit = array($limit, $offset);
    }

    /**
     * @param $items
     *
     * @return $this
     */
    public function paginate($items)
    {
        $this->per_page = $items;

        return $this;
    }

    public function build()
    {
        BurpEvent::flush('dataset.sort');
        BurpEvent::flush('dataset.page');
        
        
        if (is_string($this->source) && strpos(" ", $this->source) === false) {
            //tablename
            $this->type = "query";
            $this->query = DB::table($this->source);
            $this->total_rows = $this->query->count();
            
        } elseif (is_a($this->source, '\Illuminate\Database\Eloquent\Model')) {
            $this->type = "model";
            $this->query = $this->source;
            $this->total_rows = $this->query->count();
            $this->key = $this->source->getKeyName();

        } elseif ( is_a($this->source, '\Illuminate\Database\Eloquent\Builder')) {
            $this->type = "model";
            $this->query = $this->source;
            $this->total_rows = $this->query->count();
            $this->key = $this->source->getModel()->getKeyName();

        } elseif ( is_a($this->source, '\Illuminate\Database\Query\Builder')) {
            $this->type = "model";
            $this->query = $this->source;
            $this->total_rows = $this->query->count();

        }
        //array
        elseif (is_array($this->source)) {
            $this->type = "array";
            $this->total_rows = count($this->source);
        } else {
            throw new \Exception(' "source" must be a table name, an eloquent model or an eloquent builder. you passed: ' . get_class($this->source));
        }


        $this->paginator =  Paginator::make($this->total_rows, $this->per_page, $this->page);
        $offset = $this->paginator->offset();
        $this->limit($this->per_page, $offset);

        
        //build subset of data
        switch ($this->type) {
            case "array":
                //orderby
                if (isset($this->orderby)) {
                    list($field, $direction) = $this->orderby;
                    $column = array();
                    foreach ($this->source as $key => $row) {
                        $column[$key] = is_object($row) ? $row->{$field} : $row[$field];
                    }
                    if ($direction == "asc") {
                        array_multisort($column, SORT_ASC, $this->source);
                    } else {
                        array_multisort($column, SORT_DESC, $this->source);
                    }
                }

                //limit-offset
                if (isset($this->limit)) {
                    $this->source = array_slice($this->source, $this->limit[1], $this->limit[0]);
                }
                $this->data = $this->source;
                break;

            case "query":
            case "model":
                
                //orderby

                if (isset($this->orderby)) {
                    $this->query = $this->query->orderBy($this->orderby[0], $this->orderby[1]);
                }

                //limit-offset
                if (isset($this->per_page)) {
                    $this->query = $this->query->skip($offset)->take($this->per_page);
                }
                $this->data = $this->query->get();
                break;
        }

        return $this;
    }


    
    /**
     * @return $this
     */
    public function getSet()
    {
        $this->build();

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $view
     *
     * @return mixed
     */
    public function links($view = 'pagination')
    {
        if ($this->limit) {
            return $this->paginator->links($view);
        }
    }

    public function havePagination()
    {
        return (bool) $this->limit;
    }
}
