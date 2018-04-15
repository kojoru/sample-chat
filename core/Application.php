<?php

namespace SampleChat\Core;

class Application {
    function run() {
        $test = new \SampleChat\Database\DbConnection();
        if ($this->getCurrentUrl() == "/") {
            readfile(PUBLIC_DIR."/index.html");
        } else {
            http_response_code(404);
            echo "404: ".htmlspecialchars($this->getCurrentUrl())." not found";
        }
        
    }

    private function getCurrentUrl() {
        $path = urldecode(trim($_SERVER['REQUEST_URI']));
        if (($position = strpos($path, '?')) !== FALSE)
        {
            $path = substr($path, 0, $position);
        }
        return $path;
    }
}
