<?php

namespace Zofe\DataGrid;

use Illuminate\Support\Str;
#use Zofe\Rapyd\Persistence;

class DataGrid extends DataSet
{

    protected $fields = array();
    public $columns = array();
    public $headers = array();
    public $rows = array();
    public $output = "";
    public $label = "";
    public $attributes = array("class" => "table");
    public $button_container = array( "TR"=>array(), "BL"=>array(), "BR"=>array() );
    protected $row_callable = array();

    /**
     * @param string $name
     * @param string $label
     * @param bool   $orderby
     *
     * @return Column
     */
    public function add($name, $label = null, $orderby = false)
    {
        $column = new Column($name, $label, $orderby);
        $this->columns[$column->name] = $column;
        if (!in_array($name,array("_edit"))) {
            $this->headers[] = $label;
        }

        return $column;
    }
    
    public function build($view = '')
    {
        ($view == '') and $view = 'datagrid.datagrid';
        parent::build();
        #Persistence::save();
        $this->buildRows();

        return blade($view, array('dg' => $this, 'buttons'=>$this->button_container, 'label'=>$this->label));
    }

    /**
     * build rows and cell array
     */
    protected function buildRows()
    {
        foreach ($this->data as $tablerow) {

            $row = new Row($tablerow);

            foreach ($this->columns as $column) {

                $cell = new Cell($column->name);
                $sanitize = (count($column->filters) || $column->cell_callable) ? false : true;
                $value = $this->getCellValue($column, $tablerow, $sanitize);
                $cell->value($value);
                $cell->parseFilters($column->filters);
                if ($column->cell_callable) {
                    $callable = $column->cell_callable;
                    $cell->value($callable($cell->value));
                }
                $row->add($cell);
            }

            if (count($this->row_callable)) {
                foreach ($this->row_callable as $callable) {
                    $callable($row);
                }
            }
            $this->rows[] = $row;
        }
    }
    

