<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Html\Ajax;

use App\Legacy\Response\HttpResponseInterface;
use App\Legacy\Response\HttpResponseTrait;
use App\Model\KeywordShortcut;
use Illuminate\Database\Capsule\Manager as DB;
use Symfony\Component\HttpFoundation\JsonResponse;

class AutocompleteCity extends Ajax implements HttpResponseInterface
{
    use HttpResponseTrait;

    private $input;

    public function __construct()
    {
        $this->input = $_REQUEST;

        $return = [];

        if (mb_strlen($this->input['query']) >= 2) {
            if (!preg_match('/\*$/', $this->input['query'])) {
                $return[] = ['label' => $_REQUEST['query'].'* <i>(Minden '.$_REQUEST['query'].'-al kezdődő)</i>', 'value' => $_REQUEST['query'].'*'];
            }
            if (!preg_match('/^\*(.*)\*$/', $this->input['query'])) {
                if (preg_match('/^\*/', $this->input['query'])) {
                    $return[] = ['label' => $_REQUEST['query'].'* <i>(Minden '.$_REQUEST['query'].'-t tartalmazó)</i>', 'value' => $_REQUEST['query'].'*'];
                } elseif (preg_match('/\*$/', $this->input['query'])) {
                    $return[] = ['label' => '*'.$_REQUEST['query'].' <i>(Minden '.$_REQUEST['query'].'-t tartalmazó)</i>', 'value' => '*'.$_REQUEST['query']];
                } else {
                    $return[] = ['label' => '*'.$_REQUEST['query'].'* <i>(Minden '.$_REQUEST['query'].'-t tartalmazó)</i>', 'value' => '*'.$_REQUEST['query'].'*'];
                }
            }
            if (!preg_match('/\*$/', $this->input['query'])) {
                $this->input['query'] .= '*';
            }
            $keyword = preg_replace("/\*/", '%', $this->input['query']);
            $keywordEmpty = preg_replace('/%/', '', $keyword);

            $administratives = KeywordShortcut::where('type', 'administrative')
                ->where('value', 'LIKE', $keyword)
                ->groupBy('value')
                ->orderBy('value')
                ->take(10)
                ->get();

            foreach ($administratives as $administrative) {
                $label = preg_replace('/('.$keywordEmpty.')/i', '<strong>$1</strong>', $administrative->value);
                $return[$administrative->value] = ['label' => $label, 'value' => $administrative->value];
            }

            $cities = DB::table('templomok')
                ->select('varos')
                ->where('ok', 'i')
                ->where('varos', 'like', $keyword)
                ->groupBy('varos')
                ->orderBy('varos')
                ->take(10)
                ->get();

            foreach ($cities as $city) {
                $label = preg_replace('/('.$keywordEmpty.')/i', '<strong>$1</strong>', $city->varos);
                $return[$city->varos] = ['label' => $label, 'value' => $city->varos];
            }
            ksort($return);
        }

        $this->response = new JsonResponse();
        $this->response->setData(['results' => $return]);
    }
}
