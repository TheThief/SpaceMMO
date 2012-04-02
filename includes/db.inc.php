<?
class space_mysqli extends mysqli {

	public function __construct($host, $user, $pass, $db) {
		parent::__construct($host, $user, $pass, $db);

		if ($this->connect_error) {
			//ob_clean();
			debug_print_backtrace();
			echo '<b>mysqli connect error:</b> (' . $this->connect_errno . ') ' . $this->connect_error . "\n";
			ob_flush();
			die();
		}
		parent::set_charset('utf8');
		if($this->error) {
			//ob_clean();
			debug_print_backtrace();
			echo '<b>mysqli set_charset error:</b> ' . $this->error . "\n";
			ob_flush();
			die();
		}
	}

	function query($query) {
		$result = parent::query($query);
		if($this->error) {
			//ob_clean();
			debug_print_backtrace();
			echo '<b>mysqli query error:</b> ' . $this->error . "\n";
			ob_flush();
			die();
		} else {
			return $result;
		}
	}

	function prepare($query) {
		$stmt = new space_mysqli_stmt($this, $query);
		if($this->error) {
			//ob_clean();
			debug_print_backtrace();
			echo '<b>mysqli prepare error:</b> ' . $this->error."\n";
			ob_flush();
			die();
		} else {
			return $stmt;
		}
	}
}

class space_mysqli_stmt extends mysqli_stmt {

	function execute() {
		$result = parent::execute();
		if($this->error) {
			//ob_clean();
			debug_print_backtrace();
			echo '<b>mysqli_stmt execute error:</b> ' . $this->error."\n"; 
			ob_flush();
			die();
		} else {
			return $result;
		}
	}
}
//test
$mysqli = new space_mysqli($db_server,$db_user,$db_pass,$db_db);
?>