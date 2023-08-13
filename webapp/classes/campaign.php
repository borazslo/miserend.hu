<?php

/*
 * Heti hét templom frissítését vállalhatták embereek.
 * Jelenleg nem üzemel és ez a kód már erőst elavilt.
 * Az egész rész újraírandó, amikor újra élesztjük
 */
class Campaign {
    
    
    
    function assignUpdates() {
        global $config;

        $limit = 7;

        $numbers = array('nulla', 'egy', 'kettő', 'három', 'négy', 'öt', 'hat', 'hét', 'nyolc', 'kilenc', 'tiz');


        //users
        $query = "
            SELECT user.uid,login,email,becenev,nev,c FROM user 
            LEFT JOIN (
                SELECT count(*) as c, uid FROM updates
                WHERE timestamp > '" . date('Y-m-d H:i:s', strtotime("-160 hours")) . "' 
                GROUP BY uid 
                ORDER BY timestamp DESC
            ) u ON u.uid = user.uid 
            WHERE volunteer = 1 AND (c < " . $limit . " OR c IS NULL)
        ;";
        $result = mysql_query($query);
        $users = array();
        while ($user = mysql_fetch_assoc($result)) {
            $users[$user['uid']] = $user;
        }
        $cUsers = mysql_num_rows($result);

        //templomok
        $query = "
            SELECT t.id,t.nev,t.ismertnev,t.varos,t.nev,t.frissites,u.uid, u.timestamp 
            FROM templomok  t
                LEFT JOIN (
                    SELECT * FROM updates
                    WHERE timestamp > '" . date('Y-m-d', strtotime("-2 months")) . "' 
                    ORDER BY timestamp DESC
                ) u ON u.tid = t.id  
                LEFT JOIN (
                    SELECT * FROM eszrevetelek
                    WHERE allapot = 'u' or allapot = 'f' 
                    GROUP BY hol_id
                    ORDER BY datum
                ) e ON e.hol_id = t.id
            WHERE 
                ok = 'i' 
                AND orszag = 12
                AND ( t.nev LIKE '%templom%' OR t.nev LIKE '%bazilika%' OR t.nev LIKE '%székesegyház%')
                AND frissites < '" . date('Y-m-d', strtotime("-2 years")) . "' 
                AND u.timestamp IS NULL
                AND e.allapot IS NULL                
            GROUP BY t.id
            ORDER BY frissites, t.id ";
        //$query .= " LIMIT ".( $limit * $cUsers );

        $result = mysql_query($query);
        $templomok = array();
        while ($templom = mysql_fetch_assoc($result)) {
            $templomokFull[$templom['id']] = $templom;
        }
        $cKioszthato = mysql_num_rows($result);
        //echo "Kiosztható: ".$cKioszthato;
        //echo $cUsers * $limit;
        if (($cUsers * $limit) > $cKioszthato) {
            $mail = new \Eloquent\Email();
            $mail->subject = "Miserend.hu - Önkéntes FIGYELMEZTETÉS!";
            $mail->body = "Itt a vége?\n\n" . $cUsers . " önkéntesünk van. Nekik kéne kiosztani " . ( $cUsers * $limit ) . " templomot, de csak " . $cKioszthato . " templom van a raktáron.";
            if ($cKioszthato > 0) {
                $limit = ceil($cKioszthato / $cUsers);
                $mail->body.= "\nÚgy határoztunk hát, hogy csak " . $limit . " templomot osztunk ki fejenként.";
            }
            $mail->Send($config['mail']['debugger']);
        }

        //változók a levélhez
        $query = "
            SELECT count(*),t.nev FROM eszrevetelek
                    RIGHT JOIN templomok t ON t.id = eszrevetelek.hol_id
                WHERE datum > '" . date('Y-m-d H:i:s', strtotime("-1 week")) . "' 
                    AND ok = 'i' 
                    AND orszag = 12
                    AND ( t.nev LIKE '%templom%' OR t.nev LIKE '%bazilika%' OR t.nev LIKE '%székesegyház%')
                GROUP BY hol_id
        ;";
        $result = mysql_query($query);
        $M = mysql_num_rows($result);


        $query = "
            SELECT count(*) FROM templomok t
                WHERE frissites > '" . date('Y-m-d', strtotime("-6 months")) . "' 
                    AND ok = 'i' 
                    AND orszag = 12
                    AND ( t.nev LIKE '%templom%' OR t.nev LIKE '%bazilika%' OR t.nev LIKE '%székesegyház%')
        ;";
        $result = mysql_query($query);
        $tmp = mysql_fetch_row($result);
        $L = $tmp[0];

        $query = "
            SELECT count(*) FROM templomok t
                WHERE frissites < '" . date('Y-m-d', strtotime("-2 years")) . "' 
                    AND ok = 'i' 
                    AND orszag = 12
                    AND ( t.nev LIKE '%templom%' OR t.nev LIKE '%bazilika%' OR t.nev LIKE '%székesegyház%')
        ;";
        $result = mysql_query($query);
        $tmp = mysql_fetch_row($result);
        $O = $tmp[0];



        //minden felhasználó egyesével
        $c = 0;
        foreach ($users as $uid => $user) {

            $templomok = array_slice($templomokFull, $c * $limit, $limit, true);
            $c++;

            $list = "<ul>";
            foreach ($templomok as $tid => $templom) {
                $query = "INSERT INTO updates (uid,tid) VALUES (" . $uid . "," . $tid . ");";
                if ($config['debug'] < 1)
                    mysql_query($query);
                else
                    echo $query . "\n<br/>";
                $list .= "<li><a href='http://miserend.hu/templom/" . $templom['id'] . "'>" . $templom['nev'] . "</a>";
                if ($templom['ismertnev'] != '')
                    $list .= " (" . $templom['ismertnev'] . ")";
                $list .= ", " . $templom['varos'];
                $list .= " <font size='-1'>- utolsó frissítés: " . preg_replace('/-/', '. ', $templom['frissites']) . ".</font>";
                $list .="</li>";
            }
            $list .= "</ul>";

            //változók a levélhez

            if ($user['becenev'] != '')
                $nev = $user['becenev'];
            elseif ($user['nev'] != '')
                $nev = $user['nev'];
            else
                $nev = $user['login'];

            $query = "
                SELECT count(*),t.nev FROM eszrevetelek e
                        RIGHT JOIN templomok t ON t.id = e.hol_id
                    WHERE datum > '" . date('Y-m-d H:i:s', strtotime("-1 week")) . "' 
                        AND e.login = '" . $user['login'] . "' OR e.email = '" . $user['email'] . "' 
                        AND ok = 'i' 
                        AND orszag = 12
                        AND ( t.nev LIKE '%templom%' OR t.nev LIKE '%bazilika%' OR t.nev LIKE '%székesegyház%')
                    GROUP BY hol_id
            ;";
            $result = mysql_query($query);
            $N = mysql_num_rows($result);


            if ($O > $L)
                $ol = "de még";
            else
                $ol = "és már csak";

            $mail = new \Eloquent\Email();

            $mail->subject = "Miserend frissítése, " . date('W') . ". hét";
            $text = "
                <strong>Kedves $nev!</strong>\n
                <p>A <a href='http://miserend.hu'>miserend.hu</a>-n a múlt héten $M magyarországi templomhoz kaptunk észrevételt. ";
            if ($N == 0)
                $text .= "Reméljük, a héten te is tudsz küldeni helyesbítést.";
            elseif ($N * 5 < $M)
                $text .= "Te $N észrevételt küldtél be. És pont az ilyen sok kicsi ment ilyen sokra. ";
            else
                $text .= "Ebből $N templomhoz te küldtél be helyesbítést. Nagyon köszönjük! ";
            $text .= "
                Összesen már $L templomnak vannak fél évnél frissebb adatai, $ol $O nagyon régen frissített magyarországi templom van az adatbázisunkban.</p>\n
                <p>A következő héten a következő " . $numbers[count($templomok)] . " templom miserendjének frissítésében kérjük a te segítségedet:\n
                " . $list;

            $text .= <<<EOT
                <p>Amire érdemes figyelni információ kereséskor:</p>
                <ul>
                    <li>Nem csak azktuális miserendre szükséges rákérdezni, hanem minden más időszak miserendjére is. Pl. téli/nyári miserend, adventi idő, hétköznapra eső ünnepek. (Bármilyen egyéb időszak is felvihető a rendszerünkbe.)</li>
                    <li>Fontos megtudni, hogy mikor van a téli/nyári időszak határa (és minden más időszak határa). A tanévvel van összehangolva? Vagy a napfordulóval? Esetleg egy konkrét ünneppel?</li>
                    <li>A legbiztosabb információt közvetlen az atyától, sekrestyéstől vagy titkártól lehet kapni. A plébániai honlapok nagyon sokszor teljesen elavultak és amúgy is csak az aktuális miserendet tartalmazzák.</li>
                    <li>Ha a plébániához nincs megfelelő elérhetőség, akkor az egyházmegyei honlapot ill. annak használhatatlansága esetén az egyházmegyei titkárságot érdemes megkeresni. Ha sikerül élő elérhetőséget szerezni a plébániához, akkor azt is küldjük be a miseadatokkal. (Személyes mobilszámokat csak akkor adjunk meg, ha a tulajdonos hozzájárult, hogy megjelenjen a honlapon.)</li>
                    <li>Egy-egy plébániához/paphoz általában több templom is tartozik. Ha már sikerült felvenni egy illetékessel a kapcsolatot, akkor érdemes a fíliák és kapcsolódó templomok adatait is megtudni.</li>
                    <li>Ha hiába régen volt már frissítés, mégis minden adat stimmel a honlapunkon, akkor is kérünk visszajelzést, hogy tudjuk, nem kell újra ellenőrizni.</li>
                    <li><strong>A visszajelzéseket lehetőség szerint a templom oldalán az észrevétel beküldésénkeresztül kérjük feltölteni.</strong> Segít, ha be vagy jelentkezve, így tudjuk, hogy mebízható forrásból származik az információ. </li>
                </ul>

    EOT;
            $text .= "<p><strong>Segítségedet nagyon köszönjük!</strong></p><p>&nbsp;&nbsp;A miserend.hu önkéntes csapata</p>\n
                <p><font size='-1'>Ezt a levelet azért kaptad, mert a <a href='http://misrend.hu'>miserend.hu</a> honlapon egyszer jelentkeztél önkéntes frissítőnék. Vállalásodat bármikor visszavonhatod a <a href='http://miserend.hu/?m_id=28&m_op=add'>személyes beállításadinál</a>, vagy írhatsz az <a href='mailto:eleklaszlosj@gmail.com'>eleklaszlosj@gmail.com</a> címre. Technikai segítség szintén az <a href='mailto:eleklaszlosj@gmail.com'>eleklaszlosj@gmail.com</a> címen kérhető.</font></p>
            ";
            $mail->type = "heti7templom_hetiadag";
            $mail->body = $text;
            $mail->Send($user['email']);
            /* */
        }
    }

