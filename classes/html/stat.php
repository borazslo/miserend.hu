<?php

namespace Html;

use Illuminate\Database\Capsule\Manager as DB;

class Stat extends Html {

    public function __construct() {
        parent::__construct();
        $this->setTitle('Statisztika');

        global $user;
        if (!$user->loggedin) {
            addMessage("Hozzáférés megtagadva!", "danger");
            $this->redirect('/');
        }

        $groups = \Eloquent\Church::where('ok', 'i')
                ->countByUpdatedYear()
                ->get();
        $s1 = [];

        foreach ($groups as $group) {
            if ($group->updated_year > 0)
                $s1[] = [ $group->updated_year, $group->count_updated_year];
        }
        $this->s1 = $this->array2jslist($s1);

        $groups = \Eloquent\Remark::countByCreatedYear()->get();
        $s2 = [];
        foreach ($groups as $group) {
            if ($group->created_year > 0)
                $s2[] = [ $group->created_year, $group->count_created_year];
        }
        $this->s2 = $this->array2jslist($s2);

        $groups = \Eloquent\Church::where('ok', 'i')
                ->countByUpdatedMonth()
                ->where('frissites', '>', date('Y-m-d', strtotime('-1 year')))
                ->get();
        $s1 = [];
        $c = 0;
        foreach ($groups as $group) {
            if ($group->updated_month > 0)
                $s1[] = [ $c++, $group->count_updated_month];
        }
        $this->s3 = $this->array2jslist($s1);

        $groups = \Eloquent\Remark::countByCreatedMonth()
                ->where('created_at', '>', date('Y-m-d', strtotime('-1 year')))
                ->get();
        $s2 = [];
        $c = 0;
        foreach ($groups as $group) {
            if ($group->created_month > 0)
                $s2[] = [ $c++, $group->count_created_month];
        }
        $this->s4 = $this->array2jslist($s2);
    }

    function array2jslist($array) {
        $r = "";
        if (is_array($array)) {
            $r .= '[';
            foreach ($array as $key => $value) {
                $r .= $this->array2jslist($value);
                if ($key < count($array) - 1)
                    $r .= ',';
            }
            $r .= ']';
        } else {
            if (is_numeric($array))
                $r .= $array;
            else
                $r .= "'" . $array . "'";
        }
        return $r;
    }

}
