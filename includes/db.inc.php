<?
class space_mysqli extends mysqli{

	function query($query){
		$result = parent::query($query);
		if($this->error){
			//ob_clean();
			debug_print_backtrace();
			echo "<b>mysqli query error:</b> " . $this->error."\n"; 
			ob_flush();
			die();
		}else{
			return $result;
		}
	}

	function prepare($query){
		$stmt = new space_mysqli_stmt($this, $query);
		if($this->error){
			//ob_clean();
			debug_print_backtrace();
			echo "<b>mysqli prepare error:</b> " . $this->error."\n"; 
			ob_flush();
			die();
		}else{
			return $stmt;
		}
	}
}

class space_mysqli_stmt extends mysqli_stmt{

	function execute(){
		$result = parent::execute();
		if($this->error){
			//ob_clean();
			debug_print_backtrace();
			echo "<b>mysqli_stmt execute error:</b> " . $this->error."\n"; 
			ob_flush();
			die();
		}else{
			return $result;
		}
	}
}

$mysqli = new space_mysqli('127.0.0.1',$db_user,$db_pass,$db_db);
?>