<?php

use Illuminate\Database\Capsule\Manager as DB;

class Remark {

    public $table = 'remarks';

    function __construct($rid = false) {
        if (!isset($rid) OR ! is_numeric($rid)) {
            global $user;
            $this->name = $user->nickname;
            $this->username = $user->username;
            //email fakultatív
            //TODO: megbízható?? reliable
            $this->timestamp = date('Y-m-d H:i:s');
            $this->state = 'u';
            $this->text = "";
        } else {
            $query = "SELECT * FROM  " . $this->table . " WHERE id = $rid LIMIT 1";
            $result = mysql_query($query);
            $x = mysql_fetch_assoc($result);
            if (is_array($x)) {
                foreach ($x as $key => $value) {
                    $this->$key = $value;
                }
                $this->rid = $this->id;
                $this->tid = $this->church_id;
                $this->username = $this->login;

                if ($this->username != '') {
                    $this->user = new \User($this->username);
                }

                $this->adminmegj = preg_replace('/=("|\'|)img\//i', '=$1/img/', $this->adminmegj);

                $this->marker['url'] = "javascript:OpenScrollWindow('/templom/" . $this->tid . "/eszrevetelek',550,500);";
                if ($this->allapot == 'u') {
                    $this->marker['text'] = "Új észrevétel!";
                    $this->marker['html'] = "<img src=/img/csomag.gif title='" . $this->marker['text'] . "' align=absmiddle border=0> ";
                    $this->marker['mark'] = 'u';
                } elseif ($this->allapot == 'f') {
                    $this->marker['text'] = "Észrevétel javítása folyamatban!";
                    $this->marker['html'] = "<img src=/img/csomagf.gif title='" . $this->marker['text'] . "' align=absmiddle border=0> ";
                    $this->marker['mark'] = 'f';
                } elseif ($this->allapot == 'j') {
                    $this->marker['text'] = "Észrevétel feldolgova.";
                    $this->marker['html'] = "<img src=/img/csomag1.gif title='" . $this->marker['text'] . "' align=absmiddle border=0> ";
                    $this->marker['mark'] = 'j';
                } else {
                    $this->marker['text'] = "Nincsenek állapota";
                    $this->marker['html'] = "<span class='alap' title='" . $this->marker['text'] . "'>(nincs)</span>";
                    $this->marker['mark'] = false;
                }

                $this->church = \Eloquent\Church::find($this->tid)->toArray();
            } else {
                // TODO: There is no remark with this rid;
                return false;
            }
        }
    }

    function save() {
        if(isset($this->tid)) $this->church_id = $this->tid;
        else $this->tid = $this->church_id;
        
        if (!isset($this->reliable))
            $this->reliable = '?';

        if ($this->username != "*vendeg*")
            $where = " login = '" . $this->username . "' ";
        elseif (isset($this->email) AND preg_match("/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/i", $this->email))
            $where = " email = '" . $this->email . "' ";
        if (isset($where)) {
            $query = "SELECT megbizhato FROM " . $this->table . " where $where order by created_at DESC LIMIT 1";
            $lekerdez = mysql_query($query);
            list($megbizhato) = mysql_fetch_row($lekerdez);
            if (!empty($megbizhato))
                $this->reliable = $megbizhato;
        }

        $query = "INSERT " . $this->table . " set 
			nev='" . $this->name . "', 
			login='" . $this->username . "', 
			megbizhato='" . $this->reliable . "', 
			created_at='" . $this->timestamp . "', 
			church_id='" . $this->tid . "', 
			allapot='" . $this->state . "',
			leiras='" . sanitize($this->text) . "'";
        if (isset($this->email))
            $query .= ", email='" . $this->email . "'";
        if (!mysql_query($query))
            return false;

        //TODO: ezt teljesen ki lehetne iktatni
        $query = "UPDATE templomok set eszrevetel='i' where id='" . $this->tid . "' LIMIT 1";

        if (!mysql_query($query))
            return false;

        return true;
    }