    function clearoutVolunteers() {
        $query = "
        SELECT user.uid,
            (IF (login.count IS NULL,0,login.count) + IF (email.count IS NULL,0,email.count)) as eszrevetelek,
            IF (updates.count IS NULL,0,updates.count) as updates
        FROM user 
        LEFT JOIN (
            SELECT count(*) as count,login 
            FROM eszrevetelek 
            WHERE datum > '" . date('Y-m-d H:i:s', strtotime("-1 month")) . "' 
            GROUP BY login
            ) login 
            ON login.login = user.login
        LEFT JOIN (
            SELECT count(*) as count,email 
            FROM eszrevetelek 
            WHERE datum > '" . date('Y-m-d H:i:s', strtotime("-1 month")) . "'
                AND login like '*vendeg*' GROUP BY email 
            ) email 
            ON email.email = user.email
        LEFT JOIN (
            SELECT count(*) as count,uid
            FROM updates 
            WHERE timestamp > '" . date('Y-m-d H:i:s', strtotime("-1 month")) . "'
            GROUP BY uid
            ) updates 
            ON updates.uid = user.uid    

        WHERE volunteer = 1;";

        $limit = 10;
        $c = 1;
        $result = mysql_query($query);
        $volunteer = array();
        while ($volunteer = mysql_fetch_assoc($result)) {
            if ($volunteer['updates'] > 0 AND $volunteer['eszrevetelek'] == 0) {

                $user = new User($volunteer['uid']);
                if ($user->nickname != '')
                    $nev = $user->nickname;
                elseif ($user->name != '')
                    $nev = $user->name;
                else
                    $nev = $user->username;


                $mail = new \Eloquent\Email();

                $mail->subject = "Miserend önkéntesség";

                $text = <<<EOD
                <strong>Kedves $nev!</strong>\n
                <p>Templomaink miserendjének frissentartása elképzelhetetlen lenne önkéntesek segítsége nélkül. Sajnáljuk, hogy az elmúlt hónapban nem állt módodban teljesíteni vállalásodat. Ezért, hogy aktív önkénteseinknek biztosan jusson elég frissítendő adat, feloldunk önkéntes vállalásod alól. A továbbiakban nem küldünk neked frissítendő templomokat emailben.</p>\n
                <p>Észrevételeidet, helyesbítéseidet továbbra is köszönettel várjuk a honlapon keresztül. Valamint, ha mégis tudod vállalni újra heti hét templom frissítését, a honlapon a <a href='http://miserend.hu/?m_id=28&m_op=add'>személyes beállításadinál</a> vállalásodat megteheted.</p>\n
                <p><strong>Köszönjük korábbi és majdani minden helyesbítésedet!</strong></p>
                <p>&nbsp;&nbsp;A miserend.hu önkéntes csapata</p>\n
    EOD;
                $text .= "<p><font size='-1'>Ezt a levelet azért kaptad, mert a <a href='http://misrend.hu'>miserend.hu</a> honlapon egyszer jelentkeztél önkéntes frissítőnék. Vállalásodat bármikor módosíthatod a <a href='http://miserend.hu/?m_id=28&m_op=add'>személyes beállításadinál</a>, vagy írhatsz az <a href='mailto:eleklaszlosj@gmail.com'>eleklaszlosj@gmail.com</a> címre. Technikai segítség szintén az <a href='mailto:eleklaszlosj@gmail.com'>eleklaszlosj@gmail.com</a> címen kérhető.</font></p>
            ";
                $mail->body = $text;
                $mail->type = "heti7templom_lemondas";
                $mail->Send($user->email);
                $user->presave('volunteer', 0);
                $user->save();
                $c++;
                if ($c > $limit)
                    return true;
            }
        }
        return true;
    }

