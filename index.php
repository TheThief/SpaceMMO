<?php

spl_autoload_register('apiAutoload');
function apiAutoload($classname)
{
    if (preg_match('/[a-zA-Z0-9_]+Adapter$/', $classname))
    {
        include __DIR__ . '/mva/adapters/' . $classname . '.php';
        return true;
    }
    elseif (preg_match('/[a-zA-Z0-9_]+Model$/', $classname))
    {
        include __DIR__ . '/mva/models/' . $classname . '.php';
        return true;
    }
    elseif (preg_match('/[a-zA-Z0-9_]+View$/', $classname))
    {
        include __DIR__ . '/mva/views/' . $classname . '.php';
        return true;
    }
}

class Request
{
    public $path;
    public $verb;
    public $parameters;
    public $format;

    public function __construct()
    {
        $this->verb = $_SERVER['REQUEST_METHOD'];
        $this->path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/';
        $this->format = false;
        $this->parseIncomingParams();

        return true;
    }

    public function parseIncomingParams()
    {
        // first of all, pull the GET vars
        $this->parameters = $_GET;

        // then overwrite with any POST vars
        $this->parameters = array_replace($this->parameters, $_POST);

        // and then handle json input
        if (isset($_SERVER['CONTENT_TYPE']))
        {
            switch ($_SERVER['CONTENT_TYPE'])
            {
                case 'application/json':
                    $body = file_get_contents('php://input');
                    $json_parameters = json_decode($body);
                    $this->parameters = array_replace($this->parameters, json_parameters);
                    $this->format = 'json';
                    break;
                //case 'application/x-www-form-urlencoded':
                //    parse_str($body, $postvars);
                //    foreach($postvars as $field => $value)
                //    {
                //        $parameters[$field] = $value;
                //    }
                //    $this->format = 'html';
                //    break;
                default:
                    // we could parse other supported formats here
                    break;
            }
        }
    }

    public function chooseFormat(array $supported)
    {
        //$supported = array_map('strtolower', $supported);

        // support format with a "format" parameter
        if (isset($this->parameters['format']))
        {
            if (in_array(strtolower($this->parameters['format']), $supported, true))
            {
                $this->format = $this->parameters['format'];
                return true;
            }
            else
            {
                return false;
            }
        }
        if (!isset($_SERVER['HTTP_ACCEPT']))
        {
            // HTTP spec says that if there is no accept: header then we should assume everything is accepted
            $this->format = reset($supported);
            return true;
        }

        $best_accept = '*/*';
        $best_format = reset($supported);
        $best_q = 0;
        $accepts = explode(',', $_SERVER['HTTP_ACCEPT']);
        foreach ($accepts as $accept)
        {
            $accept = strtolower(trim($accept));
            $q = 1;
            if (strpos($accept, ';q='))
            {
                list($accept, $q) = explode(';q=', $accept, 2);
                $q = (float)$q;
            }

            if ($q >= $best_q)
            {
                if ($accept === '*/*')
                {
                    if ($best_accept === '*/*')
                    {
                        $best_q = $q;
                    }
                }
                // either the quality is better or the accept is more precise
                elseif ($q > $best_q || $best_accept === '*/*' || (substr($best_accept, -2) === '/*' && substr($accept, -2) !== '/*'))
                {
                    foreach ($supported as $support => $format)
                    {
                        if ($accept === $support)
                        {
                            if ($q == 1)
                            {
                                $this->format = $format;
                                return true;
                            }
                            else
                            {
                                $best_q = $q;
                                $best_accept = $accept;
                                $best_format = $format;
                                break;
                            }
                        }
                        elseif (substr($accept, -2) === '/*')
                        {
                            if (substr($accept, 0, -2) === $support)
                            {
                                $best_q = $q;
                                $best_accept = $accept;
                                $best_format = $format;
                                break;
                            }
                        }
                    }
                }
            }
        }

        if ($best_q > 0)
        {
            $this->format = $best_format;
            return true;
        }
        return false;
    }
}

$request = new Request();
if (!$request->chooseFormat(array('text/html' => 'html', 'application/json' => 'json', 'application/x-json' => 'json')))
{
    header($_SERVER["SERVER_PROTOCOL"]." 406 Not Acceptable");
    exit;
}

list(, $r_adapter, $r_action, $request->path) = explode('/', $request->path, 4) + array('', '', '', '');
if ($r_adapter === '')
{
    $r_adapter = 'default';
}
if ($r_action === '')
{
    $r_action = 'default';
}

$r_adapter = ucfirst(strtolower($r_adapter));
$adapter_name = $r_adapter . 'Adapter';
if (class_exists($adapter_name))
{
    $adapter = new $adapter_name();
    $action_name = $r_action . 'Action';
    $adapter->$action_name($request);
}
