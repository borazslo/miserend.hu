<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy;

/**
 * @deprecated
 */
class Path
{
    public ?string $className = null;

    public function __construct($path)
    {
        $this->url = strtolower($path);
        $this->convertAliases();
        $this->prepare();
    }

    public function prepare()
    {
        $this->arguments = [];
        $path = explode('/', $this->url);
        array_unshift($path, 'html');
        for ($i = \count($path) - 1; $i >= 0; --$i) {
            $file = implode('/', $path).'/'.$path[$i];
            if (file_exists(PATH.'classes/'.$file.'.php') && 'html/html' != $file) {
                $this->className = '\\'.preg_replace("/\//", '\\', $file);

                return;
            }
            $file = implode('/', $path);
            if (file_exists(PATH.'classes/'.$file.'.php') && 'html/html' != $file) {
                $this->className = '\\'.preg_replace("/\//", '\\', $file);

                return;
            }
            array_unshift($this->arguments, $path[$i]);
            unset($path[$i]);
        }

        return false;
    }

    public function convertAliases()
    {
        $replacementPatterns = [
            ["^templom\/([0-9]{1,5})\/eszrevetelek$", 'remark/list/$1'],
            ["^templom\/([0-9]{1,5})\/ujeszrevetel$", 'remark/addform/$1'],
            ["^templom\/([0-9]{1,5})\/ujkep$", 'uploadimage/$1'],
            ["^templom\/([0-9]{1,5})", 'church/$1'],
            ["^templom\/list$", 'church/catalogue'],
            ["^templom\/new", 'church/edit'],
            ["^remark\/([0-9]{1,5})\/feedback", 'email/remarkfeedback/$1'],
            ["^egyhazmegye\/list", 'diocesecatalogue'],
            ['^impresszum$', 'staticpage/impressum'],
            ['^gdpr$', 'staticpage/gdpr'],
            ['^hazirend$', 'staticpage/termsandconditions'],
            ['^terkep$', 'map'],
            ['^$', 'home'],
        ];
        foreach ($replacementPatterns as $replacementPattern) {
            $patterns[] = '/'.$replacementPattern[0].'/i';
            $replacements[] = $replacementPattern[1];
        }
        $this->url = preg_replace($patterns, $replacements, $this->url);
    }
}