    public function buildCSV($file = '', $timestamp = '', $sanitize = true,$del = array())
    {
        $this->limit = null;
        parent::build();

        $segments = explode('/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

        $filename = ($file != '') ? basename($file, '.csv') : end($segments);
        $filename = preg_replace('/[^0-9a-z\._-]/i', '',$filename);
        $filename .= ($timestamp != "") ? date($timestamp).".csv" : ".csv";

        $save = (bool) strpos($file,"/");

        //Delimiter
        $delimiter = array();
        $delimiter['delimiter'] = isset($del['delimiter']) ? $del['delimiter'] : ';';
        $delimiter['enclosure'] = isset($del['enclosure']) ? $del['enclosure'] : '"';
        $delimiter['line_ending'] = isset($del['line_ending']) ? $del['line_ending'] : "\n";

        if ($save) {
            $handle = fopen(public_path().'/'.dirname($file)."/".$filename, 'w');

        } else {

            $headers  = array(
                'Content-Type' => 'text/csv',
                'Pragma'=>'no-cache',
                '"Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Content-Disposition' => 'attachment; filename="' . $filename.'"');

            $handle = fopen('php://output', 'w');
            ob_start();
        }

        fputs($handle, $delimiter['enclosure'].implode($delimiter['enclosure'].$delimiter['delimiter'].$delimiter['enclosure'], $this->headers) .$delimiter['enclosure'].$delimiter['line_ending']);

        foreach ($this->data as $tablerow) {
            $row = new Row($tablerow);

            foreach ($this->columns as $column) {

                if (in_array($column->name,array("_edit")))
                    continue;

                $cell = new Cell($column->name);
                $value =  str_replace('"', '""',str_replace(PHP_EOL, '', strip_tags($this->getCellValue($column, $tablerow, $sanitize))));
                $cell->value($value);
                $row->add($cell);
            }

            if (count($this->row_callable)) {
                foreach ($this->row_callable as $callable) {
                    $callable($row);
                }
            }

            fputs($handle, $delimiter['enclosure'] . implode($delimiter['enclosure'].$delimiter['delimiter'].$delimiter['enclosure'], $row->toArray()) . $delimiter['enclosure'].$delimiter['line_ending']);
        }

        fclose($handle);
        if ($save) {
            //redirect, boolean or filename?
        } else {
            $output = ob_get_clean();

            //return \Response::make(rtrim($output, "\n"), 200, $headers);
        }
    }

    protected function getCellValue($column, $tablerow, $sanitize = true)
    {
        //blade
        if (strpos($column->name, '{{') !== false) {

            if (is_object($tablerow) && method_exists($tablerow, "getAttributes")) {
                $fields = $tablerow->getAttributes();
                $relations = $tablerow->getRelations();
                $array = array_merge($fields, $relations) ;

                $array['row'] = $tablerow;

            } else {
                $array = (array) $tablerow;
            }

            $value = $this->parser->compileString($column->name, $array);

        //eager loading smart syntax  relation.field
        } elseif (preg_match('#^[a-z0-9_-]+(?:\.[a-z0-9_-]+)+$#i',$column->name, $matches) && is_object($tablerow) ) {
            //switch to blade and god bless eloquent
            $expression = '{{$'.trim(str_replace('.','->', $column->name)).'}}';
            $fields = $tablerow->getAttributes();
            $relations = $tablerow->getRelations();
            $array = array_merge($fields, $relations) ;
            $value = $this->parser->compileString($expression, $array);

        //fieldname in a collection
        } elseif (is_object($tablerow)) {

            $value = @$tablerow->{$column->name};
            if ($sanitize) {
                $value = $this->sanitize($value);
            }
        //fieldname in an array
        } elseif (is_array($tablerow) && isset($tablerow[$column->name])) {

            $value = $tablerow[$column->name];

        //none found, cell will have the column name
        } else {
            $value = $column->name;
        }

        //decorators, should be moved in another method
        if ($column->link) {
            if (is_object($tablerow) && method_exists($tablerow, "getAttributes")) {
                $array = $tablerow->getAttributes();
                $array['row'] = $tablerow;
            } else {
                $array = (array) $tablerow;
            }
            $value =  '<a href="'.$this->parser->compileString($column->link, $array).'">'.$value.'</a>';
        }
        if (count($column->actions)>0) {
            $key = ($column->key != '') ?  $column->key : $this->key;
            $keyvalue = @$tablerow->{$key};

            $value = blade('datagrid.actions', array('uri' => $column->uri, 'id' => $keyvalue, 'actions' => $column->actions));

        }

        return $value;
    }

    public function getGrid($view = '')
    {
        $this->output = $this->build($view);

        return $this->output;
    }

    public function __toString()
    {
        if ($this->output == "") {
           try {
                $this->getGrid();
           }
           //to avoid the error "toString() must not throw an exception" (PHP limitation)
           //just return error as string
           catch (\Exception $e) {
               return $e->getMessage(). " Line ".$e->getLine();
           }

        }

        return $this->output;
    }

    public function edit($uri, $label='Edit', $actions='show|modify|delete', $key = '')
    {
        return $this->add('_edit', $label)->actions($uri, explode('|', $actions))->key($key);
    }

    /**
     * get column output 
     * @param $column_name
     * @return \Zofe\DataGrid\Column;
     */
    public function getColumn($column_name)
    {
        if (isset($this->columns[$column_name])) {
            return $this->columns[$column_name];
        }
    }

    /**
     * ad a closure to the rows
     * @param callable $callable
     * @return $this
     */
    public function row(\Closure $callable)
    {
        $this->row_callable[] = $callable;

        return $this;
    }

    /**
     * sanitize cell output if needed
     * 
     * @param $string
     * @return mixed
     */
    protected function sanitize($string)
    {
        return Str::words(nl2br(htmlspecialchars($string)), 30);
    }

    /**
     * return a attributes in string format
     * @return string
     */
    public function buildAttributes()
    {
        return array_to_attributes($this->attributes);
    }
}