    function emails() {
        global $config;
        if(isset($this->tid)) $this->church_id = $this->tid;
        else $this->tid = $this->church_id;

        $query = "select nev,ismertnev,varos,egyhazmegye, kontaktmail from templomok where id = " . $this->tid . " limit 0,1";
        $lekerdez = mysql_query($query);
        $templom = mysql_fetch_assoc($lekerdez);
        $eszrevetel = '';
        $eszrevetel.= "<a href=\"https://miserend.hu/?templom=" . $this->tid . "\">" . $templom['nev'] . " (";
        if ($templom['ismertnev'] != "")
            $eszrevetel .= $templom['ismertnev'] . ", ";
        $eszrevetel .= $templom['varos'] . ")</a><br/>\n";
        if (isset($this->email))
            $eszrevetel.= "<i><a href=\"mailto:" . $this->email . "\" target=\"_blank\">" . $this->name . "</a> (" . $this->username . "):</i><br/>\n";
        else
            $eszrevetel.= "<i>" . $this->name . " (" . $this->username . "):</i><br/>\n";
        $eszrevetel.= $this->text . "<br/>\n";

        $query = "select email from egyhazmegye where id='" . $templom['egyhazmegye'] . "'";
        $lekerdez = mysql_query($query);
        list($felelosmail) = mysql_fetch_row($lekerdez);

        $this->PreparedText4Email = $eszrevetel;

        //Mail küldés az egyházmegyei felelősnek
        if (!empty($felelosmail) AND $felelosmail != '') {
            $this->SendMail('diocese', $felelosmail);
        }

        //Mail küldés a karbantartónak / felelősnek
        if (!empty($templom['kontaktmail']) AND $templom['kontaktmail'] != '') {
            $this->SendMail('contact', $templom['kontaktmail']);
        }

        //Mail küldése a debuggernek, hogy boldog legyen
        $this->SendMail('debug', $config['mail']['debugger']);

        return true;
    }

    function sendMail($type, $to) {
        if(isset($this->tid)) $this->church_id = $this->tid;
        else $this->tid = $this->church_id;
        
        $mail = new \Eloquent\Email();
        if (!isset($this->EmailSubject))
            $mail->subject = "Miserend - észrevétel (" . $this->tid . ")";
        else
            $mail->subject = $this->EmailSubject;

        $mail->content = '';
        switch ($type) {
            case 'diocese':
                $mail->content .= "<strong>Kedves egyházmegyei felelős!</strong>\n\n<br/><br/>Az egyházmegyéhez tartozó egyik templom adataihoz észrevétel érkezett.<br/>\n";
                $mail->content .= "<div style='margin: 5px 5px 5px 20px;background-color:#D1DDE9;padding:4px;'>\n";
                $mail->content .= $this->PreparedText4Email;
                break;

            case 'contact':
                $mail->content .= "<strong>Kedves templom karbantartó!</strong>\n\n<br/><br/>Az egyik karbantartott templomod adataihoz észrevétel érkezett.<br/>\n";
                $mail->content .= "<div style='margin: 5px 5px 5px 20px;background-color:#D1DDE9;padding:4px;'>\n";
                $mail->content .= $this->PreparedText4Email;
                break;

            case 'debug':
                $mail->content .= "<strong>Kedves admin!</strong>\n\n<br/><br/>Az egyik templom adataihoz észrevétel érkezett.<br/>\n";
                $mail->content .= "<div style='margin: 5px 5px 5px 20px;background-color:#D1DDE9;padding:4px;'>\n";
                $mail->content .= $this->PreparedText4Email;
                break;
        }

        $mail->content .= "</div>\n";
        $mail->content .= "<strong>Köszönjük a munkádat!</strong><br/>\n miserend.hu";

        $mail->content = "<div style='display: none; visibility: hidden; color: #ffffff; font-size: 0px;'>" . $this->PreparedText4Email . "\n\n</div>" . $mail->content;

        $mail->body = $mail->content; unset($mail->content);
        $mail->to = $to;
        $mail->send();
    }

    function changeReliability($reliability) {
        if (!in_array($reliability, array('i', 'n', '?', 'e')))
            return false;
        if ($this->reliable == $reliability)
            return true;

        $this->reliable = $reliability;

        //A megbízhatóságot az összes beküldésénél átállítjuk
        // Gyakorlatilag az email az igazi azonosító.
        // TODO: akarunk mit kezdeni az *vendeg* de email nélkül?
        if ($this->email != '') {
            DB::table($this->table)
                    ->where('email', $this->email)
                    ->limit(1)
                    ->update(['megbizhato' => $reliability]);
        } else
            return false;
    }

    function changeState($state) {
        if (
                        DB::table($this->table)
                        ->where('id', $this->id)
                        ->limit(1)
                        ->update(['allapot' => $state, 'admindatum' => date('Y-m-d H:i:s.')])
        ) {
            $this->allapot = $state;
        }
    }

    function addComment($text) {
        global $user;
        $newline = "\n<img src='/img/edit.gif' align='absmiddle' title='" . $user->username . " (" . date('Y-m-d H:i:s') . ")'>" . $text;
        $adminmegj = $this->adminmegj . $newline;

        DB::table($this->table)
                ->where('id', $this->id)
                ->limit(1)
                ->update(['adminmegj' => $adminmegj]);
        $this->adminmegj = $adminmegj;
    }

}
