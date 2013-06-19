<?php
/*


	Criado por Jonathan Fontes.
	2011 / 2012 
	
	v.0.5
	Um pequeno script para gerir o sqlite.



*/

/**
* Namespace of class.
*/
namespace gravataLonga;

class JFLite {
	
	var 		$conn;
	var 		$debug=FALSE;
	protected 	$_last_query = "";

 	function __construct(){
		// Sempre fazer reset a connecção.
 		$this->conn= NULL;
 	}

	function JFLite(){
		$this->__construct();
	}
	
	
	function open( $dbase ) {
		if($this->has_extension($dbase,'txt') OR 
		   $this->has_extension($dbase,'db') OR 
		   $this->has_extension($dbase,'sqlite'))
		{
 			$this->conn = @sqlite_open($dbase,0666,$error);
 		}
 		$conn = $this->conn;
 		if( ! is_resource($this->conn) && ( ! file_exists($dbase) )) {
			unset($this->conn);
			@unlink(basename($dbase));
			die($this->alerts(5));
		}
		return $conn;
	}
	
	// Execute query
	function query ( $sql ){
		// cache lastest query.
		$this->_last_query = $sql;
		return sqlite_query($this->conn,$sql);
	}
	
	/*
	 *
	 * Verifica se uma tabela existe.
	 *
	 */
	 
	function table_exists($tablename){
		$q = "SELECT name FROM sqlite_master WHERE type='table' AND name='".$this->quote($tablename)."'";
		$q = $this->query($q);
		if(sqlite_num_rows($q)>0){
			return TRUE;
		}
		return FALSE;
	} 
	 
	
	/*
	* 
	* @table é o nome da tabela
	* @options sÃ£o os campos a serem criados
	* @escapeIfExist, se a tabela existir sai.
	**/
	function create_table ($table, $options, $escape_exists = TRUE){
		// Options is an array ?!
		if(!is_array($options)){
			// No
			return FALSE;
		}
		
		
		if($this->table_exists($table) && $escape_exists){
			$this->close();
			die($this->alerts(6));
		}else if($this->table_exists($table) && $escape_exists === FALSE){
			$this->drop_table($table);
		}
		
		
		// helper to build a tables
		$arrValues = array();
		
		// Foreach for all fields.
		foreach ( $options as $field => $types ){
			$arrValues[] = "'".$field."' ".$types;
		}
		
		$query = "CREATE TABLE {$table} ( ".implode(",", $arrValues)." );";
		
		// Query SQL
		if($this->query($query)){
			// Success!
			return $this;
		}
		
		// Ups, something went wrong!
		return FALSE;
		
	}
	
	/*
	*
	* Para retornar os campos pesquisados.
	*
	*/
	
	function fetch ( $resource , $type = '0'){
		if( ! is_resource($resource)){
			return FALSE;
		}
		switch ($type) {
			case '0':
				
				return sqlite_fetch_all($resource,SQLITE_ASSOC);
			break;
			case '1':
				return sqlite_fetch_all($resource,SQLITE_NUM);	
			break;
			case '2':
				 return sqlite_fetch_all($resource,SQLITE_BOTH);
			break;
			case '3':
				return sqlite_fetch_array($resource,SQLITE_ASSOC);
			break;
			default:
				$this->close();
				die($this->alerts(7));
			break;
		}
	
	}
	
	function insert ($table, $row ){
		if( ! $this->table_exists($table)){
			return FALSE;
		}
		
		$arrValues = array();
		$query = "INSERT INTO {$table} ";
		
		foreach ( $row as $field=>$value){
			$arrValues[] = "'".$this->quote($value)."'";	
		}
		
		$query .= "(".implode(",",$arrFields).") VALUES (".implode(",",$arrValues).");";
		$query = $this->query($query);
		
		// Last erros!
		$this->lastErro();
		
		if( ! $query){
			return FALSE;
		}
		return $this;
	}
	
	function insert_id ()
	{
		if ( $this->conn == NULL){
			return FALSE;
		}
		return sqlite_last_insert_rowid ( $this->conn );
	}
	
	function last_query ()
	{
		return $this->_last_query;
	}
	
	/*
	*
	* Apagar uma tabela
	*
	*/
	function drop_table ( $name ){
		if( ! $this->table_exists($name)){
			return FALSE;
		}
		
		$query = "drop table {$name};";
		$query = $this->query($query);
		if( ! $query ){
			return FALSE;
		}
		return TRUE;
		
	}
	
	
	function quote( $string ){
		return sqlite_escape_string($string);
	}
	
	// Verified if database exists.
	// fn_success is a callback in case have database
	// fn_fail is a callback in casa hasn't a database
	public function has_database ( $database, $fn_success=NULL, $fn_fail=NULL ){
		if( ! file_exists($database) ){
			if( is_callable($fn_fail) && $fn_fail !== NULL)
			{
				$fn_fail();
			}
			return FALSE;
		}
		if( is_callable($fn_success) && $fn_fail !== NULL)
		{
			$fn_fail();
		}
		return TRUE;
	}
	
	function close (){
		return sqlite_close($this->conn);
	}
	
	
	/*
	* protected functions
	*
	*
	*/
	
	
	// Has extension ?!
	protected function has_extension ( $filename, $extension ){
		$ext_tmp = end(explode(".",$filename));
		return $ext_tmp === $extension ? TRUE : FALSE;
	}
	

	// Get lastest Errors
	protected function last_erro (){
		if($this->debug && 
			$this->conn && 
			is_resource($this->conn) && 
			sqlite_last_error($this->conn) !== 0)
		{
			echo sqlite_error_string(sqlite_last_error($this->conn));
		}
	}
	
	// Internals erros!
	private function alerts ( $numb ){
		$arrAlert = array();
		$arrAlert[5] = "Can't open database or create.";
		$arrAlert[6] = "Table already exists!";
		$arrAlert[7] = "Type of fetch undefined.";
	} 
	
	// When destruct class, close connection if is open! 
	function __destruct(){
		$this->close();
	}
}
?>
