<?php
/*


	Create by Jonathan Fontes.
	www.jonathanfontes.com
	2011 / 2012 
	
	SQLite Management
	v.0.1
	
*/
class DB {
	
	protected 	var $conn;
	public 		var $debug=FALSE;

 	function __construct(){
 		$this->conn = NULL;
 	}
	
	// Abrir ou criar uma nova bases de dados.
	function open($dbase) {
		if($this->hasExtension($dbase,'txt') OR $this->hasExtension($dbase,'db') OR $this->hasExtension($dbase,'sqlite')){
 			$this->conn = @sqlite_open($dbase,0666,$error);
 		}
 		$conn = $this->conn;
 		if( ! is_resource($this->conn) && ( ! file_exists($dbase) )) {
			unset($this->conn);
			@unlink(basename($dbase));
		}
		return $conn;
	}
	
	// Execute query
	function query ( $sql ){
		// Has connection ?!
		if( ! $this->has_connection()){
			// No.
			return FALSE;
		}
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
	* @options são os campos a serem criados
	* @escapeIfExist, se a tabela existir sai.
	**/
	function create_table ($table, $options, $escapeIfExist = TRUE){
		// Options is an array ?!
		if(!is_array($options)){
			// No
			return FALSE;
		}
		
		
		if($this->table_exists($table) && $escapeIfExist){
			return $this;
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
	* Fetch result query.
	* Type: 0 -> ASSOCITIVE
	* 		1 -> NUMERIC
	*		2 -> BOTH (1 AND 2)
	* 		3 -> UNIQUE ROW
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
				return FALSE;
			break;
		}
	
	}
	
	/*
		@table is a table name,
			if not exists return false.
		
		@row is fields and values to insert.
			e.g. array("idNews"=>"1","news"=>"hello world!");
			
		return TRUE if success otherwise return false.
		
	*/
	
	function insert ($table, $row ){
		if( ! $this->table_exists($table)){
			return FALSE;
		}
		
		$arrFields = array();
		$arrValues = array();
		$query = "INSERT INTO {$table} ";
		
		// loop throw array $row
		foreach ( $row as $field=>$value){
			$arrFields[] = "'".$field."'";
			$arrValues[] = "'".$this->quote($value)."'";	
		}
		
		$query .= "(".implode(",",$arrFields).") VALUES (".implode(",",$arrValues).");";
		$query = $this->query($query);
		
		// Last erros!
		// Only get the erros if
		// $this->debug equals TRUE
		$this->lastErro();
		
		if( ! $query){
			return FALSE;
		}
		return $this;
	}
	
	
	
	/*
	*
	* Drop a table,
	* if a table isn't exists return false.
	* return true if success otherwise return false.
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
	
	/*
		Close connection.
	*/
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
	
	/*
		Verified if has connection.
		Return true if success otherwise return false.
	*/
	protected function has_connection (){
		return $this->conn == NULL ? FALSE : TRUE; 
	}
	
	// Has extension ?!
	// How to use, "teste.png","png" => true
	//			   "nin.txt","bd" => false	
	protected function hasExtension ( $filename, $extension ){
		$ext_tmp = end(explode(".",$filename));
		return $ext_tmp === $extension ? TRUE : FALSE;
	}
	
	// To protect values.
	protected function quote( $string ){
		return sqlite_escape_string($string);
	}
	
	// Return the lastest erros.
	protected function lastErro (){
		if($this->debug && $this->has_connection() && is_resource($this->conn) && sqlite_last_error($this->conn)!=0){
			echo sqlite_error_string(sqlite_last_error($this->conn));
		}
	}
	

	function __destruct(){
		$this->close();
	}
	
	
	
}
?>



