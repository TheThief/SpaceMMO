<?php
class UserModel extends BaseModel
{
    public function login($username, $password)
    {
        $query = $this->mysqli->prepare('SELECT userID,lastlogin from users WHERE username=? AND passhash=UNHEX(?)');

        $query->bind_param('ss', $username, $passhash);
        $passhash = sha1($password);

        $query->execute();

        $query->bind_result($userid, $lastlogin);
        $query->fetch();
        $query->close();

        if (!$userid)
        {
            $result = new Result();
            $result->status = 'fail';
            $result->data['reason'] = '';
            return $result;
        }

        $query = $this->mysqli->prepare('UPDATE users SET phpsessionid=UNHEX(?), lastlogin=NOW() WHERE userID=?');

        $session_key = sha1($userid . mt_rand() . $lastlogin . mt_rand() . time());
        $result = setcookie('SESSION_KEY', $userid . ':' . $session_key, 0, '/', $_SERVER['SERVER_NAME'], false, true);
        if (!$result)
        {
            user_error('Failed to set SESSION_KEY', E_USER_ERROR);
        }

        $query->bind_param('si', $sessionid, $userid);
        $sessionid = $session_key;

        $query->execute();

        $result = new Result();
        $result->status = 'success';
        return $result;
    }
    
    public function validateApiKey($apikey)
    {
        $query = $this->mysqli->prepare('SELECT userid from users WHERE apikey=UNHEX(?)');
        $query->bind_param('s', $apikey);

        $query->execute();

        $query->bind_result($userid);
        $query->fetch();
        $query->close();

        return $userid ? $userid : null;
    }
    
    public function validateSessionKey($sessionkey)
    {
        $query = $this->mysqli->prepare('SELECT 1 from users WHERE userid=? AND phpsessionid=UNHEX(?)');
        $query->bind_param('ss', $userid, $sessionid);
        list($userid, $sessionid) = explode(':', $sessionkey, 2);

        $query->execute();

        $isLoggedIn = false;
        $query->bind_result($isLoggedIn);
        $query->fetch();
        $query->close();

        return $isLoggedIn ? $userid : null;
    }
}
