<?
class space_mysqli extends mysqli{

	function query($query){
		$result = parent::query($query);
		if($this->error){
			ob_clean();
			echo "<b>mysqli query error:</b> " . $this->error; 
			ob_flush();
			die();
		}else{
			return $result;
		}
	}

	function prepare($query){
		$stmt = new space_mysqli_stmt($this, $query);
		if($this->error){
			ob_clean();
			echo "<b>mysqli prepare error:</b> " . $this->error; 
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
			ob_clean();
			echo "<b>mysqli_stmt execute error:</b> " . $this->error; 
			ob_flush();
			die();
		}else{
			return $result;
		}
	}
}

$mysqli = new space_mysqli($db_server,$db_user,$db_pass,$db_db);
?>