    function updatesCampaign() {
        global $twig, $user;

        $query = "SELECT count(*) FROM user WHERE ok = 'i'  AND volunteer = 1;";
        $result = mysql_query($query);
        $tmp = mysql_fetch_row($result);
        $C = $tmp[0];

        $query = "
                SELECT count(*) FROM templomok t
                    WHERE frissites < '" . date('Y-m-d', strtotime("2015-12-24 -2 years")) . "' 
                        AND ok = 'i' 
                        AND orszag = 12
                        AND ( t.nev LIKE '%templom%' OR t.nev LIKE '%bazilika%' OR t.nev LIKE '%székesegyház%')
            ;";
        $result = mysql_query($query);
        $tmp = mysql_fetch_row($result);
        $O = $tmp[0];

        $W = date('W', strtotime('2015-12-24')) - date('W');

        $S = (int) ( $O / $W / 7 ) + 1;

        if ($O > $L)
            $ol = "de még";
        else
            $ol = "és már csak";

        $dobozszoveg = "<span class='alap'>Alig $S önkéntes heti hét templom miserendjének frissítésével karácsonyra naprakésszé teheti az összes magyarországi templomot. <strong>Már $C ember segít nekünk";

        if ($C >= $S)
            $dobozszoveg .= ", de segítő kézre még szükségünk van. ";
        else
            $dobozszoveg .= ". ";
        if ($user->volunteer == 1)
            $dobozszoveg .= "Köszönjük, hogy te is köztük vagy!";
        else
            $dobozszoveg .= "<a href='mailto:eleklaszlosj@gmail.com?subject=Önkéntesnek jelentkezem'>Jelentkezz te is!</a>";

        $dobozszoveg .= "</strong></span>";

        $variables = array(
            'header' => array('content' => 'Hét nap, hét frissítés'),
            'content' => nl2br($dobozszoveg),
            'settings' => array('width=100%', 'align=center', 'style="padding:1px"'),
        );

        if ($C >= ( $S * 2 )) {
            return false;
        }

        return array(
            'title' => 'Hét nap, hét frissítése',
            'content' => nl2br($dobozszoveg)
        );
    }

    
}
