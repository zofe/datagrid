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

    protected $valid_sources = array('Illuminate\Database\Eloquent\Model',
                                     'Illuminate\Database\Eloquent\Builder',
                                     'Illuminate\Database\Query\Builder');

    protected $paginator;
    protected $per_page = 10;
    protected $total_rows;
    
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
        $ins->source = $ins->fixQuery($source);
        $ins->query = $ins->source;
        
        $ins->total_rows = $ins->getCount();
        $ins->key = $ins->getKeyName();
        
        //bind burp events
        BurpEvent::listen('dataset.sort', array($ins, 'sort'));
        BurpEvent::listen('dataset.page', array($ins, 'page'));
        return $ins;
    }

    /**
     * Sort source (quequed event by burp) 
     * 
     * @param $direction
     * @param $field
     */
    public function sort($direction, $field)
    {
        $this->orderBy($field, $direction);
    }

    /**
     * Set current page number (quequed event by burp)
     * 
     * @param $page
     */
    public function page($page)
    {
        $this->page = $page;
    }
    
    /**
     * build an order by link
     * 
     * @param string $field
     * @param string $dir
     * @return string
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
     * set number of items for single page (we're using "paginate" name,
     * like the eloquent "paginate" method on query builder)
     * 
     * @param $items
     * @return $this
     */
    public function paginate($items)
    {
        $this->per_page = $items;

        return $this;
    }

    /**
     * flush events, build pagination and sort items.
     * 
     * @return $this
     */
    public function build()
    {
        BurpEvent::flush('dataset.sort');
        BurpEvent::flush('dataset.page');
        
        $this->paginator =  Paginator::make($this->total_rows, $this->per_page, $this->page);
        $offset = $this->paginator->offset();
        $this->limit($this->per_page, $offset);
        
        if (is_array($this->source)) {

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
            
        } else {
                
                //orderby

                if (isset($this->orderby)) {
                    $this->query = $this->query->orderBy($this->orderby[0], $this->orderby[1]);
                }

                //limit-offset
                if (isset($this->per_page)) {
                    $this->query = $this->query->skip($offset)->take($this->per_page);
                }
                $this->data = $this->query->get();
        }
        return $this;
    }

    /**
     * check if  source is valid, then detect items count
     * 
     * @mixed \Exception | int
     */
    protected function getCount()
    {
        if (is_array($this->source)) {

            return count($this->source);

        } elseif(is_object($this->source) && in_array(get_class($this->source), $this->valid_sources))  {
           
            return  $this->query->count();
            
        } else {

            throw new \Exception(' "source" must be a table name, an eloquent model or an eloquent builder. you passed: ' . get_class($this->source));
        }
    }
    
    
    /**
     * check if source is plain text, so a table name, and start query from that table.
     * and 
     * @return $this
     */
    protected function fixQuery($source)
    {
        if (is_string($source) && strpos(" ", $source) === false) {
            return DB::table($source);
        }
        return $source;
    }

    /**
     * if possible detact key-name (to be used in datagrid)
     * @return string
     */
    public function getKeyName()
    {
        if (is_a($this->source, '\Illuminate\Database\Eloquent\Model')) {
            return $this->source->getKeyName();
        }

        if (is_a($this->source, '\Illuminate\Database\Eloquent\Builder')) {
            return $this->source->getModel()->getKeyName();
        }

        return 'id';
    }
    
    /**
     * convention, widgets should have a method get{name} 
     * that exec build() an return the object
     * 
     * @return $this
     */
    public function getSet()
    {
        $this->build();
        return $this;
    }

    /**
     * get subset of items (current page) of source
     * 
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * 
     * @param string $view
     * @return mixed
     */
    public function links($view = 'pagination')
    {
        if ($this->limit) {
            return $this->paginator->links($view);
        }
    }

    /**
     * return true if pagination is needed
     * @return bool
     */
    public function hasPagination()
    {
        return (bool) $this->limit;
    }
}
