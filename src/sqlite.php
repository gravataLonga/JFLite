<?php
namespace Gravatalonga\sqlitemng;

/**
 * Sqlite Manager - Built First time on 2014
 */
class Sqlite
{
    
    public $conn;
    public $debug = false;
    protected $_last_query = '';

    public function __construct($config)
    {
        $this->conn = null;
    }
    
    
    public function open($dbase)
    {
        if ($this->hasDatabase($dbase, 'txt') or
           $this->hasDatabase($dbase, 'db') or
           $this->hasDatabase($dbase, 'sqlite')) {
            $this->conn = @sqlite_open($dbase, 0666, $error);
        }
        $conn = $this->conn;
        if (!is_resource($this->conn) && !file_exists($dbase)) {
            unset($this->conn);
            @unlink(basename($dbase));
            die($this->alerts(5));
        }
        return $conn;
    }
    
    // Execute query
    public function query($sql)
    {
        // cache lastest query.
        $this->_last_query = $sql;
        return sqlite_query($this->conn, $sql);
    }
    
    /*
     *
     * Verifica se uma tabela existe.
     *
     */
    public function tableExists($tablename)
    {
        $q = "SELECT name FROM sqlite_master WHERE type='table' AND name='".$this->quote($tablename)."'";
        $q = $this->query($q);
        if (sqlite_num_rows($q)>0) {
            return true;
        }
        return false;
    }
    
    /*
    * 
    * @table é o nome da tabela
    * @options sÃ£o os campos a serem criados
    * @escapeIfExist, se a tabela existir sai.
    **/
    public function createTable($table, $options, $escape_exists = true)
    {
        // Options is an array ?!
        if (!is_array($options)) {
            // No
            return false;
        }
        
        
        if ($this->tableExists($table) && $escape_exists) {
            $this->close();
            die($this->alerts(6));
        } else if ($this->tableExists($table) && $escape_exists === false) {
            $this->dropTable($table);
        }
        
        
        // helper to build a tables
        $arrValues = array();
        
        // Foreach for all fields.
        foreach ($options as $field => $types) {
            $arrValues[] = "'".$field."' ".$types;
        }
        
        $query = "CREATE TABLE {$table} ( ".implode(",", $arrValues)." );";
        
        // Query SQL
        if ($this->query($query)) {
            // Success!
            return $this;
        }
        
        // Ups, something went wrong!
        return false;
        
    }
    
    /*
    *
    * Para retornar os campos pesquisados.
    *
    */
    
    public function fetch($resource, $type = '0')
    {
        if (!is_resource($resource)) {
            return false;
        }

        switch ($type) {
            case '0':
                return sqlite_fetch_all($resource, SQLITE_ASSOC);
            break;
            case '1':
                return sqlite_fetch_all($resource, SQLITE_NUM);
            break;
            case '2':
                return sqlite_fetch_all($resource, SQLITE_BOTH);
            break;
            case '3':
                return sqlite_fetch_array($resource, SQLITE_ASSOC);
            break;
            default:
                $this->close();
                die($this->alerts(7));
            break;
        }
    
    }
    
    public function insert($table, $row)
    {
        if (!$this->tableExists($table)) {
            return false;
        }
        
        $arrValues = array();
        $query = "INSERT INTO {$table} ";
        
        foreach ($row as $field => $value) {
            $arrValues[] = "'".$this->quote($value)."'";
        }
        
        $query .= "(".implode(",", $arrFields).") VALUES (".implode(",", $arrValues).");";
        $query = $this->query($query);
        
        // Last erros!
        $this->lastErro();
        
        if (!$query) {
            return false;
        }
        return $this;
    }
    
    public function insertId()
    {
        if ($this->conn == null) {
            return false;
        }
        return sqlite_last_insert_rowid($this->conn);
    }
    
    public function lastQuery()
    {
        return $this->_last_query;
    }
    
    /*
    *
    * Apagar uma tabela
    *
    */
    public function dropTable($name)
    {
        if (!$this->tableExists($name)) {
            return false;
        }
        
        $query = "drop table {$name};";
        $query = $this->query($query);
        if (!$query) {
            return false;
        }
        return true;
    }
    
    
    public function quote($string)
    {
        return sqlite_escape_string($string);
    }
    
    // Verified if database exists.
    // fn_success is a callback in case have database
    // fn_fail is a callback in casa hasn't a database
    public function hasDatabase($database, \Closure $fn_success = null, \Closure $fn_fail = null)
    {
        if (!file_exists($database)) {
            if (is_callable($fn_fail) && $fn_fail !== null) {
                $fn_fail();
            }
            return false;
        }

        if (is_callable($fn_success) && $fn_fail !== null) {
            $fn_success();
        }
        return true;
    }
    
    public function close()
    {
        return sqlite_close($this->conn);
    }
    
    
    /*
    * protected functions
    *
    *
    */
    
    
    // Has extension ?!
    protected function hasExtension($filename, $extension)
    {
        $ext_tmp = end(explode(".", $filename));
        return $ext_tmp === $extension ? true : false;
    }
    

    // Get lastest Errors
    protected function lastErro()
    {
        if ($this->debug &&
            $this->conn &&
            is_resource($this->conn) &&
            sqlite_last_error($this->conn) !== 0) {
            echo sqlite_error_string(sqlite_last_error($this->conn));
        }
    }
    
    // Internals erros!
    private function alerts($numb)
    {
        $arrAlert = array();
        $arrAlert[5] = "Can't open database or create.";
        $arrAlert[6] = "Table already exists!";
        $arrAlert[7] = "Type of fetch undefined.";
    }
    
    // When destruct class, close connection if is open!
    public function __destruct()
    {
        $this->close();
    }
}
