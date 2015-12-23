<?php

namespace Html;

class UploadImage extends Html {

    public function __construct($path) {
        $this->tid = $path[0];
        if ($_REQUEST['upload']) {
            $this->ajax();
            exit;
        } else {
            $this->form();
        }
    }

    function form() {

        if (!is_numeric($this->tid)) {
            $this->content = "<script language=Javascript>close();</script>";
            return;
        }

        $query = "select nev,ismertnev,varos,egyhazmegye from templomok where id='$this->tid' and ok='i'";
        if (!$lekerdez = mysql_query($query))
            echo "HIBA!<br>$query<br>" . mysql_error();
        list($nev, $ismertnev, $varos, $ehm) = mysql_fetch_row($lekerdez);
        $kiir.="<input type=hidden name=ehm value=$ehm>";
        $kiir.="\n<table width=100% bgcolor=#F5CC4C>
        <tr><td class=alap><big><b>$nev</b> $ismertnev - <u>$varos</u></big><br/>
        <i><strong>Kép feltöltése.</strong> Kérjük kellően jó minőségű és méretű jpeg képet töltsön csak fel.</i></big></td></tr></table>";

        $kiir .= '
      
    ';

        $this->content = $kiir;
    }

    function ajax() {
        if (isset($_FILES["FileInput"]) && $_FILES["FileInput"]["error"] == UPLOAD_ERR_OK) {
            $id = $tid = $_POST['id'];

            $konyvtar = "kepek/templomok/$tid";
            if (!is_dir("$konyvtar")) {
                //létre kell hozni
                if (!mkdir("$konyvtar", 0775)) {
                    echo '<p class=hiba>HIBA a könyvtár létrehozásánál!</p>';
                }
                if (!mkdir("$konyvtar/kicsi", 0775)) {
                    echo '<p class=hiba>HIBA a könyvtár létrehozásánál!</p>';
                }
            }
            ############ Edit settings ##############
            $UploadDirectory = $konyvtar . "/";
            ##########################################

            /*
              Note : You will run into errors or blank page if "memory_limit" or "upload_max_filesize" is set to low in "php.ini".
              Open "php.ini" file, and search for "memory_limit" or "upload_max_filesize" limit
              and set them adequately, also check "post_max_size".
             */

            //check if this is an ajax request
            if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                die();
            }


            //Is file size is less than allowed size.
            if ($_FILES["FileInput"]["size"] > 5242880) {
                die("File size is too big!");
            }

            //allowed file type Server side check
            switch (strtolower($_FILES['FileInput']['type'])) {
                //allowed file types
                /* case 'image/png': 
                  case 'image/gif': */
                case 'image/jpeg':
                    /* case 'image/pjpeg':
                      case 'text/plain':
                      case 'text/html': //html file
                      case 'application/x-zip-compressed':
                      case 'application/pdf':
                      case 'application/msword':
                      case 'application/vnd.ms-excel':
                      case 'video/mp4': */
                    break;
                default:
                    die('Unsupported File!'); //output error
            }

            $File_Name = strtolower($_FILES['FileInput']['name']);
            $File_Ext = substr($File_Name, strrpos($File_Name, '.')); //get file extention
            $Random_Number = rand(0, 9999999999); //Random number to be added to name.
            $NewFileName = $Random_Number . $File_Ext; //new file name

            if (move_uploaded_file($_FILES['FileInput']['tmp_name'], $UploadDirectory . $NewFileName)) {
                $kimenet = "$konyvtar/$NewFileName";
                $kimenet1 = "$konyvtar/kicsi/$NewFileName";
                $info = getimagesize($kimenet);
                $w = $info[0];
                $h = $info[1];

                if ($w > 800 or $h > 600)
                    kicsinyites($kimenet, $kimenet, 800);
                kicsinyites($kimenet, $kimenet1, 120);

                dbconnect();
                $query = "select nev,ismertnev,varos,kontaktmail from templomok where id = " . $id . " limit 0,1";
                $lekerdez = mysql_query($query);
                $templom = mysql_fetch_assoc($lekerdez);

                $felirat = htmlspecialchars($_REQUEST['description']);
                if ($felirat == '')
                    $felirat = $katnev;

                if (!mysql_query("insert kepek set tid='$tid', fajlnev='$NewFileName', felirat='$felirat', width=$w, height=$h "))
                    echo 'HIBA!<br>' . mysql_error();

                /* email */
                $headers = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                //$headers .= 'Bcc: eleklaszlosj@gmail.com' . "\r\n";
                $headers .= 'From: miserend.hu <info@miserend.hu>' . "\r\n";

                $eszrevetel = "<a href=\"http://miserend.hu/?templom=" . $id . "\">" . $templom['nev'] . " (";
                if ($templom['ismertnev'] != "")
                    $eszrevetel .= $templom['ismertnev'] . ", ";
                $eszrevetel .= $templom['varos'] . ")</a><br/>\n";
                $eszrevetel .= "<img src='http://miserend.hu/" . $UploadDirectory . $NewFileName . "'><br/>\n";
                $eszrevetel .= $felirat . "<br/><br/>\n";
                $eszrevetel .= "http://miserend.hu/" . $UploadDirectory . $NewFileName . "\n";

                $query = "select email from egyhazmegye where id='$ehm'";
                $lekerdez = mysql_query($query);
                list($felelosmail) = mysql_fetch_row($lekerdez);

                $mail = new \Mail();
                $mail->subject = "Miserend - új kép érkezett";

                if (!empty($felelosmail)) {
                    //Mail küldés az egyházmegyei felelősnek
                    $mail->content = "Kedves egyházmegyei felelős!\n\n<br/><br/>Az egyházmegyéhez tartozó egyik templomhoz új kép érkezett.<br/>\n" . $eszrevetel;
                    $mail->send($felelosmail);
                }

                if (!empty($templom['kontaktmail'])) {
                    //Mail küldés az karbantartónak felelősnek
                    $mail->content = "Kedves templom karbantartó!\n\n<br/><br/>Az egyik karbantartott templomhoz új kép érkezett.<br/>\n" . $eszrevetel;
                    $mail->send($templom['kontaktmail']);
                }

                //Mail küldése a debuggernek, hogy boldog legyen
                $mail->content = "Kedves admin!\n\n<br/><br/>Az egyik templomhoz új kép érkezett.<br/>\n" . $eszrevetel;
                $mail->send($config['mail']['debugger']);

                echo 'Siker! Feltöltöttük. Jöhet a következő!'; //.$UploadDirectory.$NewFileName );
                echo "<br/><img src='/" . $UploadDirectory . 'kicsi/' . $NewFileName . "'>";
                exit;
            } else {
                die('Hiba történt! Elnézést!');
            }
        } else {
            die('Something wrong with upload! Is "upload_max_filesize" set correctly?');
        }
    }

}
