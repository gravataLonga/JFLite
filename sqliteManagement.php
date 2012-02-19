<?php
/*


	Criado por Jonathan Fontes.
	2011 / 2012 
	
	v.0.2
	Um pequeno script para gerir o sqlite.



*/
class JFLite {
	
	var $conn;
	var $debug=FALSE;

 	function __construct(){
		// Sempre fazer reset a connecção.
 		$this->conn= NULL;
 	}

	function JFLite(){
		$this->__construct();
	}
	
	
	function open( $dbase ) {
		if($this->hasExtension($dbase,'txt') OR 
		   $this->hasExtension($dbase,'db') OR 
		   $this->hasExtension($dbase,'sqlite'))
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
		$arrField = array();
		$arrTypes = array();
		$arrValues = array();
		$query = "";
		
		foreach ( $options as $field => $types ){
			$arrField[] = $field;
			$arrTypes[] = $types;
			$arrValues[] = "'".$field."' ".$types;
		}
		
		$query = "CREATE TABLE {$table} ( ".implode(",", $arrValues)." );";
		if($this->query($query)){
			return $this;
		}
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
		
		$arrFields = array();
		$arrValues = array();
		$query = "INSERT INTO {$table} ";
		
		foreach ( $row as $field=>$value){
			$arrFields[] = "'".$field."'";
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
	
	
	
	/*
	*
	* Apagar uma tabela
	*
	*/
	function drop_table ( $name ){
		if( ! $this->table_exists($name)){
			return false;
		}
		
		$query = "drop table if exists {$name};";
		$query = $this->query($query);
		if( ! $query ){
			return FALSE;
		}
		return TRUE;
		
	}
	
	
	function quote( $string ){
		return sqlite_escape_string($string);
	}
	
	function close (){
		return sqlite_close($this->conn);
	}
	
	
	/*
	* protected functions
	*
	*
	*/
	
	// Verified if database exists.
	protected function hasDatabase ( $database ){
		if( ! file_exists($database) ){
			return FALSE;
		}
		return TRUE;
	}
	
	
	// Has extension ?!
	protected function hasExtension ( $filename, $extension ){
		$ext_tmp = end(explode(".",$filename));
		return $ext_tmp === $extension ? TRUE : FALSE;
	}
	

	
	protected function lastErro (){
		if($this->debug && 
			$this->conn && 
			is_resource($this->conn) && 
			sqlite_last_error($this->conn) !== 0)
		{
			echo sqlite_error_string(sqlite_last_error($this->conn));
		}
	}
	
	private function alerts ( $numb ){
		$arrAlert = array();
		$arrAlert[5] = "Can't open database or create.";
		$arrAlert[6] = "Table already exists!";
		$arrAlert[7] = "Type of fetch undefined.";
	} 
	
	function __destruct(){
		$this->close();
	}
}
?>
