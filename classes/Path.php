<?php

class Path {

    public function __construct($path) {
        $this->url = $path;
        $this->convertAliases();
        $this->prepare();
    }

    public function prepare() {
        $folder = "classes/";
        $this->arguments = array();
        $path = explode('/', $this->url);
        array_unshift($path, "html");
        for ($i = count($path) - 1; $i >= 0; $i--) {

            $file = implode("/", $path) . "/" . $path[$i];
            if (file_exists($folder . $file . ".php")) {
                $this->className = '\\' . preg_replace("/\//", "\\", $file);
                return;
            }
            $file = implode("/", $path);
            if (file_exists($folder . $file . ".php")) {
                $this->className = '\\' . preg_replace("/\//", "\\", $file);
                return;
            }
            array_unshift($this->arguments, $path[$i]);
            unset($path[$i]);
        }
        return false;
    }

    function convertAliases() {

        $replacementPatterns = [
            ["^templom\/([0-9]{1,5})\/eszrevetelek$", "remark/list/$1"],
            ["^templom\/([0-9]{1,5})\/ujeszrevetel$", "remark/addform/$1"],
            ["^templom\/([0-9]{1,5})\/ujkep$", "uploadimage/$1"],
            ["^templom\/([0-9]{1,5})", "church/$1"],
            ["^templom\/list$", "church/catalogue"],
            ["^templom\/new", "church/edit"],
            ["^remark\/([0-9]{1,5})\/feedback", "email/remarkfeedback/$1"],
            ["^egyhazmegye\/list", "diocesecatalogue"],
            ["^impresszum$", "staticpage/impressum"],
            ["^hazirend$", "staticpage/termsandconditions"],
            ["^$", "home"]
        ];
        foreach ($replacementPatterns as $replacementPattern) {
            $patterns[] = "/" . $replacementPattern[0] . "/i";
            $replacements[] = $replacementPattern[1];
        }
        $this->url = preg_replace($patterns, $replacements, $this->url);
    }

}
