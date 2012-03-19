<?php
/**
 * API class
 *
 * Simple class to handle API output
 *
 * @author Mark
 */
class API
{
    /**
     * The format to output in
     * @access private
     * @var APIFormat
     */
    private $format;

    /**
     * @param int $format APIFormat constant member to format the output in e.g: APIFormat::JSON
     */
    function __construct($format){
        $this->format = $format;
    }

    /**
     * Output the variables in the correct format
     *
     * @param array $variables An array of variable to output in the requested format
     */
    function output($variables){
        switch($this->format){
            case APIFormat::JSON:
                header("Content-type: application/json");
                echo json_encode($variables);
                break;
            default:
                break;
        }
    }
}

/**
 * enum style class to define APIFormat
 *
 * @author Mark
 */
abstract class APIFormat {
    /**
     * JSON (JavaScript Object Notation) format
     */
    const JSON = 0;
}

