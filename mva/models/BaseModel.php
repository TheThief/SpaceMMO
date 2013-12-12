<?php
class Result
{
    public $status;
    public $data;
}

class space_mysqli extends mysqli {

	public function __construct($host, $user, $pass, $db)
    {
		parent::__construct($host, $user, $pass, $db);

		if ($this->connect_error)
        {
            user_error('mysqli connect error: (' . $this->connect_errno . ') ' . $this->connect_error, E_USER_ERROR);
		}
		parent::set_charset('utf8');
		if ($this->error)
        {
            user_error('mysqli set_charset error: ' . $this->error, E_USER_ERROR);
		}
	}

	function query($query)
    {
		$result = parent::query($query);
		if ($this->error)
        {
            user_error('mysqli query error: ' . $this->error, E_USER_ERROR);
		}
        else
        {
			return $result;
		}
	}

	function prepare($query)
    {
		$stmt = new space_mysqli_stmt($this, $query);
		if($this->error)
        {
            user_error('mysqli prepare error: ' . $this->error, E_USER_ERROR);
		}
        else
        {
			return $stmt;
		}
	}
}

class space_mysqli_stmt extends mysqli_stmt {

	function execute()
    {
		$result = parent::execute();
		if($this->error)
        {
            user_error('mysqli_stmt execute error: ' . $this->error, E_USER_ERROR);
		}
        else
        {
			return $result;
		}
	}
}

class BaseModel
{
    protected $mysqli;
    
	public function __construct()
    {
        $this->mysqli = new space_mysqli($db_server,$db_user,$db_pass,$db_db);
    }
}
