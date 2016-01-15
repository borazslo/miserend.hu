<?php

namespace Eloquent;

use Illuminate\Database\Capsule\Manager as DB;

class Remark extends \Illuminate\Database\Eloquent\Model {

    public function church() {
        return $this->belongsTo('\Eloquent\Church');
    }

    function scopeSelectCreatedMonth($query) {
        return $query->addSelect(DB::raw('DATE_FORMAT(created_at,\'%Y-%m\') as created_month'), DB::raw('COUNT(*) as count_created_month'));
    }

    function scopeSelectCreatedYear($query) {
        return $query->addSelect(DB::raw('DATE_FORMAT(created_at,\'%Y\') as created_year'), DB::raw('COUNT(*) as count_created_year'));
    }

    function scopeCountByCreatedMonth($query) {
        return $query->selectCreatedMonth()
                        ->groupBy('created_month')->orderBy('created_month');
    }

    function scopeCountByCreatedYear($query) {
        return $query->selectCreatedYear()
                        ->groupBy('created_year')->orderBy('created_year');
    }

}
