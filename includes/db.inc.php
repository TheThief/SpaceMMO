<?
class spacemysqli extends mysqli{

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
}

$mysqli = new spacemysqli($db_server,$db_user,$db_pass,$db_db);
?>