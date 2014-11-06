<?php
class JsonDefaultView
{
    public function render($template, $content)
    {
        header('Content-Type: application/json; charset=utf8');
        http_response_code(400); // 400 Bad Request

        // echo json_encode($content);
        return true;
    }
}
