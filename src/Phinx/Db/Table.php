<?php
/**
 * Phinx
 *
 * (The MIT license)
 * Copyright (c) 2012 Rob Morgan
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated * documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 * 
 * @package    Phinx
 * @subpackage Phinx\Db
 */
namespace Phinx\Db;

use Phinx\Db\Adapter\AdapterInterface;

/**
 *
 * This object is based loosely on: http://api.rubyonrails.org/classes/ActiveRecord/ConnectionAdapters/Table.html.
 */
class Table
{
    /**
     * @var string
     */
    protected $name;
    
    /**
     * @var array
     */
    protected $options;
    
    /**
     * @var AdapterInterface
     */
    protected $adaper;
    
    /**
     * @var array
     */
    protected $columns;
    
    /**
     * @var array
     */
    protected $indexes;
    
    /**
     * Class Constuctor.
     *
     * @param string $name Table Name
     * @param array $options Options
     * @param AdapterInterface $adapter Database Adapter
     * @return void
     */
    public function __construct($name, $options = array(), AdapterInterface $adapter = null)
    {
        $this->setName($name);
        $this->setOptions($options);
        
        if (null !== $adapter) {
            $this->setAdapter($adapter);
        }
    }
    
    /**
     * Sets the table name.
     *
     * @param string $name Table Name
     * @return Table
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    /**
     * Gets the table name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Sets the table options.
     * 
     * @param array $options
     * @return Table
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }
    
    /**
     * Gets the table options.
     * 
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
    
    /**
     * Sets the database adapter.
     *
     * @param AdapterInterface $adapter Database Adapter
     * @return Environment
     */
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }
    
    /**
     * Gets the database adapter.
     *
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }
    
    /**
     * Does the table exist?
     *
     * @return boolean
     */
    public function exists()
    {
        return $this->getAdapter()->hasTable($this->getName());
    }
    
    /**
     * Drops the database table.
     *
     * @return void
     */
    public function drop()
    {
        $this->getAdapter()->dropTable($this->getName());
    }
    
    /**
     * Gets an array of columns waiting to be committed.
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }
    
    /**
     * Gets an array of indexes waiting to be committed.
     * 
     * @return array
     */
    public function getIndexes()
    {
        return $this->indexes;
    }
    
    /**
     * Add a table column.
     *
     * Type can be: primary_key, string, text, integer, float, decimal, datetime, timestamp, time, date, binary, boolean.
     * 
     * Valid options can be: limit, default, null, precision or scale.
     *
     * @param string $columnName Column Name
     * @param string $type Column Type
     * @param array $options Column Options
     * @return Table
     */
    public function addColumn($columnName, $type, $options = array())
    {
        // check column type
        if (is_null($type) || !in_array($type, $this->getAdapter()->getLogicalTypes())) {
            throw new \RuntimeException('An invalid column type was specified.');
        }
        
        // Valid Options
        $validOptions = array('limit', 'default', 'null', 'precision', 'scale');
        foreach ($options as $option => $value) {
            if (!in_array($option, $validOptions)) {
                throw new \RuntimeException('\'' . $option . '\' is not a valid column option.');
            }
        }
        
        // Set Defaults
        $defaults = array(
            'limit'   => null, // no limit
            'null'    => false
        );
        
        // Merge Options
        $options = array_merge($defaults, $options);

        // Define Column
        $column = array(
            'name'    => $columnName,
            'type'    => $type,
            'options' => $options
        );
        $this->columns[] = $column;
        
        return $this;
    }
    
    /**
     * Remove a table column.
     *
     * @param string $columnName Column Name
     * @return Table
     */
    public function removeColumn($columnName)
    {
        return $this;
    }
    
    /**
     * Rename a table column.
     *
     * @param string $oldName Old Column Name
     * @param string $newName New Column Name
     * @return Table
     */
    public function renameColumn($oldName, $newName)
    {
        return $this;
    }
    
    /**
     * Change a table column type.
     *
     * @param string $columnName Column Name
     * @param string $newColumnType New Column Type
     * @return Table
     */
    public function changeColumn($columnName, $newColumnType)
    {
        return $this;
    }
    
    /**
     * Add an index to a database table.
     * 
     * In $options you can specific unique = true/false or name (index name).
     *
     * @param mixed $columns Table Column(s)
     * @param array $options Index Options
     * @return Table
     */
    public function addIndex($columns, $options = array())
    {
        // Define Index
        $index = array(
            'columns' => $columns,
            'options' => $options
        );
        $this->indexes[] = $index;
        
        return $this;
    }
    
    /**
     * Removes the given index from a table.
     *
     * @param array $options Options
     * @return Table
     */
    public function removeIndex($options = array())
    {
        $this->getAdapter()->removeIndex($this->getName(), $options);
        return $this;
    }
    
    /**
     * Checks to see if an index exists.
     *
     * @param string $columnName Column Name
     * @param array $options Options
     * @return boolean
     */
    public function hasIndex($columnName, $options = array())
    {
        return $this->getAdapter()->hasIndex($this->getName(), $columnName, $options);
    }
    
    /**
     * Commits the table changes.
     * 
     * @return void
     */
    public function save()
    {
        if ($this->exists()) {
            // update table
            echo 'updating table: ' . $this->getName() . PHP_EOL;
            foreach ($this->getColumns() as $column) {
                $this->getAdapter()->addColumn($this->getName(), $column['name'], $column['type'], $column['options']);
            }
            
            foreach ($this->getIndexes() as $index) {
                $this->getAdapter()->addIndex($this->getName(), $index['columns'], $index['options']);
            }
        } else {
            // create table
            $this->getAdapter()->createTable($this->getName(), $this->getColumns(), $this->getIndexes(), $this->getOptions());
        }
    }
}