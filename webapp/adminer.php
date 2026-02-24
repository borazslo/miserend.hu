<?php
/** Adminer - Compact database management
* @link https://www.adminer.org/
* @author Jakub Vrana, https://www.vrana.cz/
* @copyright 2007 Jakub Vrana
* @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
* @version 5.4.1
*/namespace
Adminer;const
VERSION="5.4.1";error_reporting(24575);set_error_handler(function($Cc,$Ec){return!!preg_match('~^Undefined (array key|offset|index)~',$Ec);},E_WARNING|E_NOTICE);$ad=!preg_match('~^(unsafe_raw)?$~',ini_get("filter.default"));if($ad||ini_get("filter.default_flags")){foreach(array('_GET','_POST','_COOKIE','_SERVER')as$X){$tj=filter_input_array(constant("INPUT$X"),FILTER_UNSAFE_RAW);if($tj)$$X=$tj;}}if(function_exists("mb_internal_encoding"))mb_internal_encoding("8bit");function
connection($g=null){return($g?:Db::$instance);}function
adminer(){return
Adminer::$instance;}function
driver(){return
Driver::$instance;}function
connect(){$Fb=adminer()->credentials();$J=Driver::connect($Fb[0],$Fb[1],$Fb[2]);return(is_object($J)?$J:null);}function
idf_unescape($u){if(!preg_match('~^[`\'"[]~',$u))return$u;$Ie=substr($u,-1);return
str_replace($Ie.$Ie,$Ie,substr($u,1,-1));}function
q($Q){return
connection()->quote($Q);}function
escape_string($X){return
substr(q($X),1,-1);}function
idx($va,$x,$k=null){return($va&&array_key_exists($x,$va)?$va[$x]:$k);}function
number($X){return
preg_replace('~[^0-9]+~','',$X);}function
number_type(){return'((?<!o)int(?!er)|numeric|real|float|double|decimal|money)';}function
remove_slashes(array$ah,$ad=false){if(function_exists("get_magic_quotes_gpc")&&get_magic_quotes_gpc()){while(list($x,$X)=each($ah)){foreach($X
as$Ae=>$W){unset($ah[$x][$Ae]);if(is_array($W)){$ah[$x][stripslashes($Ae)]=$W;$ah[]=&$ah[$x][stripslashes($Ae)];}else$ah[$x][stripslashes($Ae)]=($ad?$W:stripslashes($W));}}}}function
bracket_escape($u,$Ca=false){static$cj=array(':'=>':1',']'=>':2','['=>':3','"'=>':4');return
strtr($u,($Ca?array_flip($cj):$cj));}function
min_version($Lj,$We="",$g=null){$g=connection($g);$Vh=$g->server_info;if($We&&preg_match('~([\d.]+)-MariaDB~',$Vh,$A)){$Vh=$A[1];$Lj=$We;}return$Lj&&version_compare($Vh,$Lj)>=0;}function
charset(Db$f){return(min_version("5.5.3",0,$f)?"utf8mb4":"utf8");}function
ini_bool($ke){$X=ini_get($ke);return(preg_match('~^(on|true|yes)$~i',$X)||(int)$X);}function
ini_bytes($ke){$X=ini_get($ke);switch(strtolower(substr($X,-1))){case'g':$X=(int)$X*1024;case'm':$X=(int)$X*1024;case'k':$X=(int)$X*1024;}return$X;}function
sid(){static$J;if($J===null)$J=(SID&&!($_COOKIE&&ini_bool("session.use_cookies")));return$J;}function
set_password($Kj,$N,$V,$F){$_SESSION["pwds"][$Kj][$N][$V]=($_COOKIE["adminer_key"]&&is_string($F)?array(encrypt_string($F,$_COOKIE["adminer_key"])):$F);}function
get_password(){$J=get_session("pwds");if(is_array($J))$J=($_COOKIE["adminer_key"]?decrypt_string($J[0],$_COOKIE["adminer_key"]):false);return$J;}function
get_val($H,$m=0,$tb=null){$tb=connection($tb);$I=$tb->query($H);if(!is_object($I))return
false;$K=$I->fetch_row();return($K?$K[$m]:false);}function
get_vals($H,$d=0){$J=array();$I=connection()->query($H);if(is_object($I)){while($K=$I->fetch_row())$J[]=$K[$d];}return$J;}function
get_key_vals($H,$g=null,$Yh=true){$g=connection($g);$J=array();$I=$g->query($H);if(is_object($I)){while($K=$I->fetch_row()){if($Yh)$J[$K[0]]=$K[1];else$J[]=$K[0];}}return$J;}function
get_rows($H,$g=null,$l="<p class='error'>"){$tb=connection($g);$J=array();$I=$tb->query($H);if(is_object($I)){while($K=$I->fetch_assoc())$J[]=$K;}elseif(!$I&&!$g&&$l&&(defined('Adminer\PAGE_HEADER')||$l=="-- "))echo$l.error()."\n";return$J;}function
unique_array($K,array$w){foreach($w
as$v){if(preg_match("~PRIMARY|UNIQUE~",$v["type"])){$J=array();foreach($v["columns"]as$x){if(!isset($K[$x]))continue
2;$J[$x]=$K[$x];}return$J;}}}function
escape_key($x){if(preg_match('(^([\w(]+)('.str_replace("_",".*",preg_quote(idf_escape("_"))).')([ \w)]+)$)',$x,$A))return$A[1].idf_escape(idf_unescape($A[2])).$A[3];return
idf_escape($x);}function
where(array$Z,array$n=array()){$J=array();foreach((array)$Z["where"]as$x=>$X){$x=bracket_escape($x,true);$d=escape_key($x);$m=idx($n,$x,array());$Xc=$m["type"];$J[]=$d.(JUSH=="sql"&&$Xc=="json"?" = CAST(".q($X)." AS JSON)":(JUSH=="pgsql"&&preg_match('~^json~',$Xc)?"::jsonb = ".q($X)."::jsonb":(JUSH=="sql"&&is_numeric($X)&&preg_match('~\.~',$X)?" LIKE ".q($X):(JUSH=="mssql"&&strpos($Xc,"datetime")===false?" LIKE ".q(preg_replace('~[_%[]~','[\0]',$X)):" = ".unconvert_field($m,q($X))))));if(JUSH=="sql"&&preg_match('~char|text~',$Xc)&&preg_match("~[^ -@]~",$X))$J[]="$d = ".q($X)." COLLATE ".charset(connection())."_bin";}foreach((array)$Z["null"]as$x)$J[]=escape_key($x)." IS NULL";return
implode(" AND ",$J);}function
where_check($X,array$n=array()){parse_str($X,$Wa);remove_slashes(array(&$Wa));return
where($Wa,$n);}function
where_link($s,$d,$Y,$Xf="="){return"&where%5B$s%5D%5Bcol%5D=".urlencode($d)."&where%5B$s%5D%5Bop%5D=".urlencode(($Y!==null?$Xf:"IS NULL"))."&where%5B$s%5D%5Bval%5D=".urlencode($Y);}function
convert_fields(array$e,array$n,array$M=array()){$J="";foreach($e
as$x=>$X){if($M&&!in_array(idf_escape($x),$M))continue;$wa=convert_field($n[$x]);if($wa)$J
.=", $wa AS ".idf_escape($x);}return$J;}function
cookie($B,$Y,$Pe=2592000){header("Set-Cookie: $B=".urlencode($Y).($Pe?"; expires=".gmdate("D, d M Y H:i:s",time()+$Pe)." GMT":"")."; path=".preg_replace('~\?.*~','',$_SERVER["REQUEST_URI"]).(HTTPS?"; secure":"")."; HttpOnly; SameSite=lax",false);}function
get_settings($Bb){parse_str($_COOKIE[$Bb],$Zh);return$Zh;}function
get_setting($x,$Bb="adminer_settings",$k=null){return
idx(get_settings($Bb),$x,$k);}function
save_settings(array$Zh,$Bb="adminer_settings"){$Y=http_build_query($Zh+get_settings($Bb));cookie($Bb,$Y);$_COOKIE[$Bb]=$Y;}function
restart_session(){if(!ini_bool("session.use_cookies")&&(!function_exists('session_status')||session_status()==1))session_start();}function
stop_session($id=false){$Cj=ini_bool("session.use_cookies");if(!$Cj||$id){session_write_close();if($Cj&&@ini_set("session.use_cookies",'0')===false)session_start();}}function&get_session($x){return$_SESSION[$x][DRIVER][SERVER][$_GET["username"]];}function
set_session($x,$X){$_SESSION[$x][DRIVER][SERVER][$_GET["username"]]=$X;}function
auth_url($Kj,$N,$V,$j=null){$zj=remove_from_uri(implode("|",array_keys(SqlDriver::$drivers))."|username|ext|".($j!==null?"db|":"").($Kj=='mssql'||$Kj=='pgsql'?"":"ns|").session_name());preg_match('~([^?]*)\??(.*)~',$zj,$A);return"$A[1]?".(sid()?SID."&":"").($Kj!="server"||$N!=""?urlencode($Kj)."=".urlencode($N)."&":"").($_GET["ext"]?"ext=".urlencode($_GET["ext"])."&":"")."username=".urlencode($V).($j!=""?"&db=".urlencode($j):"").($A[2]?"&$A[2]":"");}function
is_ajax(){return($_SERVER["HTTP_X_REQUESTED_WITH"]=="XMLHttpRequest");}function
redirect($Se,$lf=null){if($lf!==null){restart_session();$_SESSION["messages"][preg_replace('~^[^?]*~','',($Se!==null?$Se:$_SERVER["REQUEST_URI"]))][]=$lf;}if($Se!==null){if($Se=="")$Se=".";header("Location: $Se");exit;}}function
query_redirect($H,$Se,$lf,$jh=true,$Jc=true,$Sc=false,$Pi=""){if($Jc){$oi=microtime(true);$Sc=!connection()->query($H);$Pi=format_time($oi);}$ii=($H?adminer()->messageQuery($H,$Pi,$Sc):"");if($Sc){adminer()->error
.=error().$ii.script("messagesPrint();")."<br>";return
false;}if($jh)redirect($Se,$lf.$ii);return
true;}class
Queries{static$queries=array();static$start=0;}function
queries($H){if(!Queries::$start)Queries::$start=microtime(true);Queries::$queries[]=(preg_match('~;$~',$H)?"DELIMITER ;;\n$H;\nDELIMITER ":$H).";";return
connection()->query($H);}function
apply_queries($H,array$T,$Fc='Adminer\table'){foreach($T
as$R){if(!queries("$H ".$Fc($R)))return
false;}return
true;}function
queries_redirect($Se,$lf,$jh){$eh=implode("\n",Queries::$queries);$Pi=format_time(Queries::$start);return
query_redirect($eh,$Se,$lf,$jh,false,!$jh,$Pi);}function
format_time($oi){return
sprintf('%.3f s',max(0,microtime(true)-$oi));}function
relative_uri(){return
str_replace(":","%3a",preg_replace('~^[^?]*/([^?]*)~','\1',$_SERVER["REQUEST_URI"]));}function
remove_from_uri($ug=""){return
substr(preg_replace("~(?<=[?&])($ug".(SID?"":"|".session_name()).")=[^&]*&~",'',relative_uri()."&"),0,-1);}function
get_file($x,$Rb=false,$Xb=""){$Zc=$_FILES[$x];if(!$Zc)return
null;foreach($Zc
as$x=>$X)$Zc[$x]=(array)$X;$J='';foreach($Zc["error"]as$x=>$l){if($l)return$l;$B=$Zc["name"][$x];$Xi=$Zc["tmp_name"][$x];$yb=file_get_contents($Rb&&preg_match('~\.gz$~',$B)?"compress.zlib://$Xi":$Xi);if($Rb){$oi=substr($yb,0,3);if(function_exists("iconv")&&preg_match("~^\xFE\xFF|^\xFF\xFE~",$oi))$yb=iconv("utf-16","utf-8",$yb);elseif($oi=="\xEF\xBB\xBF")$yb=substr($yb,3);}$J
.=$yb;if($Xb)$J
.=(preg_match("($Xb\\s*\$)",$yb)?"":$Xb)."\n\n";}return$J;}function
upload_error($l){$gf=($l==UPLOAD_ERR_INI_SIZE?ini_get("upload_max_filesize"):0);return($l?'Unable to upload a file.'.($gf?" ".sprintf('Maximum allowed file size is %sB.',$gf):""):'File does not exist.');}function
repeat_pattern($Gg,$y){return
str_repeat("$Gg{0,65535}",$y/65535)."$Gg{0,".($y%65535)."}";}function
is_utf8($X){return(preg_match('~~u',$X)&&!preg_match('~[\0-\x8\xB\xC\xE-\x1F]~',$X));}function
format_number($X){return
strtr(number_format($X,0,".",','),preg_split('~~u','0123456789',-1,PREG_SPLIT_NO_EMPTY));}function
friendly_url($X){return
preg_replace('~\W~i','-',$X);}function
table_status1($R,$Tc=false){$J=table_status($R,$Tc);return($J?reset($J):array("Name"=>$R));}function
column_foreign_keys($R){$J=array();foreach(adminer()->foreignKeys($R)as$p){foreach($p["source"]as$X)$J[$X][]=$p;}return$J;}function
fields_from_edit(){$J=array();foreach((array)$_POST["field_keys"]as$x=>$X){if($X!=""){$X=bracket_escape($X);$_POST["function"][$X]=$_POST["field_funs"][$x];$_POST["fields"][$X]=$_POST["field_vals"][$x];}}foreach((array)$_POST["fields"]as$x=>$X){$B=bracket_escape($x,true);$J[$B]=array("field"=>$B,"privileges"=>array("insert"=>1,"update"=>1,"where"=>1,"order"=>1),"null"=>1,"auto_increment"=>($x==driver()->primary),);}return$J;}function
dump_headers($Qd,$wf=false){$J=adminer()->dumpHeaders($Qd,$wf);$qg=$_POST["output"];if($qg!="text")header("Content-Disposition: attachment; filename=".adminer()->dumpFilename($Qd).".$J".($qg!="file"&&preg_match('~^[0-9a-z]+$~',$qg)?".$qg":""));session_write_close();if(!ob_get_level())ob_start(null,4096);ob_flush();flush();return$J;}function
dump_csv(array$K){foreach($K
as$x=>$X){if(preg_match('~["\n,;\t]|^0.|\.\d*0$~',$X)||$X==="")$K[$x]='"'.str_replace('"','""',$X).'"';}echo
implode(($_POST["format"]=="csv"?",":($_POST["format"]=="tsv"?"\t":";")),$K)."\r\n";}function
apply_sql_function($r,$d){return($r?($r=="unixepoch"?"DATETIME($d, '$r')":($r=="count distinct"?"COUNT(DISTINCT ":strtoupper("$r("))."$d)"):$d);}function
get_temp_dir(){$J=ini_get("upload_tmp_dir");if(!$J){if(function_exists('sys_get_temp_dir'))$J=sys_get_temp_dir();else{$o=@tempnam("","");if(!$o)return'';$J=dirname($o);unlink($o);}}return$J;}function
file_open_lock($o){if(is_link($o))return;$q=@fopen($o,"c+");if(!$q)return;@chmod($o,0660);if(!flock($q,LOCK_EX)){fclose($q);return;}return$q;}function
file_write_unlock($q,$Lb){rewind($q);fwrite($q,$Lb);ftruncate($q,strlen($Lb));file_unlock($q);}function
file_unlock($q){flock($q,LOCK_UN);fclose($q);}function
first(array$va){return
reset($va);}function
password_file($h){$o=get_temp_dir()."/adminer.key";if(!$h&&!file_exists($o))return'';$q=file_open_lock($o);if(!$q)return'';$J=stream_get_contents($q);if(!$J){$J=rand_string();file_write_unlock($q,$J);}else
file_unlock($q);return$J;}function
rand_string(){return
md5(uniqid(strval(mt_rand()),true));}function
select_value($X,$_,array$m,$Oi){if(is_array($X)){$J="";foreach($X
as$Ae=>$W)$J
.="<tr>".($X!=array_values($X)?"<th>".h($Ae):"")."<td>".select_value($W,$_,$m,$Oi);return"<table>$J</table>";}if(!$_)$_=adminer()->selectLink($X,$m);if($_===null){if(is_mail($X))$_="mailto:$X";if(is_url($X))$_=$X;}$J=adminer()->editVal($X,$m);if($J!==null){if(!is_utf8($J))$J="\0";elseif($Oi!=""&&is_shortable($m))$J=shorten_utf8($J,max(0,+$Oi));else$J=h($J);}return
adminer()->selectVal($J,$_,$m,$X);}function
is_blob(array$m){return
preg_match('~blob|bytea|raw|file~',$m["type"])&&!in_array($m["type"],idx(driver()->structuredTypes(),'User types',array()));}function
is_mail($tc){$xa='[-a-z0-9!#$%&\'*+/=?^_`{|}~]';$gc='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';$Gg="$xa+(\\.$xa+)*@($gc?\\.)+$gc";return
is_string($tc)&&preg_match("(^$Gg(,\\s*$Gg)*\$)i",$tc);}function
is_url($Q){$gc='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';return
preg_match("~^(https?)://($gc?\\.)+$gc(:\\d+)?(/.*)?(\\?.*)?(#.*)?\$~i",$Q);}function
is_shortable(array$m){return
preg_match('~char|text|json|lob|geometry|point|linestring|polygon|string|bytea|hstore~',$m["type"]);}function
host_port($N){return(preg_match('~^(\[(.+)]|([^:]+)):([^:]+)$~',$N,$A)?array($A[2].$A[3],$A[4]):array($N,''));}function
count_rows($R,array$Z,$ue,array$wd){$H=" FROM ".table($R).($Z?" WHERE ".implode(" AND ",$Z):"");return($ue&&(JUSH=="sql"||count($wd)==1)?"SELECT COUNT(DISTINCT ".implode(", ",$wd).")$H":"SELECT COUNT(*)".($ue?" FROM (SELECT 1$H GROUP BY ".implode(", ",$wd).") x":$H));}function
slow_query($H){$j=adminer()->database();$Qi=adminer()->queryTimeout();$di=driver()->slowQuery($H,$Qi);$g=null;if(!$di&&support("kill")){$g=connect();if($g&&($j==""||$g->select_db($j))){$De=get_val(connection_id(),0,$g);echo
script("const timeout = setTimeout(() => { ajax('".js_escape(ME)."script=kill', function () {}, 'kill=$De&token=".get_token()."'); }, 1000 * $Qi);");}}ob_flush();flush();$J=@get_key_vals(($di?:$H),$g,false);if($g){echo
script("clearTimeout(timeout);");ob_flush();flush();}return$J;}function
get_token(){$hh=rand(1,1e6);return($hh^$_SESSION["token"]).":$hh";}function
verify_token(){list($Yi,$hh)=explode(":",$_POST["token"]);return($hh^$_SESSION["token"])==$Yi;}function
lzw_decompress($Ia){$cc=256;$Ja=8;$gb=array();$uh=0;$vh=0;for($s=0;$s<strlen($Ia);$s++){$uh=($uh<<8)+ord($Ia[$s]);$vh+=8;if($vh>=$Ja){$vh-=$Ja;$gb[]=$uh>>$vh;$uh&=(1<<$vh)-1;$cc++;if($cc>>$Ja)$Ja++;}}$bc=range("\0","\xFF");$J="";$Uj="";foreach($gb
as$s=>$fb){$sc=$bc[$fb];if(!isset($sc))$sc=$Uj.$Uj[0];$J
.=$sc;if($s)$bc[]=$Uj.$sc[0];$Uj=$sc;}return$J;}function
script($fi,$bj="\n"){return"<script".nonce().">$fi</script>$bj";}function
script_src($_j,$Ub=false){return"<script src='".h($_j)."'".nonce().($Ub?" defer":"")."></script>\n";}function
nonce(){return' nonce="'.get_nonce().'"';}function
input_hidden($B,$Y=""){return"<input type='hidden' name='".h($B)."' value='".h($Y)."'>\n";}function
input_token(){return
input_hidden("token",get_token());}function
target_blank(){return' target="_blank" rel="noreferrer noopener"';}function
h($Q){return
str_replace("\0","&#0;",htmlspecialchars($Q,ENT_QUOTES,'utf-8'));}function
nl_br($Q){return
str_replace("\n","<br>",$Q);}function
checkbox($B,$Y,$Za,$Fe="",$Wf="",$db="",$He=""){$J="<input type='checkbox' name='$B' value='".h($Y)."'".($Za?" checked":"").($He?" aria-labelledby='$He'":"").">".($Wf?script("qsl('input').onclick = function () { $Wf };",""):"");return($Fe!=""||$db?"<label".($db?" class='$db'":"").">$J".h($Fe)."</label>":$J);}function
optionlist($bg,$Nh=null,$Dj=false){$J="";foreach($bg
as$Ae=>$W){$cg=array($Ae=>$W);if(is_array($W)){$J
.='<optgroup label="'.h($Ae).'">';$cg=$W;}foreach($cg
as$x=>$X)$J
.='<option'.($Dj||is_string($x)?' value="'.h($x).'"':'').($Nh!==null&&($Dj||is_string($x)?(string)$x:$X)===$Nh?' selected':'').'>'.h($X);if(is_array($W))$J
.='</optgroup>';}return$J;}function
html_select($B,array$bg,$Y="",$Vf="",$He=""){static$Fe=0;$Ge="";if(!$He&&substr($bg[""],0,1)=="("){$Fe++;$He="label-$Fe";$Ge="<option value='' id='$He'>".h($bg[""]);unset($bg[""]);}return"<select name='".h($B)."'".($He?" aria-labelledby='$He'":"").">".$Ge.optionlist($bg,$Y)."</select>".($Vf?script("qsl('select').onchange = function () { $Vf };",""):"");}function
html_radios($B,array$bg,$Y="",$Rh=""){$J="";foreach($bg
as$x=>$X)$J
.="<label><input type='radio' name='".h($B)."' value='".h($x)."'".($x==$Y?" checked":"").">".h($X)."</label>$Rh";return$J;}function
confirm($lf="",$Oh="qsl('input')"){return
script("$Oh.onclick = () => confirm('".($lf?js_escape($lf):'Are you sure?')."');","");}function
print_fieldset($t,$Ne,$Oj=false){echo"<fieldset><legend>","<a href='#fieldset-$t'>$Ne</a>",script("qsl('a').onclick = partial(toggle, 'fieldset-$t');",""),"</legend>","<div id='fieldset-$t'".($Oj?"":" class='hidden'").">\n";}function
bold($La,$db=""){return($La?" class='active $db'":($db?" class='$db'":""));}function
js_escape($Q){return
addcslashes($Q,"\r\n'\\/");}function
pagination($D,$Ib){return" ".($D==$Ib?$D+1:'<a href="'.h(remove_from_uri("page").($D?"&page=$D".($_GET["next"]?"&next=".urlencode($_GET["next"]):""):"")).'">'.($D+1)."</a>");}function
hidden_fields(array$ah,array$Ud=array(),$Sg=''){$J=false;foreach($ah
as$x=>$X){if(!in_array($x,$Ud)){if(is_array($X))hidden_fields($X,array(),$x);else{$J=true;echo
input_hidden(($Sg?$Sg."[$x]":$x),$X);}}}return$J;}function
hidden_fields_get(){echo(sid()?input_hidden(session_name(),session_id()):''),(SERVER!==null?input_hidden(DRIVER,SERVER):""),input_hidden("username",$_GET["username"]);}function
file_input($me){$bf="max_file_uploads";$cf=ini_get($bf);$xj="upload_max_filesize";$yj=ini_get($xj);return(ini_bool("file_uploads")?$me.script("qsl('input[type=\"file\"]').onchange = partialArg(fileChange, "."$cf, '".sprintf('Increase %s.',"$bf = $cf")."', ".ini_bytes("upload_max_filesize").", '".sprintf('Increase %s.',"$xj = $yj")."')"):'File uploads are disabled.');}function
enum_input($U,$ya,array$m,$Y,$wc=""){preg_match_all("~'((?:[^']|'')*)'~",$m["length"],$Ze);$Sg=($m["type"]=="enum"?"val-":"");$Za=(is_array($Y)?in_array("null",$Y):$Y===null);$J=($m["null"]&&$Sg?"<label><input type='$U'$ya value='null'".($Za?" checked":"")."><i>$wc</i></label>":"");foreach($Ze[1]as$X){$X=stripcslashes(str_replace("''","'",$X));$Za=(is_array($Y)?in_array($Sg.$X,$Y):$Y===$X);$J
.=" <label><input type='$U'$ya value='".h($Sg.$X)."'".($Za?' checked':'').'>'.h(adminer()->editVal($X,$m)).'</label>';}return$J;}function
input(array$m,$Y,$r,$Ba=false){$B=h(bracket_escape($m["field"]));echo"<td class='function'>";if(is_array($Y)&&!$r){$Y=json_encode($Y,128|64|256);$r="json";}$th=(JUSH=="mssql"&&$m["auto_increment"]);if($th&&!$_POST["save"])$r=null;$rd=(isset($_GET["select"])||$th?array("orig"=>'original'):array())+adminer()->editFunctions($m);$Bc=driver()->enumLength($m);if($Bc){$m["type"]="enum";$m["length"]=$Bc;}$dc=stripos($m["default"],"GENERATED ALWAYS AS ")===0?" disabled=''":"";$ya=" name='fields[$B]".($m["type"]=="enum"||$m["type"]=="set"?"[]":"")."'$dc".($Ba?" autofocus":"");echo
driver()->unconvertFunction($m)." ";$R=$_GET["edit"]?:$_GET["select"];if($m["type"]=="enum")echo
h($rd[""])."<td>".adminer()->editInput($R,$m,$ya,$Y);else{$Dd=(in_array($r,$rd)||isset($rd[$r]));echo(count($rd)>1?"<select name='function[$B]'$dc>".optionlist($rd,$r===null||$Dd?$r:"")."</select>".on_help("event.target.value.replace(/^SQL\$/, '')",1).script("qsl('select').onchange = functionChange;",""):h(reset($rd))).'<td>';$me=adminer()->editInput($R,$m,$ya,$Y);if($me!="")echo$me;elseif(preg_match('~bool~',$m["type"]))echo"<input type='hidden'$ya value='0'>"."<input type='checkbox'".(preg_match('~^(1|t|true|y|yes|on)$~i',$Y)?" checked='checked'":"")."$ya value='1'>";elseif($m["type"]=="set")echo
enum_input("checkbox",$ya,$m,(is_string($Y)?explode(",",$Y):$Y));elseif(is_blob($m)&&ini_bool("file_uploads"))echo"<input type='file' name='fields-$B'>";elseif($r=="json"||preg_match('~^jsonb?$~',$m["type"]))echo"<textarea$ya cols='50' rows='12' class='jush-js'>".h($Y).'</textarea>';elseif(($Mi=preg_match('~text|lob|memo~i',$m["type"]))||preg_match("~\n~",$Y)){if($Mi&&JUSH!="sqlite")$ya
.=" cols='50' rows='12'";else{$L=min(12,substr_count($Y,"\n")+1);$ya
.=" cols='30' rows='$L'";}echo"<textarea$ya>".h($Y).'</textarea>';}else{$nj=driver()->types();$if=(!preg_match('~int~',$m["type"])&&preg_match('~^(\d+)(,(\d+))?$~',$m["length"],$A)?((preg_match("~binary~",$m["type"])?2:1)*$A[1]+($A[3]?1:0)+($A[2]&&!$m["unsigned"]?1:0)):($nj[$m["type"]]?$nj[$m["type"]]+($m["unsigned"]?0:1):0));if(JUSH=='sql'&&min_version(5.6)&&preg_match('~time~',$m["type"]))$if+=7;echo"<input".((!$Dd||$r==="")&&preg_match('~(?<!o)int(?!er)~',$m["type"])&&!preg_match('~\[\]~',$m["full_type"])?" type='number'":"")." value='".h($Y)."'".($if?" data-maxlength='$if'":"").(preg_match('~char|binary~',$m["type"])&&$if>20?" size='".($if>99?60:40)."'":"")."$ya>";}echo
adminer()->editHint($R,$m,$Y);$bd=0;foreach($rd
as$x=>$X){if($x===""||!$X)break;$bd++;}if($bd&&count($rd)>1)echo
script("qsl('td').oninput = partial(skipOriginal, $bd);");}}function
process_input(array$m){if(stripos($m["default"],"GENERATED ALWAYS AS ")===0)return;$u=bracket_escape($m["field"]);$r=idx($_POST["function"],$u);$Y=idx($_POST["fields"],$u);if($m["type"]=="enum"||driver()->enumLength($m)){$Y=$Y[0];if($Y=="orig")return
false;if($Y=="null")return"NULL";$Y=substr($Y,4);}if($m["auto_increment"]&&$Y=="")return
null;if($r=="orig")return(preg_match('~^CURRENT_TIMESTAMP~i',$m["on_update"])?idf_escape($m["field"]):false);if($r=="NULL")return"NULL";if($m["type"]=="set")$Y=implode(",",(array)$Y);if($r=="json"){$r="";$Y=json_decode($Y,true);if(!is_array($Y))return
false;return$Y;}if(is_blob($m)&&ini_bool("file_uploads")){$Zc=get_file("fields-$u");if(!is_string($Zc))return
false;return
driver()->quoteBinary($Zc);}return
adminer()->processInput($m,$Y,$r);}function
search_tables(){$_GET["where"][0]["val"]=$_POST["query"];$Qh="<ul>\n";foreach(table_status('',true)as$R=>$S){$B=adminer()->tableName($S);if(isset($S["Engine"])&&$B!=""&&(!$_POST["tables"]||in_array($R,$_POST["tables"]))){$I=connection()->query("SELECT".limit("1 FROM ".table($R)," WHERE ".implode(" AND ",adminer()->selectSearchProcess(fields($R),array())),1));if(!$I||$I->fetch_row()){$Wg="<a href='".h(ME."select=".urlencode($R)."&where[0][op]=".urlencode($_GET["where"][0]["op"])."&where[0][val]=".urlencode($_GET["where"][0]["val"]))."'>$B</a>";echo"$Qh<li>".($I?$Wg:"<p class='error'>$Wg: ".error())."\n";$Qh="";}}}echo($Qh?"<p class='message'>".'No tables.':"</ul>")."\n";}function
on_help($mb,$bi=0){return
script("mixin(qsl('select, input'), {onmouseover: function (event) { helpMouseover.call(this, event, $mb, $bi) }, onmouseout: helpMouseout});","");}function
edit_form($R,array$n,$K,$wj,$l=''){$_i=adminer()->tableName(table_status1($R,true));page_header(($wj?'Edit':'Insert'),$l,array("select"=>array($R,$_i)),$_i);adminer()->editRowPrint($R,$n,$K,$wj);if($K===false){echo"<p class='error'>".'No rows.'."\n";return;}echo"<form action='' method='post' enctype='multipart/form-data' id='form'>\n";if(!$n)echo"<p class='error'>".'You have no privileges to update this table.'."\n";else{echo"<table class='layout'>".script("qsl('table').onkeydown = editingKeydown;");$Ba=!$_POST;foreach($n
as$B=>$m){echo"<tr><th>".adminer()->fieldName($m);$k=idx($_GET["set"],bracket_escape($B));if($k===null){$k=$m["default"];if($m["type"]=="bit"&&preg_match("~^b'([01]*)'\$~",$k,$qh))$k=$qh[1];if(JUSH=="sql"&&preg_match('~binary~',$m["type"]))$k=bin2hex($k);}$Y=($K!==null?($K[$B]!=""&&JUSH=="sql"&&preg_match("~enum|set~",$m["type"])&&is_array($K[$B])?implode(",",$K[$B]):(is_bool($K[$B])?+$K[$B]:$K[$B])):(!$wj&&$m["auto_increment"]?"":(isset($_GET["select"])?false:$k)));if(!$_POST["save"]&&is_string($Y))$Y=adminer()->editVal($Y,$m);$r=($_POST["save"]?idx($_POST["function"],$B,""):($wj&&preg_match('~^CURRENT_TIMESTAMP~i',$m["on_update"])?"now":($Y===false?null:($Y!==null?'':'NULL'))));if(!$_POST&&!$wj&&$Y==$m["default"]&&preg_match('~^[\w.]+\(~',$Y))$r="SQL";if(preg_match("~time~",$m["type"])&&preg_match('~^CURRENT_TIMESTAMP~i',$Y)){$Y="";$r="now";}if($m["type"]=="uuid"&&$Y=="uuid()"){$Y="";$r="uuid";}if($Ba!==false)$Ba=($m["auto_increment"]||$r=="now"||$r=="uuid"?null:true);input($m,$Y,$r,$Ba);if($Ba)$Ba=false;echo"\n";}if(!support("table")&&!fields($R))echo"<tr>"."<th><input name='field_keys[]'>".script("qsl('input').oninput = fieldChange;")."<td class='function'>".html_select("field_funs[]",adminer()->editFunctions(array("null"=>isset($_GET["select"]))))."<td><input name='field_vals[]'>"."\n";echo"</table>\n";}echo"<p>\n";if($n){echo"<input type='submit' value='".'Save'."'>\n";if(!isset($_GET["select"]))echo"<input type='submit' name='insert' value='".($wj?'Save and continue edit':'Save and insert next')."' title='Ctrl+Shift+Enter'>\n",($wj?script("qsl('input').onclick = function () { return !ajaxForm(this.form, '".'Saving'."â€¦', this); };"):"");}echo($wj?"<input type='submit' name='delete' value='".'Delete'."'>".confirm()."\n":"");if(isset($_GET["select"]))hidden_fields(array("check"=>(array)$_POST["check"],"clone"=>$_POST["clone"],"all"=>$_POST["all"]));echo
input_hidden("referer",(isset($_POST["referer"])?$_POST["referer"]:$_SERVER["HTTP_REFERER"])),input_hidden("save",1),input_token(),"</form>\n";}function
shorten_utf8($Q,$y=80,$ui=""){if(!preg_match("(^(".repeat_pattern("[\t\r\n -\x{10FFFF}]",$y).")($)?)u",$Q,$A))preg_match("(^(".repeat_pattern("[\t\r\n -~]",$y).")($)?)",$Q,$A);return
h($A[1]).$ui.(isset($A[2])?"":"<i>â€¦</i>");}function
icon($Pd,$B,$Od,$Si){return"<button type='submit' name='$B' title='".h($Si)."' class='icon icon-$Pd'><span>$Od</span></button>";}if(isset($_GET["file"])){if(substr(VERSION,-4)!='-dev'){if($_SERVER["HTTP_IF_MODIFIED_SINCE"]){header("HTTP/1.1 304 Not Modified");exit;}header("Expires: ".gmdate("D, d M Y H:i:s",time()+365*24*60*60)." GMT");header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");header("Cache-Control: immutable");}@ini_set("zlib.output_compression",'1');if($_GET["file"]=="default.css"){header("Content-Type: text/css; charset=utf-8");echo
lzw_decompress("h:M‡±h´ÄgÌĞ±ÜÍŒ\"PÑiÒm„™cQCa¤é	2Ã³éˆŞd<Ìfóa¼ä:;NBˆqœR;1Lf³9ÈŞu7&)¤l;3ÍÑñÈÀJ/‹†CQXÊr2MÆaäi0›„ƒ)°ìe:LuÃhæ-9ÕÍ23lÈÎi7†³màZw4™†Ñš<-•ÒÌ´¹!†U,—ŒFÃ©”vt2‘S,¬äa´Ò‡FêVXúa˜Nqã)“-—ÖÎÇœhê:n5û9ÈY¨;jµ”-Ş÷_‘9krùœÙ“;.ĞtTqËo¦0‹³­Öò®{íóyùı\rçHnìGS™ Zh²œ;¼i^ÀuxøWÎ’C@Äö¤©k€Ò=¡Ğb©Ëâì¼/AØà0¤+Â(ÚÁ°lÂÉÂ\\ê Ãxè:\rèÀb8\0æ–0!\0FÆ\nB”Íã(Ò3 \r\\ºÛêÈ„a¼„œ'Iâ|ê(iš\n‹\r©¸ú4Oüg@4ÁC’î¼†º@@†!ÄQB°İ	Â°¸c¤ÊÂ¯Äq,\r1EhèÈ&2PZ‡¦ğiGûH9G’\"v§ê’¢££¤œ4r”ÆñÍDĞR¤\n†pJë-A“|/.¯cê“Du·£¤ö:,˜Ê=°¢RÅ]U5¥mVÁkÍLLQ@-\\ª¦ËŒ@9Áã%ÚSrÁÎñMPDãÂIa\rƒ(YY\\ã@XõpÃê:£p÷lLC —Åñè¸ƒÍÊO,\rÆ2]7œ?m06ä»pÜTÑÍaÒ¥Cœ;_Ë—ÑyÈ´d‘>¨²bnğ…«n¼Ü£3÷X¾€ö8\rí[Ë€-)Ûi>V[Yãy&L3¯#ÌX|Õ	†X \\Ã¹`ËC§ç˜å#ÑÙHÉÌ2Ê2.# ö‹Zƒ`Â<¾ãs®·¹ªÃ’£º\0uœhÖ¾—¥M²Í_\niZeO/CÓ’_†`3İòğ1>‹=Ğk3£…‰R/;ä/dÛÜ\0ú‹ŒãŞÚµmùúò¾¤7/«ÖAÎXƒÂÿ„°“Ãq.½sáL£ı— :\$ÉF¢—¸ª¾£‚w‰8óß¾~«HÔj…­\"¨¼œ•¹Ô³7gSõä±âFLéÎ¯çQò_¤’O'WØö]c=ı5¾1X~7;˜™iş´\rí*\n’¨JS1Z¦™ø£ØÆßÍcå‚tœüAÔVí86fĞdÃy;Y]©õzIÀp¡Ñû§ğc‰3®YË]}Â˜@¡\$.+”1¶'>ZÃcpdàéÒGLæá„#kô8PzœYÒAuÏvİ]s9‰ÑØ_AqÎÁ„:†ÆÅ\nK€hB¼;­ÖŠXbAHq,âCIÉ`†‚çj¹S[ËŒ¶1ÆVÓrŠñÔ;¶pŞBÃÛ)#é‰;4ÌHñÒ/*Õ<Â3L Á;lfª\n¶s\$K`Ğ}ÆôÕ”£¾7ƒjx`d–%j] ¸4œ—Y¤–HbY ØJ`¤GG ’.ÅÜK‚òfÊI©)2ÂŠMfÖ¸İX‰RC‰¸Ì±V,©ÛÑ~g\0è‚àg6İ:õ[jí1H½:AlIq©u3\"™êæq¤æ|8<9s'ãQ]JÊ|Ğ\0Â`p ³îƒ«‰jf„OÆbĞÉú¬¨q¬¢\$é©²Ã1J¹>RœH(Ç”q\n#rŠ’à@e(yóVJµ0¡QÒˆ£òˆ6†Pæ[C:·Gä¼‘ İ4©‘Ò^ÓğÃPZŠµ\\´‘è(\nÖ)š~¦´°9R%×Sj·{‰7ä0Ş_šÇs	z|8ÅHê	\"@Ü#9DVLÅ\$H5ÔWJ@—…z®a¿J Ä^	‘)®2\nQvÀÔ]ëÇ†ÄÁ˜‰j (A¸Ó°BB05´6†bË°][ŒèkªA•wvkgôÆ´öºÕ+k[jm„zc¶}èMyDZií\$5e˜«Ê·°º	”A˜ CY%.W€b*ë®¼‚.­Ùóq/%}BÌXˆ­çZV337‡Ê»a™„€ºòŞwW[áLQÊŞ²ü_È2`Ç1IÑi,÷æ›£’Mf&(s-˜ä˜ëÂAÄ°Ø*””DwØÄTNÀÉ»ÅjX\$éxª+;ĞğËFÚ93µJkÂ™S;·§ÁqR{>l;B1AÈIâb) (6±­r÷\rİ\rÚ‡’Ú‚ìZ‘R^SOy/“ŞM#ÆÏ9{k„àê¸v\"úKCâJƒ¨rEo\0øÌ\\,Ñ|faÍš†³hI“©/oÌ4Äk^pî1HÈ^“ÍphÇ¡VÁvox@ø`ígŸ&(ùˆ­ü;›ƒ~ÇzÌ6×8¯*°ÆÜ5®Ü‰±E ÁÂp†éâîÓ˜˜¤´3“öÅ†gŸ™rDÑLó)4g{»ˆä½å³©—Lš&ú>è„»¢ØÚZì7¡\0ú°ÌŠ@×ĞÓÛœffÅRVhÖ²çIŠÛˆ½âğrÓw)‹ ‚„=x^˜,k’Ÿ2ôÒİ“jàbël0uë\"¬fp¨¸1ñRI¿ƒz[]¤wpN6dIªzëõån.7X{;ÁÈ3ØË-I	‹âûü7pjÃ¢R#ª,ù_-ĞüÂ[ó>3À\\æêÛWqŞq”JÖ˜uh£‡ĞFbLÁKÔåçyVÄ¾©¦ÃŞÑ•®µªüVœîÃf{K}S ÊŞ…‰Mş‡·Í€¼¦.M¶\\ªix¸bÁ¡1‡+£Î±?<Å3ê~HıÓ\$÷\\Ğ2Û\$î eØ6tÔOÌˆã\$s¼¼©xÄşx•ó§CánSkVÄÉ=z6½‰¡Ê'Ã¦äNaŸ¢Ö¸hŒÜü¸º±ı¯R¤å™£8g‰¢äÊw:_³î­íÿêÒ’IRKÃ¨.½nkVU+dwj™§%³`#,{é†³ËğÊƒY‡ı×õ(oÕ¾Éğ.¨c‚0gâDXOk†7®èKäÎlÒÍhx;ÏØ İƒLû´\$09*–9 ÜhNrüMÕ.>\0ØrP9ï\$Èg	\0\$\\Fó*²d'ÎõLå:‹bú—ğ42Àô¢ğ9Àğ@ÂHnbì-¤óE #ÄœÉÃ êrPY‚ê¨ tÍ Ø\nğ5.©àÊâî\$op l€X\n@`\r€	àˆ\r€Ğ Î ¦ ’ ‚	 ÊàêğÚ Î	@Ú@Ú\n ƒ †	\0j@ƒQ@™1\rÀ‚@“ ¢	\$p	 V\0ò``\n\0¨\n Ğ\n@¨' ìÀ¤\n\0`\rÀÚ ¬	à’\rà¤ ´\0Ğr°æÀò	\0„`‚	àî {	,\"¨È^PŸ0¥\n¬4±\n0·¤ˆ.0ÃpËğÓ\rpÛ\rğãpëğópûñqñQ0ß%€ÑÑ1Q8\n Ô\0ôkÊÈ¼\0^—àÒ\0`àÚ@´àÈ>\nÑo1w±,Y	h*=Š¡P¦:Ñ–VƒïĞ¸.q£ÅÍ\rÕ\r‘péĞñ1ÁÑQ	ÑÑ1× ƒ`Ññ/17±ëñò\r ^Àä\"y`\nÀ Œ# ˜\0ê	 p\n€ò\n€š`Œ ˆr ”Q†ğ¦bç1Ò3\n°¯#°µ#ğ¼1¥\$q«\$Ñ±%0å%q½%Ğù&Ç&qÍ ƒ&ñ'1Ú\rR}16	 ï@b\r`µ`Ü\rÀˆ	€ŞÀÌ€dàª€¨	j\n¯``À†\n€œ`dcÑP–€,ò1R×Ÿ\$¿rIÒO ‚	Q	òY32b1É&‘Ï01ÓÑÙ ’Ó fÀÏ\0ª\0¤ Îf€\0j\n f`â	 ®\n`´@˜\$n=`†\0ÈÒv nIĞ\$ÿP(Âd'Ëğô„Äà·gÉ6‘™-Šƒ-ÒC7Rçà‡ —	4à ô-1Ë&±Ñ2t\rô\"\n 	H*@	ˆ`\n ¤ è	àòlÕ2¿,z\rì~È è\r—Fìth‰Šö€Ø ëmõäÄì´z”~¡\0]GÌF\\¥×I€\\¥£}ItC\nÁT„}ªØ×IEJ\rx×ÉûÂ>ÙMp‹„IHô~êäfht„ë¯.b…—xYEìiK´ªoj\nğíÅLÀŞtr×.À~d»H‡2U4©Gà\\Aê‚ç4ş„uPtŞÃÕ½è° òàÍL/¿P×	\"G!RîÎMtŸO-Ìµ<#õAPuI‡ëRè\$“c’¹ÃD‹ÆŠ €§¢-‚ÃGâ´O`Pv§^W@tH;Q°µRÄ™Õ\$´©gKèF<\rR*\$4®' ó¨ĞÈÊ[í°ÛIªó­UmÑÆh:+ş¼5@/­l¾I¾ªí2¦‚^\0ODøšª¬Ø\rR'Â\rèTĞ­[êÖ÷ÄÄª®«MCëMÃZ4æE B\"æ`ö‚´euNí,ä™¬é]Ïğtú\rª`Ü@hö*\r¶.Vƒ–%Ú!MBlPF™Ï\"Øï&Õ/@îv\\CŞï©:mMgnò®öÊi8˜I2\rpívjí©Æ÷ï+Z mT©ueõÕfv>f´Ğ˜Ö`DU[ZTÏVĞCàµTğ\r–¹Uv‹kõ^×¦øLëÙb/¾K¶Sev2÷ubvÇOVDğÖImÕ\$ò%ÖX?udç!W•|,\rø+îµcnUe×ZÆÄÊ–€şöë-~X¯ºûîÀêÔöBGd¶\$i¶çMv!t#Lì3o·UI—O—u?ZweRÏ ëcwª. `È¡iøñ\rb§%©b€â¦H®\"\"\"hí _\$b@ázªä\0f\"ŒérW¨®*ŠæB|\$\$¬BÖ× \"@r¯‚(\r`Ê îC÷¸Ç(0&†.`ÒNk9B\n&#(Äêâ„@ä‚¯Ú«d—ü^÷º®Šü £@²`ÒI-{ƒ0£â\n–B{‚4sG{§ø;z®©b÷{ Ñ{bƒ×¯„){BàÁxKÂÀÅ‡5=cÚª‰«yåî&ìJ£PrÅI/‡ƒÜ \0ÚâV\r¥×‰í‰È=¸£‰‚N\\Ø¦=ÃK‰è}XVíx¹Š—µŠØ¥ŠË‹x²©døÕŠÛŒ*H'¦Î´¸»{XÆ=ØÊ=\0ï8¼\0¾¹…å[É«†J†ÚtÙùOØe…¹ØÉ‹èŞ\røıŒ ÊDXı§Å‡Äı}×z°“¾ ù)y'Ù'ÃÑÙIÌ(ù[l(5™`f\\Á`¿”ùe—.lY(¹=z—×”!Y%h€¾O¹+‹ù•—`Ù™\"e“ æçÄ—˜º–Kòù¥ş¿¯£˜¸ÿ– ßšÙ#S™¹EIœYû›.HÖJtG·—œ`¾ŒH¼J5»Í5˜™~ ¸€6C‹¥hø˜§ùXDz\n–x¡‚yshššFK¡c¡zj¢Z€Y8(¹ş%Ù|yŸI«£ß‘Øƒ›Úée¡úY¡X»¡™u¢Ú ´Úiœ]¦Úc¡ÚM¥ú;ŸÈ§‘ùò>Ç¡ƒšQ T©øüú¨ [~Wé~Ùcİ‚z›©úµz¥º½¢ú\r¬:  \0èrYû¢x)‚Ê!ªúÉ¡¹K¦ú+§z!£šÓ€C+˜š°´Ù®âÃ¯:İ§ª™¤ú©¢Zgšû~z4f¥¯	¥:÷£’sºÓª—ê+õxÊÂš%Œ»›=³™G–ÛIf3?˜úãø¿µ+Y´úq¶@àûGœúá™y¶»oµÙÑ´Ûp\rª~Á{Wœš¶[…·¹é®yè:\0Æ\\»‹·;e¹Û¡¶YI\"·¸zdÂ˜k©Zö|[uš‚uÏ+˜×¹9q¼¹nR Ë®¥B—˜»Ø×z|\rŠá¤„ık¤^»€î“ª[1ªÛ%‹.“pA­2<›Û=¼Ø¡•è\$é;Ö5œ)³›m¸œ!‹»ÑXXıº‹YÃx¨5vT\\®QÀ%:À¢>ÀàÉ›Û;¸›e’|/·•yÁÅ§ÅW§x× |g®œŠ™ÓÄCİÆ\\‰›ü‡¼<¼9z\\®#ğ.FV;8¡èNÍX7ø×ÊÎ\"8&d5¬P…4Gj?Ê\0Ü?\"=˜­ùHER");}elseif($_GET["file"]=="dark.css"){header("Content-Type: text/css; charset=utf-8");echo
lzw_decompress("h:M‡±h´ÄgÆÈh0ÁLĞàd91¢S!¤Û	Fƒ!°æ\"-6N‘€ÄbdGgÓ°Â:;Nr£)öc7›\rç(HØb81˜†s9¼¤Ük\rçc)Êm8O•VA¡Âc1”c34Of*’ª- P¨‚1©”r41Ùî6˜Ìd2ŒÖ•®Ûo½ÜÌ#3—‰–BÇf#	ŒÖg9Î¦êØŒfc\rÇI™ĞÂb6E‡C&¬Ğ,buÄêm7aVã•ÂÁs²#m!ôèhµårùœŞv\\3\rL:SA”Âdk5İnÇ·×ìšıÊaF†¸3é˜Òe6fS¦ëy¾óør!ÇLú -ÎK,Ì3Lâ@º“J¶ƒË²¢*J äìµ£¤‚»	¸ğ—¹Ášb©cèà9­ˆê9¹¤æ@ÏÔè¿ÃHÜ8£ \\·Ãê6>«`ğÅ¸Ş;‡Aˆà<T™'¨p&q´qEˆê4Å\rl­…ÃhÂ<5#pÏÈR Ñ#I„İ%„êfBIØŞÜ²”¨>…Ê«29<«åCîj2¯î»¦¶7j¬“8jÒìc(nÔÄç?(a\0Å@”5*3:Î´æ6Œ£˜æ0Œã-àAÀlL›•PÆ4@ÊÉ°ê\$¡H¥4 n31¶æ1Ítò0®áÍ™9ŒƒéWO!¨r¼ÚÔØÜÛÕèHÈ†£Ã9ŒQ°Â96èF±¬«<ø7°\rœ-xC\n Üã®@Òø…ÜÔƒ:\$iÜØ¶m«ªË4íKid¬²{\n6\r–…xhË‹â#^'4Vø@aÍÇ<´#h0¦Sæ-…c¸Ö9‰+pŠ«Ša2Ôcy†h®BO\$Áç9öw‡iX›É”ùVY9*r÷Htm	@bÖÑ|@ü/€l’\$z¦­ +Ô%p2l‹˜É.õØúÕÛìÄ7ï;Ç&{ÀËm„€X¨C<l9ğí6x9ïmìò¤ƒ¯À­7RüÀ0\\ê4Î÷PÈ)AÈoÀx„ÄÚqÍO#¸¥Èf[;»ª6~PÛ\rŒa¸ÊTGT0„èìu¸ŞŸ¾³Ş\n3ğ\\ \\ÊƒJ©udªCGÀ§©PZ÷>“³Áûd8ÖÒ¨èéñ½ïåôC?V…·dLğÅL.(tiƒ’­>«,ôƒÖœÃR+9i‡‡ŞC\$äØ#\"ÎAC€hV’b\nĞÊ6ğT2ƒewá\nf¡À6m	!1'cÁä;–Ø*eLRn\rì¾G\$ô2S\$áØ0†Àêa„'«l6†&ø~Ad\$ëJ†\$sœ ¦ÈƒB4òÉéjª.ÁRCÌ”ƒQ•jƒ\"7\nãXs!²6=ÎBÈ€}");}elseif($_GET["file"]=="functions.js"){header("Content-Type: text/javascript; charset=utf-8");echo
lzw_decompress("':œÌ¢™Ğäi1ã³1Ôİ	4›ÍÀ£‰ÌQ6a&ó°Ç:OAIìäe:NFáD|İ!‘Ÿ†CyŒêm2ËÅ\"ã‰ÔÊr<”Ì±˜ÙÊ/C#‚‘Ùö:DbqSe‰JË¦CÜº\n\n¡œÇ±S\rZ“H\$RAÜS+XKvtdÜg:£í6Ÿ‰EvXÅ³j‘ÉmÒ©ej×2šM§©äúB«Ç&Ê®‹L§C°3„åQ0ÕLÆé-xè\nÓìD‘ÈÂyNaäPn:ç›¼äèsœÍƒ( cLÅÜ/õ£(Æ5{ŞôQy4œøg-–‚ı¢êi4ÚƒfĞÎ(ÕëbUıÏk·îo7Ü&ãºÃ¤ô*ACb’¾¢Ø`.‡­ŠÛ\rÎĞÜü»ÏÄú¼Í\n ©ChÒ<\r)`èØ¥`æ7¥CÊ’ŒÈâZùµãXÊ<QÅ1X÷¼‰@·0dp9EQüf¾°ÓFØ\r‰ä!ƒæ‹(hô£)‰Ã\np'#ÄŒ¤£HÌ(i*†r¸æ&<#¢æ7KÈÈ~Œ# È‡A:N6ã°Ê‹©lÕ,§\r”ôJPÎ3£!@Ò2>Cr¾¡¬h°N„á]¦(a0M3Í2”×6…ÔUæ„ãE2'!<·Â#3R<ğÛãXÒæÔCHÎ7ƒ#nä+±€a\$!èÜ2àPˆ0¤.°wd¡r:Yö¨éE²æ…!]„<¹šjâ¥ó@ß\\×pl§_\rÁZ¸€Ò“¬TÍ©ZÉsò3\"²~9À©³jã‰PØ)Q“Ybİ•DëYc¿`ˆzácµÑ¨ÌÛ'ë#t“BOh¢*2ÿ…<Å’Oêfg-Z£œˆÕ# è8aĞ^ú+r2b‰ø\\á~0©áş“¥ùàW©¸ÁŞnœÙp!#•`åëZö¸6¶12×Ã@é²kyÈÆ9\rìäB3çƒpŞ…î6°è<£!pïG¯9àn‘o›6s¿ğ#FØ3íÙàbA¨Ê6ñ9¦ıÀZ£#ÂŞ6ûÊ%?‡s¨È\"ÏÉ|Ø‚§)şbœJc\r»Œ½NŞsÉÛih8Ï‡¹æİŸè:Š;èúHåŞŒõu‹I5û@è1îªAèPaH^\$H×vãÖ@Ã›L~—¨ùb9'§ø¿±S?PĞ-¯˜ò˜0Cğ\nRòmÌ4‡ŞÓÈ“:ÀõÜÔ¸ï2òÌ4œµh(k\njIŠÈ6\"˜EYˆ#¹W’rª\r‘G8£@tĞáXÔ“âÌBS\nc0Ék‚C I\rÊ°<u`A!ó)ĞÔ2”ÖC¢\0=‡¾ æáäPˆ1‘Ó¢K!¹!†åŸpÄIsÑ,6âdÃéÉi1+°ÈâÔk‰€ê<•¸^	á\nÉ20´FÔ‰_\$ë)f\0 ¤C8E^¬Ä/3W!×)Œu™*äÔè&\$ê”2Y\n©]’„EkñDV¨\$ïJ²’‡xTse!RY» R™ƒ`=Lò¸ãàŞ«\nl_.!²V!Â\r\nHĞk²\$×`{1	|± °i<jRrPTG|‚w©4b´\r‰¡Ç4d¤,§E¡È6©äÏ<Ãh[N†q@Oi×>'Ñ©\rŠ¥ó—;¦]#“æ}Ğ0»ASIšJdÑA/QÁ´â¸µÂ@t\r¥UG‚Ä_G<éÍ<y-IÉzò„¤Ğ\" PÂàB\0ıíÀÈÁœq`‘ïvAƒˆaÌ¡Jå RäÊ®)Œ…JB.¦TÜñL¡îy¢÷ Cpp\0(7†cYY•a¨M€é1•em4Óc¢¸r£«S)oñÍà‚pæC!I†¼¾SÂœb0mìñ(d“EHœøš¸ß³„X‹ª£/¬•™P©èøyÆXé85ÈÒ\$+—Ö–»²gdè€öÎÎyİÜÏ³J×Øë ¢lE“¢urÌ,dCX}e¬ìÅ¥õ«mƒ]ˆĞ2 Ì½È(-z¦‚Zåú;Iöî¼\\Š) ,\n¤>ò)·¤æ\rVS\njx*w`â´·SFiÌÓd¯¼,»áĞZÂJFM}ĞŠ À†\\Z¾Pìİ`¹zØZûE]íd¤”ÉŸOëcmÔ]À ¬Á™•‚ƒ%ş\"w4Œ¥\n\$øÉzV¢SQDÛ:İ6«äG‹wMÔîS0B‰-sÆê)ã¾Zí¤c|Ë^RšïEè8kMïÑÌsŒd¹ka™)h%\"Pà0nn÷†/Áš#;Ög\rdÈ¸8†ŞF<3\$©,åP);<4`Î¢<2\n”Êõé@w-®áÍ—AÏ0¹ºª“¹LrîYhìXCàa˜>ºæt‹ºLõì2‚yto;2‡İQª±tîÊfrmè:§”Aíù‰¡÷ANºİ\\\"kº5oVëÉƒ=îÀt…7r1İpäAv\\+9ª„â€{°ç^(iœ‰f¬=·rŠÒºŠuÚÊûtØ]yÓŞ…ĞùCö¶ºÁ³ÒõİÜgi¥vfİù+¥Ã˜|Êì;œ€¸Âà]~ÓÊ|\re÷¥ì¿“šİ‚Ú'ƒíû²‰”¦ä¯²°	½\0+W‡coµw6wd Su¼j¨3@–Œò0!ã÷\n .w€m[8x<²ËcM¬\n9ı²ı'aùŞˆ1>È£’[¶ïµúdïŞux¯à<\"Yc¸ŞB!i¹¥ê•wÀ}’ô5U¹kººÜØ]­¶¸ÔÒÀ{óI×šR…‰–¥=f W~æ]É(bea®'ubïm‘>ƒ)\$°†P÷á-šƒ6şR*IGu#Æ•UKµAXŒtÑ(Ó`_Âà\" ¾£p¸ &UËËÙIíÉ]ıÁYG6P]Ar!b¡ *Ğ™JŠo•µÓ¯åÿ™óïÁòvı½*À Ø!éš~_ªÀÙ4B³_~RB˜iKùŒ’ş`ç‰&JÛ\0­ô®N\0Ğ\$àÌşåCÂK œSĞòâjZ¤Ğ Ìû0pvMJ bN`Lÿæ­eº/`RO.0Pä82`ê	åüÆ¸d Â˜GxÇbP-(@É¸Ó@æ4¨H%<&–ÀÌZà™Àèp„¬°Š%\0®p€ĞĞ„øêã	…¯	àÈ/\"ö¢J³¢\ns†–_ÀÌ\rŒàg`‹œ!käpX	èĞ:Ävíç6p\$ú'ğÇ¥RUeZÿ¨d\$ì\nLáBºâ†ó.ŞdŒn€î¤Òtm€>v…jä•í€)‘	Mº\r\0Â.àÊŠH’Ñ\"…5‚*!eºZJº‰è’ëãf(dc±¼(xÜÑjg\0\\õ€ÂõÀ¶ Z@ºàê|`^›r)<‹(’ˆ„ˆ†È)ÌëªóÊĞì@YkÂmÌíl3QyÑ@É‘ŒÑfÎìPn„ç¼¨ĞT ò¯N·mRÕq³íâVmvúNÖ‚|úĞ¨Z²„È†Ú(Ypø‰\"„4Ç¨æàò&€î%lÒP`Ä€£Xx bbdĞr0Fr5°<»Cæ²z¨¯6ähe!¤ˆ\rdzàØK;Ät³²\nÙÍ …HÆ‹Qš\$QŸEnn¢n\rÀš©#šT\$°²Ëˆ(ÈŸÑ©|c¤,¼-ú#èÚ\r Üá‰Jµ{dÑE\n\$²ÆBrœiTÔò‘+Å2PED•Be‹}&%Rf²¥\nüƒ^ôˆCàÈZàZ RV“ÅA,Ñ;‘«ç<ÂÄì\0O1éÔêc^\r%‚\r ìë`Òn\0y1èÔ.Âğ\r´Ä‚K1æM3H®\r\"û0\0NkXPr¸¯{3 ì}	\nSÈd†ˆÚ—Šx.ZñRTñ„’wS;53 .¢s4sO3FºÙ2S~YFpZs¡'Î@Ù‘OqR4\n­6q6@DhÙ6ÍÕ7vE¢l\"Å^;-å(Â&Ïb*²*‹ò.! ä\r’!#çx'G\"€Í†w‰Á\"úÕ È2!\"R(vÀXŒæ|\"DÌvÀ¦)@á,¸zmòAÍwT@ÀÔ  Ğ\n‚ÖÓğºĞ«hĞ´IDÔP\$m>æ\r&`‡>´4ÈÒA#*ë#’<”w\$T{\$´4@›ˆdÓ´Rem6¯-#Dd¾%E¥DT\\ \$)@Ü´WC¬(t®\"MàÜ#@úTFŸ\r,g¦\rP8Ã~‘´Ö£Jü°c öŒàÄ¹Æ‚ê Ê\"™LªZÔä\r+P4ı=¥¤™Sâ™TõA)0\"¦CDhÇM\n%FÔpÖÓü|fLNlFtDmH¯ªş°5å=HÍ\n›Ä¼4ü³õ\$à¾Kñ6\rbZà¨\r\"pEQ%¤wJ´ÿV0Ô’M%ål\"hPFïA¬áAãŒ®ò/G’6 h6]5¥\$€f‹S÷CLiRT?R¨şC–ñõ£HU§Z¤æYbFş/æ.êZÜ\"\"^Îy´6R”G ²‹ÌnâúÜŒ\$ªÑå\\&OÖ(v^ ÏKUºÑ®ÎÒam³(\r€Šïº¯¾ü\$_ªæ%ñ+KTtØö.Ù–36\nëcµ”:´@6 újPÃAQõF’/S®k\"<4A„gAĞaU…\$'ëˆÓáfàûQO\"×k~²S;ÅÀ½ó.ïË: ˆk‘¼9­ü²Šóe]`nú¼Ò-7¨˜;îß+VËâ8WÀ©2H¢U‹®YlBívŞöâ¯ÖÔ†´°¶ö	§ıâîp®ÖÉl¾m\0ñ4Bò)¥XÁ\0ÊÂQßqFSq—4–ÿnFx+pÔò¦EÆSovúGW7o×w×KRW×\r4`|cqîe7,×19·u Ïu÷cqä’\"LC tÀhâ)§\r€àJÀ\\øW@à	ç|D#S\rŸ%Œ5læ!%+“+å^‡k^Ê™`/7¸‰(z*ñ˜‹€ğ“´E€İ{¦S(Wà×-“XÄ—0V£‘0Ë¥—îÈ=îÍa	~ëfBëË•2Q­êÂru mCÂìë„£tr(\0Q!K;xNıWÀúÿ§øÈ?b< @Å`ÖX,º‡`0eºÆ‚N'²Â‘…šœ¤&~‘øt”Óu‡\"| ¬i… ñBå  7¾Rø” ¸›lSu†°8Aû‰dF%(Ôú äúïó?3@A-oQŠÅº@|~©K†ÀÊ^@xóbšœ~œD¦@Ø³‰˜¸›…TNÅZ€C	WˆÒÂix<\0P|Äæ\n\0\n`¨¥ ¹\"&?st|Ã¯ˆwî%…ˆàèmdêuÀN£^8À[t©9ƒªB\$àğ§©ğ¦'\">UŒ~ÿ98‡ é“òÃ”FÄf °¹€u€È°/)9‡À™ˆ\0á˜ëAùz\"FWAx¤\$'©jG´(\"Ù ±s%T’HŠîßÀe,	Mœ7ï‹b¼ Ç…Øa„ Ë“”Æƒ·&wYÔÏ†3˜°Øø /’\rÏ–ù¯ŸÙ{›\"ùİœp{%4b„óŒ`íŒ¤Ôõ~n€åE3	•Î ›°9å3XÖd›äÕZÅ9ï'š™@‡¨‡‘l»f¯õØQbP¤*G…oŠåÅ`8•¨‘¯ùA›æB|Àz	@¦	àb¡Zn_Íhº'Ñ¢F\$f¬§`öóº†HdDdŒH%4\rsÎAjLRÈ'ŞùfÚ9g IÏØ,R\\·ø”Ê>\n†šH[´\"°Àî©ª\rÓ…ŒÂ•LÌ,%ëFLl8gzLç<0ko\$Çk­á`ÒÃKPÔvå@dÏ'V:V”ØMü%±èÕ@ø6Ç<\ràùT«‹®LE´‰NÔ€S#ö.¶[„x4¾açÌ­´LL‚® ª\n@’£\0Û«tÙ²å\n^F­—º¥ºŠ5`Í R“7ÈlL uµ(™d’º¡¹ Ô\räBf/uCf×4ÿcÒ Bïì€_´nLÔ\0© \$»îaYÆ¦¶¸€~ÀUkïv¥eôË¥¦Ë²\0™Z’aZ—“šœXØ£¦|CŠq“¨/<}Ø³¡–ÅÃº²”º¶ Zº*­w\nOã‡Åz`¼5“®18¶cø™€û®¯­®æÚIÀQ2YsÇK‹˜€æ\n£\\›\"›­ Ã°‡c†ò*õB¶€îÌ.éR1<3+õÅµ*ØSé[õ4Ómì­›:Rh‹‘ITdevÎIµHäèÒ-Zw\\Æ%nè56Œ\nÌWÓi\$ÕÅow¬˜+© ºùËrÉ¶&Jq+û}ÒDàø¼Ój«dÅÎ?æU%BBeÇ/M‚¶Nm=Ï„óU·Âb\$HRfªwb|•²x dû2æNiSàóØgÉ@îq@œß>ÎSv „§—•ƒ|ïkrŒx½Œ\0{ÔRƒ=FÿÏÎÎâ®Ï#r½‚8	ğˆZàvÈ8*Ê³£{2Sİ+;S¦œ‚Ó¨Æ+yL\$\"_Ûë©Bç8¬İ\"E¸%ºàºŒ\nø‘ĞÂp¾p''«p‚ówUÒª\"8Ğ±I\\ @… Ê¾ ‡Lnğæ Rß#MäDµşqLNÆî\n\\’Ì\$`~@`\0uç‰~^@àÕlˆ-{5ñ,@bruÁo[Á²¾¨Õ}é/ñy.×é {é6q‚°R™pàĞ\$¸+13ÛúÚú+ƒ¨O!D)…® à\nu”<¯,«áñß=‚JdÆ+}µd#©0ÉcÓ3U3»EY¹û¢\rû¦tj5Ò¥7»e©˜w×„Ç¡úµ¢^‚qß‚¿9Æ<\$}kíÍòŒRI-ø°¸+'_Ne?SÛRíhd*X˜4é®üc}¬è\"@Šˆvi>;5>Dn‰ ˜\räë)bNéuP@YäG<ñ¨6iõ#PB2A½-í0d0+ğ…ügKûø¿í?¨néãüdœdøOÀ‚Œ¯åácüi<‹ú‘‹0\0œ\\ù—ëÑgî¦ùæê¡––…NTi'  ·ô;iômjáÜˆÅ÷»¸uÎJ+ªV~À²ù 'ol`ù³¿ó\",ü†Ì£×ÓFÀå–	ıâ{C©¸¤şT aÏNEÛƒQÆp´ p€+?ø\nÆ>„'l½¤* tÉKÎ¬p°(YC\n-qÌ”0å\"*É•Á,#üâ÷7º\"%¨+qÄ¸êB±°=åi.@x7:Å%GcYIĞˆ0*™îÃkÀÛˆ„\\‡·¯ğQ_{¤ ÅÇ#Áı\rç{H³[p¨ >7ÓchënÎÂÔ.œµ£¦S|&JòMÇ¾8´Àm€OhşÄí	ÕÑqJ&a€İ¢¨'‰.bçOpØì\$ö–­Ü€D@°C‚HB–	ƒÈ&âİ¡|\$Ô¬-6°²+Ì+ÂŒ †•Âàœpº…à¬¡AC\r’É“…ì/Î0´ñÂî¢M†ÃiZŠnEœÍ¢j*>™û!Ò¢u%¤©gØ0£à€@ä¿5}r…É+3œ%Â”-m‹¢G‚<”ã¥T;0°¯¨’†DV£dÀgÛ9'lM¶ıHˆ£ F@äP˜‹unütFB%´MÄt'äGÔ2ÅÀ@2¢<«e™”;¢`ˆõ=LXÄ2àÏäX»}oc.LŠ+âxÓ†&D¨a’€¡€É«ÁF2\ngLEƒ°.\\xSLıx­;lwÑD=0_QV,a 5Š+Léó+Û|\$Åi­jZ\nê—DÖEÎ,B¾t\\Ï'H0ÁŒ±R~(\\\"¢Ö:”Ğn*ûšÕ(¡×o®1wãÕQí×röÒÃEteÓF•…\$èSÑ’]Ğ\rLäyF„‰‘\\BŒiÀh”hdáÿ&áš‡h;fo›¾B-y`ÅÔğ0ˆ„JlPéxao·\$ŠXq¼,(Ö¡†C*	Îë:¤/‚”öé®HG\"‚ğc€ˆC¢¡Q¸\nFÁÔ„Ò#ğ¶…8í¢F:Ğ£\0œ€Ok¾âDüÆ])›ÏštT8Láğ’¨”æn©`ÕÎ±|ªHJ³ˆ€Ö œ˜ \"Ò6ø{‹­ƒÁ?=I<HGc Å¤FÒ@†,C ¼@jì‰\$LŸ·â(‰nEÊ‘P¢æjb¿nãÎ‘«¶äWá \rÀLqé‰èÏĞsPH€ê‰z\\V\$kÄÒtr5‹,¤lšÈØè<ñ'\0^S02¸0f -5\"ac¼\"3U“p£æ“\"Ü˜©%•®\0'Zt\"96‘Ì9_ @Z{™0Iˆç¬DÀZE@ôÎNÃh`¡\"½` \0µ„ˆàĞÉ¹(GÃHâÄCh¥ ™I¼òf`@ZD¹\$)âKá;ZÚø\0ä/éC‘T>r_R@Oå`1r†TÒ¨Ib\0ç*¹8… ÄÇËh\$é_’pùRÄ•\$®¥Ni^ÊªP/O)¸Â.Å¹T6Ü\\’Ù”@T€¾ÑrÄ…`)øöÀT=ân\0Œ€2–œe«+€9Ê¢\\®—@¥äú‚>ÉPH1	äŠy#Êô¥rú<°a¸eÜK„Û/cM@_.\09Ëˆ““¨…ÔĞ¬B®ÔÁÙ0i†aó\n’ğdea´%|S2ô¿€å#€“¸nˆ»D\$/¹+EÎd‘•øÖ_2PšË\$s,ok¡#ü<‰	²AÂÄ‘r{B”Ù†A-Q4Ò¤Ù\nª\ryù!Æbä±«ñáOÚö@É¬Ák¤¼ ê±\"§rà*¤İ‡Œ’YÒ€/ğÈ‘ a0ñÙ%•.gE~ºù&© 89”áÃ#@M_ À”ı7Käƒ¸J`òX)²B\$¯(	:Ÿg‰–n*ù|†M6PZ†ªHtêJtq‰Cx†[Ú¼—äá…l=\n•®ÅU3Êf\\Ì”JîP	,™:É}TA»SYH(\n¢¸ØI¶Ù²Ä!t(2U\"Ë\\çX­^sÌ	Æ“a!®\nPrˆ`ÉX3fnb¥•©àèJ÷¬Ü&¸zåzQSf £üät¡!T?à9%€(QƒBø}6B°kP\0ó>õg”&~fhUğœr§,¢ p5HiˆÆpƒ„…¢qÉšügöVçVüÏOg“WEJ8â0GìÔak°Õ@N NMÄä°UĞUxÈª­ßS¦x	Áà	ğK‚@c 1yê±VlÏ ¦ÂC’“‚ğ2Q^rP6|ıI^Mª,¦j%dİ`Ü«àüF§Ï\\#%³|ÄC–¿­¡7ì‹¢ÔGÚTN–„Šãùi«H™–ÎQ­O¦ÏÁCÌyB’Ñ\$±%T°‹*á>z\rMM KpÓ J7OÛ·é4å%ò•\$¤pà’é4”°€”ŠÍ‚£¯EÒª\"Tõ\0O€\0’Õ@>	r›O¨]š¡¢xÒ}^¥IÚÖ@Ê Åºqnç…İ0©Bb¡Èµ‚IÉ(¤M/ı;é¦Ê}RN\n¡C£<b­PÔµu?Â=Pe¹C’™•…L^'ìSÔÎ?}4)ŒÓS-ÕÃğ1\r5S«OEóSFœÓ˜©AOR+ÓŞ™+v§å5Â&C)Ù®›KSDBß³N|E\rcÚUôYÊ¾Àê£Väøˆ?H˜)å®Ÿ+sFäákºLPW-ø,üU:’&™ãt{‘®Vo¤·ŠJ”l'¨ğWÈe74Xn GFª'‚®Ş`æÉCcö±%Ilñju6£ßÈÂvÂU³ğZë‹\0*œš¨NÔŸ#ö¤(¼ˆ¨n¥-;|•4«]XÇîÁy'œ °;İZÅ‘ñ) s9ÈÀ˜%€R+\$À°	¿‘QŞà(\"¡_kX˜„‘°¦˜\nM#€¦\"!p~:è*úÀ™°\$µ3O‰¸ÄÆŠª6½+•ƒà\nB{1ğà|H·K<[`3ğ#å®F@èÍÇ! |©ØŠ\0àğ—>‹Œ®˜ˆ[nrMMı+…á®mO_2¹ÑÈ†Å\0«e^	Ì7Z¸&êµBÅJè¤“h7QO%rfÆp ÎâÖ¥mØ¨â¾Ã‡Â4Eàl«úü+•àäV®£iñN SZàWté2WÅ[;ªÀv\"%Å\$^Ö-(I\$ÊÈS@R-&³Tãz¬šk(²–	ä%R8ìuY\0[9-¢ÈÎ(õ)E¹è‰8¡=^¹†¡ÁG˜5#Á¼€¾)1V¦Éb\r]”Ne;&ÌY›`r¬êI§ØPİ±ÜËÁÖ²ª \0Å@Pç7°·â0Hª¨ÃØR­x¾\0000C|än=¨Š`ĞáTT¿Ø\rEhONÈ´Á' Ò&Ütc©K ‡Ü•U5œşÖßÂÎÃõP3\\î‡à2\"\0yó5¢V]¼©6>ĞU!¡@ËhuÌÚ(¼\"E%07B…½6¼dáHN±¢–‘µìij';@‚ÕeËMzlSfjKY–Öó­®-uhó‰H–œ¯smL@éĞ\"r×jÊºéj'l7	ò•(u‘u‹ÑEåÂ•·e¥a†@ñ„+K‰:Ó•Â%n«z Vñ·ˆÑ;ä[î_Vz_­•Eàãâ8†<…Sb›¨™‹ÜÍÖ6gÀ¼:cƒÍşÀ7\nµ¨­ì%Q› K¡7óÜ®BÛë‘Úñw¨u¹5©ì0»”ÖšãÊ¹yÃncnK™‰úæ¦T8åÊ™÷s±ºW=+—=K\n_[p¢G¿Ä·C5¢ÁÖÃ'ÛD\"„İM<\":|Mq4¹¹Îf•sÁx	qlÍ°›‚QPÓ²aOY×E=ûõî6nTë–’–BtœhÄC\0pÿ×@n£ÎD(aÜP°\"„Šï‹'ZN…äÛ¬¢®\rüLNXŠg±Š<!w•¶¸›Ú[û…B)´§)~½×ãcÂx”àvšiÂ¦ÿqÉø•¶˜a¤@KÕğ7s§EQdÃ½˜ïkô÷Ä?\"Ú3-\"UÆ|•½ıíÂï|21D>ß³â]Â­&ŠŠŠ\\hèTÆ³5š\0`Tz¢ás -¼N£¹ÉÙ\"†f¸NåLU¹]n(D©(˜ê&%\"e\\¬—OãÉNæInÛ¿¤”\0ÒĞ€ìÆ•±Ø÷@Á€ÑïVä|RˆMYCÛTßÁûÿbÔUHğp)À€ÈSÕsÀ qÓi±–`Z5vtå‰¸*áOO\nñ(…£İÖëFà¦Ø58Ã!ax@€{^P¾Õ½¸?«°Àeh}\\³j^2ò„L½,6Á.ØN	K…%±•ß–u”„ipÈÈ!?²lŠ‘† -5íw½†K\"VÈØ\\ÃIs¢Ï2!ßğ\$4º5v\n’àèògrÃòNÖå}÷£;İı­Âú‡‚æW%D(pWaë\0¡v'à±6ú®Vê«ÔÆ¿0WÀñ„E4ÒEUlÂ8ÇLDî„¶EÂ<kOŠñHÉßDUÚ	`vS·¬L“Ã!DTMbnWV™ÁCd‡Š)ZeèŸ€¸ö:¾2Çd8š¦KåŞ„ş4®-GübÍ¾wQWæ30\rüf\0Ê,µ`Qhl±ÖÙ0ËPõà0h@\\Ôr·8×ÇT–ğŒâ›œÂ1ğ`¤&ÿŒÌw–Xï>ÈF?‘—|P‘*ñM¤qZÑ¯Œ¬}†Ë0k`‰œ#ÀÕ«cò’'[ÇÖ±Ë|sÉIJ˜î\rŞã¬û¿<OaÆ¼@ÔW‘¬u°TÆÆ:ÑóE^ª²ƒ¾„²!kŠĞÿ„Îa\$È>5ò–u_äâKcCQ¿r-ÑŠä'\rÈiCìœŸ§Ù@8ÎS„PSÁ_XglÒ%£	Án1r.<…w_aÉºÄ³èGhÒ4\næW×Z“ïaBn,\\\0¬±DU\nbbZ'ŠÒá72ºÍrÛÂ¢®–}¿Y>/Àw\\YĞ`^7J«jŒS‡¢•¯ğ¨S.À’o%æJg\0GD,¼Æé>7 ¹’Rî„ˆ¹0á¹¯Æ›ø3¼ß6ø%i\0Sª^Lœ·AÔØ\riòäO<º™Àa phv[¯{œ¥‡\0éE«^xóÜ¼g–YzWÎyGa»ç‹:(”>C½€öÖe\0ãÖÚ])ô3yts_a€7ç+áæ†BúœC˜eT·Şf‚oÅP€Û¤Õ2E·C¾ÚvÇ>Ùwöl–zÛ*pêY²ıö±q°™öØšQâp\nv[|qõÒ¨E[ÑXi€ó¢ì®=²z(	ÈMÛn]7F\r§©Cs4|-} ’˜Ä¿(NU£?,À¥Ú…ı°†âØºq	¸âp†q~ü¬ÿ ¦ê©F–Â% 88·×é¦‡¢\$×Ş°—[¼±µrÄo!3ãı(†°†—g†Æô×¥pJ!éÁ´qÚZ°v?Ñøc­ıÑL£7£Ğ6èü\$‡mö’Öq§í8l!Ãù5­Cš;Q,ÔdŞsFõ-O˜§fÃˆø\$äğ„6Í%U¨C¸´f\"‚çe(jº\rMtÇFœƒèëR÷x;n¦B\$÷¹SSôx'¢õGöşé™ŠMÓ	˜Ë4Í¬'kš¿~±×#9e´³Yº¢Ö~¢ìë­ˆ;fŞ+Îj¼K„9p¨ÉÔM†'XŒ/rt²\0Õ\\ÍJ%Q¨İè·R‡\rĞ²O3¤|‹å¯šù×ÂÏ±³4˜İxF–×ğµs5EÈÔ;Ô’WR’ÒJX›Ê¶—Jì\$şÁwzOöÏ&ÇµÁÄzkS×\nœ\nNUPŒâ°.ö»0À”…bdk‚ŸPåÌÚ	G6Ö+BÜz‡1ÎhQ>sHv³ÃÂÄQÙ EØp‰İMä€)›Ø\nŠ\\ŒÑÜPzÄèí.sÛÍÂ gÅá)a~ÖÆÈ¥İ!(!Gìhr[²*ª„£ªîÕ¢…`”˜~Í\"!âO’¿‰5¹G3Å*qkgB—,\$öãÛ**1€c.»n	8¨¥\$d ´±VSne‹MiZ¶íÅ7Å¾g¶Aù5Üˆ½‚Ú\nú`¶,‰2ºÇa¦Ò¯ÿömMkÊ»´ßÉ¯ğ²/-İ6µ@?#`ˆØ)ãÔ€Šha©Â†ñŠ†á)VcÆ]Ò_= Rz\\ïVR§µ=¾Ø·³(-ãotõ\$Ü¥È\n÷¢‰dSm³yµÚfÓ©ÙN\rùm(t;DÍÁÿp¸2¤İ¶²ÃZRl)Ğ9MÌ›À,/“YixªÑkÑ)’.¤2@S^úöuÚådŠ6¤!Ë>VB’à x<•¸Kt06ƒ‰ò@ÈŒ\nG‚AáP°(ûªNbD•ĞK\n•\"µäcN¬´\rÄƒ.põ€¤'2L•‡d…êŸ²µÑß\\Ly§A=	õÄDŠƒm3Ÿ%Ä@Œ™±Ùˆ¡¥Á8åqbSP\"âŞ¢™Æ®/ÏDzëC&»OûÇ\0007f€ÂD^1ÅXº/ãƒ,\n„÷vçWx%f)ŒÎ' àDdQ@™„I(Ò‹7Y¾Â|ÉİºAÿQ±¸D«—Ú e 8×‡7k)_ ñ@\"\"½¼%à}¸	¡(Ìë11Ø§\rõ¡Êãeò†á?-ÉµH&ëÍäõé\rLÛêâ€'»eÛ®0ÔT×]ÍÔC!ÀemNzì	UzöñÀÉˆ‰¢S“Üœaf¶7˜Mê^CŠD£õÂ(_ïìÃœãâ#\"ídr5¦9±Ùõ81‰Öhf¨È­áa_—Ã—tZX\0èU¼­†{2nn]¾ ;FRû²!Š}>séƒHiÎy#³´…?\"Å¤¥çíÀ>{°®Î/?7îF®òY¯°úª?Aj’Á.†Uœ!5`Â‡HÀæ\$r\0î'\n¾\":.ŒûdÔ‚Ù™ÆªíqÙRÕ­ohõİ>êŸÌ{ç×1‚İ+ä>èËÉ·t†Íkğ%-Dì=9Ê}ÄC@ã8cm’Hr°ï ÁWÀnÊ \0Ä<(ÂRR«8¾ú´YVàÅ`ëppÜ.Uƒe_`®…°¹^¦õìµ›n^ç_ÅR|ßrÎ…p‰7/!M5±ìÅ|…×À\nû&¢Fù±VVz‚‡O­AÖ~Ñˆ|Æ›¶Ğ4NÈ’¿¬Õ”ò¸ğ”g¿yh-¿\nN\"r\"³ôÕGcôsª‘©€D' XoÙ§¥ø‘O„{¥{Y{¯ÆEø=TŠeìZ‘¸ºú•î{\";•HÛÑXz¤t±ğwê*-ºÕŞõU¨çè§wú-ş¤\"›¦<A^¿OºÍT ¶]ƒD?:—şùåû©å…íæ<‘‚p„qõ[¿‰È,)©&`Û{xKIÂI`º`Îcş°0ƒ±ùªDÇy8ö‡ÉqC–­YëCFõ˜çJÍÙnkã[¹8÷É¢ñ:\n^ÛÖ«ÄTØ!X*Mú<”5`\0¯É6Aò2oĞP.µé£aøAH¨¶#x[·—†€â–ïË 'o@¿æO0^äê¨óh|ŞP=+Í)ºd[©ÇÈøX-ôWÂ!Ÿ…ÓèÃ†”/:\"‰0k#XÇ<ôâ°ôhƒCG‰İ @Fƒ(éŒk†ö‘¹l¢&H½F0OSz…ÅwæQ—ı3İÅÙz|+ˆ\r9b½TÅ}'Ü¬wA´\r°nFù‹©”Ñ!Èg0Šlp›lÑ1û+ø|¤h‘kz—Ôi&÷ªuëD±{KÖî\\¾ ¢\$t(¶;èÒäÃ¬şªHır|Bw§D3[Mâ!:(İ{ŒZ®å(|-ÓHy0ê^“'×½…}ï*£üÒöNK…¯«‘Š5KU›²ájMå\"…Âwá–]%üû–{1qÙÈz †Ÿ)]ÑÅ®[k˜\0O4ßıÒìû“UFÀ\0ócâ“œmZEGt‘sDQZã)n;7<’qhlXx§IÆÂ^ÌVîå&†Í·ÑC–`,É‘%£¡1\"@1Ç|Í)—R¥kßşVÏê}S,Ä#!ÉÍGµôá]ı¤ExåİıYTüı<%ÿQÑ¿Û@Úíö…mô¤¶Jcææ™B£‹B iœ”âGñÇf2 Š˜¨cDÇänÕ§§=Jü€I_¶ûğ‚›šî'ŸÌïóiA &,™Ğ{ËùcÃÚ4ºÇoV%„d¡2ıx€e»…‘#s_UÓHåÕ‰W!  =Û·ÏOú<(y\0€.À€G¹'Ï\r‰ğ‰57äpVòº¶(æ¿Ã¾:îç}ôRRHHy[Òÿ	´²¿ı 1åÂøO\")ññL¦lÀñ1ÂÿûíÇŞ‰«û¡Š+<~™	\0¿Âçsø¯ë?ĞB@¯ô€dÿãıäÍ?nÿ‰~Á&LĞ„­ ?ğ«ÿ@:@;ıÈy¾òğQèº>È‰ãÓfü«ù:\0¼tæ+jşszéK,b^áp·ÀıHXÅ?†PÀ\\Dè?v\"£îËü…\"¢&° ?­÷¯»‡tş›`áV?«\0“úäJ„wC1Oğ„“#êÆƒ*	ûş@Ì¿é\0ÃşÁÆ‡‹û¡/#8\"¢OÅ\"¥\0€ã¡ø6NcìÃ¤[ıp@Cóh\0{\0	¾pDOşÀFt£ÈH/!h@æÿL°;À@ÿì¦wÁôIÔ~CëË€Â¸)îE¡©4+¼¯°)”§áEbç?]«d¤í‘\$ä<¤é‡Ì`o¸¾Ò£îï?}°8Æb¾Ø¸/°Jª§Ùo#ò¼ÚIV,Ac¤´3íXa äÈoîªxiËõ£ğ\"æ¤ŒCUÁª‚D°kˆYÈŠé}©\n\r\0,GÆ\0Ê|q»¯ ‚.ÅŠ€ÆÀNqÄpN†Ğ”’jBO\$|Cõp}ŸÆÂƒ4`±ğÂÀ\\*4ÖĞbA¤àó+æD_ôòÀƒÄ™X¡\$Œ‚·‹„@œ¢6\n\0\$…~Ë£æ\0À®Jbİ…¡œÂ U…p”XõiD\"üÛ…ç lgÑt'£ş‘ ç+xÂ<¨ÓNŞ51eà’Â0`ò¿ñB8qŞ\"O-â€Š	C!¦ÒšØmÉµƒŞÚŞ*¸¸f@#6…ZĞ›9 ¤”ZRàÇ°ê¸ÅÀã	HZL€ eò½¢÷î9Â9œÀ T n€Î?xX\$î”0“´%\0002€\nÁy„!šeà:\$ÈQssAµnxKÂçl1' €Nz!p¥À¬.á¹†êcép¾“¤1@‹…)mÍ:@PÂ\0á1\nä(CRä5D(¼Š”PÌ1#	İd7’+\n‚£Buø‘haM	aî\0”>¸1W¨ı¡\0ağ˜¾4 sÒ-×‚'‘jp«‹å\nJmQ¨ş‰È) ");}elseif($_GET["file"]=="jush.js"){header("Content-Type: text/javascript; charset=utf-8");echo
lzw_decompress("v0œF£©ÌĞ==˜ÎFS	ĞÊ_6MÆ³˜èèr:™E‡CI´Êo:C„”Xc‚\ræØ„J(:=ŸE†¦a28¡xğ¸?Ä'ƒi°SANN‘ùğxs…NBáÌVl0›ŒçS	œËUl(D|Ò„çÊP¦À>šE†ã©¶yHchäÂ-3Eb“å ¸b½ßpEÁpÿ9.Š˜Ì~\n?Kb±iw|È`Ç÷d.¼x8EN¦ã!”Í2™‡3©ˆá\r‡ÑYÌèy6GFmY8o7\n\r³0²<d4˜E'¸\n#™\ròˆñ¸è.…C!Ä^tè(õÍbqHïÔ.…›¢sÿƒ2™N‚qÙ¤Ì9î‹¦÷À#{‡cëŞåµÁì3nÓ¸2»Ár¼:<ƒ+Ì9ˆCÈ¨®‰Ã\n<ô\r`Èö/bè\\š È!HØ2SÚ™F#8ĞˆÇIˆ78ÃK‘«*Úº!ÃÀèé‘ˆæ+¨¾:+¯›ù&2|¢:ã¢9ÊÁÚ:­ĞA,IñÌv4Ç¢ûê†Œ£˜P-«\nÒ¸¯¨ØË%>(à¬c(P‹ƒ¸74c8XĞï`X…âó”:\r£ä¨3Š ÙKIAHH…Èsë\"NÒ8RÅ0HY5GƒD¹W(®Šã¹3¬Œ¯Ut¢Œê  PŞ9MˆÂùVdû?Œ4\rCPª…bØ¼2*bà3®T`Üön¨VM•sb ˆ0]pGµ%n\\£EÏ]ğ¢8ß‹íhÆ7µ¡E`Ö@PIí¥jV½öTöíz‰\rC+Œ„¸R8\ró\0a‰RØ¾7ŒÃ0æı¸½÷l_¶2dYAxPZAÛ°Š@y°AğR…ôT Èo¨ä^CK~c˜óôéâŠ°{}c¸øèãZ.›…~†!Ğ`ö¿Á@C«.‡‚Ş’.‡¹ô¨é¹¤ıyô¡\nòlöé9wt\\C\$pÕ¨pÉÙ8æ/Áåª¤eyn_˜³§šæãàHç!fwZôõ%hö…°c5~[ÃH{\$»î\nµ»\r!öô4…¡nÄÿìn6ÍŠ…cHºéçÛJ.6ƒ|`Ó›÷;.“Ş°[—‹ãùpˆÊà¡ÀW›İªİô>ùı\\÷Öî’hWªôZ¾³­ÌOÔÊ7PúöÌxA¾pUWñ)«µ”€ç¹!ˆ/‡pÒiÓ[ÁÀ…´³~‹Xà\nRàôù³â\$Á8?BEÕy!cô†PÚCá¢5.\nH±]=«y*\$Âé–s©ĞÀtš`¡«5¦¼7a¬\r\0è5ÇjÌÜ-gÌÀû˜ºŞ\0õÍ¤#“•êoAĞÃĞî‚\"p´;£‚\nH<•¹ºğÑm!¡Œƒ² ¼dÃ™—K´>+dî ø=¡p)ªpP	#À|«<)˜70“Û¬-»ãÀ—á(ek‡ş9HéÊEè9†€¶Ìô’œ.ƒ N¬ä”’ÄJ‡ hL>e<Û¿¼Cš`K´éxVA¿˜ öaPĞA9W”I‚y‹4Wjçp…W«¥ÕÁd²ERĞ2Ëip#)ÑÌÂØÚİCD?€rºu°‰ªxs—³|Ï¸œAX+?§şl‘Â<H &ĞÖğîñĞT#¤|Ğ Q£b ¶-\$°}Ah:t0íPöD¨9!9Sm‚ÂHûi\ro}ˆ¿ˆÆªP_EÑa¿æx­fš¸u’{šÓ²vàâ<)Â/#ÑQC*Üª\0ºrNirêÒtÚGNo¤w>’ØÄÀµMÔÓ¼‡ò DJ†¹Cv`ò`N÷a@]¸(˜U ó¦ıS5{’È=ïØ·‰9N´‰Óç8z™3„^<‡»	ë¤Ñ	 ğX¢c¥\n=@ü¿s™3&‰êš Šd¥ù˜»Aj%\rÀy\\{<#áš	Uš—gÏR`‚¤^Á›K4lå¿!÷t°¼´{…\0ÜW«&ƒ˜|-àƒé¢U¬À/7yU°ÊCº¿ÎĞXªÏR¡6uåH†¤åV‰u|I V§Õ\nq<é¼‡*p÷)ó¾Óüÿ©&N‚øq¡¼/RÙ„\nV	¸8©²•‡úÃáÀ3‡<;†©ÔÁÄø}_¬“ƒŠph\rø ¥ ÓŠpt¢9#%<¨¾2iàd3æR–s¹\náÛøkOfÈÇÓä«9pA•\nÊä¸9ƒ ·‚ú¼ I”ùYÌÈòşC¬c,U„²”2æ^Ì\0í0\$öNÀ®qsJÎ+d¶*ƒ@1:uÂ¶†ë´Ï‡ôúíkÎ†©!Ó4;é@zšZ‘Ç&¤Ìd\n3\$• Åßİ C¨]¦¤ú£QÊİBVwpª.KÌ\\Î¬ÔŒ\$9Ài<2Zp:a`UÔ´ÁïÖS¨3¤İ|T!¼&PéÊö,c=´Ä0Ó=ˆÇËN‹ÖÛd’ë›­6nÏZyiTTJ¶¨wÚûeSîuÈ'šnÅmí‹¸I¨n\r;—İ”Ÿ³Â„İ*)A…ãiŒƒ™1šyQí\rÛ_8?âÕ¾®7¥6–ğÚÁËl1øÇ½‚şùß{ò™½¿±°‚à‡cƒ²†­vrŠãû»{\\ğ®Î.—,Û¼»ßeêvù˜kàÛ›eó~L÷^“ç7†…À®\n@.síÿçİ8t–}É˜8¦C‚-ä›Ñ»ô-ı¸4ßIédO{sÕ»8ä–ä[Ëµòf·;}QÄÁ³¹s^İ¹×QÚ2[ª(@Ä\nL\n)À†‚(Aòağ\" ç˜	Á&„PøÂ@O\nå¸«0†(M&è‚}š'š! …0Š{6ñŞ}ûº•k÷Ê˜@;ğpx6ázgÖ|+œ‚ÌòDòîâ¾+‹øÏ¤ÊyJŠßL#}Îó¬~ûü*/}ñïÍÈ4·Áä|•Awıûó<À„èwOñ¬èäX\0Â€ÄâƒÎÕç~ü®ğş\rŞÚæŞİ­ÜşÎZåì¨Ä*¢Ù\nğÏ§\0vä0 ïèäïş*Íòù/—hDâ?Oú\rnêéğBïPFøoêíÏñ0\\ÿ`ç0fú°kï„ï°rùOïHğp€ÿğhıîxïpqÏÒÖPá‡T b´‰ ¶åOP”Ä¯õ8æ¢æÍÑP ÚOıoÆ.Îí0§Î‡\0Æ\rÀÍ	°ªúîêPE°Kì®•Í™\rP)\röâoÄğTşèv ê\r‚DÜ¯ı°ŸoâÿíğüMöA(XhC‚L&º›\"h\r,ÒNÂ^qKkb ¶Ø\"‚‘	ğÇ}qy\"ïÀRÍ`ú°Ä\0”°¶Ğ²º†n›+”´®\rn¡„ò³qH«HLñ®µ\0V›%ŠŸF: Ø‚½Œ\$\rñ¬ƒ‚fé¬¶Ñ˜jBçm©Qm£G\\è•±˜¦„nk«’%\"V½±d¬Âk ‚@ä òª€ç!2+6·Ò%‹ƒ §~”ÈÍÄ%ë r.ÌR[È 2?\"Ì¹#\0¶Ô€w\$ÂU%±#!%²)\$ò	\$LÈmA-W¬È{@Ü·¬ß#Ò_&ìÔxÒÜò]\$S'\0ä\r„ò½ã’gÉ@mè¹0¡`dÍfº`G&L\0È':xƒjxç“*Ğ¾ìDÈLÇä²êè´Ä¶±º™ÅŞ(±ÀqÅ‹°Â,&ÔÜïlÀNt* \n ¤	 †%f(‡£¼ÏĞ¾µkZ˜	„¶ˆ%i®n\".ëÄ»Çæ°®Æ~\0æU@Ä¤d€¾4Äö'r¤\rn#`äì2HÁ ¶ÍgŠ6ë&œ£v ¶ºŒ×'¢\rr€‚S^”\$åš@ÀÌXf>Îƒk6Ãr7`\\	5ˆV‹'W5à¶\rdTb@Eî£2`P( B'ã€¶€º0 ¶/àôwâ‘sÚ³Şã&r.SVsÑ”9ÉJJòx&—8³´€»ÓvÀÔ!`z4\$k´\0ãĞxš7pIó¤ Ó©Aé9µ;´ª€Î\rÅ~¯è¯4¯ó>~'‡\nPŒŒs0PâíQA+/7`WOåéG1‘Fpæš´\n|í\0P¹G‹GtƒI\"TíiG O@°½FÔV~Gè”2Ø\$»éª%¸«96´,7LĞÖæÑLSoL’hÍóP5Ê¼æĞ£\0¤ Î£PÀÔ\râ\$=Ğ%ƒnUjXUÄÜÈkÜÏ‹àN\0æ«ç”\rÀ¾)F€*h“@ök B³”Ú5\$˜«56Lbs|Mo8+8\"õ:ÍóG4³ON“S5‹Î#j²\"ó³Nn§®cçJtå½T¢%(DŸU“SÕ]MÕj\$TK`’5„öo@ú‰è²ÈÌÍ§rYSNR1ERÖ\rÀ¶³‘¾E²ğXrôNJ†7Õ“b‹“gTUx®M5«*î0rÕ:3¦³	ô™	•2iœš1QŠøµk¼Få¼Ğ0•YZstÍe¼½•¦c\n:oHÊFE£ xu¢Í#„ú4ãS#	 	\$¨t?õ¦E(p•å(êR\"|eB X¦ƒê8	4Å>\r/´<í\0E,^çD.€ËE{5 ÛaµÜ†*äĞ\rÑàZ‰»gç|ÔÖ~Ö\r:mocÔÑ9õ¨ÍJøv*ŒôÃB´Ò7rTÕ&Ğ­nlH¶ÆPVÊ6ÔÇmDwÈ)m ö\rµñCV¨wãú \$ùuŸSô°ÓwS`AD€èL‡S6qˆk³Š)Jkl'L£hB9h–Œ Jimn<\0Ğ  ‚<æ·\0¾[…¼:\0ìK(¬”~ª•˜Ïs\0÷KÌ’ö¯Y'ÊˆgÙaçÄO‡¦¸‚´Ø(¶—]v‘:¦&!`íPäàxV^w‚²¶ nºÄ¹àø7\0¾&Œg|B\0(ÒÂÓì*,Á×Ä¾×Â²dº˜7â›¬tÇöz’w¥zŒ\n»E\",\0Ô\"fb¤\$Bã(óh(Í4Õª5b?ÃÎw¦Áq|@Æ˜+‚ëØ€Ş¶×ô¸&ÉŠÛ~Nâ´â•Ì×—øN6<u¦FxWQµÀ^À^¦¿§;P.#/­‚ƒç|WÈƒ8k.ÕÅ/7K/wÈQlÁ8ƒ~QÏˆŠ³\\1Ã\\˜“Ì&\"Ø¦WRï‰Ë/Œ)|¾A5r§µeEƒ@¾Ákµˆ\0OàÍwK&×fØÓ\"'Lm¸Üğl@ùøÛ„PZ³ùã÷7ªÈğÄ\r•#‘oØx†`]ÄbÌ„NzZ@¾0NRè,é†x[P¤¹…øc•²8z“XÈ\r†?ŒóÇÌ?Š9÷2Ãx‡}€LÁÌF'LPğyzÃ°\\Æ™ÇŒTÃÌ Å¤¼¬iNÇæÇ€ÂÇ×ÒTx%…xau’cw’¸#l,‡Œ\"àPˆ£Ûb”*¶›˜g†#ZudÍè,5\$¢D¤ä3]‡Ø›?ˆh~«0\n½yæN7Æb˜´Íùşz‹\0Şa5q•˜Ìk·pÃ÷vŒ“’±Q™µù,D–[œ¹A\\EyK†yP#U¡¹Zk¹óˆ&)˜EŠ9qš¸ÜèÀî¿\"ª7¹³’˜!£€Ú[™ÍQ™ĞMdÛ”šuQšJ#\$oŒ¹]¥jÛ¥¹g‰ÀO¦\n¦XDèÍ6Éê£¢Øe¹·§¶XÇZ£ø§¤:¢åE©:O©šU©ÙbÂz]‘7s–ú«›ØDÃìcƒ£0¹`Â?¢–\\ÖS{İyõ¯Èé¯S‰ihÑzÅEiçij&‘®×«e'¼kº­“X’y f6V-Zë„WewÅŠ;G„\$á´×åˆ{S´¸K—ÎÊ7	³1nº—>@ÌizúÃzÿw«9 ú‰›{ x;Ÿºš\0˜é¸Ú\nIû…¹šíyk‹‰[¥©7{·Ş»8-~—ÄÓwñ,[lÈŒÄ@Ï·•’ VÔ˜+Šá¹Ó‹˜ŠØ¿û‰½j½Ûc¸Ø¤Íø©\\qÇŠûˆ¹Y¾¼¾ì'¸Æ¤z½£Y»´˜İ»«»Ëœœ™?ašA:‹QÙ­ãûæ(¥â} ó‡\n¡‡yî#Sóy\0Ï[‡ì?àÎÏÈ/¡š—¡Ù]¼ªÁÕ«™M£y£{Ë£Ü9¬¼=PÚÏ«¹O†šLs\\sWDÁÀØ»¾Ë±‡|7œñjN-ˆE Ë•+‡`uÆ¼¡\rM}×å~¿»ØI™øÕ~i¦Ú´š±|ç—lvÃù}ÏY€ÄL1˜l>\r¹ŠÅÅúñ±9–à,o¢YŸĞ9£}‰¢™ÇÕSgg¿Œ¬…»éŠ¼ÊèóË:…Ëu)ËÜÀE¼ÅÌ€CÌÀøR%»·ë~|Ù~ÌwÍë³Î0]Î|êÇ\\îÃyÏ™ŒÃy˜\\öÂØ¬7Ğ¹•ĞìeÑ,m™Ìuš¥Ò7Öı(T],wñˆÎ¸fU=¯…š‚TRW6–<ÖëÒKÖ½¸š¾gÚ;ô³Ë¦||1Æ\0Qy®\"9ùvb\$5·mwö²Î†—o•è\r\0xb€kH˜é|µÉš ÀZ\rëh»ÀWÊœ\\¦«Ô±±Ôö.…ó3Uö\rË½Ø˜\r½Çá>?2)—á©Ÿâ/â=âŞ5Ëş0@Æ…H×~<ŸĞ½âx·ˆŞ_Šş/Ë¾3æ~I+~l~H‰YäÉ{åıáŞYâ^]À^aãeè^h•ë^r+>C÷¹ÕbB¯,¶û´«2/LÊè²Á ¼Rı#mµRKI€Kˆ'í”•EöW­1ï]Fµz´_]óTŞÑ%4Ì”\0ÚV=í4á;\$T’ Çæ{Ÿ¯?æ ÷ï¬¼Ôó3Àùn\r¦z ¼ûX?c§p—\n?ú#ØĞaîd¡î¤ºµÓX´\nÕÇ:zàÌ-‚^Xì! Ò`ø:\0äÇöy,DlãÕJ`û¢A)hÕUõéµúµõÿê+‘ç¼çñËè5+êüÃæç~_É‰ñãş¿¨–¹+<¹b]<m5~'—óôŸ—ÿ]¹ù')ôŞ¬„šÜº/ú™½Pœ¾rèœ4Óoõ{‹ô_˜ng¿ HFÈpBsÜHû1)îbó•Şbñ§Ê?•í†¼\"[ÖC<ıU~<0¶Úyã€:õG @}è„¬zØïŞºòw)}¡ú[ê–ôçì<8š&·X\"`İB­Ww­µ{Åkù”U²¿½¢€.ûãä§E;À=ÏpQÉ¢³óR)t\0;ø¡Ô¼ÒÎ*­†J›C^ ¤d“ë,ı+d-¨€~¸*¿ğxpn‚œ@û¥Añ?ÎQh{ä„³'A5öP{dX¼`ßH+”‹êsSªÅkX/’”E(3=¨!00˜4¦’\rjÅ‚ĞZaÛôÁÁ>Œmú­İ4¡»¾À?og3xÆ•úJW\$°EQÀ’è^&ìÉ\nQE©•ßh¯Òj€è•ÃqC„NüÆ ,yáêH™›ÌÎ²\$'@\nğ¶ú;\0\\]÷Ï›Ğ²(é\n6arÇ©Àu¡Pä/ò;P¼#q1ÀõË\n£PB.à6©õ°„ğ`\n×FÙ°ˆÍ’W»»ËÂş¦ 3dbZUºÄÖœï±=¨ø×ğ›xØaö@=®‚ƒfËÀÍZ¦³;B‘kè¬€À–ïÅëmJîN™gÌ^¢öİpér²¹äÙ²¯(Ilc‡¢ƒúø¯p*ö‡ŒAŸÒOá«UÂ7\\D<Tö§Ôf+ THÄËÏ `RÇô‚ÁZq’[`of\\ŸˆÂ\"€Ï€xÒ|E‰Ñf€¢Æ²áºÅ°P/‡S\"²_Î8Å-CöFô]\"jãh®ØFù29Àé!EùÓåìb[‹âóÑøEö*ĞêŒŒMìxÂ\0Œ`9ŒDU_»t½£¹Ñq¼^ÄÍ(ÏÅÕñ•‹j!˜ÍÆtX®'›ŠEì_Ø»¥MÆQd^bÁ³|€ò,è{4\\Mò°X°Ffù-¬kN`7,¦ùàBJG5À&ã*1L‰Ì4	#£–-ƒ®üÏÇ`'\n£L?\0)Å|Àr	XŒ˜‘|”çe\nJ9@Ê¬¶€È¥À6qÄX\"ÉqE¦	PmÑÂ¢N»ˆÒ–7¤}	ø¡<I\nªAÍŒj¢£uø÷ÈL+FöÜ'”£CZÈd&Rn›cIÉÅlò\$„ğ€»\"‰)|7Ë4hCvcsÉÅ}Âs‘ª”G0~#fÀèeBğ°¥í.Š’ràO!<]/dñƒ‘[A\$ ©)šJP’±¾\0Y%F`&B÷´—ÂvM•II P€*7ÀäÖ2‡Ô&lüXo€.\0ªKZ”Bq&<Jáp	”•eÿi;\r‡¡0“PBÅÙH…ÒM²•ÀL˜üÄ°=ÉTŞòX„šc1&y-I¨6fN’|¨¤¯&yRÉn0r¨	ä—%VÈÀêÊRKR–dĞ€Hà €´A ü¤Y\nÜè<JÄº’ƒ“L±œúù'~V \"•œœ¥l!dÊè'€`”Šqå´ù«>Iit3:LÉ²\\s%ĞÍª¡E@HC¶˜¡ì”\nf\"¤‡‚@ 1İ1 l¨nÍ†€úª¼îç/X\\‰DK à^-²nÏ|À\"\n‰ƒ8@œ{à)P æ(P(äòs f y0óÀM˜ @°\0&bÊQX¦]3	™8‰®–<Ãæ#11<Ì.bÓ˜ÔÃf*p'<ó4šÅÏ)1 \0®À)™ÚnÀ~cÈT S˜ ætI11š(\0–P,€‚d\"=üĞ@½6¹²\0‚Àw\\ÕfzY LÖn(›œİO}5	ºÍàW=„Òæİ2Y“Í–e@OlÜ€¹7I»Nò“mX\0”ù‹N:n‰ãïB˜ò\0¢kÀ|¨æ,p>Nxnœxh¦í5éÎ˜	ÀGšd'€Å3é‰MØS\$HÀ©1i‰NÔ0ó¸œİ€8¹–MvÓÄ\0P€\\©ĞNHó\0|9ç¦@\0!d€H§NÉ¥L”\nSØÔØ€š*MQu@&£7i‹8ò“–œÀ¦)1\0#Ljró3\\àç9HKÎŞdÓ?hgŸ:	îOzvsà”ø§ÈÉóO¢|\0F4ßçÕ>ùöÏ¾pSÕ|õç³<*LBw)ä <è¨?9ìĞ@	3ç¥è+7ŠÏ²esîœÏ\0@™ÓĞ‚y”\$€\n(#BĞ'ĞR‚Ó« Åè5CiğĞ 4: çCÚ Ğ¾}4D¡”ã(i<jÌPôQ›üÓ\0AD™ñÏf€´%¡ı¨‡>¹»L”õÓ4ŠT€‰@•I°OáúX¹ÀX ×(”&l–')}\$†eI±fÆN_% Ğ4àÆi²\\À±UhğCÒ=D§u“œóàË'@‘àv¢Ò8dBĞ-%(“TŸ%Ò7´óã–¨Ôf\n„X\0mˆ@CÎĞ0ÑòI´±\rÉ½€w<úQ£õhS0‘9@òÚI,t´')Ë¦\0J7°\r‚—Ë\0!†‡Æ·W1\0åôù~¶_ÔÆ\rİ2\nfÜŠû§œ@QKĞ9\r†›“\rXi{/¹~ª‚£§¥İ2Z_ò™ôÑúùÂ2'*o’œ¬	UØ³©ê\0¡{Óe(\$§¸œiáM£4T4Ì4í§}6)âº„ËômV}Aê3Q\0Óìl”Ó/=@QZ…:„kµNƒÀÀ|Q­„&¢Õ4J³†»R*iSPÔ¨5„â‡\n—„®t@æœ€Ô_õ)‡•QI€MXoªŞ äk19B7à=ĞÈäÇ\0É·Ì†l€|ŸØ¦¨[aaÓ.§Ô¨°\n\0½49§Î’v@Gš ´€PO'ŒZHÃX'VZ@T²•n›ÜÙgß7ğ>âl3c™DÙæÓXZÄÏfj‡Y«í_ËmX)Ê€¢zG¦¡ÅÀ‚à\"P2|\0NàjÏX™†ìƒ‰{º\0ç0dä¢Tl´ \nq;Ùß:bSŸ¡äÊhfy¨Õø)ŠQ+jSCQàä²˜ySÉÕ¸§0ôH–qà`	Ú`ÒF¹„l®pT+ yçÒrºjZÕKªc«¡é¹WmAÖ:¹Çyè5ß\0P&úšúÂzWÇÉZ™)D¢	TùvD«V¸Õ3VºõÔF§È­°RjÖ­û¨p³v®5„)šŠÑ'X&@.°åC@ç`›pTª°lSw_ªâ	¢Á#ßí:!/Ô5¢rrâĞr¿ğ;ºF»&«M@À\\C\0\"‹\$Øÿ(TÛX+ŠşÈ\$t+Ór¬Š84Xf¸ÖI•’ì‡dë#&¨€cIPëÓZÒõÓälÁÌ±(l¬ÁZùƒÖÈÌ6^¦è‚œ3•æ¾|¯•sÅ\\Ô=€‚EàrçÁœŠ¿3­¯©w+¬(±,Œ Ğc§ÄÀ‹Ğ^ª|Ú:`†h[ÛUah÷t¥¤ZÔÀËÔ¶ËO;¡Şqyˆvì\\êùA^°¥„ñx!ıj2VÕ¤Õ´E¢ªd´0ŒØ±õÖ°4H«²±°YHz •¶0+Ø¿Rjô´ô‰f_k¢µ…¥AJÁjÌà[´©,U\\jXXó=´©°ZDw5uË¤‚ÓÕŸnù	%'’£}–&¾p&´ )¾Ò¬q´XÔÖ\0+_9İC)õIÛŠ)Rı¬ì§‡`Äµÿ¦@ê†/!+UAfâö–áÃ\0RĞ=ÓAóš´%àr3{”\0`%z0æ®\$ê>Ñ¸‡•=¦hœ¬]/û6š…–§4\0i•_2¶U¶«•eªè¦;:J±NuV|ë@ø	¨€üGºhU§=Qh'Œ(T>,şn‹?#Êİts¥ıfç©=cĞVvu`¡U'X)ÖMÒé÷QºpÕp7×¤!a´˜J¨lÙ0@ZFçE•¨=ClJd­›áóÓíuAJtÈªp˜0’¦W™¼UwŠØëÆ‚ñ÷‘Fa\nišİ»X‚¢J*»àÙo*6Š†ÚèkÕ8ıN®÷[* /Ñu¯MCUMaJ”Ş²¶V!¶½ìUŸ!+ÛÅ¬´p—xhæà<@B‚íâÀ½] ;€ë  íu”®­Ğ_2RñL¸ÅÌ:Çßˆ	«4½.f1ë@b¬%\0ÇÃä!{ø=MÛ¿°|Š¢`x”	\nÑ‚oú!p)_ıtãÈ¾ûŒİ÷#ˆpa¥¿ı±i\\˜ï3D–¸À.ˆ¶ñ¶•Y÷2ÀxÅF„gÑë„¹8'(Ñ0BJ¼É@b£Z£n	p\"Ee9„ »‚ÁJç0X3ô«„b¸\r; ÅS¢1[yÈ=(73À†	Ã‘ƒ‘2œ™”¸*ÀÈl0‚!V¥lr‰Z@<ˆ´‚ŸT¸ÙKmŒáXiF\nUÚ?fTˆ\$i8GS)L\$¬8B±iD!\\B#<4aT–·»+ˆ@®-ü7\\¨Ğx6Âp°¡ƒ¼?\r” N/é»°¨%L+`¸hÀtÂÌ<W‡>á•{¢Í~(@ìü…üáØRä™06ÇP+˜¾{EsÃ¶\$ñ*Ù¼b‹	˜&¦#ğ¥…Ì[XÌ¯˜—Áş&†‰ØŞbùïn‚ÎñS…ÌU•¯¸læ,0G~ç}àƒcUf'dCs<m\r;Ø<Æî‚*4”ÎÜÇ¬Ç~±Ç‰oam4¸]/î0ÄÜ2cÈFxw¦H;Ràâ»qïµ¾&	kXã?AIÆ Æ\">†€´¬xĞ?°÷,PÄôbäiÅ«ñ)c<\\+Ù+ ^n3’Å‘ÔÒä|N'!+PG–N5ìT°ÙÁşBK ‘§!ù1\":¦2bP¤,ä Fy*ÒöNÃ“<a[&Â3ÉÀ²té–‡7ù\$\\Çqß” 2ecInŞTãy•2šc_	@\nuşp Áüx‡ÿ+çXUq·<®A.ØÂKÊ•ÿÊ!2¥?¿8ŠfrË—8ƒ\r8(íôp^±!ë€öÿ×!ŸYÊ=q>´\ràv-Ï€Ù—°¯	Ë1âÆgşf,ïõ[ã«,e'ZX:2\\H¡ó ’ƒøy<€1)[Î±Ò;àD|#ğ©‰H@ÁŠòLSÑ3€¨>;ô’]2X¬vjï.GEßBi+d®%ƒŞÂ,Qr%Ğ¦Â¶*ıŸIÔèà‹5`¦tÑ-És–bª8EÍÛ¾€ƒe\0=ç´2î¿/è¼ùYq9-eZ®„¼1\\ÊçÒ^öU½†`&g WJËY×hK]8W@;Ğp–ô# âªè#BŠynqÄ•œš\$u™ä¦Y¯ç—!á\$§ö)(rX@/+œL8ÉO^‹Ê”p6,¼åÚÑ°wÂ<%MS©S=Z%´‚WèÌ\r…\nHy/¢2+eÚ1¦Eı†É£\\ÊUw	(p\n-°ÃØI¶¨Sî“EŒñZiI@1	˜ô¥`ãÆ\$ñ44‰¥´…8íÓ>\0Âäi·MùŒÓˆ4æƒQ jºY½©yÑp#éxú`Óî™“¦m'é¥ZÚ‚6œ¨zaé»S iÑ&´í¨Ê’Rü>z\nöôÃ{TiÿP:˜Ôöœ—jİZj“TÁt¦Rïù¨@:à‹¨•Ş­5«h”j{\râfÏÚrñ–½Ğ\"‡xœ |¦cxÍ?§ró²àkú¨p†‘Õ.²rÓ>tqĞC¡ªê	k5h¯­a†\nó­U:yòĞó¥ã®xW8èk·â×)3Ú!Ò‹kó^ÔtÒ}ôÒ–-x5ï^¸²B(q@±×Qd]Æ´CrØ\"kw[&ÏÊuís‡ÖW:Éê•N@îÀ×Ód±ô¸‰¹=°•³+Z9©¤NÄôµ±°„³@ã¾móÏë{-%>çH‚Ã¦·‡R0*”7K/<~œ•áŒ,jsÖÒön§P\09.ÖÍµµ‚şSj\nØË74İ±,í\$;EÚÎÕŠ-¶†Çmé\0*È»vÔü7µc;u&vİÖ²¬37íØ¡»y(·ˆtõn;JßÛàA¶ïÚG4¢hfáñù¹R†’@5€)V{[şYÍÅmàb£²©è6û¸1Š‘pÛJİ6ãŠ¸ÍÀîœ;[.ĞÅŠ[r¯–Üb9¤V¹÷0­Ëî´\rwİ€“÷C·—Ïw×à×VT¤ &=×,âhª•zHä€)êè 8¼õE—sI–t<@e+0yÃé¬njçT¤¤ŞÆ®€w©…~’dÁJÿØÏƒù«@û)c‹±+hñª,íûêŠØ«8pµíL KÏÃ:QîA­ñ‘og™õ×1ÄoŸç»?IÊZ.Æ?Á=~”ß¬¥n¹°¼©kF¬!n%/éEŸt0'Ì”€P<Æµ…GÂqPä´“F¦ÎxAøq¿µêÄ×âƒ«vn‹`,ùºcWÀ{á9Kúß‡{|±+s£<é£÷4Z+×¦¹6ÁP…éPL¿ÈÙÇÀ(L=¼Õ®¡—jf¾h‹Û>)½Aïí˜ ıq–ÿpKÌ†¼˜ÕåÒ ü~À6d0€¥ÔY½#y¿}ütOÆî°RıæCSÆ_²ç‡œßğÈ|bHwë¯s…O%UĞğwâpÙÜNòˆœ¤‰ºY]é•ÆíŞÓU\"rMît¦ù»\0jxoW¦DÁƒË[[ÌM± Øy·ÄTÀò•8üÃ@·9˜àh‡“Öâ˜!šùŸÌ‹r`›ïà‹\\/®4Áu{œdÖ8SÇ¡Ásb¹\"ò ¤Á¬ié;™úji¨Ç¿¬kıj}v£iÖ74ß½­JÃä9=Õ—54ğ0'ù?›íÕ(Ş7öqgûøà t	ôŠ_¶âİü[§úízñÓŒ\\wÌ_>sÇÁ“_ÿŞÒg\0¹ç·ú©ŒVœ|\$äpœ¸-½ŞBsğXÜ‡À.ÇÙÈ;ÿ¾3„—¤g²û€PCD¹€êGy1‰‚j\0y=MË;FŸĞm(ÂoD7y³kÇ÷ÁÌbåo”=ç!:’.Ó%C%í¸tß¿‘¹²Xm\$½Ì6&öPÉbjÀëTŞuÑ*ÀTx€\nÀd5¾¼õìÎt^d³(S|²ô×-qËŠøãÑ‘ï«\0©¨Åú(tXYQ!HFî´k‡³÷à·0t«˜’æ4H|Š³oNoûÈN•”%°\\†Çw\"0½ÎBq‹µ\$[ç™ùÂf|q›Îü7~EyÖíî¥Xº¡ç—qø×¨>|ë Ob*Ñ\nÒÅèImßcËEĞ®ôºeÈĞ6eŸ¦üvËŸLÀÉÕnÉ©äKxx~aú›ÇœÀf)9„ËŸ]F¦!¤sòI‰iNÄh~áÓ”©ƒ×R£úÒŞ.ì÷µœì¯GF½ú÷«Œ8¢ï/†zdC•fğ6-“#g|ûÎï½tÛÂŠĞ;¿ŞÖ4™TVô)·kVŞßÓñ/y„ÀC ×ÀƒĞÉ9òÊ07h@œëÜò).Hqã€EİîñN}üšK¯+›ØY€r¹\nb3@ŒØK1 Ö)†lAË§Ş=#œ«HiL®ıÍÊ„5‹o¾A€†ïãÍ“—B>Y‹@\n1Hºà·!+â×È£s¼0èGH~^7ÀÙ€ ĞÃÉQrIô8²Íğ\0ÃŒĞ`¤‡\nw¦=0A›y¨[QÚ8HÊã¢O¡˜üg m¼–ï#Ê®ukHB§ÿ°#°o›uf oİêk íãñ^!ÿñp{À}»š›Ø½4Iv½—ºíÅû?x{¨›äCY¬-åIC×Ğõó»È’>0¤ûl\r¥Ñ\0°Ø|Q×1åÏ5Lö/±öîj¿ù3;›Lï´·^ï{ÆUŞn(}íºÿîÌb½ÍW‘ÈÙ¡Üä+š>æï'¸·‰ğÑ{WsC~qM;PäéR¿vÌ¢×ÆŠº:púàóQšïÕGÀà 7„„a§;Àéá_Ïz‹Üæ)|¿£Á:ğg\0Y‡*Æ/kÄ—\n—Ê>UòÀ0xŸH@ë-=\"0H^U°˜E+Òx+ÿû#Æ;èáª1¯kÅyú’´ƒ£ÍThü:GÛ&ª-Œ!qs‡3^|úÛàxWë-lëƒı!×¸íF°÷X“ôt]ÇîBXY;Q€LÎ‰Ê‹½êé0cIÄojèé„AøQºıŠÆàLşùGGãâˆ%\$(wÒ¹ĞEhÈXKğa¹·ïçÑoúºb€¿5ËÎøúÄ…‹sAÕğât/\r‡İ’`­wÕ7<MP´–*yY¿h>Pî‚r‡Ì=zjW01ÿgùdlşiD/â}^VÉ\"b·À>Ğ”›ŸáàX¼€Rn›Ïİâƒrˆ.0õëüÿôÌ™9@ÌÙĞæ ¥ÜÿÛ®È·Ö;å&³^û2š‚hYXh£(´ÿ¡b ‡\0¦Ø€Á/Ü\0ÊlÆ:0ä÷Ü‚Å?ˆóÃt%¥> À€CG4@Öí­@ËE¯<“ã Àh	OŒê0Kä\0‡@rà[ì\"±¾À)›AÎoX4§z¼ è¹NRºÖÌƒ«`ö¼jä€k¬ÈÀ¦P‡” £]OˆlÀ‚÷ë2\nì³ï*»b½5DnÛö€ï…’ã2òˆ(ş\$ÁÓ<)»Hac:¶Ï‹ƒ/Ë8Ài:ùn6:à0;Î<1úLP\$ Ø£âYÂş‹\$»¡³®Ñ:0›´¢¨µ–ƒğ‡‚jIP¾\n“rL!w”Œ¢û’ŠN\0>~/`4É+\0æ¤Á<‰€^RX°U†6¦„É:\0ö–bNÂèŒ*€.éN¯Ìpxp_¶Ã 8\0XoÂKbè˜–|Él\0Æ–ÜÂö)\0’°P€Áª:<pl¥\n”@»A½SPP°º¬Æš\\»Ò AÔ×03\0006 ºø(à.ÇØÓpv´}Ø…9©z«ıäµ¦À@N\$Å†?5§ã…Ÿ¼i+Av“8`»€y¨Ÿ ¨‚¦\n;Ô ¹ óêV€…¤pß€ú\"Ïïj¤í•E=ÙxÁ0d\$§PèĞV…	xßXñ ëg\\?\0ePaAJ/`ÓpS¤ÁLĞ™Á	¨(PYBqÁÎĞAï!.bÂVsª¡\$ß	|Pf%gzTã£A¥¤ĞkÁ½Ô0l%.Œ¢l¥5I¦É+“8I+¶“’“‘BH¸*©pÂQ\n‘G^B«	rLPUBµq hB¼–ÈénBÂ”œ,bè¤Ù4€ºÁÍY×`…	|#`.Bæ’Ô.­ÎBïÌ\$ğŸ†6!ì*Ğs\$â”#<ˆB%¡€‹€QÌ*ó”e	‰NÒÂˆ•rLĞÚ\0ÎN1!i+\0·ãÑ¤÷\"Œ60bCgaNŞ\rPUCqä/PÑ\$BNIBÁµ,%#£-÷\r´+e˜³h&pÂÂº/d+ğÄÁ·P²C-ÉBÕD;D…C}<BB“”:0¨Ã¸\rPîCPèĞôBiÌ1À•Ár”£Î	à'‚Àìc[Áƒ\r?P•*?ĞæÂ³ü+pñA·,1Q\0ÃL@°qDh.Ğ÷½YˆPùÂÃ¨û¤´`0¢@‡ç¤6Q·b\n\r€Ş•¬0‘Ã\r\$1Á­@²ÃØœ=ÄºBÖ”¨ -€têX°ìBù”C1®“,©+BI”ìÏ´%€ù 	ààÖ³(ÙĞíKT\0ŞĞF@¬/¢7Xá\nDÒØ`€ˆœ`ã[Î•ñ¶p”DÇ¬LÇúDĞ¼Q\0\0îœN`3€^ \n@€°%È	9Áü§ø€„›\0Ø…ó€[ğ ş	³LİÄÏÔMAë¯¤Q“2Q8)”úHWñGDíb€%\np‹	ØSšª …„€(à#¶t–Á‘‹ÚDò’HQq[Éf‘]ƒ\\'(B@€^á(CCvÄúŒV±[Å˜`(ñ^E¡üZc!„7ºÃ‘EÌ*ŒY1mEuìYñ_E¥¤\\`ÿEÊc,[1eÅ×ô]Ñ`Eá°\"ä†¬86 ×ñzÅq¼]±hÅ€xí@†…ÌçOE®™\$O6¥}ÀQq=€›«™!\nÅ<bÑ:µQÜc¢ùOŠ†Æ'„bïÒ\nÜTàÅ(|QqFÅŒR`&E*1ÔRã!L^ÑfÅÙì`Ñ`Å†ñc\0^H!‘“ÆygÑ|Fƒ\rñbFŠ¦€l‘<ÆÌcqeF™äiÑpF¡”hÏ–\0ËÌ]Q¦ÆôhqbÆ¨4O #\$=\$g±®FÏ”kñ¢FŒp.ª<\0å„k`©Æün‘´Æ¦§äO(J ³ä[q¿F»j1»FÖ4¤\\(¼¤’3\\TgÿD-œTÑCA´+ \rà7€ÎíM€€àx¾Á	\0Z	R›\0005†…p\r1ÕE\nìVIÆ(;R…š~[>`3„6˜¤rp”™	Ô% ÀÚ-°ÁĞ–\0ù	dCÃ±ü(9¦ÑøÆA‚x“@2Áş¤!±İ*`\0002Ç²~8SñåŒã	P¡•AÚŠ/ Æ#æ©‰-§8€1€¤nÁÚt€*\0†#O±øƒ0=0	€'\0d €	À’( xàGù  \$ƒÓ\0 Hôà(¦2Š\n²¦3ø€fïînğâ 7â\nÌƒ`7GşF@>H.á5 >?ÿèB>Ç<ãzò\$`¡À>0ĞR¨útY´…ã¶°+ ÂF®àQÎƒƒ äl@>\0ŠÓÌ…1·0”«\$VòÆ´Kò&\0½¤Š@0µ ûHà>4úäo\0006ÆôŸqî¶8»1ÃP=9Æ\n®ğ1°7ê²\0¦D*Ò<€ñ’#H‘\"1|ò#£é!K3Ò=~=nmòHî˜,€ñ#ô…,{I#Út„ÃÉ~)‡ Ö“\r b6òIğ!1gàE¢/ì’r\$ä’äÅ)\0ô„…ó€øÈÒ6”Ì'ß0v3g@†É\\ú@\0Â!¹ ‰3!4ä©HdLùfÃ_è°9rŒ,[ŒxëebĞf ÿHHhpf1~ÈD%,„Áa)”0 Ö¥ã%<ƒN´©øc€>½& K!K0Â-Ø€;øÍH0ª¢‚&—Üà¬›.Á|#´‡°.È@3Éá!Kå\0002\0ó!‰07‰Ê¿ƒeJ9h¿²\\=dğTœò\\\0ßQ™DI«d°\r(II 8‰Dr„Z~(;bİJ8ËÀCŒ¶U!£RH¼\rˆ» 3Iô¦Ä—¡_ãıAÉN#“32™¨æ1ø@äú{¬£@ÿJ+#¨=R}ˆú2à-‰~H.Ê!`:€àÉT—ñ^‚!Kû )„JĞ»(\\…-\"#’¥ÊL0	’tÊƒ)d…/ï„›!ˆr‹”]8¸„Ñ‹ô.SKÒ\0ŒH\\wGà:Ã)„G÷·Ê˜¼®¯Û5+ÈN	‹”æNà\rŒÊJÌËÒÿò¾t¡0&‚(\náa‹Í!I„sá!!d€1¼ğ0‚²ÊDè	2:Ë5¨¬á‰3)°WîµJ%‹½òÁ:0âÅÒË‚²#Z*ŸHë-kç×´æ\rÈR²Ú<-|Z’Ö2§LµÀÖ\0¬ødxæ›KŠAty2v‹®L­A8Ê\0ÉK²²3–+\\¬’Šƒ¼ÿ{eïöƒ¼ë¼  ˆ…äì!Ö²ï¼ÿ\$¬gãIª.Ã¦ÉO#«Êò‚µ)Ø>°\$g!PØ€6KFè#Òq\nÖWØP2NˆzS{)|ˆ/'¿²û±ë(k„òá¥øfQ*ÒìJdJ´»€>­©øÁ!0“2ò\0Ñ0“RbrÊÚ4œ•ÁyJ˜ª \r <Ø^H@‚Ì¸S™©°dÁÁWlTÅóÌ\\¦T¼£è¦TÃF;q!àMCÈx!\$ºÖ9²_ñAy?ù–²z¸2|½I|„Ë.«ì2²)*c´BR€‹+ÜÀ‚Ë2Ô­`K›2û\\RåË›+ÀBó0Ë™L©“4ÌÍ3dÀ )ÌÈß(­ËG,ø„\"´Ìˆô¯Ò’3x¿S?LÕ3<ÎSAM.tÏÒù2³.‹Á !+·%8!ÁJ”H@ˆµK%û±ÌÊÍ4Ãÿ³AÌÙ3“±³NÍ*dÔ,ÊM4ä/Å¿£Zc¦ÍO1¸\r‰\0\0ß(¤¢Á§M</ÔÖÓQÌå5Ì­38J75äÌS_=5Ü©AŠ-0’:)~HRàQt¬Í‘\0\rsdGsdLº.Ì‚ó´[6@Öâ5—é6È2¥MÌ’ -/ÍÆ	\0cóLÍÎ¦ùÌÀäMÈûÿ³e€İ7x³q‘\0003‡b½4ŒÑÙ4`1.jä­@•H46\$Wà6\0„\rª™²iÎ\n©ôº¡y\0±8h;Éí>ÌÑÛQ_<40?RH10ÛG (LüfQ5/°„xX’†øİsŒ­¤bL§`<‰Ë8{£r§´ûSìá‘É­9@Bçß½8x6à¯'´ˆN\nÎRÙQf`ü€İ8|è¡\n'´ØÔŸï»6T(2^A*/'0E²tÊ*¼xáÉ‡#dËgàHØÒë²RHÙ(B›ó°¶U#`\n \$ÎÌc±â\0(#é9ì§2\r>¤óê‹Ü\nÌ`CrıKòHP ©£ìÁÛeP7ˆ­\$ğ8Ò†É6„½ñèğY°6¨|¶T»ØhàÂœº RĞ„,4s³ÇŠ*¬ˆ èKœª)Ds€JVÙÔŸ‚õ5¦\n™MH?í/|õ\0006@.LÀhùË¨À>óÆKv§L™\"!K(\n“ËI´£¤÷¡Ov¤ó)I —Œø“ÌÏv6Ãœ3ã¨é>dò€¥!Œ÷à‡O¦¸f/ŞKºĞ¥ÎLv€…0Â	T’\nØ‘,á,ËÇãÈÜ¹Ğ6ÈM³|’ºOdğ»!'U=ÃC/øJd*¬Ç­F<ÿÓ¸¥±!H; Ú‚;Ñ™A~—^<aSe¸2Ö˜¡=Oõ1• 9L{9è ,:­9Ğ ,Ğ7™øµ¸-\0`¶\0Ö`¸ Ìu:1| €e1ÌVáL#AŒÆ	ÏAp\r“ªú`À©Ì7)ŒÆ@à™,dÄ¡ëĞ{+¯óÜÊÇ5…\0bìÍ`‡°¤Lh¦U\n¥Ğ¦e\nSïÎ–}ä‡\0ÖĞJAñø!2\0çA,u\0006PKQ|3ğO	PS€68\0%@0É¦\n˜L­ò5.É™ €¤\0',”Ô<€ä)@¢“í†?(P5óJŠ¨ÇgIª\rd—€9\0îùTÌ’´ƒ)ĞBòLA\$c»b)ü¦\r˜ÈXÿèC´O³(ì\$ÂNÑJ „qHV `\"M•8¾tñìÆ\r‹;0»3|QC7Å“XLu1}3X:übO4]©”ëU’çMaEÈ?T=€å%0¤â‚C!ÃT®ÂLHøÇ`ÕÑ*P8K‚/\\ÙËK6{ïSY+€“·Íª´àäKQ6@6ôVÍ‘<ƒeI€P¯6CSÔzOÙ\0ÕËPŒf±›€áÈ>¡˜80àÂÇôÈaÏ\"gI,gmFû¢çá™”r ®IdŠ|ƒF„R\$ áÈ‹Hñ!“2FÜ¦P †HR¸	î‹²¸	±8„²aÈ5”•ƒõIh.QLÉ(@;Ó\r³ZÑ3%\0=)@ØBà*ÇíHø?T£\0†#Ÿô¢ÇíI\r”’ĞJpõô•LÉJU%ªeR_Iå&@:R±%Õ&ÔªRq+*‹øR¸Y°?R\"†J…*R¨3ËJ°iÔ¬‚\"ı,ô¸ÒÁF5&jäÌ6ì.¢ˆŠ'KE+Ô£Ë&u)T¶RML)T”°£LD~Ô¹R¶E”ÂSK½	Ô¤ÓKÕ1€!ÒÍL„ª 7ÆÅDÀc‘Kê-3ò4ÓD÷‚‰ÓA/x%T@SVÁI±ûS^= À`\r’Ëê\n5#”Û0	R2Q‚8ªòT–I#¨%S·Âwº´Ş‚|RÏ7ÓœÁ/EÓÊ\0¸¿´ë…›‡~0A/‚\$ÅÚÓ¿@82L!dšƒ¦ÒĞ˜tY°Oô|7<aœ(¼Ê®6­4d@\r‹O¸;tÔœAÅ?…Q¶iIá¾|&€,’<wÑñ˜À-¦ä¢“ÁCÂ“¤ñ‚\$0Û€ë+ˆ¬åğ‚B1”ô­“Pñ\"Š ¬Ò*¦è#\0ãHş§­E£JOÊú¬’5Æ\0\rŒ¢ñ]OÑì²ˆ²^\rxzò:ƒ^	òÁ_(´—Á6M'%[É-i‡]Fà#Ò“€åNü¿Ë÷\0Ä•ÁLNÅ4²fÕ#i:SˆXÂ@4®÷%ù[´Ê™• Í=ã‡&#e’ªÊHcõ\0Â€€8øf  Æp4ƒ\0öag®àÌ/eD,AÚ€@àbE	\$PõÄJjÔ2\0ƒ*”:ÑÃ .‰Ãç24à£¨-P@u“È=ô )Ò\n=x )\0‚˜ò”‚€ =è\n`+§ÃH()\0#€˜x÷€& =:kiÓ&¼€)‘H+>UéßÕ‡Chi\0€OZ~@+“Âx	€\"À.F?B	ôBµmA”üCÉ[¢íDèÕmQuF‡j”µR/4ÁÃT¤uK%‰TÂ“MD–còW!B\0„EÉÁĞ“`É=èªÒ&L=ô€cÏB(\nÌ¨[ÛĞ0âU\n ÉÕO¸ÏŒ1•EUTu]’\rUİ\\Dé[†¸\r€GËí`1ğÖ'Xµd€„UÁTæ?C£¼HµsV3WEcuuÕ!Xğëu\$çà‰,Bâ}e5~ÄUµPÄ1Ö.–'••A:>p¾ ‚Ã¨•iÑ\n¤ùúS‘İSµ¥E\n1Ğ\"±T@µ@#Á³lNà1[(Ã±:³Á¤PQËUÍl4DÕ[%lÑĞ„%[Xğu·Dú5ºb¢	ÊkHb×Dµpq“W]p@9\0[R8¡õÅŠÚ(-RÇN©ñ.£Â.UpTW%\\á*UÌ¦)\\áƒ@€ÉC­tUÍŸŞj\"\0<WRc}uuÅu]v3’‚¼Q•sõÕ×!]}§ˆWX]­w“·×.¦muê<I\\ 5•×¹†‡ÍrÕÙŠ˜ dæh×^usMó†¸+ 9ÏEuàƒ\njÉÉW¼½y’ã^èÆUòW[_E{õÒ„ãHĞ>µöWÈAõ~Uáƒï_¨: è®k]xÙµş\0b(ä\\B×\$õ€89\\dàñX4€UÙ…aèH8Wì#esïœ…^é€A?ø+=uóÿ\0×`™Äµê•ÊÕ|\0äz)ıuïpµÓÓãQ½uàâ)”`£”“é]xOu@6¾ewàğØ`qäÅÈ9‡Æ?½„ ÅW@hñÊv#	Ë@m‰\$©X›˜+ <×~(ãf\"j§D¢¶×e‹À1XÁ_=rA=¿›W9—\\ 5ØËc=ŒE@Èl€ 1X´Ì: ±^éÆ\0À\$=bM‰sûØ®˜¢FV¯bŠ\$©Y	b8WöC…Tİs6%Y •’@Ù(-’Ö)ÙTÚb±/­ŠD…Ø‘d­¶HØœ®LÇ`À@d	VGÙ=eU‹ü€ĞÑùøvX\ne•”ÖZWF{­—MMY9eM“Ñ²u 2GN(˜-(Ù+bµ˜Ö	K_fX#Öf»9dåŠÖcØvnRb‰Š%4'-—\"İ\n9\$(J•×W%fÕ™6j@7gvr4ÿd/1ÓÉÏgÕ×€Û[eŒ…}Ó‡c%˜à†ÉÏc¨âN„J\$éøaaÙ¬N…›	Y2”dS¸Úb­¡Q›`\0Å ¯WÊ\r…ÂrÚ,Hü®\r†éhÅrQù…æ\"°‚àº‡i\n•ÌFÚKK˜;ö”·iP+Vª™iu¤pÚc3ğ]ÖšZ`8j\0Ø-‰¹•×ÚEiØ¥ÑØ+d]Š\"Ù‰hƒ	VLÏ¼¦Å•ÕïÙf}¢1ÙÙj¡6¦Æ7dM‰ö\"XYjÀW@Ù¡ A\r…e%¬\0¢ÚÅjªb–tY§:…¡öªZµ˜Ûá§ÚÒe­v¹¦(Õ®6¬ŠdÈA¡Zûk\r¬iŠE¢§Kœ6ÁÚÕl*Ñ<J]hu®ö¼ÚÿÚ ¨®¨¢–6[#lbò;ØóbÕ³v¶dttà7ØJt°A§‚¼Ùˆ¸8ØòĞŞA\nlxÒ–¹Zş&°!~©Ò˜¥´€•[OmH¶Õ´~µÊ<\0ém«öÚZ™m»ä,×[Gd¶uéŒi-ŸDqZL¼î66[nE¸ Û‡eÌj1Â[l¥“6Ş—J}s‚/[»me¸–Í#èmÆÚJ¤¹vòF7n?Jt€[Ró\\6ĞYoŠÖL…±c`à5Úíoı¯Öòëüvm–Xç-ıŒ6µ‰a côöZsl%¡C@£[‰••ŠWa¬ ÔÖí[õ¼‚W[Mo•¶Åg\\±¬/\0ÅoØ \"µƒsbM 9ƒ«dÍÄ£×À—ãä5Ç8<vÖ#4ÒmÇ,}Ù†ç§V|Ml¥Ÿ‚S\\Îö‚…im;ÿçá†»Ûâ+YQrEÈ·%ÚÉrjÆ7Û¯a}¡À¨ZúMrW\"ÜsÊ3ÿØŠ(êB—4ÜÁqà76q¾Co…*µÙ+q­ÆñÜÛhsv<……oÕ”W-ÛÉrí“7?Ü§t\r· €Øµt-“—>tHá)­rJ7\"@w]Å¬7IY‹pmÒ·7XYt\nG¬Ê]\rp\rÑQ\n8\nÃ2€Ñ°TezPİ\0î=ÕvİNEÉ#ÁÜ½uÌ—Eİiu-‚—NÖËsíÔ^İ.é}È÷CÜ•dË‚2Á]1f½¬.gİr0é4ÖÀÚx°õ‘>WvEÚ7.]—jİÛUÇ\\k4qö!ÜõtØwIİÁF]pwq„5w)\0•ıİÑqµÒp\0\"\0_az]^Û7v]Å[-Ü—<İåvŞ–L7®c]ƒa7?\$¯-Ü7;^w5àvµ]=va¡gÚví¼€¯	k\$w±ÊÜ;Z€!µ©Véql@;Vğ0íl÷\\‹[ôv-óİı`X ·xWõ_½Ò\"øÜnğ à<^uwíÙÄ^ew=Ó·›]ÕÄr‰u¹pñá*²urĞZ[]™şÁœGÄ>Ã©5CÇÌ»€òÉ(0*É’€Œ˜µV0W‚Å	 ‘ ¦ (\nÕ‚¨[zÈò`)^¸ZkÉ±'ä#Åî\n\n^ä()—·€’šBiW¼ Ê\n°\nƒİ¦ ˆò7¶ŞŞ<ˆé¥'Ğ@	 °^Æ:À3\0*«@ºíì\nĞJãÓ&h\nÀ€…|ï Ì¡zõì\0)_\n=ì  _D=h\n5_Z˜Œ’`*\0²ºÈ3Ò.Àº(\nÀ'¦Îã!h'¼(õ`Â«><ˆ -\0‹{zi /€®L8ò`/¦:øI‹Uv@Z]€†<ì×À_{À0£ËÖ~=õ`ßm{,7ÔE{=í+£c~òl—·ßÇ}5ìWó_²X×É&\$Jk×õ1mü7÷ßO{%üà«8hx`\"¦Àá­û·ı'{}î7°^è¢î÷Ğ€t…ò@\$€¢šÕî—ú€™zĞ\n… Vißß±{øi‹&JÅÿWïŞß|Ø\n	¥UZÍşwÏ_€NWşß(»ÿé:_R„R½ß3}æ—ş`&\n²iAh0™Æ\0¸	¡|EñSŞßŒà¤_%òÃÊ_2Ö ö7Îß¶8ÍıØßÉ|{8%_Y}pcß\0‡}ök¦_k‚ğò©‹_s}İ÷ «_~Î?€`	×»à{µìùàL™pf8;\0¡^8\0¥†˜_¸E` \"b<úwéóß~@	÷å_˜-ùØJß£ƒÌ€—º_­˜-ƒ‚:fW·_Ã~Mùiôa1~~wéá=€&í_?„Zo÷·ÕuUàò7º|XJ³aUrbõdßğpZ	ŞÕ¨<ÖwêŞĞ™0f#ÔU{~@&_æá€	€*€š˜¾£Ô€¯{zwØ_3` #ŞÀ\$(0¸gU¥ƒPX?Î‡Rk`>+\$.8gßÃ†şxà…âlIßaã†İü8[Õ{…ÕX8aßæğóIÒá÷†€\n¸\ràc~Ş€aÍ…µWxà?ˆ\rÿ8&°L>!—Â^µ€|÷Út=åa×`=”‡Øß« %ëéˆ  âd	ÔâM}x\nW´+|-ì áUãÓx	iaC…˜òÕWa†-òs‡ß¯€ØòÉî'f(ZÙ'}‰ªèã×¦\"^'éˆb“Š˜\nxkbª=5ıƒÉßhÒdCİz=6\$CØ€‚&+¸¦­~%8µ\0W‰`ô´â'‰ˆòƒË`‹v&õYà‹µûø\n&0<â€cÍ¨Ô<¸	 !MØóƒÏ\0¡Šş,8bÉŠ–\$˜Ä?EíXÉc\nµşªİU”­&Ahaí€10ØÃã‰8ø¶âV»F2Ø¹âeNIˆbq‹Ş3·À>øÖ\0V..¸Ø€WƒœˆÉ—¦b˜°€#aª=BìêÏ¨[ŠP‰ˆ*=€	ƒĞ*à˜Êè¸‚`7Œö¸q+Hº 	`\$â‘UØ5k^º=ĞZ	äbš=íí²ã»}÷Xóc½.(¸õ¡«†dÃ_3)ø_§3ôCÒbÜ\nF1¸±OŒ†,õ…ÕQ‹Åûycõ‹ö.¸#¦c‹Øó	¿ãé.A©¼`>™í5…§LšF9¦:<Æ+u…àß‹\"‰ªä€V•[(=>X%ÒŒ=H\nS9aÌ†xãI‘vE «a[ &2‹£dh=PZVU]U†#ŠÄd^\n¶‰˜ã7‘FG™\"&½èT£&•‘Şàâå‡F?¸ä\0&Hy)bïîK9)ãw;P\nù\$d£æ#øÿbË‰&J³§g€vK#Ê=‘nLXÓã“H*Ø§^Û€ığ *'K“•†á‰n¹,¦`®:Ù:\0Vá 	)ï&!|ÎPØ²¦ÁVO‚å¦-ÃÔ§}”š{¸·b	“ÖPØEe#”æO˜›cgşPÀ+ÕZrtù(O{Øôr®â˜ÎByLN˜î2ø2å(&U	‹ß\0Xêã€ªìk¤MÀf5]Ö˜†VXUU\\<Úp˜íaHÒ#\0˜=öZ˜àXvW	ßá¤,Rû`…ŠØ)˜fß•†6Ucá„0Øë,œb…ƒĞÕ¨f'`%á›”Î©eİ€Ş^8\0Ÿ€ş^‰ú§Ò.\nX__­Š¦P\0)Áé‡.B@«d‘va9)'¹‘şN÷×åşO÷ñ+~¾+À«+€¦XA˜¦-‘€	õj3˜¨	®M˜şaX‚Õ]ƒ\"f)Ù€ª>y:æLà\n÷Å…¡€¸ò˜4€V™½Xø_…’péà0=-ı@Â§zªíUY…b–ØÃ\0¨¸µ‚È¸x\n™&ñš†jXà&“š¨*É‹_r{R7KˆÖi‰—+q3–kw³€±›8f9šÏ{‚6]éaš ê´¤€V›lùb€’›>jy®fï›Şy´‚¬<Åó)—Öœ(\nkfñœj‰Ô«@­X \0Q~¸÷Ø@åF8CßÖL0ô@*\0¾›`òÙÎ^Õ~r…x®gDº6t•Tß|ä€x£á# >uÙÒâ	e)YÏªÇ®uñú€‡JkyÕçš2èÃÊ&±šeUø{§MŠ;ªĞâi‰U·âL=êu#×g£X*tÉßä+Œ0×¾Èš 	 'µœ¥úîUYŠ^)5VÕ“‹®*X-Uİa\"ªÇ†¦ÊÒ8`­}aå×•ÕUÙ{gæ­Îùâd¡n-8»\0Ÿ…æt9¦”<æLµ„d«‡h÷ ,€¾=I0ÚhŸ8òú¦\"šò´:€¾Ÿ8	Ô£cØ3h1Œ\0i˜§§zğ8„Ö™6„c’d·¶è8­Á1ÎhiŒÖ†Éß'€õXË9™|«£Ì_–•a‚èK ²´)­ß¯Xn‰9µ¨Z0ò5„\0Œ›Æˆxá\0<Ğ˜àã¸ àèÈ\nX½èÀ.U˜à¦WicÉà:»:ƒ«´§ºø	½€şQ&§µ €\nZ\r“<ö„YÇèñ£Öƒ£Íèÿ£ê´9Vgµ‡Rè¸¶®¿‹ªbÊ¨Z«‚bùúä*˜åIGş˜àk¢€©¢Êcš.ª½‡rmP\0³3–Y\0\"'¾ŸÂ…ª2ŞïUj…IùiI¢º´9È ]W`+èèL>Ò-cÈ< ø|\0¿‰–Zç…¥æ\0¸èe¶{©ó€‰NŒ‹£H´˜¶ƒÚ=h+¤&™ú@á1¦¦dºS^¿‡hôy=fWšØUa3~…úXNè;ŠB‹‰¼+FŸOÚva\"MøÚwgí„†cÈ€˜ÒíšG'Á ğòºGiá¢b)Şçí”@öXC¸f<ö\0¸ÈôØ``¯Ú´:ˆcğ<Èšp6<ÆŠcÑaïX^Y„cò=-ax€’CRK¨Z<Æ›ÊÒcf%¾?ø‘€ƒ©n,Ù†gç¨®‚š>h/œx÷ÚHç­‘Vyeè‰ÆYWVZ€§}l©ˆ\0±€-ó¹ûj–O8²_[•0\nY\\§Qª~œ€#€¿n…Z¤€¿Š>wyìà§ªØõY_[¥èöX\rê¶šŞ«¸|egUj·:«Õ£–sË¢a‚À\nZ&È­\r`ŠÒ.«îIÔ`{¦.¹nå†­«ø\næq•Æ–WÉd.Ÿ’´)Òæa–®`>g†šÔÎX‹ä}Ÿ8ò¸‹€²»(ö\nÒ.Í›v{¸T7œˆ€,3£Îd«£g»~0óØ”á¤ô	<{Î<}°3§‚\$ÍÏ”)±8ˆ@†R34¨:í•­Úäµ¥]¼à~6U!<–@íjZ ÖºS„‹Ø^ÀÓˆ´œB€~ 5ôãÉ³,›S¨,(˜Mó©ëÕ9p 3ìÎX©/ZæÛu'ø:·FÏp\\è)|Æ£¯X\rÀ<‡Y8Xtè„•!`6 4Æó°.¸à6ìÙĞ+ZÿPÛ°€n¡ñÉ<­0˜Ûñ&b”w®0>ÁŠSr<§@6«d\n•ƒzãlO°úFRlO±;H5°Ú*„ºL^x0¶>g6–s /ãqœ‘Ye\0ˆ‹@<§z=\$‹CØ¦•ø\n`+'S£9ëäXõ8«_/‚¾Šiñjöˆ	·Ìh7²5Y\0&†c©êwøÍäa™Ö¡ØEìÉŸ­Xyfu‡Îb{5ìÍ³vGµjgãˆnÎXdìè\n²{ƒÔlÇ«†Í™Š§³~­ìV…¡³è	?¦3´Ì÷·éW}æŠÇR	Ch0æ>R½=÷ 7Æ¬”à9µxİ´>S‰\"Ó‚4zöÓúğÒ´0f\"`ğ1½·:æ„¸x:ò³’x;ZÊdÆÖ”íÉeµğK€;Ğğ@Òg¶TML´¡7N3R¾k™€¾N(iÔF‰;)€Orn:Ó¶°\"â>^#ëe;nİ!Q\0 /Ô†fU\0\"ı@Rp6ò ˆ¨Bóm•®0€ó	·!ÛYír/T†ÊtŒ1™VÉeOQzBrÑ9,Æ@9mÓ!–İt‡¼11;‚\"cmMu,Sæå<Å®W\"ó¶yv¡K)Và»mƒV,ÄÓb¨úkà\"à1¯µhxÅğÈF&…à†È9¹tƒÒ?àÜ„Iq¶ ¤ï\0gø5»MK!,„ò?Áå!PSQGÿe€@šÈa:Àbƒ¾Hd@(: İîŸDd†ÔFJ1HÌ‡ 8Èw&şã@â…'i¹VmL E/å¡[R˜RË»ZõnÅfÕ§ŞÙ´_¹Gâ‹hl¤bÅ‡‹»´ŸôlÉ.¦ïÁ3%IÎï[c6ğ 3ká,€ID…íçX%›¿JSoxêv„•KøBûq[iDìÊanıO@¼Û±mÚÕ/è|ÌiK¥2RjfVğr±ïZEJ³n™g\0_ºHê;PHDÓnã-ò•ÍiïQº°aÁ)Œ)¯PgÛáëL6»±‹¾%l»±kÕ:†÷s¨kÚ1ŞúS©mÓNF½›ëo¢(óSJIe:†øÔºJ¯¾=l»ñïFæòÁ)ï²‹\$xàüÍ£¼>óûÃ†9¼X›úo¼<û'ºÊmHf!ñï£D;\0áfĞSA˜ër/ã;Všl<üeğšµ±Š¯œ»Ø»2JÎA\$ü£kœUÀŒ¢çÏÄY¯…š€Ú ³	dù\0×»|pP˜!fÅÒğZ\r@â“À¦ã’Oğ'_x6;\\4§%´Z6[‚6„tŠ‰ÊKÅ°#Óu1|î2…XOo&Ş6~¥±DqàÖO<İ<Œ:Ó¶|­–p%%ÌÖ³RĞ&\rÀ*oÊ¡x\0C[Ê¸#¸çõ:lpwªÍ\$KLÈ€;shƒ`äaRnˆzà;Ï”í;·±|8LªÏÜØ=OEÇ¥H†* )š-•Tª/â›²_íH._%›‚öã„ÒHê¯Á7THÙSD5>Šô°SË_µcr~…yÍEì\0Ò*ä^¶ùÿ,ÍüFS¸è=†\0é#¨>¦å@c·Eé¢ÅäMA7oØ\r R·ñq½{S©ÜóşÕ´1İ'ø˜®á'm¹³À{p0f2/¼Ì»<m:-HÆ¸Â˜Œ%N'[P°wd…fÍâ\nÀ5<tˆ¶	<n…š5ğF3ñ¹+¨b¡ğ*ÏqÆed›—Æ«0”µ@šS;¸é’d„°‚ì–À:†;=>Ø=€ĞÖ#¼‡ÅóÈàNÙR2Ï`ÿFÆd›\"‰ƒwQİµUN«±¦ÀR†€æ¯”utvÓ¡\r§'øK#ˆ¶Îş²O\0É°ì”‘Ïír”×'•rƒHË.| í;O°K.8Èê¿L²€Ü„„HTÀ|sr‹ÌƒIMr¤ú/4<ä¦Ş<”rª®Æ–0ì5nöÆÛÈln,Ûl?ËNÀà¢ƒaËf¸Ü òá!fÆ¹òåo~À|ºòñ°°@2/œ¦À<¹òûËĞ%|³òÉ1~òiL¾	_1 5ì'Ë}½üÅrÜ!ï1´6òÿEo2¼ºsÌÏ.à•óÌ \r¼ÅrûËı7<ÌóÌß2<Çsn;r5*OÔûlLD°F@Øl[Íœ«;ì]Í¦ÆHaGw7¼5¶YÆÌš€®°5HµÕ\$ÅA·ª\\<t|µARQD¤ã!D\\îãI¹qvî€š@à}§ŒùÎØ_şÄlh•(ñ¦ÅáSK‰iÏ)|iwT÷Â’ÆÆÎ¼òO>æ,aÃñô–>T™Îá4|‹sÖd`#y\nuÃ,‹Üò\rÎ·@AŠsØ8=Üüt“dñÜYËw&		NßBñhœRãì2ªt %Ÿ»q,TŠıï¡wM˜ñ[!O=ıë²xİˆNHJ@ƒC6ßFİƒ¶~øa\n.ÏG»q•\"'(“„ô‹qà\r8CvhJ`9\0ğ/ãÀ´|t–&w@á&ÏÒ+ë\0‡‚¼°]ó¹Ëì<bt‰Ğ×?œDqO:ÌİØ</aÊX‚!8À‰YEÑU”Eñ“½Òl¼İ7G=ÓÑaLÊtñÓÿ@1^óÛÏÿ><ôĞ‡QõX°½„âÜ€	\0Ç}?CwÔœ©]‚=ÑEWƒŸŒ¥\ríçÑíàäéNR€+½NÔÍ#ß]?õ1ÇoG¬öô}.wG³ïÁ¤}%×6QŸÑèÀ‘#ô²@.åİh;NÙ <PåÕä 1„Ü8ˆtÏÎâ¡¼şuŸÏ€I¢ÅMĞá!]tUÖ¨DÁ7q&0Ü›Tôz¦ş×\0ÆqHØ‡tI¬|`6lIe°R¢‹Ø¦M¥°ˆŸ^ .õˆ5€Äuóe^†Mİö×_\"½õó7_½'o+Àè0ÑÓs×çQİ?†FØ€€º«H¡İ‹uîXR+€Â—JÌ¢O¤\\Â\rÏ7`N¿ÿ|EtÏĞHû½w¼Yã_„İ»Oa#b@²ìGÒkÕØNÿÀ0ôœ S„½‹¶U„õÈ¸Şµ×h7=\$Œ_Ú0cáxõŒ7e2qtDå¡p–ñ0½€uómÏbÆƒëØ±*]‹^è%À7Nİ?öl/PüZôêwO=·‚ÔgnVÜôóÛÅ¡ vçeÕ©wn1ağ^]Áö]<p\\úŞœÛàÂöÓÏ&AzX4#§İ¯7Ø¯_=Ñk`Ğ‹}‹!EŒØÁ²õóÁ^ß“ËKØ·t2_÷CØ·\\IX3Ø·ı°Ñ:%Õ‚w/P‹QĞ.Ä™£Gİ¬†A?•[Ó—İ“‚'İwd\n9p)ÁÉƒr7vj[àóıŞ@!õìw^ÙR1ÛÚ¤7[Ö„İİÇe;‘‘qïm[«wkØ´µ°¯/5Ù¢rö, ?bƒ']­Úr§ò[\r‹/5Úİ°ª×¯·rÀ.îñ%ÄUáZv-ßl…6~ËÍBõCóã­ÿ×ÈÖı‹Ghø‡½³S ÄougM¶Ñï€p,×O?Ë÷€ÙßD…d½mwà?]²u!Ã R¾4âV×_!§xIwö×^øH9_=>÷}á0ÅìŞ‚½)Üå=ÔHÉÛO_=İø€dØ¡Œø†íØ ¥İdƒ 3÷Ş]¬¹İşšØÏ_6:xDx7B‹³Ó¼\0Ô]ğ/È=ÖvIµJ<Aºİ×XÎ,M×Ëeô;GÂ›\0002x|&\nÒtS•½×ÎÜc4uÿã}Âøpv]\"9x_ObÜ'S÷K¥½‹(áKØaŞDøğX·‹ÌSmİÇç…‡áì3€çä§_<;ÏT½şM‚¥äŠ†Mù\rHo‹¾*Ëã_ò“¹Ñèƒö-Ş˜RœÔÍ'NàU:Šô×¬ñÒH‘Úª’%\0¾5¿g-tˆ\riÅk5ÃÂÇ\n¯ù˜`åHsù¡Í¯isû~qOoí”\r^m>\rŒéO÷/fÕ&?wæÔõ	İùrÔ3*}îtóµ‰İ<†‹çwOı÷¸\\eÉV&d%}!.ŸßŞå{Jªöf×&Fx°8+Rªï×:š¢€Œù‹Ó,xà€ú\rØ^üšø2uŞÔV]òqOs]ø™x=#C÷I}¬ıeİîÅ1bÒšõoÒtàJŸIæwP½\$vñ±´î<Fp—<¦–òyN¤\\·\0Ê=ÖÄâÜ+5ÃÂPl›õ\nS»È³ıÿëÓ¾ç©\\6íƒ¿vû»iõÃ9q4Şg#åïª|ùÕÔ¤vìz¨KäŞ½»µtÅ/İ/ë¯Ü5éÒÊj_IÂöo%!7I¸t±¯Q’Äãk‚\nÈMÖwLMë­àé‚pu!0K\n/W¯şº¬]Ú—´æ[åï®´jzı·ƒ¶)™Ô}\\Xí€)ÓOş¿ö>}ÂÌS•ìÙ8Á@\0Ö1—Gş{?Ş÷yU÷ÊaíGĞ/¶/´Kğ~¨Kßí‡-œO_7µ}*«Oa¾Ó{z%áó³Ï/¸Á,Ôµí\0#İSû…í=¢`…7í¨aZLí°ı»{ŒH^Ş>ĞpµÂ7EOîğ\"àüù†©/¸Şé{ÓÈà=TNƒë½…]¦ÖG.Ô ²€øW·¦Ü×ûãé?]ˆOß¿vLVFÊû{åïíİÁJÈÙïÏTmioÿì÷¨Ô@œéxO¦?zÆ/0OİYõ|.Pb|+Û˜»Æ\nğJaW½/ŸÄ+!¯óì?ÎïÅ\\V—_ìWªô|±Õ=¸‘ÇğŠO²ıÊ†\\úF‰habÒúoC¸Ç@ìÉOqç|…û\nzÀ§OÂ2ùñ‡j=EøQò—I—÷éñìş¹v07‘4ÍˆÍ®7³=­z‹İß¿2ØMÈ×Í=)ú²M7[!öåİßj·ƒ„J	§Ë}¼^æwÍÿõ­ómáhzÇx9à{î„İ¶MŸ½¹×A”Û|ëìÌèö÷ô³>o‚)ô0Öı¹¸\"ßBx}40ßKì(Ü\rmİ¹ykß§Ğ1|ÓOnP–tÓè¬”–{û•¼åÜCqÍÛÖ\\dû1ÒŸÓa.uô·Ÿ]t¿7ˆcnãÈYöDEVÑÛdÇÎ5(}	g×½u\ró×ÙåşÙ<T¿\\ı“×\$·cÔ«ó*TûûğşÖœ;•[·«’q{ûŞ×]U}Õ¶	Î_tÉÇã¢ƒ5udÀ”kmq_Ş¾7µKğ†İ>ı+y{„İ&Ãa?Ô>N,¼Â5ËQö—¦^±ı}ÿÑ¡.}aEr>ç+åÌ§@ÔÍê×Ïİníãì?ce|X}'oìücñ«e_•voìï{Ê÷}-÷x¿~Så_Æ›Ê=ùwn\0;Ø=¸Á|âË'ì8’öâä']³ÿ{CÃèınü?æÏ[´uIÿÖ±4ü)~ŸëÒ=…uMáßë=½À1ì]>F\$ğsRšüª¯òOšäã\r‹òåMw{°/I»â]íì?²•5í“ú¼ÒõNµûè“û„íØ6›u“ÃàR¿üyJ‡³ tî×Z\"ÈÙpñŠŸtÓÚÁîİÙÿV_È©šœ¡¿fşsÛ‡oÏzÖ×óÓf€É¹·óÁ7Ì«¿ûğfVúAY\$ª«ç|óëzÕí[n¦P;¥ü[ı\0Ÿİ[Éú/÷eCÒˆ€¬O)wßòUÈBV¶Wï\\±üƒçàpÔT‚]ˆåØá™_ëô¨	¯GáŠú#Ó_v}ŠÛ5^éĞMµ/óõò‡õŞ\nD²¨ÜdÙîK­\0B³Ò\$ûóšQz¨¥j ˜³“MD)5Ä‘À4«!V¬¸¤\"®XêÌÈU\$ş\\!W:©2OÊ¦Âp!V\r9bõ†Ê°	ôª¤U°‰1J\$à.ªÒè§ ¤‚pÑ@.â|Ä÷“\0T½˜YÚÔ)ğ Œ%ØˆV\0¬õéJËÉf’·s\0RŠ%åfÀp€ƒµ€4\n\0â˜G«€–V´„¦´µjÑ1ÀAq\n¶eÈ¢A¤€tm4­™Š¶”O)ÅAyùâ?\r[ø^tTD¹Ë@^G‰y‡HÅŞ×”@i%­Ğèè\rËYà8¢¡+Ê¸é_ê¾(Ù E,dW<@Wê½ÕÇAr H_\$³ñ;|	Q2K„–X¬-àBâËµwàé P®CYD´2¹%´‹WTÀ¥]2sŠÄoÁZşT·@)jurKoè-\\L`èğ'Zı/\0…Énƒws¤ŒàaÀ¢z–zøµ½nk h¬lpïUa©KÔWğ6	\rš¹\nëi¥…ô o®™rVšÂĞÃ×Ğ9``:®‰z(.h°×#-€ó–<ÔNÀÀl©¹[P€tK5E¡¯ZxŠd¨p\n ×+%Ü\0	@Utix€ı«›X„p¦jº7ÍaOÕÑ‰ZÚh½`NU·‚Å¨)ı4Ò²…ÃˆƒğQk( M¼ ÀYbåÖ-ÃDï\"èé¼¨ü8%@	ÔG‡„â¸.dåbÄÂƒÁ¢Ÿ.\n®zŒpËW«9Òg&Ë°ŸCææ\nĞ²^gŠ«Ši7€õ-XÑ—V\0†Ï\nÊ{Ó†,mƒÕ5j¬1Uk†\n²û“£_öÂñ•`-,­ƒÌ5e4Ëõ—°yæ_\n«Ù‚2‡‚¦Ğ¹ƒ3…ú¡îÉ“@\$lêÏà™ÈFaŒß ½2 füÂí…ë\"¨,BaÀ-0È'FÈ•.h-¥iW³~UŒÃÚü DÒŠ³Ë`–ÆÑœ!V84Ì’˜\rÁ–+I­’‘5æ&L/X—3_ÄĞ%{õüE×Z‡’‚¤Ê%–s(È6¬MQ‹9ƒ†Äq†YtfŒ¨ØºÁ°&\\M¤‰:86\rà¸±ß_A~X\n\nÃX™Áºƒ¨M!ì5şÌ‘àØ1pƒÅ<v-egà…5“ƒÔÍİUk3î\0€%?ƒŞÓ< ò-</¾dÁ¼<øy@óMàØ0-&ÄÔ V¡h	éÂ\0ª¾|¬c?h6c`÷“Ö+sæ\r°&!0†š²gj_À=ó3‚·P‡ ´Ãƒ»Ù”ÛCE3P{Ø¬€B\\&ªl(7@ÚQ¯›ƒŞÕŞ\rƒ–'0àæB'b eXlöÇëÛÃÙAÛ'N2{Åøm=Úx²éc€Å*ë>	l½Zg4GgÚÇ\r)Ã\0	¬Ñ\0AP„ºAû%²êĞj\0\"€Ggß	‰’“F/„Ù:Á­dÉyŸğyH6Œ—šÂkf2¿ˆœdfKĞr`ÏB=„ìÅº{=F~Ì— ï³bÃaUt¶}š!B²…	&ˆ<Ì—šD2^f[µ‚»%è?F	±/ÆcÖÆñ’ô §ÁåY/B	…‹ë\0(AmB¡?±„\rt*ÖplÈ¡Y\0+„4ÅY’ô!èV°ˆI­B#…wšÛ'˜EIœ¡\\Â„^&‹?(M\"—Ã„…šÉzÓ¸ZZÚ0\0M…jÖ\r¬-HW!4BC_Ã	 Y;8I—˜Ş%iìÂÁŒ1„ÌaØÀ±‡'z¿]‘;†7LÌ	êÂe*ÆÆéœ¢°ø+°la73ö…ñÊ”/è_pXİAÅc€Æêä¶Ìna}Âƒ¢ÆrÜ(Ø:ÁìÀ«AÙ…UÔ1&&ÌaŒÂ'FÆê3¦‘n¡K³/`®ÆêÜ¨S°X9Aÿcu\nŠ1ˆ_p©¡1¶c?\n±ĞUğ„aÂû„(Æê³\0èh†ØÛBºcu\nñ¢3&2lLÕÃG…„Ğê\\4xXÌa!d±¶jîÆêŒ4¦V°gÀ)ÂÕ†­	úÄ-¶6Ğ·˜!€+…Á	\nk¦7Œ#XŞ¯°6LİùZFŠm2â´Î(VÀµ—\"ÿEï°uX2²\0c¢UÅ³ƒ5&;,X\0Kl’¾`=32VB¬ÖÚ`ÁgjzÔ4ºë Ğ	x×¶Ã*ƒù\n~øy†‘„Z“¯h_tÔ5—{/`á\$*È^Ó	Q‘\"X™¿4Ÿd„ÃhÖ¤!¢·LíØ<•¸f¤V@šÃvÍe‰¬“[b5Úƒ!¶G¤ÌX^0)„ØÉö<&Hñ†Ã/ƒ¹Æ«Dû0 A.7l‡¨ùFóùTşõŸ!9´Kò§‘¾«~ÀóLÒ‚Ğ\0´My„\\=—*[œ0.¸wZá‰šùZkeÌ3Wf¢U˜+8¶UdùÚ2ú_ÖÇøœ“Wæ´-Ó÷SP¶YµP÷­¦…F*mH	,+6fRåÑYÇ3a'å#<Õa\n«UWB	Läô=0–É¼\$—'RÈ­|TVHìæIíA£OzÅóv}LûƒÍÄ4…ÙÙ†»?víµ4÷gîNùŸÌ%V.í\0šD;lzÌ`<‹	ØˆÌèÙĞDKgQR\"s9èWQYŞ4lzÎ½›tìç›±€h Õuzó&ècš•¹&\$ÆõªcDöŠ!éš04^‡4M!£35%Wh´lbZ]¨ªì?¢bÌıZ{Ú'âÒ1\"Ø>Í'Ù£“½iÎ•tº#\$h@IWZR´§.ºÒ¤¡YA&áä™Ò³­_\nÎÉòEÆeL.Y­Ä‡H¸Í,£3N¢tkÜZ|'iúÓğŸ\"f 90H'¾Ôz°¶£0ÒZ‰µ)'¾Ô®LkÍ˜¿“¦‡øÒ²¤BF±a1O\0›ñ¬+ V&áé	˜/š*¾Ö\0ûXfWıšÅ0ˆº] [x?M)¢±2ƒF¬!­f‡­iI«\nôÜ¤,Ô ­ÌÛ™<Ô0Üõ,X]–èÎ›¡\$tî±ñB#t´Õ­Ôì·SHhÈÀ‹„ª€~\\\\*J)İnüD¬N“DÑ¼°nTÿ‰¹bA¦æ	R¿âî!{uÃÏÎ)Û^¶·mzäE!ÛntÙfœ[{6ßqÖ¤„ü¢yFâÆ€¸Ô/„\n€–Ö\rÂB:<I\"à\\*‚MÀhC—[o>f #ëp—n	CµK¬	q<:`6ßàEK4\$,Ø©·Ù@ÀÕâwUQ¸4T\0‹mÆÔˆ‚\\ZD1Ù¸3ş%>`J…ñÅ1o‘¹q¥{/ò“|·™krá½cÒk‡Üj|Ÿq¶oXà‘&ˆaF¾±Yç%4‘~+\$>äé;^™ÿ|[1Ûw8«íÜÖÄvzêŒ˜CÁ˜ ¹@7ŠoÈ:Ü	HáÅ!Œ”ÅÅ”‹4Ü`\rÊJaD‰Öæ|\"ßM¯XPpÍçßÜ»Ø‹NßYµhX¦æOƒŒR®25¦¡;8){î(¦>0Ø¥*AÇ†\n)„…ĞL½-®ba/wƒ0ÿ±ÍúJG8.C\"s…1mŠã¨@*×T„Àˆ\$ÿw>÷¸ LÚ!Mƒ®}ß‘6‹ÉàWé´U‘Åè‹ÌØ,l=1!Qhñ¨Ìô/-ˆ‘ i“Åğ‰\nøÁ<_â6ÀGFïFpàİ:ë³¿‘‚b²6-ŒÜ‰*€ãøĞ7[V¸ qHâÀ:Gö·ÎFFqJ’àä\nJR—Î-[Õ'¶Ip\$)%ÃŒdÂî˜I¸Év™dÀÀ é‰£ãq@ıLS¢É‡âé/× E p†ü1âÉ€Hÿã¼SN«yÔdMÀ#F=YÚ“ıÅÊ&§i_ÆQ\$ß±ÆZøÅAÜddrÆ¼ld\"Î~£!ë½;nÔëeÙ{…ĞQ™Ş Æj	€“qïëßFöÎŒsFrwRáÍ¶RïèÎqœQ®áqî**ÏpJnL7 YŒ×Ä'ş± ßÛFnØ†î,^G¾F\\ß(¿×{Võ¹¹sŞ¤âëÀà¼;LüÛ‰hRŠÈÓ‚«£Kº¨Jø’5Ò—´âÄ#C;>Sv5[å¸Õñ©#O–~¿ÍáÜTXÓéÇe½Š@ö)Äã4¹ƒ¡İ¬V‚.Á8LTU˜³ÂöFÂ’†îĞLÙ…cdFÄ|·8Œl÷ó€‹ciÆÎT~¶Ò6ÜkĞíæUqnº4|n7¿¢jRÅEo2®´!Ün–ó)í\0ØÆèÜø!g4@cğ6”÷Æ÷ì®Ø',oßĞúÖ=6ñ®B8¸ÖÀá^ÿ=àm†20±š£¤§KxF!­ŠTÈ¦„qXâQu!õN–¦Ädhİ)ÁÔ?jtB¢2*(å.µÀä½RAH,ZÊ'²QBÇ&\0Ö›Š9¡×øæÑÎİ†½Švé4Ã§x¨°ú£ ºmÊ:=xè¨nŞ«A&†î:[¤gbU£tÇQtNŞÆ:Z—n1Q^Ç[CvpSñ(ç¦®˜“Iÿ8X(ëÍğ*ŒÀ¸ÛÄTQ•§#”ºİgô/‹«8İ\"_¿iq;âÄGÊÏÀƒ?Ò‹õ»¤wØğn\$c´>Púmô“ôÈğ¥Uã½€dôqÛ¤xÇIñå£ÁG˜ı‰½¼y¸òïÌŸŸ•i	…PQæ—9„¹ÑñÀYs¢ƒ´¾d\0¥]<Ç±“Ì)ÓğÀL®cÛAXõö/[p%D@J£Ùu|ziA;Üøöññ£â,u¸øÊ\\|¨÷ÂøÕÊ¥~CB>™8ù`K#æÆ…SPÛ‰zOúQî\0¬‚ÃW*íİM~EÉ\ncŞÇ¸#ææ>²ğt‹1ûcæGÛø	Iêœ}˜û±÷R¶³xø5ñø¢„¼¯SŸT*XM\"n;J\\çLƒé+\$ıXÂ¨Ì­“¥{1kŸä¸IJ\$\"Z@“½u¯ïÕ©8_{÷‰uÄkÅ.âßtÇİŞ–>³­‹2	’ÅÇñŠ‘dÔPS QNg·v‚˜ıÇ0¾sû±jd€xş¼_oäÅv\\¦‰mÄ’Æ¨,FQœ¢¤¥ï|vŸÑ^ÒSY	å c\$ÿ|v³}¯m§Ê2Ç»ãm”¸Z9&Áø­ßÛÓ-ùB.€2HÔ\0€=<Øü²ë³áàDd3¹bnÀ&™l«Ä×€JâşÅúm>“àØq#Ğ ÃıH\\j	4Ø@xCÆC·z˜Sl„q€IÆ?ê\r	'’¦´›ê6GtG!J\\^DkÅ§ğ@b‡­ìT£u'÷(È‰>šÄâãıÜªj±R?İå¥HQ¾¼ó2Ë´ñÚŸU']Ù±Rl‹¦ı/+ß1º/OÖâF‡¿®«Ü¦%}òÁÒ:iG&¦”Zà\0nö	•û`)`a†ß·\"fQÔ‹úwö6Şó‚~*‹#‚1òŒµ8'¤nù‘¼öx/dc·‘`ĞWQ>Ì÷©îªĞ‡­‰á­äá#äKÀ˜øNé\$N¸!O(¬@G€yÜÀ¾E’\$)F[{!ï•ø»‡ÖàT #ê¥J©|‰•¸¼AüX%øÌo—Ş¼®w|¶±ñÀ¨Q4n“Â`šá‘¨çQëcøeu©s 2¹ñ‘ä#\\“gàIjÕìGjj+Tv§×¯DcˆI*xûü0¢”¦ó2\$Ë}jŸª?[„tq±_\"€h%±è ;¸W²\nÀİ#Dj&™·L°DÏÏßt¾„‚S#F@Ëü'—NK‹p“²EšèımÑ\$cIQ’èÛ½‹§—ã/MR,Éza…4»§˜±¡bäÉO¸ÕÔR{Ïà)ÇLy‘`sØ±0E#ÜŠ ğıÓËØ)2ò?¾tK| ÀŒ…gOï`Ÿ:E¥&f=ÒÊhî\0RR;uvråâÛ¶è#o«\$¶9I“g#RKd{´ò]ÏÉyŒÁÙƒ^Y.ÏßÊI±ğ°M;§ù\0ï†¤º'P^à<#Ö6Ô*íİ·¤|ºøíéËnGYIáSª¯X¬ô•tKŸN·®{2®¶Hb™UD(Ş±Éš˜¶3ôii!o÷‰MID|ü¡ëÖÙRzŞ‘'wúşm­ÈG 5)ÁO?’Zq†P+ùµ§\nF%º?W[\"¦Ph÷¹ W¥\$Iú'¢ˆ™?îäTJ”,şnJ¡÷GàGõÊöè¡ñ\$ØŸÂ@ßÊ*‘¶ÔšT§ñ`ÅR‚‰ÒsÄ	îQ‰G8şòeFË”T¸ª¨\$y/Œ]¼^\0âÎR3¤ïoiÏ#Ö))Fü¤—@nÜe)/Y=#RR‘©åÀ+`c7È†Tİä`¸?ÄèiBŸFæv¤¹…éÁ…’ ™%'²Q8Ş@/â*÷ãÏô^½4”æü±Ø¤zâ	=\$å¾}\0üMû<ZÙO'ß\$©èmBõûØ0NA\r>š¨øµõ\$gJod´+îéMŞdr¦Ü2…»Ûù(ĞaÂZ¹Sà”Ÿ‚1q9Ú?ì¢‰BNMRtÊ¨•@øåÔû_‰UÀ”¤8\$ùBX²b?Ì«a¦Í¯\0Œ>»uBŠU¸	W®n\0©%ŞK ä>RğHN…£4:çI¢Ş8}\"x!uàGçI=•£)	×cØwøÃ”¥U•¡e‹€(±D}@ÆÒJ¬êö4c®LÒ1Òlëvú?W|ŠTİ•^Éú•ŞéeŠBiÏP–Ù“õ)úO´¬ÿ.…SËX-'±0Ğ” œ ÉûNó .Xkî™b\rŠ_ïƒØz|œaï¬°Éb’#Ò÷½v@ïNXğÓt¶ª#%ÈöÆƒÕTzµ[¤³à‡B¸ªp–z®2Vd·B!¿VhƒuT”IJÈU|x‰H\0BFğ†BÏ€@½\\\n9ÃFfI ¤€¤˜”ÔÂXã!\\è–c,¾Z!-Cteš	è–¸¬Ô””È	ÒÎÕŸÀ(UıP’Ò´YjRÑ—¥+LDû\\µH\0jÔÕ¦À\\Vå,ÆZ¤È¢FB£2Và¼xxÈQ7PQCË|%Ÿ-ùq`H«ÖË‹”Ø'r¹ VÒZ]ìKv–)_\\¹)qÃûÕê,¡—6oŒ. uvòåŠïWwî	\\¸¸İ‹%QË“î0,ºÄvräÖm;—e.yÚ\\vË'Àt^?-²ª9¨aI¥ÃÀit#”ºÉq2ñÑİ;>—{Ê[¸¡Òàå½ËÄ4ŒÉşÉş•¬+ËeçÁ@^-N^0©y’ò`¤ ^¤ÿü>Ì¸)iÛ¨2„–Ï8HEğ-ÎB`c\0N%Œ¿6{°È˜öÃX†9îÄ0Èil—×à1½&pÇ|%Ğ	Ìp„ÄC‡6¬1[\"u…ÒĞC\\eÎV’ÈXwläá4Ş&2Ñ—\0XIÌü¡/2÷eáò3\0µü15W¿Aèa\0ÁaT6V‹İ0†a0†`ãY†Lf0…U\\Îi…ü0¶rLZXB3¬`y0ùzÿ6ká Ñ±Ğ˜.N¥¦Y:’0sX¤3ĞˆAœ˜ë4 ö,eYì3Ú(RÏëÀöâL\$ƒÏé£ä,H‹lí¢ÌkgsR\"ÄI˜`o\"0]ˆÄÅ#CÆƒ1S4:hpÑ	ì6ÂeÑb41Ãe°™’UØ°¡Z3ŒhĞÅ\"4G†ZWW¾Ó(V•¥1V6!YT5Aˆ}ıƒk+¦¢&¡9Dƒ.ŒU}š;†”-*¶”iº&\$áUø~q'bPD(ƒ„¢ZFŸ1*	œ¤–”=“Pr…q,Yœ5gòÔzc³Q¦°]CÌÌÈdÈZ&Rv®Œ»™0Ã¦VËú#…ó“\rj5‰eP]•CX¸›êË°í+6¤	¸‚Y–À¢¦Ôô¿HH<a´ëjK\"ãT­ÛPX±¨ª‡…?aA\n\$Ä™ú1•&›iÉ;)7ğ¨ŒHş.2'+iT‚©.\"¶™l\nÚ¡È¤VcO3–6ñmhD/3n4Î|Ôæ>XHRÚ}&soÉEi\rR×+l%!»pÖã\$±äù7N¨“¢0ƒ^p¿MÛ O©E-z’gÃ%‚‰Æ1í!k‹G‰Uã¶Ç§s•ª\0lzÇ9ÒŞB.Kë/˜ˆ^=òM7fïLßµƒrFç|	lÑ—âîÎÿÉ)OÌüZR#Ö7r#ŠL©¾0)”êŞ'“„ö\"‰^aÔ•ôS*S‰/×í‰JMbu’“qÜ!’Wöß	ƒïsÒ”&F¼•Ñ‘£èÆ’8¥Ò3¿õƒÒ(é¾9|ôöfQkîçê*‰·p²ò5ş›²	t*íİÍ©­=3öw\nZOª©­ÄÜ×›S\\äQÅ5›¡NŒhÔşïì#B-›M+®K³Ó•À+K\$=ÿv£6ñæ¢9!€K[NH'ZXíVOD¥;²&ŞM‘ŒBëFnTfçãoôÒKÙ0ûN›Æ¡é´U2¾-’ÍÒD“×û¢ê·›ÆQân\$¹e%ô@—¥-Î\0,\0¤àÕ‰Ç%é-ZYt)f*Ìå+>BÎ8f*¤EÒÓÁwKQ›ë8N<·B\\ĞĞüKv\\;7Ø”tM €2ĞoLGĞNàÉeå2§G@…VèëØ(±Ğ@@\0001\0n”˜¤â0à\0€4\0g8¤œâ@\0ÀpZ8ÄÚØP@\r§N\$\0l\0à°YÇ³ŠÀÎ+œ‡8¸ÀyÇ¦Ô\0Î4œ–\0À¤ä™Ç³’@NPœ¦\0Şq¤ä©ÄS'Î@œ¦ml™Êà@N6œF\0âq˜iÌÓŒ@€7\0l\0Âq|å¹Ä€@\0007ZÉ9pœäÊs‰§=Nrœ“9äÄä9ÍæÖç/€7œ£9©€9ÈSŸ§.ÎZœo8˜¸s g?N.\0s9Şq4ä\0S¥'G›PœÃ:.t|âYÄó§'N.\0m8ğZoàÀç8N:œ¯8–sê@\r§6ÎMA:ªudé¹Ö3¨g*NkœI9´¤ê‰×“™'GNg\0`\0Ä´ë¹ÌàgÎ œÏ8Îtœå03¡§RN8\0d\0Ör”çéÎ 'aN*œß:²ryÇS¦'4\0000\0k;:sÃs¹Ïsg_Nhœ›;vqÄìyÕÓœ§,Nµœ‡:Ns¤îÕ`\rÑ€c6\0Âstì#û3¿ç(ÎÅœÙ:jwäèÉÎÁ§NÇœÑ9sìèùÍ³­'8Î¡\0æuÜê©Í³˜HìÎ~i;ŠrœçYÄÓ¾€N¾\r:Úr¼èiİŒ€Na\n\0æy\\ïYÜÇ§7O	-;w„âiÙ€\rgŒ€b\0j’sTóÙ“Ég#ÎÌ9t”ì©Ç\0@NçœÇ:JryS	ã³Ä€N.+8ŒÚ€Iã“ÛÎíœÓ;’rìóÜ@\r§›NËƒ:bstå	á““'ÎSW9æsöÙŞ“Ö§fNCœ;ªsôñ¹Ú³œç0ÏJœM=jrÄòâs—çO{6·=ÒrøIà×\0Ï9œÑ9ºz8 “‰'DNÜË9²tœòéÖSÇçÉOW—;|ôâiç“›§pÎE§;yløéÒ \r'“Nâ\0e>†süèyÏÓÓ'SÏ„Ÿk<zxäåyÉ³Ø'µOvÉ<š}úÙÍóÑçÎÑŸg:F|ŒèiÜ€g­Näa>Jr<âéêi¿§IO“œO<¦{¬ïIÅ“¦çiN:œm<js°‰òóŸ'n-tı9V|Tóiüä§´NŠ#9œŒóÉùÓÓgŠÏë	>ÎqÔúyÇ–'‘«›ŸÏ9Bw4öéÜSÊçÇÎ3¥;Š{Dä‰İ³°'ñÎT[9yİ\0ÙÒ3ç'ÏZc9V~„ë)Ç-Îç¿NI%>:u¤úÉËóå§iNÁœm<Dû!óÿçÈO\\ñ:òs„øiÙ³Ã'6Î-ŸÇ:vÚ3ß§q% h\0Î}æ9ñ¤Å'êP@Ÿ™<æ{H\n³ÀP4 A:yı\0ÙË³Ï@Ïdœk=ît‰µ	ç3ÖgO³›?NuœëZ	Sï€Ï Q=ªyìê¹å´çFN†?Ærô÷9òsú§YÎ¹œ‹>&s„å9ÒÔ§ÿÎQ}:xÚ„è%4§cÎí };’u¤ıÉï3¸¨Îtµ=vMYÊ³éHÍĞD\0Å:JrÍÉëSÃè/Pi:^€óùß³¿Í­Î» Ï;~z-ãÓ´è0 »:†qÜı™ê“Æ(\$N4¡U<J‚í9ú3âè/OÆ¡q=j~ı	yÉ³'£ÏL U;<Ú„ÿ©ÇÓ§rOü¡›:*‚¤äÙÍ³á§˜()œÇ>¾„´ñùİó‘èXĞ£œ«=’u½Z4ç&ĞÁ Õ:tª\0“³hSOÿœ“B\"‚¬ôyË´5'’Ï†“9:{Í\n\0”§µÎp ›@sTıÙóS§g8N«¡-?Vv<ÿZSâ¨Î¡¡Y@rÀYô3–'ÖP|ÿ>6ˆµ\rùè“½'oÏªœWCRz­©ÓÓàhO\0M9¾s|ïyüS°“P\0m?:†œí)Ê´2§Îj¡‹@â}õùøóõçzNâ›9îvÄö\n³÷@Nœ?³DØŒÜû	ïô-gP7Ÿ!@şq])Ì”='ÊPù¢;~e\0¶çsì'UĞØŸBrvØyë”'¨‚N¬¡×9by4ø\nóÖ§Ïn 9BZqÌù*S’gÑ7¢S9ú„-\0Iæ'/Î— ;9‚{DóÊSœ§&Oı WDÚ|Tøj\"3×¨Pé*a;êƒ4'7ÑdœÏ8şŠõú3¨gÑ“ =2r9¹æ3Õh±Q%çFrs­\0ÙÚtD§—ÏaBòx”ä	ØóèÙN0œó8¶-	Üô_çKÑ4œY>Ş…ÄòZ,ôk(UÑD¾\rŠ0“«hQ²¢E:¶‚ÁS\n3£ÍĞX£=	(9ı³Šç´O;Ÿ#;rŠäçZ\0”g­Ğ«¡'D–‹ìùÛ”_çÅOhœYGj€™Í“ğè,Nn£O=bx]9üTUPQ@6q\$íz~§IÑï@^µ	içó·(«ÏûŸÃDÊ€})ş4d)P²œTTÂ´ô¹Ò4^§Ğ\r¡µ<¦ˆâª)´>ç‘Nı£FÎ‰-!:4N§PÎ.¤>â{”êË‹(ĞwŸıC‚‚­Ií3¦'&R!œcFäóğSè}O“¢Ó>*‘ÔòYèô\rhîÎ—¢XŠq\rZS‰gØN;¢IE–†=!ª3t…ç:PHÇD‚Uöçt=§V#³MüÜîr\rYâTKè\0ÑÔ¡o8ªyÜøéüSg®Nù¡WGò‰mºTC¨`P#¹EÂ~}	ZMÔ>çKÑz¤…Cr}\$û9ÆsÔÆåO%œë9‘khJQÓ¤g´Q/Ÿ™@ı©ç“ğ€’P‚¤#HÊ¼öê\n´Dè”PK5F†€5Š\0%'àQÙ EòsôüÊOóö§SÑúŸ‘>V‹mº;ThÈÍÒ^±B´	-‰Ì4<§ÖQs¥)>Â€ IÍ³î§ñPB£};‰¥&Êû§¿NLgD‚myĞt\\ççĞòœ¡K\"v¤êúS´IiLÒ2Ÿ«@BsE	©õ“£§õÏU¡³:²tUªT¬(ÃÏMœÑCš—„í™âSÆ)kÑø¡‡=ºsE)YÛ”b§vRïœá:*|‰Ô3²)=ÎË¢‡:2|Ğ\$³Ï'İP¥U=:y¥éş°i9QÿL\"•¥\nt((|Ò‚¢Ë;\"‹uUÃsR \0¨‹½zÃ<•íçÉ/mbÊ±™Pzeë1XG/ÂfÀÎbã7)„TĞYZ´Å'~½}˜Ã¶^,O™:´»b8ÚJšÊiŒYá	ˆbÜÉÙ‰­5i°™Y¯L\"¦¸ÌV‹\"Ø5ĞùËÃõc¬¾R@JlÌÊñ¸É4<›°) ĞÁŸSiMn›;ŠnŒ‘é»CõfŸÜˆ€: S b»lˆ Í5Û/)„LB`¾Ì	‡şÎU~èy õôà@ÌSƒ¬1%\\Æ8Jl¿—õ³ìa	Zš½4àZÖ©Ğ1_‰‚–&:6”-=˜Ä#§P’^SJ:nñ\r)ÏÂ^_×	yŠı7¶~ÔßWî“Œ†t]*étfš\"ÖmINmë4öi”`¢©äºlwÁÔ<Ë¸]ñp¦•kÂ÷4~]PZií4%‚ÒÈôL2FörDâ	¼0‹&KLü,ÇÕûpÂšÂ3’VÂ-X->¶6S˜°§êV‚<85ÔıZ¥«iƒ312eĞÛƒÓTGîÌØŠ€ÛQû°KbJP3ntäÀÂµĞQb ‘4ª‚DÊÜ±£æWk’€f+æY¿SaUÙÍ‹yvŠ„pé\0Â¯‰&;P†16.\r™Á˜—jbÅb›§¶·‰;\0\0I§ÎÙ•ƒ4`ÌÇ¡üT=&œLfœ ¥ÚÂÍšâ6\0Â\0ıEmDJ…ìÑ€)°”'ÒÀ’\$83;,B€ÂÔ[`É3™Ô8ÊiØÃÖÃS„`Æn¢Ó¦Œ¦Ù»—\0„Íé›ÈVÊì\"À¤\0skˆ1¼­š@×´Óù„åOíÜs_Uµ…šø…f¶ëò/ƒaf»ÀW˜G²~©¬}5’m#ÙŠÓ§ˆJ&\"HÚ\0aj\rTœ‰MPÒ¤â”jwì#Ø™6cÍC!¥ï¬µ™Ş¯}©#M>¤›\n:•¬‡!¦SV©_Rf¥“Rš•Œÿê]ÃŸe­†ä¸3U&êTÔÀby`šU8¡OCŸë²ó¨úË–\rLBxŒ3iû3f&¡J2'°\nõj;i›å›<ú}\0çC'8Ë§@‹+J,æ™nC²¦“0á–DW¡§ü\$Ç¥>wÄV‰XVSğ°ê|²ÂhÔÌM– V\"ìÛI™'Ä©:VhŸû6Ö,ÊÍ/‹fÚÌØ\n²3Val^¡ß…kMO•€êÅY-/Z¨Ì)bİÒ\rM<	‡0ñÀÁÚ&añ˜¬Ú£Õƒß\0M_?Q©'p÷´ëªK€C©qTÅ‹p óËîàß´vªVÉš{TZi5OàØ—Ff!U~õCøeURC¯H	\n%£ShUU*”ªˆĞ.ª¼†EM) ÇµCª…R©Ã…óÌXÙ5—„P¢\$&†…\r\nª¢0‡QUµK7êŒUJ*¸/jb'ê¢,(öu?Ù‡º_§U†WÆk¬QjÀ4iƒÉà<ûg\n°¬f™ŸU8^ß0±ƒõXI†[—éÓ]ªÃM5­SxSJ°ZÉ°<…·U³ËfÕşÇª¼Âh«CRUŒ…9ú´ĞªØĞÕDfpMjª#46jŒ¦©ª°d`Àš=Y6ÄÖÊ´abÜÀµV¥[dUpšU±„7Uõ\\ˆDÕVÉôUÊbèÄq¡MUÆõWª1ÔÄ…sVÖ­T*–°ÓÉß©š«‘TÒK1ª–5eØ2€Y&’Í³[¦PQ&\0ÌC…—V~¯SrcQ&%ÕñdL.vµQš°d	¶eXØ^p™!3U\"…çn…BŠ¥Túa}ª´cT½„\r`Ú’Õx@ÓZcuTêûÆ«ó*˜BøªƒWVŸ4/¶uQëLª’½ªª]bZhµSI“ÕƒµUF\n•UB|U„fÕX¬UU‰‹{ê¹•WZ€ÕÕ¬ªĞfˆÕ‹=V«Çc y*ÆŒ­j·Sœ¬oY\n«ˆ-\n®lÆ+Ì†¾¾«Åd¶<5a˜Õ}ewMª²}Z:¯uaêÁ°'¬´Æb²äöV•b«0±™ƒ7YefjÌ5ej\"Ã(§EWŠ°ì/µU*² ù/²VYÂ­=eº±‹şk0Ö[V¦´ZÆa}Õ¯¬C0­”'rc÷ªÛ26«qŠ³ Ú·µ¥YC³!f™\r®i–‘Õ£ÙZÂ·­IWù©ug\nÒlMjèÕÍ¬ÃZ®®€ZºAëjêUt­_WbµU3†›Õ©ÃÖÕÜ6İa:v+UUæ&½³ƒªÙ0kdÕğ­0\r±ıg\n¾µ³ÙZÕ÷«òY‹…g\n¿µ¡Ú—V\0&V6\r…RjÁ5‘kËì« Âò°{Oxwõ«+aT˜­ˆ¿\n\rÓB˜2m<*°A°¬1TòRÿŠÃ•ºØSFª¿TŠ\r…bzxKö+xA¶ªÍU:±p\nÆTÉïV3®\rÅoÊÇc«ÕÓ¬{ZÚ·5q:¬•ŠİVD«EMjqZ\n­õÃàäVQ¬°Âö¸•fŠÌu—Y\"U~¬Á\\f¹SÈuµd«˜Vb®eV\"©;ªä• «7×;¬Ë0¾¹­gJå0Ù«;ÖFªaÂ³İYÕğ³¡¶€¨QYº¦%tÖÑu\n3°‹„Àeuh7U£«¡Ö‘«hÄÆ¥µif~•¦+šÖš\rZq|K\"i,ä¡	Õ¼®ÏVş´õuÚÔ5ÙkQ×C«“\\úš¥\\úÇuİªå—V­eFµ¥qzÊ•¡+šÖ“¦­]ò»iÊk,+£Â3«Á[º5t:ÙUŞ*ö×C­£]â¶œ\nÚÍ,*ıSB®k[nímØ5•€Wü‰Ìí3y†CkqC…h6Ì™|%`ŠõÁäëƒ¶}ø@õÀ@\$×º«-X:©sOv\0pwÙWºˆFV9š\r3Ğò0JÇWÃ&Ÿ]|\rDPóHÿ—Â\0V*é0ˆ˜³ö«ôæ˜¬“§0*¥âıÖŒ`WõšáNÜŠ¬\rı~WÓ•©Ìª­¯ÛR³“9:ûŒ5©Ì±Xa¬~¥;pò-©ÚÂı&½_8˜ÍS€Éˆ³O{NÜe{†?VøØ\0¯‹Nõ†«\rp\nm™aWÊgæOÊİ€ë\0L’Éœ±\rg÷`v¼œöx,Y Ì¨Y`Š¿ûÌÕ)ÛÓ5°ZÎ¡~}yƒ•YÀ/¯™\0œÕl¬ú1fÇmCÌ38'}_&ÁÛ:913, ³¨¯.ÎNŸ	4ª£Œ¤˜@Á¸inO@yú`ÜX°›UŒ=õªèXl€G°aP<›<›\nì™hTC°ÁOÅ›†vÌ–k€B°¦ÁÖÃu{š}  ¯Óİ¬XOf©˜ÚÂÄÄl;€Y¦aÚ«9=›uÁÀ*X‚&Ñ[É¢½|ÊÈö,@€Y+b\"Ãı>›‹çIìª³ay\\)Uu9\nz½jŠÓĞ&`àuL¦›Q,áÓ÷±	aâÄUS\nÈv#lH°Œ®;b6šÕŠ»ZX˜bÇOÕ±å„¦1l\\µ¬«8Ã~Åã&ú÷5˜ÃSî±zÒŞºÃP«0ƒß4À°Â2Æ;)º~\\X°Óüg~Um–u†ö-µòk XÛ°åca”Yv‹PplqXßbÚÅÂÂ°V-¶:J­²Æg%T<Õt›VÃÌUf&L©šOû5``çÄH_§c²ÇãÚğÍSÅWÏ¨FMàûØˆæbcÓº‰vÏî}vsĞ×	óÕx°–ÊrÈ“3Ú‹5I¼l²\$¥*ÈõBÒín@1p'Oa½³ë9ö'lñª‡½hèPm2{6¬4€(mOf½¥CV€lç«ö…e,ÎzÆıUVuYT?°J^Ê+:0÷Ìª\"0'¯‘c±‡U•(­WêàÔTh7X>ªµ•eW}I‰5²™eX›1¡éj—YY¨zNÉ\r–{#vZ˜kAù¨\0 Ò\rƒTí5ò¬¸Ù=²àM¢É£¶•±U‚²Å®^¿L	%—æ:Ö;ZÙf\$&¯H€öL\n*9U–h9_áºö–¬€àÙÕk[a|A[¦aàÌUhÙvU£\r\"¸6*ùTğª†D‡e«Rr¡3ösì\rÛ@°³RLJÃ}•»5V7ìÙY°„=eæÍu•À\n–;,ÚÙ»²`º ´î’7×o©)Q‚¤ yà\nåªN2~FjÂ=}›-ìÜT^÷gJÀ\"@J²ÍOZœØ\nj@iŠË;ì2+šAÜ²*Âõ³Å{Û;áê¬³C:„ ¡•L\"kà\0+€D„µ=³Ÿ[LÄØÕ³±¾ªºdË\r†dÌ‰Œ2oe­`æĞ=Š•ŒwjÔ¬`2Ğq5åëğB«E²\nbsR²©=T:–PlØEÄ¸©~ÁN¥ûKfƒ¶YYÉYÊ¦i`Á{Û-f¶blÛÙv³oc|=t ”ğ¬gÖ°±ÙA†L†rV+ÿÙ¿´jMâÂ;Xöª’€AYÚ´Ò;[H†Ö\rp°Í«-g¹£öYâm1Æ±‘_©ƒTz‰9XÓÜaÊÉæÀ–’wÄÉ@+2â´Éc^\". \n¥fÂÆ`{]’¾í¦ÒcØ¬Ús´`Øı¥í¢ëO\0í3n^Ó¦¦»J2mŒëØfÚ‚&ÈÎ¾Ô¨[DÚ-´£²Aj-¨X˜Öªt•Ÿ©ÓU¾Ó5®ŒCj¯g%Sı—bc¶ ÙÒÚ‰©Û1\n½Ã;ÒbÚY¦V~a1jŠİP«¬v08`L–§í–’dw*|ÕóiâËµ§‹ìEìk€VYÀŞ(ŞÈ¢06 ‡@!ÚMªkÍ¬‡ -\"ÚG´’\0¡À(PSQ¡W3Ís@0Å­U~ç9\$#ÔÍìA:;*Yhë”à¢Ì*Ù›÷-n[ÉlsÈ â –!5 1ª¥ÀÉ€.#¾èĞÀ3Ö¿ÀÇE«<¬J\\'0ÅÂl¥Â\"¨4¾–¢ØPÌ•å0?\0001€d\0^\nŒœêÀÄ´ƒg<\0\\ğTV@’À…Y&°4¡%š;\"]m“ÉÈ\nÏlªr…²Â¼¶Êö/\0klÊ	½²ëe!KÀ[8“Ml¼)x{gÖÍ-˜\08¶‡lêÙ¨P6ÑÖ[@\0ÂÚbôà½‡açcÛQ6AT	)Ô–Ìí¤[XœámX7mµà–Ø-—ÛT¶omnÙÍ´Ûe6Õ-Ûh¶m®õ¶KnQm¹[S¶•mÖÚÀKoaÔÛ{¡múƒ-·‰Õ–Õm·[D·mhYmµËmA{)\$@Œ·m¦ÚúIâÛÛ¶¯I!e¸[k´’-±Ûš·nJ˜·ŠIÛmÇÛo·mÆÜõ¹IÑ­ÔÛ—¶ïn¶Û\rÛjì-—ÏR·nVİ0ùêVå-–ÛŸ¥nfİ-´IêVçmàÛ]¥mŞ-»à\$¶é-Ü[Â·Qo&ÜŒõ+u–ô-«ÏR¶—nÒÙISoVöö[Ú·onöŞ·û{€ç7Û„·©m†ß-»ËqVämòÛÀ·›mvß-¼K}öçíòÛÈ·ço²Ş]·‹|¶Ğ­ñÛå·§oŞÚ½¾[zöùí—Îo··pÙMÀk{öúî\0Ûâ¸²à5¾ë|gæ[úåpZvE·‰Äöù®\0[a¸7oªÜµÁëevøî\rÛò¸'m†‰À»„6Ëè™Ü ·sDÎà•ÀË…\0In\\2¸Yn†ß;y—	îÏ\0¶ñDÎßıÃËe4LîÜ¸opâ5Ä9Ø÷\n®Ü¸’²r­¸‡4îÜ¸§pfâ½¿Ëàî(ÜA¸mq*ŒÅS°÷®&[D 1pºã4òÛ†7\nî%O’¸ÅoÚâÅ	õ7®-\\O¸¹<¶âõÆÀ½”2n2Û]¡“q¢ãõÂ;‹t2nÜz\0¿C&ã…ÅûvşmñĞÉ¸ëqÆä8[6îhdÜE¹ mBâEÉ{‰vŞ.L\\€·?rbã]ÉKŠ—#n+\\ƒqrrv=Èû‘W\$g\\¨¸Ãr’ãË¶çÂ—Ü¹!rÖäõ¹–·(m¢\\µ¹næå­È›×-nÜ«¹kr¨:Èëk6Î.e[_¶Ñs6ä-ÌÛ˜W3nbÜÉYo>Ü­ÌÛ73nK-ÏYcm>æeÍ{o·3îq\\ ¹•qJÜ}Î{š78®iÜâ¹«sæEÎ{›w8®nÜÖ¹¿rÆçMÍzHw8n{Üæ¹õq®æUÆëœ—=îvÜ÷¹ßsêçÏ«w=î\"€#\0Ó9Òt0—Cºİœé8–vLók™S«.~,»œápªèİÏë›ó“nši¡ıt”ÒŒêÛ¥À(‚İ+w<.ép:7Lg8\\õ¹•>*é¤ù;§7H®N.ºss®æüôÛ§7Aî İ	ºƒt.éíÓkšåL.šN:º›tê]Ô£Ó¹î¦İDº§uêÔ‹¤ÓÏ.šNoº»t‚æ¼æ««·T.oÏdº»tèôê{«·V.“N?º»u*æüâ{¦“®¹İ<ºM9bëÕ+®÷Z.»İVºïu²éLâ»®w\\.NUºi8–ì=×k¥3ŒîÃİz»uòì]×ë±w`.–Nh»vé1µ¦“¥îÍİŠºY:JìİØë³÷d.Ïİ’»?vRé|ìË³wf\r(…/ºb’ëËdkîg\\ù»elÆèË°\$—i\0İÏ@»iv¢q·K·&š@’]¬&\0 ¦İÛk·W8îå]Äœév¾çEÍ•j\nnÑ]½ŸãwBí5İk¸ îè]Æ»{sâî•Û+ŸwpîÖÎe»_>¾ï=İYÆ3µîóİÙ»×wntİŞ{»ÓŒn]ä»Gjí{s»¿7Y.İP@»óvòï¥İ¹Òw~nùÒ²»_têïµÛÙõ—îş]Ä«xğ{À÷vç(Ş¼u2ğLãç÷kî§^Ÿãx6ílâÛÃ—„gÆŞ¸ê\0Œ¨ úHt‹î{]ºœ‹tVñHËÅs®ÖGOñ¼OxÄ\ru\$:÷gl^¼uzğõûµ÷X¯ Ş »ušò\rá­Wníİn¼ƒxë•áë®—”®Ï]½ºñyJïTú+µó›¯-İÛ»yJğ,ò[µ÷c/Oâ¼ÇxFræ;»wf¯]œ¼ÛyV‘ıäk·³Ó.×Ï0¼ëwºŒÕ«µ³Æ.˜ÛT¼û<¶óıç Ë·n.æšQ;x\0ì=Şã°÷pÃŞz&\0vï\$;¹7xV]ÒC»czVî}á›ÇWŸn\nŞš»¯zzôTâkÓ—£€İÒC½#w‚èé[¼w¡oGİæ½5w¢õ}Ş©Ì·¢'Ş}œëzÚõ eÙÌ6Ë Ÿ†psk¢_]pK]kšÖ6½ŠÎ–}t9 ël×x®¡[‚\rÓªèl:­WWC\0”Ou\rsXe0~áOAş_]öÜ2¸sğÒWC‡ƒT\rƒHëuÍX½\0Kk']½ZÚï\0Ø¶×]®İVù³­{XBuÜï}Á{©¾¾föªïˆ˜\$×x´y_J¼tLjòL(«šÃY¯%[2¼¥tÈ7VàØW™gš¿½ÒFö¨•I!ÃP¯Y]ı˜œ%\nvñ	*TBZ.İWÅ¡):Zf×Ê%Ù¾YUV¥ïZ¾7±¡:VAfÿh*ùìŠåÎCÌl—U\\íìÊ…U|og×„…)\\Òú;{Úµ|ok“XªˆMŠ©ÔÄš¾7¸aËŞŞ`é\nh›\$)Ëê°Ëor1k®_\n†{!º¨—º˜>Õñ½Û{¾û4Êé@\nàÑ€D°Všù•^KŞ5¢áRµ±eVÆ¥wc•pª†2^‚õ0÷ı5Kà7Éa4Ww«Éf‚ø-øp\nÜa\\ÖÆ«ÑW²ød-!•E*ö_«É}.ü¼-¶dÛª¢XG…ÈÉ‚Ñ8–rÊ²^0ƒõZJµõ]êÌ7ÊXúÖp²!Tf³\rìh`·Í—¹X¢†|ê²«+V”×ÏY\rÖy¬k}ömmJç•1%şWñ¿‹>¶s«é·¶a’1…˜~ù•­õKÛ·×:Vv‡+{rdÀFJ5_ë0Şç§Çîû!?*ĞÚ¬yÃ9­ZòñDîõ¦o¼±¬\nPÊÔ¿ ¾‡¾¿ÌN8[à—ú!Uç­‘Y†Á¿æv”«eÓôÀ&`ù¦FÇoè2¿¿0¶œ7g,u-KXê‡ˆ!—Ì161Õ°à†6ÂÎ¡ƒjˆ„Ñ,¿_°CQÎÌãE{ä\r˜Ú†T¬³ÿz£L[ş•3¦kß{f²¦¥BRkÑ Ä³ÀrÍÂ¨d‹!ÕYnÃ³ªESá'h<fÕ©ÛVÊ\nt¥éÄò`ƒ)ml…zŠÌ%QÆ£j÷®ÚÒå\$=Àºzió€i/4’[	ÅŠÈJjœ2Zöÿ.º’ƒ£oò¤<“s6¾E+_…wàHZ}•Î’0;Œ0Àöûà¡-‡¸“É`mŞŸe´¸åÉ#©‹Á²`ˆrş¨<’‘jpíÙ­g6–‚ HP&ì&\0005àˆq<\\#~	¢Í¥£-àˆŒ»3#©³¢Ø\$ã2·_ŒÉ¢Ğ`(ËÉäŒS¤Ù\0Å'8¤– ª.’Q`pØKÇ«Ì‡j&0N¤Ù~‚…Ãa÷\$›*\"ş¨°Rl0¨°Ûş¬q™òpŸ¨´îíãP7àÕ’zà\0ø`3ª«È^¶`}ÁÆ%­Ûºyı`x”:«\$•PUS6ıx<FÙ`á¨+Ğ~=AVò€\0=6ßƒÉÕ{…ŒÎ¥…u_\"ƒërïÉö\0Â¤lºîôëN@(¸D\rpÈ1+·„@UFĞ7/Mb¿»â.\$¤{®ÿC0‘`õL¤6Œ\$.×Kİ;]‹¨ƒÉm@gkªaï`²ÁPî	˜wÅ MºT6ësˆÛ»PŒp6[à^p\0/‡8ã-°NŒëğ\nÈl&AEâšºü!\$øG\n°\$¦ü¿#¹¸ATöL¦É#Şşà}vé…Œó‡ŒEã¹ái…Š0;¾,,ï¯p¸F©,Y×á¬@?Ø^[øH3† Ã¸à. JœH9Ã_\n“¯Ì*Tó}HFZpÅÿfÇîËğba’‚jŠQ™€ 0¡ÚŸ t~tX²†°äxd‘ÊÄ§†ˆ;æh®¯Œ06E¯Ó†lªºåg—AÀè-û†C\rŠ×ğ5©ÆÜOa´Ãdºà`æ'6¸kÆáµ\\ƒª	xãÉ8-FÍVp†àab—Øe@ÂÉªo†Qİ0E°f\"BÀ;*nA‡mĞ*p,;øu@×aÛ6R¿qd°ŒáêSL.l=cfğù,˜¨ë…ÈÄQf±ã!¨-Œg‡ú2„d˜ÉâÍ£&Sw;`iN`æØvâ€â\0ï\"µR– à†ÀvéÌaÕÀì!6eHQOO0êá>ø!İ¾4X‡bŠûÂ+`¸{ˆœ;uwb˜RàÈšT;ñß¦ä€CPòV#Š4)]\réÁ7¸Ól°cˆdb|Bö>—Ä…ˆÎÙKŸìHxbÄˆı\rõãŠ@¬ÇÚÄ—l¦Bè%,8’öbYŠ¼¯IÚæ%KÒÄé3Ä Û	öaæöä/q!¸½q\rƒh2^&èÅÎÅ‹bt\0ÚìQ[+ê±8ÃŠpÈ|ÄòYÏN'‘\0À™A6aaÄü²,x%À\$8hAÎây·õq¬nRAM@áÄğ°Òñ¬RôñqCâ\"\n“}&ˆœbğˆwŠòíÍtptvºi¾b²@Å|¦§<W¬B¡fâ½`ÔÅ?%'tWÌ/í¨°©ÅdÅmŠÑı“›LObÿq`H¤Ä5œÏş#ìèŞ]ñF\r.èş+*ÆøoBñ8EÅ¨İ£>WÁÑ~1O¿ÁŠÙ+<¬ğ#²³Àâ¤Ñ\0ôî0ÄØ3Øºƒ`X\r@ñŒŞ00¸]±yœÅÔpD¦Ã«`apRâüŒ[‚ÌÄ+uP Ì@;:û-Î³Ë\0f8¥eÑc‹lU¹v0áe6\0ÜbÜµ±Œ=ş®¬bøÄÖÓ‡4àÑ…­ (¥\$0yšGç‚û0<¼d@›ã+#èAK”o°#.H1œÄjë‘PÜg/ğQ‘G§–S5%VèÒb­t…Ÿ–±\0N×‚®TLJÜ±¡ÁÆ‰8JØpÁpEm°„r¼¤5`9öM¶C\0_°ª'¨\$ªÀ#¸–E!\\ŸaÀ)•âs ˜¡Û‚çÒ1¸‹\0¦–—›rìn¤Ïq¦€eÆÕˆ7°\n¯Nt#Yf`˜¡™äÁ@Fq¿­íÆ÷L­z4Ô¼o¸Ñ%õ¨õàÚ[ÚZ¢˜Ä\"ÆgáÆ‹qÔMIVé¿ë¦f)<]¢§Æo!'Ê—@V˜Ï¦ö\0");}elseif($_GET["file"]=="logo.png"){header("Content-Type: image/png");echo"‰PNG\r\n\n\0\0\0\rIHDR\0\0\09\0\0\09\0\0\0~6¶\0\0\0000PLTE\0\0\0ƒ—­+NvYt“s‰£®¾´¾ÌÈÒÚü‘üsuüIJ÷ÓÔü/.üü¯±úüúC¥×\0\0\0tRNS\0@æØf\0\0\0	pHYs\0\0\0\0\0šœ\0\0´IDAT8Õ”ÍNÂ@ÇûEáìlÏ¶õ¤p6ˆG.\$=£¥Ç>á	w5r}‚z7²>€‘På#\$Œ³K¡j«7üİ¶¿ÌÎÌ?4m•„ˆÑ÷t&î~À3!0“0Šš^„½Af0Ş\"å½í,Êğ* ç4¼Œâo¥Eè³è×X(*YÓó¼¸	6	ïPcOW¢ÉÎÜŠm’¬rƒ0Ã~/ áL¨\rXj#ÖmÊÁújÀC€]G¦mæ\0¶}ŞË¬ß‘u¼A9ÀX£\nÔØ8¼V±YÄ+ÇD#¨iqŞnKQ8Jà1Q6²æY0§`•ŸP³bQ\\h”~>ó:pSÉ€£¦¼¢ØóGEõQ=îIÏ{’*Ÿ3ë2£7÷\neÊLèBŠ~Ğ/R(\$°)Êç‹ —ÁHQn€i•6J¶	<×-.–wÇÉªjêVm«êüm¿?SŞH ›vÃÌûñÆ©§İ\0àÖ^Õq«¶)ª—Û]÷‹U¹92Ñ,;ÿÇî'pøµ£!XËƒäÚÜÿLñD.»tÃ¦—ı/wÃÓäìR÷	w­dÓÖr2ïÆ¤ª4[=½E5÷S+ñ—c\0\0\0\0IEND®B`‚";}exit;}if($_GET["script"]=="version"){$o=get_temp_dir()."/adminer.version";@unlink($o);$q=file_open_lock($o);if($q)file_write_unlock($q,serialize(array("signature"=>$_POST["signature"],"version"=>$_POST["version"])));exit;}if(!$_SERVER["REQUEST_URI"])$_SERVER["REQUEST_URI"]=$_SERVER["ORIG_PATH_INFO"];if(!strpos($_SERVER["REQUEST_URI"],'?')&&$_SERVER["QUERY_STRING"]!="")$_SERVER["REQUEST_URI"].="?$_SERVER[QUERY_STRING]";if($_SERVER["HTTP_X_FORWARDED_PREFIX"])$_SERVER["REQUEST_URI"]=$_SERVER["HTTP_X_FORWARDED_PREFIX"].$_SERVER["REQUEST_URI"];define('Adminer\HTTPS',($_SERVER["HTTPS"]&&strcasecmp($_SERVER["HTTPS"],"off"))||ini_bool("session.cookie_secure"));@ini_set("session.use_trans_sid",'0');if(!defined("SID")){session_cache_limiter("");session_name("adminer_sid");session_set_cookie_params(0,preg_replace('~\?.*~','',$_SERVER["REQUEST_URI"]),"",HTTPS,true);session_start();}remove_slashes(array(&$_GET,&$_POST,&$_COOKIE),$ad);if(function_exists("get_magic_quotes_runtime")&&get_magic_quotes_runtime())set_magic_quotes_runtime(false);@set_time_limit(0);@ini_set("precision",'15');function
lang($u,$Jf=null){$ua=func_get_args();$ua[0]=$u;return
call_user_func_array('Adminer\lang_format',$ua);}function
lang_format($dj,$Jf=null){if(is_array($dj)){$Ng=($Jf==1?0:1);$dj=$dj[$Ng];}$dj=str_replace("'",'â€™',$dj);$ua=func_get_args();array_shift($ua);$md=str_replace("%d","%s",$dj);if($md!=$dj)$ua[0]=format_number($Jf);return
vsprintf($md,$ua);}define('Adminer\LANG','en');abstract
class
SqlDb{static$instance;var$extension;var$flavor='';var$server_info;var$affected_rows=0;var$info='';var$errno=0;var$error='';protected$multi;abstract
function
attach($N,$V,$F);abstract
function
quote($Q);abstract
function
select_db($Nb);abstract
function
query($H,$oj=false);function
multi_query($H){return$this->multi=$this->query($H);}function
store_result(){return$this->multi;}function
next_result(){return
false;}}if(extension_loaded('pdo')){abstract
class
PdoDb
extends
SqlDb{protected$pdo;function
dsn($nc,$V,$F,array$bg=array()){$bg[\PDO::ATTR_ERRMODE]=\PDO::ERRMODE_SILENT;$bg[\PDO::ATTR_STATEMENT_CLASS]=array('Adminer\PdoResult');try{$this->pdo=new
\PDO($nc,$V,$F,$bg);}catch(\Exception$Hc){return$Hc->getMessage();}$this->server_info=@$this->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);return'';}function
quote($Q){return$this->pdo->quote($Q);}function
query($H,$oj=false){$I=$this->pdo->query($H);$this->error="";if(!$I){list(,$this->errno,$this->error)=$this->pdo->errorInfo();if(!$this->error)$this->error='Unknown error.';return
false;}$this->store_result($I);return$I;}function
store_result($I=null){if(!$I){$I=$this->multi;if(!$I)return
false;}if($I->columnCount()){$I->num_rows=$I->rowCount();return$I;}$this->affected_rows=$I->rowCount();return
true;}function
next_result(){$I=$this->multi;if(!is_object($I))return
false;$I->_offset=0;return@$I->nextRowset();}}class
PdoResult
extends
\PDOStatement{var$_offset=0,$num_rows;function
fetch_assoc(){return$this->fetch_array(\PDO::FETCH_ASSOC);}function
fetch_row(){return$this->fetch_array(\PDO::FETCH_NUM);}private
function
fetch_array($tf){$J=$this->fetch($tf);return($J?array_map(array($this,'unresource'),$J):$J);}private
function
unresource($X){return(is_resource($X)?stream_get_contents($X):$X);}function
fetch_field(){$K=(object)$this->getColumnMeta($this->_offset++);$U=$K->pdo_type;$K->type=($U==\PDO::PARAM_INT?0:15);$K->charsetnr=($U==\PDO::PARAM_LOB||(isset($K->flags)&&in_array("blob",(array)$K->flags))?63:0);return$K;}function
seek($C){for($s=0;$s<$C;$s++)$this->fetch();}}}function
add_driver($t,$B){SqlDriver::$drivers[$t]=$B;}function
get_driver($t){return
SqlDriver::$drivers[$t];}abstract
class
SqlDriver{static$instance;static$drivers=array();static$extensions=array();static$jush;protected$conn;protected$types=array();var$insertFunctions=array();var$editFunctions=array();var$unsigned=array();var$operators=array();var$functions=array();var$grouping=array();var$onActions="RESTRICT|NO ACTION|CASCADE|SET NULL|SET DEFAULT";var$partitionBy=array();var$inout="IN|OUT|INOUT";var$enumLength="'(?:''|[^'\\\\]|\\\\.)*'";var$generated=array();static
function
connect($N,$V,$F){$f=new
Db;return($f->attach($N,$V,$F)?:$f);}function
__construct(Db$f){$this->conn=$f;}function
types(){return
call_user_func_array('array_merge',array_values($this->types));}function
structuredTypes(){return
array_map('array_keys',$this->types);}function
enumLength(array$m){}function
unconvertFunction(array$m){}function
select($R,array$M,array$Z,array$wd,array$dg=array(),$z=1,$D=0,$Wg=false){$ue=(count($wd)<count($M));$H=adminer()->selectQueryBuild($M,$Z,$wd,$dg,$z,$D);if(!$H)$H="SELECT".limit(($_GET["page"]!="last"&&$z&&$wd&&$ue&&JUSH=="sql"?"SQL_CALC_FOUND_ROWS ":"").implode(", ",$M)."\nFROM ".table($R),($Z?"\nWHERE ".implode(" AND ",$Z):"").($wd&&$ue?"\nGROUP BY ".implode(", ",$wd):"").($dg?"\nORDER BY ".implode(", ",$dg):""),$z,($D?$z*$D:0),"\n");$oi=microtime(true);$J=$this->conn->query($H);if($Wg)echo
adminer()->selectQuery($H,$oi,!$J);return$J;}function
delete($R,$fh,$z=0){$H="FROM ".table($R);return
queries("DELETE".($z?limit1($R,$H,$fh):" $H$fh"));}function
update($R,array$O,$fh,$z=0,$Rh="\n"){$Ij=array();foreach($O
as$x=>$X)$Ij[]="$x = $X";$H=table($R)." SET$Rh".implode(",$Rh",$Ij);return
queries("UPDATE".($z?limit1($R,$H,$fh,$Rh):" $H$fh"));}function
insert($R,array$O){return
queries("INSERT INTO ".table($R).($O?" (".implode(", ",array_keys($O)).")\nVALUES (".implode(", ",$O).")":" DEFAULT VALUES").$this->insertReturning($R));}function
insertReturning($R){return"";}function
insertUpdate($R,array$L,array$G){return
false;}function
begin(){return
queries("BEGIN");}function
commit(){return
queries("COMMIT");}function
rollback(){return
queries("ROLLBACK");}function
slowQuery($H,$Qi){}function
convertSearch($u,array$X,array$m){return$u;}function
value($X,array$m){return(method_exists($this->conn,'value')?$this->conn->value($X,$m):$X);}function
quoteBinary($Dh){return
q($Dh);}function
warnings(){}function
tableHelp($B,$ye=false){}function
inheritsFrom($R){return
array();}function
inheritedTables($R){return
array();}function
partitionsInfo($R){return
array();}function
hasCStyleEscapes(){return
false;}function
engines(){return
array();}function
supportsIndex(array$S){return!is_view($S);}function
indexAlgorithms(array$yi){return
array();}function
checkConstraints($R){return
get_key_vals("SELECT c.CONSTRAINT_NAME, CHECK_CLAUSE
FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS c
JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t ON c.CONSTRAINT_SCHEMA = t.CONSTRAINT_SCHEMA AND c.CONSTRAINT_NAME = t.CONSTRAINT_NAME
WHERE c.CONSTRAINT_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
AND t.TABLE_NAME = ".q($R)."
AND CHECK_CLAUSE NOT LIKE '% IS NOT NULL'",$this->conn);}function
allFields(){$J=array();if(DB!=""){foreach(get_rows("SELECT TABLE_NAME AS tab, COLUMN_NAME AS field, IS_NULLABLE AS nullable, DATA_TYPE AS type, CHARACTER_MAXIMUM_LENGTH AS length".(JUSH=='sql'?", COLUMN_KEY = 'PRI' AS `primary`":"")."
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
ORDER BY TABLE_NAME, ORDINAL_POSITION",$this->conn)as$K){$K["null"]=($K["nullable"]=="YES");$J[$K["tab"]][]=$K;}}return$J;}}add_driver("sqlite","SQLite");if(isset($_GET["sqlite"])){define('Adminer\DRIVER',"sqlite");if(class_exists("SQLite3")&&$_GET["ext"]!="pdo"){abstract
class
SqliteDb
extends
SqlDb{var$extension="SQLite3";private$link;function
attach($o,$V,$F){$this->link=new
\SQLite3($o);$Lj=$this->link->version();$this->server_info=$Lj["versionString"];return'';}function
query($H,$oj=false){$I=@$this->link->query($H);$this->error="";if(!$I){$this->errno=$this->link->lastErrorCode();$this->error=$this->link->lastErrorMsg();return
false;}elseif($I->numColumns())return
new
Result($I);$this->affected_rows=$this->link->changes();return
true;}function
quote($Q){return(is_utf8($Q)?"'".$this->link->escapeString($Q)."'":"x'".first(unpack('H*',$Q))."'");}}class
Result{var$num_rows;private$result,$offset=0;function
__construct($I){$this->result=$I;}function
fetch_assoc(){return$this->result->fetchArray(SQLITE3_ASSOC);}function
fetch_row(){return$this->result->fetchArray(SQLITE3_NUM);}function
fetch_field(){$d=$this->offset++;$U=$this->result->columnType($d);return(object)array("name"=>$this->result->columnName($d),"type"=>($U==SQLITE3_TEXT?15:0),"charsetnr"=>($U==SQLITE3_BLOB?63:0),);}function
__destruct(){$this->result->finalize();}}}elseif(extension_loaded("pdo_sqlite")){abstract
class
SqliteDb
extends
PdoDb{var$extension="PDO_SQLite";function
attach($o,$V,$F){return$this->dsn(DRIVER.":$o","","");}}}if(class_exists('Adminer\SqliteDb')){class
Db
extends
SqliteDb{function
attach($o,$V,$F){parent::attach($o,$V,$F);$this->query("PRAGMA foreign_keys = 1");$this->query("PRAGMA busy_timeout = 500");return'';}function
select_db($o){if(is_readable($o)&&$this->query("ATTACH ".$this->quote(preg_match("~(^[/\\\\]|:)~",$o)?$o:dirname($_SERVER["SCRIPT_FILENAME"])."/$o")." AS a"))return!self::attach($o,'','');return
false;}}}class
Driver
extends
SqlDriver{static$extensions=array("SQLite3","PDO_SQLite");static$jush="sqlite";protected$types=array(array("integer"=>0,"real"=>0,"numeric"=>0,"text"=>0,"blob"=>0));var$insertFunctions=array();var$editFunctions=array("integer|real|numeric"=>"+/-","text"=>"||",);var$operators=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL","SQL");var$functions=array("hex","length","lower","round","unixepoch","upper");var$grouping=array("avg","count","count distinct","group_concat","max","min","sum");static
function
connect($N,$V,$F){if($F!="")return'Database does not support password.';return
parent::connect(":memory:","","");}function
__construct(Db$f){parent::__construct($f);if(min_version(3.31,0,$f))$this->generated=array("STORED","VIRTUAL");}function
structuredTypes(){return
array_keys($this->types[0]);}function
insertUpdate($R,array$L,array$G){$Ij=array();foreach($L
as$O)$Ij[]="(".implode(", ",$O).")";return
queries("REPLACE INTO ".table($R)." (".implode(", ",array_keys(reset($L))).") VALUES\n".implode(",\n",$Ij));}function
tableHelp($B,$ye=false){if($B=="sqlite_sequence")return"fileformat2.html#seqtab";if($B=="sqlite_master")return"fileformat2.html#$B";}function
checkConstraints($R){preg_match_all('~ CHECK *(\( *(((?>[^()]*[^() ])|(?1))*) *\))~',get_val("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($R),0,$this->conn),$Ze);return
array_combine($Ze[2],$Ze[2]);}function
allFields(){$J=array();foreach(tables_list()as$R=>$U){foreach(fields($R)as$m)$J[$R][]=$m;}return$J;}}function
idf_escape($u){return'"'.str_replace('"','""',$u).'"';}function
table($u){return
idf_escape($u);}function
get_databases($hd){return
array();}function
limit($H,$Z,$z,$C=0,$Rh=" "){return" $H$Z".($z?$Rh."LIMIT $z".($C?" OFFSET $C":""):"");}function
limit1($R,$H,$Z,$Rh="\n"){return(preg_match('~^INTO~',$H)||get_val("SELECT sqlite_compileoption_used('ENABLE_UPDATE_DELETE_LIMIT')")?limit($H,$Z,1,0,$Rh):" $H WHERE rowid = (SELECT rowid FROM ".table($R).$Z.$Rh."LIMIT 1)");}function
db_collation($j,$jb){return
get_val("PRAGMA encoding");}function
logged_user(){return
get_current_user();}function
tables_list(){return
get_key_vals("SELECT name, type FROM sqlite_master WHERE type IN ('table', 'view') ORDER BY (name = 'sqlite_sequence'), name");}function
count_tables($i){return
array();}function
table_status($B=""){$J=array();foreach(get_rows("SELECT name AS Name, type AS Engine, 'rowid' AS Oid, '' AS Auto_increment FROM sqlite_master WHERE type IN ('table', 'view') ".($B!=""?"AND name = ".q($B):"ORDER BY name"))as$K){$K["Rows"]=get_val("SELECT COUNT(*) FROM ".idf_escape($K["Name"]));$J[$K["Name"]]=$K;}foreach(get_rows("SELECT * FROM sqlite_sequence".($B!=""?" WHERE name = ".q($B):""),null,"")as$K)$J[$K["name"]]["Auto_increment"]=$K["seq"];return$J;}function
is_view($S){return$S["Engine"]=="view";}function
fk_support($S){return!get_val("SELECT sqlite_compileoption_used('OMIT_FOREIGN_KEY')");}function
fields($R){$J=array();$G="";foreach(get_rows("PRAGMA table_".(min_version(3.31)?"x":"")."info(".table($R).")")as$K){$B=$K["name"];$U=strtolower($K["type"]);$k=$K["dflt_value"];$J[$B]=array("field"=>$B,"type"=>(preg_match('~int~i',$U)?"integer":(preg_match('~char|clob|text~i',$U)?"text":(preg_match('~blob~i',$U)?"blob":(preg_match('~real|floa|doub~i',$U)?"real":"numeric")))),"full_type"=>$U,"default"=>(preg_match("~^'(.*)'$~",$k,$A)?str_replace("''","'",$A[1]):($k=="NULL"?null:$k)),"null"=>!$K["notnull"],"privileges"=>array("select"=>1,"insert"=>1,"update"=>1,"where"=>1,"order"=>1),"primary"=>$K["pk"],);if($K["pk"]){if($G!="")$J[$G]["auto_increment"]=false;elseif(preg_match('~^integer$~i',$U))$J[$B]["auto_increment"]=true;$G=$B;}}$ii=get_val("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($R));$u='(("[^"]*+")+|[a-z0-9_]+)';preg_match_all('~'.$u.'\s+text\s+COLLATE\s+(\'[^\']+\'|\S+)~i',$ii,$Ze,PREG_SET_ORDER);foreach($Ze
as$A){$B=str_replace('""','"',preg_replace('~^"|"$~','',$A[1]));if($J[$B])$J[$B]["collation"]=trim($A[3],"'");}preg_match_all('~'.$u.'\s.*GENERATED ALWAYS AS \((.+)\) (STORED|VIRTUAL)~i',$ii,$Ze,PREG_SET_ORDER);foreach($Ze
as$A){$B=str_replace('""','"',preg_replace('~^"|"$~','',$A[1]));$J[$B]["default"]=$A[3];$J[$B]["generated"]=strtoupper($A[4]);}return$J;}function
indexes($R,$g=null){$g=connection($g);$J=array();$ii=get_val("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($R),0,$g);if(preg_match('~\bPRIMARY\s+KEY\s*\((([^)"]+|"[^"]*"|`[^`]*`)++)~i',$ii,$A)){$J[""]=array("type"=>"PRIMARY","columns"=>array(),"lengths"=>array(),"descs"=>array());preg_match_all('~((("[^"]*+")+|(?:`[^`]*+`)+)|(\S+))(\s+(ASC|DESC))?(,\s*|$)~i',$A[1],$Ze,PREG_SET_ORDER);foreach($Ze
as$A){$J[""]["columns"][]=idf_unescape($A[2]).$A[4];$J[""]["descs"][]=(preg_match('~DESC~i',$A[5])?'1':null);}}if(!$J){foreach(fields($R)as$B=>$m){if($m["primary"])$J[""]=array("type"=>"PRIMARY","columns"=>array($B),"lengths"=>array(),"descs"=>array(null));}}$mi=get_key_vals("SELECT name, sql FROM sqlite_master WHERE type = 'index' AND tbl_name = ".q($R),$g);foreach(get_rows("PRAGMA index_list(".table($R).")",$g)as$K){$B=$K["name"];$v=array("type"=>($K["unique"]?"UNIQUE":"INDEX"));$v["lengths"]=array();$v["descs"]=array();foreach(get_rows("PRAGMA index_info(".idf_escape($B).")",$g)as$Ch){$v["columns"][]=$Ch["name"];$v["descs"][]=null;}if(preg_match('~^CREATE( UNIQUE)? INDEX '.preg_quote(idf_escape($B).' ON '.idf_escape($R),'~').' \((.*)\)$~i',$mi[$B],$qh)){preg_match_all('/("[^"]*+")+( DESC)?/',$qh[2],$Ze);foreach($Ze[2]as$x=>$X){if($X)$v["descs"][$x]='1';}}if(!$J[""]||$v["type"]!="UNIQUE"||$v["columns"]!=$J[""]["columns"]||$v["descs"]!=$J[""]["descs"]||!preg_match("~^sqlite_~",$B))$J[$B]=$v;}return$J;}function
foreign_keys($R){$J=array();foreach(get_rows("PRAGMA foreign_key_list(".table($R).")")as$K){$p=&$J[$K["id"]];if(!$p)$p=$K;$p["source"][]=$K["from"];$p["target"][]=$K["to"];}return$J;}function
view($B){return
array("select"=>preg_replace('~^(?:[^`"[]+|`[^`]*`|"[^"]*")* AS\s+~iU','',get_val("SELECT sql FROM sqlite_master WHERE type = 'view' AND name = ".q($B))));}function
collations(){return(isset($_GET["create"])?get_vals("PRAGMA collation_list",1):array());}function
information_schema($j){return
false;}function
error(){return
h(connection()->error);}function
check_sqlite_name($B){$Pc="db|sdb|sqlite";if(!preg_match("~^[^\\0]*\\.($Pc)\$~",$B)){connection()->error=sprintf('Please use one of the extensions %s.',str_replace("|",", ",$Pc));return
false;}return
true;}function
create_database($j,$c){if(file_exists($j)){connection()->error='File exists.';return
false;}if(!check_sqlite_name($j))return
false;try{$_=new
Db();$_->attach($j,'','');}catch(\Exception$Hc){connection()->error=$Hc->getMessage();return
false;}$_->query('PRAGMA encoding = "UTF-8"');$_->query('CREATE TABLE adminer (i)');$_->query('DROP TABLE adminer');return
true;}function
drop_databases($i){connection()->attach(":memory:",'','');foreach($i
as$j){if(!@unlink($j)){connection()->error='File exists.';return
false;}}return
true;}function
rename_database($B,$c){if(!check_sqlite_name($B))return
false;connection()->attach(":memory:",'','');connection()->error='File exists.';return@rename(DB,$B);}function
auto_increment(){return" PRIMARY KEY AUTOINCREMENT";}function
alter_table($R,$B,$n,$jd,$ob,$xc,$c,$_a,$E){$Bj=($R==""||$jd);foreach($n
as$m){if($m[0]!=""||!$m[1]||$m[2]){$Bj=true;break;}}$b=array();$og=array();foreach($n
as$m){if($m[1]){$b[]=($Bj?$m[1]:"ADD ".implode($m[1]));if($m[0]!="")$og[$m[0]]=$m[1][0];}}if(!$Bj){foreach($b
as$X){if(!queries("ALTER TABLE ".table($R)." $X"))return
false;}if($R!=$B&&!queries("ALTER TABLE ".table($R)." RENAME TO ".table($B)))return
false;}elseif(!recreate_table($R,$B,$b,$og,$jd,$_a))return
false;if($_a){queries("BEGIN");queries("UPDATE sqlite_sequence SET seq = $_a WHERE name = ".q($B));if(!connection()->affected_rows)queries("INSERT INTO sqlite_sequence (name, seq) VALUES (".q($B).", $_a)");queries("COMMIT");}return
true;}function
recreate_table($R,$B,array$n,array$og,array$jd,$_a="",$w=array(),$jc="",$ja=""){if($R!=""){if(!$n){foreach(fields($R)as$x=>$m){if($w)$m["auto_increment"]=0;$n[]=process_field($m,$m);$og[$x]=idf_escape($x);}}$Vg=false;foreach($n
as$m){if($m[6])$Vg=true;}$lc=array();foreach($w
as$x=>$X){if($X[2]=="DROP"){$lc[$X[1]]=true;unset($w[$x]);}}foreach(indexes($R)as$Be=>$v){$e=array();foreach($v["columns"]as$x=>$d){if(!$og[$d])continue
2;$e[]=$og[$d].($v["descs"][$x]?" DESC":"");}if(!$lc[$Be]){if($v["type"]!="PRIMARY"||!$Vg)$w[]=array($v["type"],$Be,$e);}}foreach($w
as$x=>$X){if($X[0]=="PRIMARY"){unset($w[$x]);$jd[]="  PRIMARY KEY (".implode(", ",$X[2]).")";}}foreach(foreign_keys($R)as$Be=>$p){foreach($p["source"]as$x=>$d){if(!$og[$d])continue
2;$p["source"][$x]=idf_unescape($og[$d]);}if(!isset($jd[" $Be"]))$jd[]=" ".format_foreign_key($p);}queries("BEGIN");}$Ua=array();foreach($n
as$m){if(preg_match('~GENERATED~',$m[3]))unset($og[array_search($m[0],$og)]);$Ua[]="  ".implode($m);}$Ua=array_merge($Ua,array_filter($jd));foreach(driver()->checkConstraints($R)as$Wa){if($Wa!=$jc)$Ua[]="  CHECK ($Wa)";}if($ja)$Ua[]="  CHECK ($ja)";$Ki=($R==$B?"adminer_$B":$B);if(!queries("CREATE TABLE ".table($Ki)." (\n".implode(",\n",$Ua)."\n)"))return
false;if($R!=""){if($og&&!queries("INSERT INTO ".table($Ki)." (".implode(", ",$og).") SELECT ".implode(", ",array_map('Adminer\idf_escape',array_keys($og)))." FROM ".table($R)))return
false;$kj=array();foreach(triggers($R)as$ij=>$Ri){$hj=trigger($ij,$R);$kj[]="CREATE TRIGGER ".idf_escape($ij)." ".implode(" ",$Ri)." ON ".table($B)."\n$hj[Statement]";}$_a=$_a?"":get_val("SELECT seq FROM sqlite_sequence WHERE name = ".q($R));if(!queries("DROP TABLE ".table($R))||($R==$B&&!queries("ALTER TABLE ".table($Ki)." RENAME TO ".table($B)))||!alter_indexes($B,$w))return
false;if($_a)queries("UPDATE sqlite_sequence SET seq = $_a WHERE name = ".q($B));foreach($kj
as$hj){if(!queries($hj))return
false;}queries("COMMIT");}return
true;}function
index_sql($R,$U,$B,$e){return"CREATE $U ".($U!="INDEX"?"INDEX ":"").idf_escape($B!=""?$B:uniqid($R."_"))." ON ".table($R)." $e";}function
alter_indexes($R,$b){foreach($b
as$G){if($G[0]=="PRIMARY")return
recreate_table($R,$R,array(),array(),array(),"",$b);}foreach(array_reverse($b)as$X){if(!queries($X[2]=="DROP"?"DROP INDEX ".idf_escape($X[1]):index_sql($R,$X[0],$X[1],"(".implode(", ",$X[2]).")")))return
false;}return
true;}function
truncate_tables($T){return
apply_queries("DELETE FROM",$T);}function
drop_views($Nj){return
apply_queries("DROP VIEW",$Nj);}function
drop_tables($T){return
apply_queries("DROP TABLE",$T);}function
move_tables($T,$Nj,$Ii){return
false;}function
trigger($B,$R){if($B=="")return
array("Statement"=>"BEGIN\n\t;\nEND");$u='(?:[^`"\s]+|`[^`]*`|"[^"]*")+';$jj=trigger_options();preg_match("~^CREATE\\s+TRIGGER\\s*$u\\s*(".implode("|",$jj["Timing"]).")\\s+([a-z]+)(?:\\s+OF\\s+($u))?\\s+ON\\s*$u\\s*(?:FOR\\s+EACH\\s+ROW\\s)?(.*)~is",get_val("SELECT sql FROM sqlite_master WHERE type = 'trigger' AND name = ".q($B)),$A);$Lf=$A[3];return
array("Timing"=>strtoupper($A[1]),"Event"=>strtoupper($A[2]).($Lf?" OF":""),"Of"=>idf_unescape($Lf),"Trigger"=>$B,"Statement"=>$A[4],);}function
triggers($R){$J=array();$jj=trigger_options();foreach(get_rows("SELECT * FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($R))as$K){preg_match('~^CREATE\s+TRIGGER\s*(?:[^`"\s]+|`[^`]*`|"[^"]*")+\s*('.implode("|",$jj["Timing"]).')\s*(.*?)\s+ON\b~i',$K["sql"],$A);$J[$K["name"]]=array($A[1],$A[2]);}return$J;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER","INSTEAD OF"),"Event"=>array("INSERT","UPDATE","UPDATE OF","DELETE"),"Type"=>array("FOR EACH ROW"),);}function
begin(){return
queries("BEGIN");}function
last_id($I){return
get_val("SELECT LAST_INSERT_ROWID()");}function
explain($f,$H){return$f->query("EXPLAIN QUERY PLAN $H");}function
found_rows($S,$Z){}function
types(){return
array();}function
create_sql($R,$_a,$si){$J=get_val("SELECT sql FROM sqlite_master WHERE type IN ('table', 'view') AND name = ".q($R));foreach(indexes($R)as$B=>$v){if($B=='')continue;$J
.=";\n\n".index_sql($R,$v['type'],$B,"(".implode(", ",array_map('Adminer\idf_escape',$v['columns'])).")");}return$J;}function
truncate_sql($R){return"DELETE FROM ".table($R);}function
use_sql($Nb,$si=""){}function
trigger_sql($R){return
implode(get_vals("SELECT sql || ';;\n' FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($R)));}function
show_variables(){$J=array();foreach(get_rows("PRAGMA pragma_list")as$K){$B=$K["name"];if($B!="pragma_list"&&$B!="compile_options"){$J[$B]=array($B,'');foreach(get_rows("PRAGMA $B")as$K)$J[$B][1].=implode(", ",$K)."\n";}}return$J;}function
show_status(){$J=array();foreach(get_vals("PRAGMA compile_options")as$ag)$J[]=explode("=",$ag,2)+array('','');return$J;}function
convert_field($m){}function
unconvert_field($m,$J){return$J;}function
support($Uc){return
preg_match('~^(check|columns|database|drop_col|dump|indexes|descidx|move_col|sql|status|table|trigger|variables|view|view_trigger)$~',$Uc);}}add_driver("pgsql","PostgreSQL");if(isset($_GET["pgsql"])){define('Adminer\DRIVER',"pgsql");if(extension_loaded("pgsql")&&$_GET["ext"]!="pdo"){class
PgsqlDb
extends
SqlDb{var$extension="PgSQL";var$timeout=0;private$link,$string,$database=true;function
_error($Cc,$l){if(ini_bool("html_errors"))$l=html_entity_decode(strip_tags($l));$l=preg_replace('~^[^:]*: ~','',$l);$this->error=$l;}function
attach($N,$V,$F){$j=adminer()->database();set_error_handler(array($this,'_error'));list($Md,$Mg)=host_port(addcslashes($N,"'\\"));$this->string="host='$Md'".($Mg?" port='$Mg'":"")." user='".addcslashes($V,"'\\")."' password='".addcslashes($F,"'\\")."'";$ni=adminer()->connectSsl();if(isset($ni["mode"]))$this->string
.=" sslmode='".$ni["mode"]."'";$this->link=@pg_connect("$this->string dbname='".($j!=""?addcslashes($j,"'\\"):"postgres")."'",PGSQL_CONNECT_FORCE_NEW);if(!$this->link&&$j!=""){$this->database=false;$this->link=@pg_connect("$this->string dbname='postgres'",PGSQL_CONNECT_FORCE_NEW);}restore_error_handler();if($this->link)pg_set_client_encoding($this->link,"UTF8");return($this->link?'':$this->error);}function
quote($Q){return(function_exists('pg_escape_literal')?pg_escape_literal($this->link,$Q):"'".pg_escape_string($this->link,$Q)."'");}function
value($X,array$m){return($m["type"]=="bytea"&&$X!==null?pg_unescape_bytea($X):$X);}function
select_db($Nb){if($Nb==adminer()->database())return$this->database;$J=@pg_connect("$this->string dbname='".addcslashes($Nb,"'\\")."'",PGSQL_CONNECT_FORCE_NEW);if($J)$this->link=$J;return$J;}function
close(){$this->link=@pg_connect("$this->string dbname='postgres'");}function
query($H,$oj=false){$I=@pg_query($this->link,$H);$this->error="";if(!$I){$this->error=pg_last_error($this->link);$J=false;}elseif(!pg_num_fields($I)){$this->affected_rows=pg_affected_rows($I);$J=true;}else$J=new
Result($I);if($this->timeout){$this->timeout=0;$this->query("RESET statement_timeout");}return$J;}function
warnings(){return
h(pg_last_notice($this->link));}function
copyFrom($R,array$L){$this->error='';set_error_handler(function($Cc,$l){$this->error=(ini_bool('html_errors')?html_entity_decode($l):$l);return
true;});$J=pg_copy_from($this->link,$R,$L);restore_error_handler();return$J;}}class
Result{var$num_rows;private$result,$offset=0;function
__construct($I){$this->result=$I;$this->num_rows=pg_num_rows($I);}function
fetch_assoc(){return
pg_fetch_assoc($this->result);}function
fetch_row(){return
pg_fetch_row($this->result);}function
fetch_field(){$d=$this->offset++;$J=new
\stdClass;$J->orgtable=pg_field_table($this->result,$d);$J->name=pg_field_name($this->result,$d);$U=pg_field_type($this->result,$d);$J->type=(preg_match(number_type(),$U)?0:15);$J->charsetnr=($U=="bytea"?63:0);return$J;}function
__destruct(){pg_free_result($this->result);}}}elseif(extension_loaded("pdo_pgsql")){class
PgsqlDb
extends
PdoDb{var$extension="PDO_PgSQL";var$timeout=0;function
attach($N,$V,$F){$j=adminer()->database();list($Md,$Mg)=host_port(addcslashes($N,"'\\"));$nc="pgsql:host='$Md'".($Mg?" port='$Mg'":"")." client_encoding=utf8 dbname='".($j!=""?addcslashes($j,"'\\"):"postgres")."'";$ni=adminer()->connectSsl();if(isset($ni["mode"]))$nc
.=" sslmode='".$ni["mode"]."'";return$this->dsn($nc,$V,$F);}function
select_db($Nb){return(adminer()->database()==$Nb);}function
query($H,$oj=false){$J=parent::query($H,$oj);if($this->timeout){$this->timeout=0;parent::query("RESET statement_timeout");}return$J;}function
warnings(){}function
copyFrom($R,array$L){$J=$this->pdo->pgsqlCopyFromArray($R,$L);$this->error=idx($this->pdo->errorInfo(),2)?:'';return$J;}function
close(){}}}if(class_exists('Adminer\PgsqlDb')){class
Db
extends
PgsqlDb{function
multi_query($H){if(preg_match('~\bCOPY\s+(.+?)\s+FROM\s+stdin;\n?(.*)\n\\\\\.$~is',str_replace("\r\n","\n",$H),$A)){$L=explode("\n",$A[2]);$this->affected_rows=count($L);return$this->copyFrom($A[1],$L);}return
parent::multi_query($H);}}}class
Driver
extends
SqlDriver{static$extensions=array("PgSQL","PDO_PgSQL");static$jush="pgsql";var$operators=array("=","<",">","<=",">=","!=","~","!~","LIKE","LIKE %%","ILIKE","ILIKE %%","IN","IS NULL","NOT LIKE","NOT ILIKE","NOT IN","IS NOT NULL","SQL");var$functions=array("char_length","lower","round","to_hex","to_timestamp","upper");var$grouping=array("avg","count","count distinct","max","min","sum");var$nsOid="(SELECT oid FROM pg_namespace WHERE nspname = current_schema())";static
function
connect($N,$V,$F){$f=parent::connect($N,$V,$F);if(is_string($f))return$f;$Lj=get_val("SELECT version()",0,$f);$f->flavor=(preg_match('~CockroachDB~',$Lj)?'cockroach':'');$f->server_info=preg_replace('~^\D*([\d.]+[-\w]*).*~','\1',$Lj);if(min_version(9,0,$f))$f->query("SET application_name = 'Adminer'");if($f->flavor=='cockroach')add_driver(DRIVER,"CockroachDB");return$f;}function
__construct(Db$f){parent::__construct($f);$this->types=array('Numbers'=>array("smallint"=>5,"integer"=>10,"bigint"=>19,"boolean"=>1,"numeric"=>0,"real"=>7,"double precision"=>16,"money"=>20),'Date and time'=>array("date"=>13,"time"=>17,"timestamp"=>20,"timestamptz"=>21,"interval"=>0),'Strings'=>array("character"=>0,"character varying"=>0,"text"=>0,"tsquery"=>0,"tsvector"=>0,"uuid"=>0,"xml"=>0),'Binary'=>array("bit"=>0,"bit varying"=>0,"bytea"=>0),'Network'=>array("cidr"=>43,"inet"=>43,"macaddr"=>17,"macaddr8"=>23,"txid_snapshot"=>0),'Geometry'=>array("box"=>0,"circle"=>0,"line"=>0,"lseg"=>0,"path"=>0,"point"=>0,"polygon"=>0),);if(min_version(9.2,0,$f)){$this->types['Strings']["json"]=4294967295;if(min_version(9.4,0,$f))$this->types['Strings']["jsonb"]=4294967295;}$this->insertFunctions=array("char"=>"md5","date|time"=>"now",);$this->editFunctions=array(number_type()=>"+/-","date|time"=>"+ interval/- interval","char|text"=>"||",);if(min_version(12,0,$f))$this->generated=array("STORED");$this->partitionBy=array("RANGE","LIST");if(!$f->flavor)$this->partitionBy[]="HASH";}function
enumLength(array$m){$zc=$this->types['User types'][$m["type"]];return($zc?type_values($zc):"");}function
setUserTypes($nj){$this->types['User types']=array_flip($nj);}function
insertReturning($R){$_a=array_filter(fields($R),function($m){return$m['auto_increment'];});return(count($_a)==1?" RETURNING ".idf_escape(key($_a)):"");}function
insertUpdate($R,array$L,array$G){foreach($L
as$O){$wj=array();$Z=array();foreach($O
as$x=>$X){$wj[]="$x = $X";if(isset($G[idf_unescape($x)]))$Z[]="$x = $X";}if(!(($Z&&queries("UPDATE ".table($R)." SET ".implode(", ",$wj)." WHERE ".implode(" AND ",$Z))&&$this->conn->affected_rows)||queries("INSERT INTO ".table($R)." (".implode(", ",array_keys($O)).") VALUES (".implode(", ",$O).")")))return
false;}return
true;}function
slowQuery($H,$Qi){$this->conn->query("SET statement_timeout = ".(1000*$Qi));$this->conn->timeout=1000*$Qi;return$H;}function
convertSearch($u,array$X,array$m){$Ni="char|text";if(strpos($X["op"],"LIKE")===false)$Ni
.="|date|time(stamp)?|boolean|uuid|inet|cidr|macaddr|".number_type();return(preg_match("~$Ni~",$m["type"])?$u:"CAST($u AS text)");}function
quoteBinary($Dh){return"'\\x".bin2hex($Dh)."'";}function
warnings(){return$this->conn->warnings();}function
tableHelp($B,$ye=false){$Re=array("information_schema"=>"infoschema","pg_catalog"=>($ye?"view":"catalog"),);$_=$Re[$_GET["ns"]];if($_)return"$_-".str_replace("_","-",$B).".html";}function
inheritsFrom($R){return
get_vals("SELECT relname FROM pg_class JOIN pg_inherits ON inhparent = oid WHERE inhrelid = ".$this->tableOid($R)." ORDER BY 1");}function
inheritedTables($R){return
get_vals("SELECT relname FROM pg_inherits JOIN pg_class ON inhrelid = oid WHERE inhparent = ".$this->tableOid($R)." ORDER BY 1");}function
partitionsInfo($R){$K=(min_version(10)?$this->conn->query("SELECT * FROM pg_partitioned_table WHERE partrelid = ".$this->tableOid($R))->fetch_assoc():null);if($K){$ya=get_vals("SELECT attname FROM pg_attribute WHERE attrelid = $K[partrelid] AND attnum IN (".str_replace(" ",", ",$K["partattrs"]).")");$Oa=array('h'=>'HASH','l'=>'LIST','r'=>'RANGE');return
array("partition_by"=>$Oa[$K["partstrat"]],"partition"=>implode(", ",array_map('Adminer\idf_escape',$ya)),);}return
array();}function
tableOid($R){return"(SELECT oid FROM pg_class WHERE relnamespace = $this->nsOid AND relname = ".q($R)." AND relkind IN ('r', 'm', 'v', 'f', 'p'))";}function
indexAlgorithms(array$yi){static$J=array();if(!$J)$J=get_vals("SELECT amname FROM pg_am".(min_version(9.6)?" WHERE amtype = 'i'":"")." ORDER BY amname = '".($this->conn->flavor=='cockroach'?"prefix":"btree")."' DESC, amname");return$J;}function
supportsIndex(array$S){return$S["Engine"]!="view";}function
hasCStyleEscapes(){static$Qa;if($Qa===null)$Qa=(get_val("SHOW standard_conforming_strings",0,$this->conn)=="off");return$Qa;}}function
idf_escape($u){return'"'.str_replace('"','""',$u).'"';}function
table($u){return
idf_escape($u);}function
get_databases($hd){return
get_vals("SELECT datname FROM pg_database
WHERE datallowconn = TRUE AND has_database_privilege(datname, 'CONNECT')
ORDER BY datname");}function
limit($H,$Z,$z,$C=0,$Rh=" "){return" $H$Z".($z?$Rh."LIMIT $z".($C?" OFFSET $C":""):"");}function
limit1($R,$H,$Z,$Rh="\n"){return(preg_match('~^INTO~',$H)?limit($H,$Z,1,0,$Rh):" $H".(is_view(table_status1($R))?$Z:$Rh."WHERE ctid = (SELECT ctid FROM ".table($R).$Z.$Rh."LIMIT 1)"));}function
db_collation($j,$jb){return
get_val("SELECT datcollate FROM pg_database WHERE datname = ".q($j));}function
logged_user(){return
get_val("SELECT user");}function
tables_list(){$H="SELECT table_name, table_type FROM information_schema.tables WHERE table_schema = current_schema()";if(support("materializedview"))$H
.="
UNION ALL
SELECT matviewname, 'MATERIALIZED VIEW'
FROM pg_matviews
WHERE schemaname = current_schema()";$H
.="
ORDER BY 1";return
get_key_vals($H);}function
count_tables($i){$J=array();foreach($i
as$j){if(connection()->select_db($j))$J[$j]=count(tables_list());}return$J;}function
table_status($B=""){static$Fd;if($Fd===null)$Fd=get_val("SELECT 'pg_table_size'::regproc");$J=array();foreach(get_rows("SELECT
	relname AS \"Name\",
	CASE relkind WHEN 'v' THEN 'view' WHEN 'm' THEN 'materialized view' ELSE 'table' END AS \"Engine\"".($Fd?",
	pg_table_size(c.oid) AS \"Data_length\",
	pg_indexes_size(c.oid) AS \"Index_length\"":"").",
	obj_description(c.oid, 'pg_class') AS \"Comment\",
	".(min_version(12)?"''":"CASE WHEN relhasoids THEN 'oid' ELSE '' END")." AS \"Oid\",
	reltuples AS \"Rows\",
	".(min_version(10)?"relispartition::int AS partition,":"")."
	current_schema() AS nspname
FROM pg_class c
WHERE relkind IN ('r', 'm', 'v', 'f', 'p')
AND relnamespace = ".driver()->nsOid."
".($B!=""?"AND relname = ".q($B):"ORDER BY relname"))as$K)$J[$K["Name"]]=$K;return$J;}function
is_view($S){return
in_array($S["Engine"],array("view","materialized view"));}function
fk_support($S){return
true;}function
fields($R){$J=array();$ra=array('timestamp without time zone'=>'timestamp','timestamp with time zone'=>'timestamptz',);foreach(get_rows("SELECT
	a.attname AS field,
	format_type(a.atttypid, a.atttypmod) AS full_type,
	pg_get_expr(d.adbin, d.adrelid) AS default,
	a.attnotnull::int,
	col_description(a.attrelid, a.attnum) AS comment".(min_version(10)?",
	a.attidentity".(min_version(12)?",
	a.attgenerated":""):"")."
FROM pg_attribute a
LEFT JOIN pg_attrdef d ON a.attrelid = d.adrelid AND a.attnum = d.adnum
WHERE a.attrelid = ".driver()->tableOid($R)."
AND NOT a.attisdropped
AND a.attnum > 0
ORDER BY a.attnum")as$K){preg_match('~([^([]+)(\((.*)\))?([a-z ]+)?((\[[0-9]*])*)$~',$K["full_type"],$A);list(,$U,$y,$K["length"],$ka,$va)=$A;$K["length"].=$va;$Ya=$U.$ka;if(isset($ra[$Ya])){$K["type"]=$ra[$Ya];$K["full_type"]=$K["type"].$y.$va;}else{$K["type"]=$U;$K["full_type"]=$K["type"].$y.$ka.$va;}if(in_array($K['attidentity'],array('a','d')))$K['default']='GENERATED '.($K['attidentity']=='d'?'BY DEFAULT':'ALWAYS').' AS IDENTITY';$K["generated"]=($K["attgenerated"]=="s"?"STORED":"");$K["null"]=!$K["attnotnull"];$K["auto_increment"]=$K['attidentity']||preg_match('~^nextval\(~i',$K["default"])||preg_match('~^unique_rowid\(~',$K["default"]);$K["privileges"]=array("insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1);if(preg_match('~(.+)::[^,)]+(.*)~',$K["default"],$A))$K["default"]=($A[1]=="NULL"?null:idf_unescape($A[1]).$A[2]);$J[$K["field"]]=$K;}return$J;}function
indexes($R,$g=null){$g=connection($g);$J=array();$Ai=driver()->tableOid($R);$e=get_key_vals("SELECT attnum, attname FROM pg_attribute WHERE attrelid = $Ai AND attnum > 0",$g);foreach(get_rows("SELECT relname, indisunique::int, indisprimary::int, indkey, indoption, amname, pg_get_expr(indpred, indrelid, true) AS partial, pg_get_expr(indexprs, indrelid) AS indexpr
FROM pg_index
JOIN pg_class ON indexrelid = oid
JOIN pg_am ON pg_am.oid = pg_class.relam
WHERE indrelid = $Ai
ORDER BY indisprimary DESC, indisunique DESC",$g)as$K){$rh=$K["relname"];$J[$rh]["type"]=($K["partial"]?"INDEX":($K["indisprimary"]?"PRIMARY":($K["indisunique"]?"UNIQUE":"INDEX")));$J[$rh]["columns"]=array();$J[$rh]["descs"]=array();$J[$rh]["algorithm"]=$K["amname"];$J[$rh]["partial"]=$K["partial"];$ee=preg_split('~(?<=\)), (?=\()~',$K["indexpr"]);foreach(explode(" ",$K["indkey"])as$fe)$J[$rh]["columns"][]=($fe?$e[$fe]:array_shift($ee));foreach(explode(" ",$K["indoption"])as$ge)$J[$rh]["descs"][]=(intval($ge)&1?'1':null);$J[$rh]["lengths"]=array();}return$J;}function
foreign_keys($R){$J=array();foreach(get_rows("SELECT conname, condeferrable::int AS deferrable, pg_get_constraintdef(oid) AS definition
FROM pg_constraint
WHERE conrelid = ".driver()->tableOid($R)."
AND contype = 'f'::char
ORDER BY conkey, conname")as$K){if(preg_match('~FOREIGN KEY\s*\((.+)\)\s*REFERENCES (.+)\((.+)\)(.*)$~iA',$K['definition'],$A)){$K['source']=array_map('Adminer\idf_unescape',array_map('trim',explode(',',$A[1])));if(preg_match('~^(("([^"]|"")+"|[^"]+)\.)?"?("([^"]|"")+"|[^"]+)$~',$A[2],$Xe)){$K['ns']=idf_unescape($Xe[2]);$K['table']=idf_unescape($Xe[4]);}$K['target']=array_map('Adminer\idf_unescape',array_map('trim',explode(',',$A[3])));$K['on_delete']=(preg_match("~ON DELETE (".driver()->onActions.")~",$A[4],$Xe)?$Xe[1]:'NO ACTION');$K['on_update']=(preg_match("~ON UPDATE (".driver()->onActions.")~",$A[4],$Xe)?$Xe[1]:'NO ACTION');$J[$K['conname']]=$K;}}return$J;}function
view($B){return
array("select"=>trim(get_val("SELECT pg_get_viewdef(".driver()->tableOid($B).")")));}function
collations(){return
array();}function
information_schema($j){return
get_schema()=="information_schema";}function
error(){$J=h(connection()->error);if(preg_match('~^(.*\n)?([^\n]*)\n( *)\^(\n.*)?$~s',$J,$A))$J=$A[1].preg_replace('~((?:[^&]|&[^;]*;){'.strlen($A[3]).'})(.*)~','\1<b>\2</b>',$A[2]).$A[4];return
nl_br($J);}function
create_database($j,$c){return
queries("CREATE DATABASE ".idf_escape($j).($c?" ENCODING ".idf_escape($c):""));}function
drop_databases($i){connection()->close();return
apply_queries("DROP DATABASE",$i,'Adminer\idf_escape');}function
rename_database($B,$c){connection()->close();return
queries("ALTER DATABASE ".idf_escape(DB)." RENAME TO ".idf_escape($B));}function
auto_increment(){return"";}function
alter_table($R,$B,$n,$jd,$ob,$xc,$c,$_a,$E){$b=array();$eh=array();if($R!=""&&$R!=$B)$eh[]="ALTER TABLE ".table($R)." RENAME TO ".table($B);$Sh="";foreach($n
as$m){$d=idf_escape($m[0]);$X=$m[1];if(!$X)$b[]="DROP $d";else{$Hj=$X[5];unset($X[5]);if($m[0]==""){if(isset($X[6]))$X[1]=($X[1]==" bigint"?" big":($X[1]==" smallint"?" small":" "))."serial";$b[]=($R!=""?"ADD ":"  ").implode($X);if(isset($X[6]))$b[]=($R!=""?"ADD":" ")." PRIMARY KEY ($X[0])";}else{if($d!=$X[0])$eh[]="ALTER TABLE ".table($B)." RENAME $d TO $X[0]";$b[]="ALTER $d TYPE$X[1]";$Th=$R."_".idf_unescape($X[0])."_seq";$b[]="ALTER $d ".($X[3]?"SET".preg_replace('~GENERATED ALWAYS(.*) STORED~','EXPRESSION\1',$X[3]):(isset($X[6])?"SET DEFAULT nextval(".q($Th).")":"DROP DEFAULT"));if(isset($X[6]))$Sh="CREATE SEQUENCE IF NOT EXISTS ".idf_escape($Th)." OWNED BY ".idf_escape($R).".$X[0]";$b[]="ALTER $d ".($X[2]==" NULL"?"DROP NOT":"SET").$X[2];}if($m[0]!=""||$Hj!="")$eh[]="COMMENT ON COLUMN ".table($B).".$X[0] IS ".($Hj!=""?substr($Hj,9):"''");}}$b=array_merge($b,$jd);if($R==""){$P="";if($E){$eb=(connection()->flavor=='cockroach');$P=" PARTITION BY $E[partition_by]($E[partition])";if($E["partition_by"]=='HASH'){$Cg=+$E["partitions"];for($s=0;$s<$Cg;$s++)$eh[]="CREATE TABLE ".idf_escape($B."_$s")." PARTITION OF ".idf_escape($B)." FOR VALUES WITH (MODULUS $Cg, REMAINDER $s)";}else{$Ug="MINVALUE";foreach($E["partition_names"]as$s=>$X){$Y=$E["partition_values"][$s];$zg=" VALUES ".($E["partition_by"]=='LIST'?"IN ($Y)":"FROM ($Ug) TO ($Y)");if($eb)$P
.=($s?",":" (")."\n  PARTITION ".(preg_match('~^DEFAULT$~i',$X)?$X:idf_escape($X))."$zg";else$eh[]="CREATE TABLE ".idf_escape($B."_$X")." PARTITION OF ".idf_escape($B)." FOR$zg";$Ug=$Y;}$P
.=($eb?"\n)":"");}}array_unshift($eh,"CREATE TABLE ".table($B)." (\n".implode(",\n",$b)."\n)$P");}elseif($b)array_unshift($eh,"ALTER TABLE ".table($R)."\n".implode(",\n",$b));if($Sh)array_unshift($eh,$Sh);if($ob!==null)$eh[]="COMMENT ON TABLE ".table($B)." IS ".q($ob);foreach($eh
as$H){if(!queries($H))return
false;}return
true;}function
alter_indexes($R,$b){$h=array();$ic=array();$eh=array();foreach($b
as$X){if($X[0]!="INDEX")$h[]=($X[2]=="DROP"?"\nDROP CONSTRAINT ".idf_escape($X[1]):"\nADD".($X[1]!=""?" CONSTRAINT ".idf_escape($X[1]):"")." $X[0] ".($X[0]=="PRIMARY"?"KEY ":"")."(".implode(", ",$X[2]).")");elseif($X[2]=="DROP")$ic[]=idf_escape($X[1]);else$eh[]="CREATE INDEX ".idf_escape($X[1]!=""?$X[1]:uniqid($R."_"))." ON ".table($R).($X[3]?" USING $X[3]":"")." (".implode(", ",$X[2]).")".($X[4]?" WHERE $X[4]":"");}if($h)array_unshift($eh,"ALTER TABLE ".table($R).implode(",",$h));if($ic)array_unshift($eh,"DROP INDEX ".implode(", ",$ic));foreach($eh
as$H){if(!queries($H))return
false;}return
true;}function
truncate_tables($T){return
queries("TRUNCATE ".implode(", ",array_map('Adminer\table',$T)));}function
drop_views($Nj){return
drop_tables($Nj);}function
drop_tables($T){foreach($T
as$R){$P=table_status1($R);if(!queries("DROP ".strtoupper($P["Engine"])." ".table($R)))return
false;}return
true;}function
move_tables($T,$Nj,$Ii){foreach(array_merge($T,$Nj)as$R){$P=table_status1($R);if(!queries("ALTER ".strtoupper($P["Engine"])." ".table($R)." SET SCHEMA ".idf_escape($Ii)))return
false;}return
true;}function
trigger($B,$R){if($B=="")return
array("Statement"=>"EXECUTE PROCEDURE ()");$e=array();$Z="WHERE trigger_schema = current_schema() AND event_object_table = ".q($R)." AND trigger_name = ".q($B);foreach(get_rows("SELECT * FROM information_schema.triggered_update_columns $Z")as$K)$e[]=$K["event_object_column"];$J=array();foreach(get_rows('SELECT trigger_name AS "Trigger", action_timing AS "Timing", event_manipulation AS "Event", \'FOR EACH \' || action_orientation AS "Type", action_statement AS "Statement"
FROM information_schema.triggers'."
$Z
ORDER BY event_manipulation DESC")as$K){if($e&&$K["Event"]=="UPDATE")$K["Event"].=" OF";$K["Of"]=implode(", ",$e);if($J)$K["Event"].=" OR $J[Event]";$J=$K;}return$J;}function
triggers($R){$J=array();foreach(get_rows("SELECT * FROM information_schema.triggers WHERE trigger_schema = current_schema() AND event_object_table = ".q($R))as$K){$hj=trigger($K["trigger_name"],$R);$J[$hj["Trigger"]]=array($hj["Timing"],$hj["Event"]);}return$J;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER"),"Event"=>array("INSERT","UPDATE","UPDATE OF","DELETE","INSERT OR UPDATE","INSERT OR UPDATE OF","DELETE OR INSERT","DELETE OR UPDATE","DELETE OR UPDATE OF","DELETE OR INSERT OR UPDATE","DELETE OR INSERT OR UPDATE OF"),"Type"=>array("FOR EACH ROW","FOR EACH STATEMENT"),);}function
routine($B,$U){$L=get_rows('SELECT routine_definition AS definition, LOWER(external_language) AS language, *
FROM information_schema.routines
WHERE routine_schema = current_schema() AND specific_name = '.q($B));$J=idx($L,0,array());$J["returns"]=array("type"=>$J["type_udt_name"]);$J["fields"]=get_rows('SELECT COALESCE(parameter_name, ordinal_position::text) AS field, data_type AS type, character_maximum_length AS length, parameter_mode AS inout
FROM information_schema.parameters
WHERE specific_schema = current_schema() AND specific_name = '.q($B).'
ORDER BY ordinal_position');return$J;}function
routines(){return
get_rows('SELECT specific_name AS "SPECIFIC_NAME", routine_type AS "ROUTINE_TYPE", routine_name AS "ROUTINE_NAME", type_udt_name AS "DTD_IDENTIFIER"
FROM information_schema.routines
WHERE routine_schema = current_schema()
ORDER BY SPECIFIC_NAME');}function
routine_languages(){return
get_vals("SELECT LOWER(lanname) FROM pg_catalog.pg_language");}function
routine_id($B,$K){$J=array();foreach($K["fields"]as$m){$y=$m["length"];$J[]=$m["type"].($y?"($y)":"");}return
idf_escape($B)."(".implode(", ",$J).")";}function
last_id($I){$K=(is_object($I)?$I->fetch_row():array());return($K?$K[0]:0);}function
explain($f,$H){return$f->query("EXPLAIN $H");}function
found_rows($S,$Z){if(preg_match("~ rows=([0-9]+)~",get_val("EXPLAIN SELECT * FROM ".idf_escape($S["Name"]).($Z?" WHERE ".implode(" AND ",$Z):"")),$qh))return$qh[1];}function
types(){return
get_key_vals("SELECT oid, typname
FROM pg_type
WHERE typnamespace = ".driver()->nsOid."
AND typtype IN ('b','d','e')
AND typelem = 0");}function
type_values($t){$Bc=get_vals("SELECT enumlabel FROM pg_enum WHERE enumtypid = $t ORDER BY enumsortorder");return($Bc?"'".implode("', '",array_map('addslashes',$Bc))."'":"");}function
schemas(){return
get_vals("SELECT nspname FROM pg_namespace ORDER BY nspname");}function
get_schema(){return
get_val("SELECT current_schema()");}function
set_schema($Fh,$g=null){if(!$g)$g=connection();$J=$g->query("SET search_path TO ".idf_escape($Fh));driver()->setUserTypes(types());return$J;}function
foreign_keys_sql($R){$J="";$P=table_status1($R);$fd=foreign_keys($R);ksort($fd);foreach($fd
as$ed=>$dd)$J
.="ALTER TABLE ONLY ".idf_escape($P['nspname']).".".idf_escape($P['Name'])." ADD CONSTRAINT ".idf_escape($ed)." $dd[definition] ".($dd['deferrable']?'DEFERRABLE':'NOT DEFERRABLE').";\n";return($J?"$J\n":$J);}function
create_sql($R,$_a,$si){$wh=array();$Uh=array();$P=table_status1($R);if(is_view($P)){$Mj=view($R);return
rtrim("CREATE VIEW ".idf_escape($R)." AS $Mj[select]",";");}$n=fields($R);if(count($P)<2||empty($n))return
false;$J="CREATE TABLE ".idf_escape($P['nspname']).".".idf_escape($P['Name'])." (\n    ";foreach($n
as$m){$xg=idf_escape($m['field']).' '.$m['full_type'].default_value($m).($m['null']?"":" NOT NULL");$wh[]=$xg;if(preg_match('~nextval\(\'([^\']+)\'\)~',$m['default'],$Ze)){$Th=$Ze[1];$hi=first(get_rows((min_version(10)?"SELECT *, cache_size AS cache_value FROM pg_sequences WHERE schemaname = current_schema() AND sequencename = ".q(idf_unescape($Th)):"SELECT * FROM $Th"),null,"-- "));$Uh[]=($si=="DROP+CREATE"?"DROP SEQUENCE IF EXISTS $Th;\n":"")."CREATE SEQUENCE $Th INCREMENT $hi[increment_by] MINVALUE $hi[min_value] MAXVALUE $hi[max_value]".($_a&&$hi['last_value']?" START ".($hi["last_value"]+1):"")." CACHE $hi[cache_value];";}}if(!empty($Uh))$J=implode("\n\n",$Uh)."\n\n$J";$G="";foreach(indexes($R)as$ce=>$v){if($v['type']=='PRIMARY'){$G=$ce;$wh[]="CONSTRAINT ".idf_escape($ce)." PRIMARY KEY (".implode(', ',array_map('Adminer\idf_escape',$v['columns'])).")";}}foreach(driver()->checkConstraints($R)as$ub=>$wb)$wh[]="CONSTRAINT ".idf_escape($ub)." CHECK $wb";$J
.=implode(",\n    ",$wh)."\n)";$zg=driver()->partitionsInfo($P['Name']);if($zg)$J
.="\nPARTITION BY $zg[partition_by]($zg[partition])";$J
.="\nWITH (oids = ".($P['Oid']?'true':'false').");";if($P['Comment'])$J
.="\n\nCOMMENT ON TABLE ".idf_escape($P['nspname']).".".idf_escape($P['Name'])." IS ".q($P['Comment']).";";foreach($n
as$Wc=>$m){if($m['comment'])$J
.="\n\nCOMMENT ON COLUMN ".idf_escape($P['nspname']).".".idf_escape($P['Name']).".".idf_escape($Wc)." IS ".q($m['comment']).";";}foreach(get_rows("SELECT indexdef FROM pg_catalog.pg_indexes WHERE schemaname = current_schema() AND tablename = ".q($R).($G?" AND indexname != ".q($G):""),null,"-- ")as$K)$J
.="\n\n$K[indexdef];";return
rtrim($J,';');}function
truncate_sql($R){return"TRUNCATE ".table($R);}function
trigger_sql($R){$P=table_status1($R);$J="";foreach(triggers($R)as$gj=>$fj){$hj=trigger($gj,$P['Name']);$J
.="\nCREATE TRIGGER ".idf_escape($hj['Trigger'])." $hj[Timing] $hj[Event] ON ".idf_escape($P["nspname"]).".".idf_escape($P['Name'])." $hj[Type] $hj[Statement];;\n";}return$J;}function
use_sql($Nb,$si=""){$B=idf_escape($Nb);$J="";if(preg_match('~CREATE~',$si)){if($si=="DROP+CREATE")$J="DROP DATABASE IF EXISTS $B;\n";$J
.="CREATE DATABASE $B;\n";}return"$J\\connect $B";}function
show_variables(){return
get_rows("SHOW ALL");}function
process_list(){return
get_rows("SELECT * FROM pg_stat_activity ORDER BY ".(min_version(9.2)?"pid":"procpid"));}function
convert_field($m){}function
unconvert_field($m,$J){return$J;}function
support($Uc){return
preg_match('~^(check|columns|comment|database|drop_col|dump|descidx|indexes|kill|partial_indexes|routine|scheme|sequence|sql|table|trigger|type|variables|view'.(min_version(9.3)?'|materializedview':'').(min_version(11)?'|procedure':'').(connection()->flavor=='cockroach'?'':'|processlist').')$~',$Uc);}function
kill_process($X){return
queries("SELECT pg_terminate_backend(".number($X).")");}function
connection_id(){return"SELECT pg_backend_pid()";}function
max_connections(){return
get_val("SHOW max_connections");}}add_driver("oracle","Oracle (beta)");if(isset($_GET["oracle"])){define('Adminer\DRIVER',"oracle");if(extension_loaded("oci8")&&$_GET["ext"]!="pdo"){class
Db
extends
SqlDb{var$extension="oci8";var$_current_db;private$link;function
_error($Cc,$l){if(ini_bool("html_errors"))$l=html_entity_decode(strip_tags($l));$l=preg_replace('~^[^:]*: ~','',$l);$this->error=$l;}function
attach($N,$V,$F){$this->link=@oci_new_connect($V,$F,$N,"AL32UTF8");if($this->link){$this->server_info=oci_server_version($this->link);return'';}$l=oci_error();return$l["message"];}function
quote($Q){return"'".str_replace("'","''",$Q)."'";}function
select_db($Nb){$this->_current_db=$Nb;return
true;}function
query($H,$oj=false){$I=oci_parse($this->link,$H);$this->error="";if(!$I){$l=oci_error($this->link);$this->errno=$l["code"];$this->error=$l["message"];return
false;}set_error_handler(array($this,'_error'));$J=@oci_execute($I);restore_error_handler();if($J){if(oci_num_fields($I))return
new
Result($I);$this->affected_rows=oci_num_rows($I);oci_free_statement($I);}return$J;}function
timeout($uf){return
oci_set_call_timeout($this->link,$uf);}}class
Result{var$num_rows;private$result,$offset=1;function
__construct($I){$this->result=$I;}private
function
convert($K){foreach((array)$K
as$x=>$X){if(is_a($X,'OCILob')||is_a($X,'OCI-Lob'))$K[$x]=$X->load();}return$K;}function
fetch_assoc(){return$this->convert(oci_fetch_assoc($this->result));}function
fetch_row(){return$this->convert(oci_fetch_row($this->result));}function
fetch_field(){$d=$this->offset++;$J=new
\stdClass;$J->name=oci_field_name($this->result,$d);$J->type=oci_field_type($this->result,$d);$J->charsetnr=(preg_match("~raw|blob|bfile~",$J->type)?63:0);return$J;}function
__destruct(){oci_free_statement($this->result);}}}elseif(extension_loaded("pdo_oci")){class
Db
extends
PdoDb{var$extension="PDO_OCI";var$_current_db;function
attach($N,$V,$F){return$this->dsn("oci:dbname=//$N;charset=AL32UTF8",$V,$F);}function
select_db($Nb){$this->_current_db=$Nb;return
true;}}}class
Driver
extends
SqlDriver{static$extensions=array("OCI8","PDO_OCI");static$jush="oracle";var$insertFunctions=array("date"=>"current_date","timestamp"=>"current_timestamp",);var$editFunctions=array("number|float|double"=>"+/-","date|timestamp"=>"+ interval/- interval","char|clob"=>"||",);var$operators=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL","SQL");var$functions=array("length","lower","round","upper");var$grouping=array("avg","count","count distinct","max","min","sum");function
__construct(Db$f){parent::__construct($f);$this->types=array('Numbers'=>array("number"=>38,"binary_float"=>12,"binary_double"=>21),'Date and time'=>array("date"=>10,"timestamp"=>29,"interval year"=>12,"interval day"=>28),'Strings'=>array("char"=>2000,"varchar2"=>4000,"nchar"=>2000,"nvarchar2"=>4000,"clob"=>4294967295,"nclob"=>4294967295),'Binary'=>array("raw"=>2000,"long raw"=>2147483648,"blob"=>4294967295,"bfile"=>4294967296),);}function
begin(){return
true;}function
insertUpdate($R,array$L,array$G){foreach($L
as$O){$wj=array();$Z=array();foreach($O
as$x=>$X){$wj[]="$x = $X";if(isset($G[idf_unescape($x)]))$Z[]="$x = $X";}if(!(($Z&&queries("UPDATE ".table($R)." SET ".implode(", ",$wj)." WHERE ".implode(" AND ",$Z))&&$this->conn->affected_rows)||queries("INSERT INTO ".table($R)." (".implode(", ",array_keys($O)).") VALUES (".implode(", ",$O).")")))return
false;}return
true;}function
hasCStyleEscapes(){return
true;}}function
idf_escape($u){return'"'.str_replace('"','""',$u).'"';}function
table($u){return
idf_escape($u);}function
get_databases($hd){return
get_vals("SELECT DISTINCT tablespace_name FROM (
SELECT tablespace_name FROM user_tablespaces
UNION SELECT tablespace_name FROM all_tables WHERE tablespace_name IS NOT NULL
)
ORDER BY 1");}function
limit($H,$Z,$z,$C=0,$Rh=" "){return($C?" * FROM (SELECT t.*, rownum AS rnum FROM (SELECT $H$Z) t WHERE rownum <= ".($z+$C).") WHERE rnum > $C":($z?" * FROM (SELECT $H$Z) WHERE rownum <= ".($z+$C):" $H$Z"));}function
limit1($R,$H,$Z,$Rh="\n"){return" $H$Z";}function
db_collation($j,$jb){return
get_val("SELECT value FROM nls_database_parameters WHERE parameter = 'NLS_CHARACTERSET'");}function
logged_user(){return
get_val("SELECT USER FROM DUAL");}function
get_current_db(){$j=connection()->_current_db?:DB;unset(connection()->_current_db);return$j;}function
where_owner($Sg,$rg="owner"){if(!$_GET["ns"])return'';return"$Sg$rg = sys_context('USERENV', 'CURRENT_SCHEMA')";}function
views_table($e){$rg=where_owner('');return"(SELECT $e FROM all_views WHERE ".($rg?:"rownum < 0").")";}function
tables_list(){$Mj=views_table("view_name");$rg=where_owner(" AND ");return
get_key_vals("SELECT table_name, 'table' FROM all_tables WHERE tablespace_name = ".q(DB)."$rg
UNION SELECT view_name, 'view' FROM $Mj
ORDER BY 1");}function
count_tables($i){$J=array();foreach($i
as$j)$J[$j]=get_val("SELECT COUNT(*) FROM all_tables WHERE tablespace_name = ".q($j));return$J;}function
table_status($B=""){$J=array();$Kh=q($B);$j=get_current_db();$Mj=views_table("view_name");$rg=where_owner(" AND ");foreach(get_rows('SELECT table_name "Name", \'table\' "Engine", avg_row_len * num_rows "Data_length", num_rows "Rows" FROM all_tables WHERE tablespace_name = '.q($j).$rg.($B!=""?" AND table_name = $Kh":"")."
UNION SELECT view_name, 'view', 0, 0 FROM $Mj".($B!=""?" WHERE view_name = $Kh":"")."
ORDER BY 1")as$K)$J[$K["Name"]]=$K;return$J;}function
is_view($S){return$S["Engine"]=="view";}function
fk_support($S){return
true;}function
fields($R){$J=array();$rg=where_owner(" AND ");foreach(get_rows("SELECT * FROM all_tab_columns WHERE table_name = ".q($R)."$rg ORDER BY column_id")as$K){$U=$K["DATA_TYPE"];$y="$K[DATA_PRECISION],$K[DATA_SCALE]";if($y==",")$y=$K["CHAR_COL_DECL_LENGTH"];$J[$K["COLUMN_NAME"]]=array("field"=>$K["COLUMN_NAME"],"full_type"=>$U.($y?"($y)":""),"type"=>strtolower($U),"length"=>$y,"default"=>$K["DATA_DEFAULT"],"null"=>($K["NULLABLE"]=="Y"),"privileges"=>array("insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1),);}return$J;}function
indexes($R,$g=null){$J=array();$rg=where_owner(" AND ","aic.table_owner");foreach(get_rows("SELECT aic.*, ac.constraint_type, atc.data_default
FROM all_ind_columns aic
LEFT JOIN all_constraints ac ON aic.index_name = ac.constraint_name AND aic.table_name = ac.table_name AND aic.index_owner = ac.owner
LEFT JOIN all_tab_cols atc ON aic.column_name = atc.column_name AND aic.table_name = atc.table_name AND aic.index_owner = atc.owner
WHERE aic.table_name = ".q($R)."$rg
ORDER BY ac.constraint_type, aic.column_position",$g)as$K){$ce=$K["INDEX_NAME"];$lb=$K["DATA_DEFAULT"];$lb=($lb?trim($lb,'"'):$K["COLUMN_NAME"]);$J[$ce]["type"]=($K["CONSTRAINT_TYPE"]=="P"?"PRIMARY":($K["CONSTRAINT_TYPE"]=="U"?"UNIQUE":"INDEX"));$J[$ce]["columns"][]=$lb;$J[$ce]["lengths"][]=($K["CHAR_LENGTH"]&&$K["CHAR_LENGTH"]!=$K["COLUMN_LENGTH"]?$K["CHAR_LENGTH"]:null);$J[$ce]["descs"][]=($K["DESCEND"]&&$K["DESCEND"]=="DESC"?'1':null);}return$J;}function
view($B){$Mj=views_table("view_name, text");$L=get_rows('SELECT text "select" FROM '.$Mj.' WHERE view_name = '.q($B));return
reset($L);}function
collations(){return
array();}function
information_schema($j){return
get_schema()=="INFORMATION_SCHEMA";}function
error(){return
h(connection()->error);}function
explain($f,$H){$f->query("EXPLAIN PLAN FOR $H");return$f->query("SELECT * FROM plan_table");}function
found_rows($S,$Z){}function
auto_increment(){return"";}function
alter_table($R,$B,$n,$jd,$ob,$xc,$c,$_a,$E){$b=$ic=array();$kg=($R?fields($R):array());foreach($n
as$m){$X=$m[1];if($X&&$m[0]!=""&&idf_escape($m[0])!=$X[0])queries("ALTER TABLE ".table($R)." RENAME COLUMN ".idf_escape($m[0])." TO $X[0]");$jg=$kg[$m[0]];if($X&&$jg){$Nf=process_field($jg,$jg);if($X[2]==$Nf[2])$X[2]="";}if($X)$b[]=($R!=""?($m[0]!=""?"MODIFY (":"ADD ("):"  ").implode($X).($R!=""?")":"");else$ic[]=idf_escape($m[0]);}if($R=="")return
queries("CREATE TABLE ".table($B)." (\n".implode(",\n",$b)."\n)");return(!$b||queries("ALTER TABLE ".table($R)."\n".implode("\n",$b)))&&(!$ic||queries("ALTER TABLE ".table($R)." DROP (".implode(", ",$ic).")"))&&($R==$B||queries("ALTER TABLE ".table($R)." RENAME TO ".table($B)));}function
alter_indexes($R,$b){$ic=array();$eh=array();foreach($b
as$X){if($X[0]!="INDEX"){$X[2]=preg_replace('~ DESC$~','',$X[2]);$h=($X[2]=="DROP"?"\nDROP CONSTRAINT ".idf_escape($X[1]):"\nADD".($X[1]!=""?" CONSTRAINT ".idf_escape($X[1]):"")." $X[0] ".($X[0]=="PRIMARY"?"KEY ":"")."(".implode(", ",$X[2]).")");array_unshift($eh,"ALTER TABLE ".table($R).$h);}elseif($X[2]=="DROP")$ic[]=idf_escape($X[1]);else$eh[]="CREATE INDEX ".idf_escape($X[1]!=""?$X[1]:uniqid($R."_"))." ON ".table($R)." (".implode(", ",$X[2]).")";}if($ic)array_unshift($eh,"DROP INDEX ".implode(", ",$ic));foreach($eh
as$H){if(!queries($H))return
false;}return
true;}function
foreign_keys($R){$J=array();$H="SELECT c_list.CONSTRAINT_NAME as NAME,
c_src.COLUMN_NAME as SRC_COLUMN,
c_dest.OWNER as DEST_DB,
c_dest.TABLE_NAME as DEST_TABLE,
c_dest.COLUMN_NAME as DEST_COLUMN,
c_list.DELETE_RULE as ON_DELETE
FROM ALL_CONSTRAINTS c_list, ALL_CONS_COLUMNS c_src, ALL_CONS_COLUMNS c_dest
WHERE c_list.CONSTRAINT_NAME = c_src.CONSTRAINT_NAME
AND c_list.R_CONSTRAINT_NAME = c_dest.CONSTRAINT_NAME
AND c_list.CONSTRAINT_TYPE = 'R'
AND c_src.TABLE_NAME = ".q($R);foreach(get_rows($H)as$K)$J[$K['NAME']]=array("db"=>$K['DEST_DB'],"table"=>$K['DEST_TABLE'],"source"=>array($K['SRC_COLUMN']),"target"=>array($K['DEST_COLUMN']),"on_delete"=>$K['ON_DELETE'],"on_update"=>null,);return$J;}function
truncate_tables($T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views($Nj){return
apply_queries("DROP VIEW",$Nj);}function
drop_tables($T){return
apply_queries("DROP TABLE",$T);}function
last_id($I){return
0;}function
schemas(){$J=get_vals("SELECT DISTINCT owner FROM dba_segments WHERE owner IN (SELECT username FROM dba_users WHERE default_tablespace NOT IN ('SYSTEM','SYSAUX')) ORDER BY 1");return($J?:get_vals("SELECT DISTINCT owner FROM all_tables WHERE tablespace_name = ".q(DB)." ORDER BY 1"));}function
get_schema(){return
get_val("SELECT sys_context('USERENV', 'SESSION_USER') FROM dual");}function
set_schema($Hh,$g=null){if(!$g)$g=connection();return$g->query("ALTER SESSION SET CURRENT_SCHEMA = ".idf_escape($Hh));}function
show_variables(){return
get_rows('SELECT name, display_value FROM v$parameter');}function
show_status(){$J=array();$L=get_rows('SELECT * FROM v$instance');foreach(reset($L)as$x=>$X)$J[]=array($x,$X);return$J;}function
process_list(){return
get_rows('SELECT
	sess.process AS "process",
	sess.username AS "user",
	sess.schemaname AS "schema",
	sess.status AS "status",
	sess.wait_class AS "wait_class",
	sess.seconds_in_wait AS "seconds_in_wait",
	sql.sql_text AS "sql_text",
	sess.machine AS "machine",
	sess.port AS "port"
FROM v$session sess LEFT OUTER JOIN v$sql sql
ON sql.sql_id = sess.sql_id
WHERE sess.type = \'USER\'
ORDER BY PROCESS
');}function
convert_field($m){}function
unconvert_field($m,$J){return$J;}function
support($Uc){return
preg_match('~^(columns|database|drop_col|indexes|descidx|processlist|scheme|sql|status|table|variables|view)$~',$Uc);}}add_driver("mssql","MS SQL");if(isset($_GET["mssql"])){define('Adminer\DRIVER',"mssql");if(extension_loaded("sqlsrv")&&$_GET["ext"]!="pdo"){class
Db
extends
SqlDb{var$extension="sqlsrv";private$link,$result;private
function
get_error(){$this->error="";foreach(sqlsrv_errors()as$l){$this->errno=$l["code"];$this->error
.="$l[message]\n";}$this->error=rtrim($this->error);}function
attach($N,$V,$F){$vb=array("UID"=>$V,"PWD"=>$F,"CharacterSet"=>"UTF-8");$ni=adminer()->connectSsl();if(isset($ni["Encrypt"]))$vb["Encrypt"]=$ni["Encrypt"];if(isset($ni["TrustServerCertificate"]))$vb["TrustServerCertificate"]=$ni["TrustServerCertificate"];$j=adminer()->database();if($j!="")$vb["Database"]=$j;list($Md,$Mg)=host_port($N);$this->link=@sqlsrv_connect($Md.($Mg?",$Mg":""),$vb);if($this->link){$he=sqlsrv_server_info($this->link);$this->server_info=$he['SQLServerVersion'];}else$this->get_error();return($this->link?'':$this->error);}function
quote($Q){$pj=strlen($Q)!=strlen(utf8_decode($Q));return($pj?"N":"")."'".str_replace("'","''",$Q)."'";}function
select_db($Nb){return$this->query(use_sql($Nb));}function
query($H,$oj=false){$I=sqlsrv_query($this->link,$H);$this->error="";if(!$I){$this->get_error();return
false;}return$this->store_result($I);}function
multi_query($H){$this->result=sqlsrv_query($this->link,$H);$this->error="";if(!$this->result){$this->get_error();return
false;}return
true;}function
store_result($I=null){if(!$I)$I=$this->result;if(!$I)return
false;if(sqlsrv_field_metadata($I))return
new
Result($I);$this->affected_rows=sqlsrv_rows_affected($I);return
true;}function
next_result(){return$this->result?!!sqlsrv_next_result($this->result):false;}}class
Result{var$num_rows;private$result,$offset=0,$fields;function
__construct($I){$this->result=$I;}private
function
convert($K){foreach((array)$K
as$x=>$X){if(is_a($X,'DateTime'))$K[$x]=$X->format("Y-m-d H:i:s");}return$K;}function
fetch_assoc(){return$this->convert(sqlsrv_fetch_array($this->result,SQLSRV_FETCH_ASSOC));}function
fetch_row(){return$this->convert(sqlsrv_fetch_array($this->result,SQLSRV_FETCH_NUMERIC));}function
fetch_field(){if(!$this->fields)$this->fields=sqlsrv_field_metadata($this->result);$m=$this->fields[$this->offset++];$J=new
\stdClass;$J->name=$m["Name"];$J->type=($m["Type"]==1?254:15);$J->charsetnr=0;return$J;}function
seek($C){for($s=0;$s<$C;$s++)sqlsrv_fetch($this->result);}function
__destruct(){sqlsrv_free_stmt($this->result);}}function
last_id($I){return
get_val("SELECT SCOPE_IDENTITY()");}function
explain($f,$H){$f->query("SET SHOWPLAN_ALL ON");$J=$f->query($H);$f->query("SET SHOWPLAN_ALL OFF");return$J;}}else{abstract
class
MssqlDb
extends
PdoDb{function
select_db($Nb){return$this->query(use_sql($Nb));}function
lastInsertId(){return$this->pdo->lastInsertId();}}function
last_id($I){return
connection()->lastInsertId();}function
explain($f,$H){}if(extension_loaded("pdo_sqlsrv")){class
Db
extends
MssqlDb{var$extension="PDO_SQLSRV";function
attach($N,$V,$F){list($Md,$Mg)=host_port($N);return$this->dsn("sqlsrv:Server=$Md".($Mg?",$Mg":""),$V,$F);}}}elseif(extension_loaded("pdo_dblib")){class
Db
extends
MssqlDb{var$extension="PDO_DBLIB";function
attach($N,$V,$F){list($Md,$Mg)=host_port($N);return$this->dsn("dblib:charset=utf8;host=$Md".($Mg?(is_numeric($Mg)?";port=":";unix_socket=").$Mg:""),$V,$F);}}}}class
Driver
extends
SqlDriver{static$extensions=array("SQLSRV","PDO_SQLSRV","PDO_DBLIB");static$jush="mssql";var$insertFunctions=array("date|time"=>"getdate");var$editFunctions=array("int|decimal|real|float|money|datetime"=>"+/-","char|text"=>"+",);var$operators=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL");var$functions=array("len","lower","round","upper");var$grouping=array("avg","count","count distinct","max","min","sum");var$generated=array("PERSISTED","VIRTUAL");var$onActions="NO ACTION|CASCADE|SET NULL|SET DEFAULT";static
function
connect($N,$V,$F){if($N=="")$N="localhost:1433";return
parent::connect($N,$V,$F);}function
__construct(Db$f){parent::__construct($f);$this->types=array('Numbers'=>array("tinyint"=>3,"smallint"=>5,"int"=>10,"bigint"=>20,"bit"=>1,"decimal"=>0,"real"=>12,"float"=>53,"smallmoney"=>10,"money"=>20),'Date and time'=>array("date"=>10,"smalldatetime"=>19,"datetime"=>19,"datetime2"=>19,"time"=>8,"datetimeoffset"=>10),'Strings'=>array("char"=>8000,"varchar"=>8000,"text"=>2147483647,"nchar"=>4000,"nvarchar"=>4000,"ntext"=>1073741823),'Binary'=>array("binary"=>8000,"varbinary"=>8000,"image"=>2147483647),);}function
insertUpdate($R,array$L,array$G){$n=fields($R);$wj=array();$Z=array();$O=reset($L);$e="c".implode(", c",range(1,count($O)));$Pa=0;$ne=array();foreach($O
as$x=>$X){$Pa++;$B=idf_unescape($x);if(!$n[$B]["auto_increment"])$ne[$x]="c$Pa";if(isset($G[$B]))$Z[]="$x = c$Pa";else$wj[]="$x = c$Pa";}$Ij=array();foreach($L
as$O)$Ij[]="(".implode(", ",$O).")";if($Z){$Rd=queries("SET IDENTITY_INSERT ".table($R)." ON");$J=queries("MERGE ".table($R)." USING (VALUES\n\t".implode(",\n\t",$Ij)."\n) AS source ($e) ON ".implode(" AND ",$Z).($wj?"\nWHEN MATCHED THEN UPDATE SET ".implode(", ",$wj):"")."\nWHEN NOT MATCHED THEN INSERT (".implode(", ",array_keys($Rd?$O:$ne)).") VALUES (".($Rd?$e:implode(", ",$ne)).");");if($Rd)queries("SET IDENTITY_INSERT ".table($R)." OFF");}else$J=queries("INSERT INTO ".table($R)." (".implode(", ",array_keys($O)).") VALUES\n".implode(",\n",$Ij));return$J;}function
begin(){return
queries("BEGIN TRANSACTION");}function
tableHelp($B,$ye=false){$Re=array("sys"=>"catalog-views/sys-","INFORMATION_SCHEMA"=>"information-schema-views/",);$_=$Re[get_schema()];if($_)return"relational-databases/system-$_".preg_replace('~_~','-',strtolower($B))."-transact-sql";}}function
idf_escape($u){return"[".str_replace("]","]]",$u)."]";}function
table($u){return($_GET["ns"]!=""?idf_escape($_GET["ns"]).".":"").idf_escape($u);}function
get_databases($hd){return
get_vals("SELECT name FROM sys.databases WHERE name NOT IN ('master', 'tempdb', 'model', 'msdb')");}function
limit($H,$Z,$z,$C=0,$Rh=" "){return($z?" TOP (".($z+$C).")":"")." $H$Z";}function
limit1($R,$H,$Z,$Rh="\n"){return
limit($H,$Z,1,0,$Rh);}function
db_collation($j,$jb){return
get_val("SELECT collation_name FROM sys.databases WHERE name = ".q($j));}function
logged_user(){return
get_val("SELECT SUSER_NAME()");}function
tables_list(){return
get_key_vals("SELECT name, type_desc FROM sys.all_objects WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') ORDER BY name");}function
count_tables($i){$J=array();foreach($i
as$j){connection()->select_db($j);$J[$j]=get_val("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES");}return$J;}function
table_status($B=""){$J=array();foreach(get_rows("SELECT ao.name AS Name, ao.type_desc AS Engine, (SELECT value FROM fn_listextendedproperty(default, 'SCHEMA', schema_name(schema_id), 'TABLE', ao.name, null, null)) AS Comment
FROM sys.all_objects AS ao
WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') ".($B!=""?"AND name = ".q($B):"ORDER BY name"))as$K)$J[$K["Name"]]=$K;return$J;}function
is_view($S){return$S["Engine"]=="VIEW";}function
fk_support($S){return
true;}function
fields($R){$qb=get_key_vals("SELECT objname, cast(value as varchar(max)) FROM fn_listextendedproperty('MS_DESCRIPTION', 'schema', ".q(get_schema()).", 'table', ".q($R).", 'column', NULL)");$J=array();$zi=get_val("SELECT object_id FROM sys.all_objects WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') AND name = ".q($R));foreach(get_rows("SELECT c.max_length, c.precision, c.scale, c.name, c.is_nullable, c.is_identity, c.collation_name, t.name type, d.definition [default], d.name default_constraint, i.is_primary_key
FROM sys.all_columns c
JOIN sys.types t ON c.user_type_id = t.user_type_id
LEFT JOIN sys.default_constraints d ON c.default_object_id = d.object_id
LEFT JOIN sys.index_columns ic ON c.object_id = ic.object_id AND c.column_id = ic.column_id
LEFT JOIN sys.indexes i ON ic.object_id = i.object_id AND ic.index_id = i.index_id
WHERE c.object_id = ".q($zi))as$K){$U=$K["type"];$y=(preg_match("~char|binary~",$U)?intval($K["max_length"])/($U[0]=='n'?2:1):($U=="decimal"?"$K[precision],$K[scale]":""));$J[$K["name"]]=array("field"=>$K["name"],"full_type"=>$U.($y?"($y)":""),"type"=>$U,"length"=>$y,"default"=>(preg_match("~^\('(.*)'\)$~",$K["default"],$A)?str_replace("''","'",$A[1]):$K["default"]),"default_constraint"=>$K["default_constraint"],"null"=>$K["is_nullable"],"auto_increment"=>$K["is_identity"],"collation"=>$K["collation_name"],"privileges"=>array("insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1),"primary"=>$K["is_primary_key"],"comment"=>$qb[$K["name"]],);}foreach(get_rows("SELECT * FROM sys.computed_columns WHERE object_id = ".q($zi))as$K){$J[$K["name"]]["generated"]=($K["is_persisted"]?"PERSISTED":"VIRTUAL");$J[$K["name"]]["default"]=$K["definition"];}return$J;}function
indexes($R,$g=null){$J=array();foreach(get_rows("SELECT i.name, key_ordinal, is_unique, is_primary_key, c.name AS column_name, is_descending_key
FROM sys.indexes i
INNER JOIN sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
INNER JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id
WHERE OBJECT_NAME(i.object_id) = ".q($R),$g)as$K){$B=$K["name"];$J[$B]["type"]=($K["is_primary_key"]?"PRIMARY":($K["is_unique"]?"UNIQUE":"INDEX"));$J[$B]["lengths"]=array();$J[$B]["columns"][$K["key_ordinal"]]=$K["column_name"];$J[$B]["descs"][$K["key_ordinal"]]=($K["is_descending_key"]?'1':null);}return$J;}function
view($B){return
array("select"=>preg_replace('~^(?:[^[]|\[[^]]*])*\s+AS\s+~isU','',get_val("SELECT VIEW_DEFINITION FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_SCHEMA = SCHEMA_NAME() AND TABLE_NAME = ".q($B))));}function
collations(){$J=array();foreach(get_vals("SELECT name FROM fn_helpcollations()")as$c)$J[preg_replace('~_.*~','',$c)][]=$c;return$J;}function
information_schema($j){return
get_schema()=="INFORMATION_SCHEMA";}function
error(){return
nl_br(h(preg_replace('~^(\[[^]]*])+~m','',connection()->error)));}function
create_database($j,$c){return
queries("CREATE DATABASE ".idf_escape($j).(preg_match('~^[a-z0-9_]+$~i',$c)?" COLLATE $c":""));}function
drop_databases($i){return
queries("DROP DATABASE ".implode(", ",array_map('Adminer\idf_escape',$i)));}function
rename_database($B,$c){if(preg_match('~^[a-z0-9_]+$~i',$c))queries("ALTER DATABASE ".idf_escape(DB)." COLLATE $c");queries("ALTER DATABASE ".idf_escape(DB)." MODIFY NAME = ".idf_escape($B));return
true;}function
auto_increment(){return" IDENTITY".($_POST["Auto_increment"]!=""?"(".number($_POST["Auto_increment"]).",1)":"")." PRIMARY KEY";}function
alter_table($R,$B,$n,$jd,$ob,$xc,$c,$_a,$E){$b=array();$qb=array();$kg=fields($R);foreach($n
as$m){$d=idf_escape($m[0]);$X=$m[1];if(!$X)$b["DROP"][]=" COLUMN $d";else{$X[1]=preg_replace("~( COLLATE )'(\\w+)'~",'\1\2',$X[1]);$qb[$m[0]]=$X[5];unset($X[5]);if(preg_match('~ AS ~',$X[3]))unset($X[1],$X[2]);if($m[0]=="")$b["ADD"][]="\n  ".implode("",$X).($R==""?substr($jd[$X[0]],16+strlen($X[0])):"");else{$k=$X[3];unset($X[3]);unset($X[6]);if($d!=$X[0])queries("EXEC sp_rename ".q(table($R).".$d").", ".q(idf_unescape($X[0])).", 'COLUMN'");$b["ALTER COLUMN ".implode("",$X)][]="";$jg=$kg[$m[0]];if(default_value($jg)!=$k){if($jg["default"]!==null)$b["DROP"][]=" ".idf_escape($jg["default_constraint"]);if($k)$b["ADD"][]="\n $k FOR $d";}}}}if($R=="")return
queries("CREATE TABLE ".table($B)." (".implode(",",(array)$b["ADD"])."\n)");if($R!=$B)queries("EXEC sp_rename ".q(table($R)).", ".q($B));if($jd)$b[""]=$jd;foreach($b
as$x=>$X){if(!queries("ALTER TABLE ".table($B)." $x".implode(",",$X)))return
false;}foreach($qb
as$x=>$X){$ob=substr($X,9);queries("EXEC sp_dropextendedproperty @name = N'MS_Description', @level0type = N'Schema', @level0name = ".q(get_schema()).", @level1type = N'Table', @level1name = ".q($B).", @level2type = N'Column', @level2name = ".q($x));queries("EXEC sp_addextendedproperty
@name = N'MS_Description',
@value = $ob,
@level0type = N'Schema',
@level0name = ".q(get_schema()).",
@level1type = N'Table',
@level1name = ".q($B).",
@level2type = N'Column',
@level2name = ".q($x));}return
true;}function
alter_indexes($R,$b){$v=array();$ic=array();foreach($b
as$X){if($X[2]=="DROP"){if($X[0]=="PRIMARY")$ic[]=idf_escape($X[1]);else$v[]=idf_escape($X[1])." ON ".table($R);}elseif(!queries(($X[0]!="PRIMARY"?"CREATE $X[0] ".($X[0]!="INDEX"?"INDEX ":"").idf_escape($X[1]!=""?$X[1]:uniqid($R."_"))." ON ".table($R):"ALTER TABLE ".table($R)." ADD PRIMARY KEY")." (".implode(", ",$X[2]).")"))return
false;}return(!$v||queries("DROP INDEX ".implode(", ",$v)))&&(!$ic||queries("ALTER TABLE ".table($R)." DROP ".implode(", ",$ic)));}function
found_rows($S,$Z){}function
foreign_keys($R){$J=array();$Uf=array("CASCADE","NO ACTION","SET NULL","SET DEFAULT");foreach(get_rows("EXEC sp_fkeys @fktable_name = ".q($R).", @fktable_owner = ".q(get_schema()))as$K){$p=&$J[$K["FK_NAME"]];$p["db"]=$K["PKTABLE_QUALIFIER"];$p["ns"]=$K["PKTABLE_OWNER"];$p["table"]=$K["PKTABLE_NAME"];$p["on_update"]=$Uf[$K["UPDATE_RULE"]];$p["on_delete"]=$Uf[$K["DELETE_RULE"]];$p["source"][]=$K["FKCOLUMN_NAME"];$p["target"][]=$K["PKCOLUMN_NAME"];}return$J;}function
truncate_tables($T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views($Nj){return
queries("DROP VIEW ".implode(", ",array_map('Adminer\table',$Nj)));}function
drop_tables($T){return
queries("DROP TABLE ".implode(", ",array_map('Adminer\table',$T)));}function
move_tables($T,$Nj,$Ii){return
apply_queries("ALTER SCHEMA ".idf_escape($Ii)." TRANSFER",array_merge($T,$Nj));}function
trigger($B,$R){if($B=="")return
array();$L=get_rows("SELECT s.name [Trigger],
CASE WHEN OBJECTPROPERTY(s.id, 'ExecIsInsertTrigger') = 1 THEN 'INSERT' WHEN OBJECTPROPERTY(s.id, 'ExecIsUpdateTrigger') = 1 THEN 'UPDATE' WHEN OBJECTPROPERTY(s.id, 'ExecIsDeleteTrigger') = 1 THEN 'DELETE' END [Event],
CASE WHEN OBJECTPROPERTY(s.id, 'ExecIsInsteadOfTrigger') = 1 THEN 'INSTEAD OF' ELSE 'AFTER' END [Timing],
c.text
FROM sysobjects s
JOIN syscomments c ON s.id = c.id
WHERE s.xtype = 'TR' AND s.name = ".q($B));$J=reset($L);if($J)$J["Statement"]=preg_replace('~^.+\s+AS\s+~isU','',$J["text"]);return$J;}function
triggers($R){$J=array();foreach(get_rows("SELECT sys1.name,
CASE WHEN OBJECTPROPERTY(sys1.id, 'ExecIsInsertTrigger') = 1 THEN 'INSERT' WHEN OBJECTPROPERTY(sys1.id, 'ExecIsUpdateTrigger') = 1 THEN 'UPDATE' WHEN OBJECTPROPERTY(sys1.id, 'ExecIsDeleteTrigger') = 1 THEN 'DELETE' END [Event],
CASE WHEN OBJECTPROPERTY(sys1.id, 'ExecIsInsteadOfTrigger') = 1 THEN 'INSTEAD OF' ELSE 'AFTER' END [Timing]
FROM sysobjects sys1
JOIN sysobjects sys2 ON sys1.parent_obj = sys2.id
WHERE sys1.xtype = 'TR' AND sys2.name = ".q($R))as$K)$J[$K["name"]]=array($K["Timing"],$K["Event"]);return$J;}function
trigger_options(){return
array("Timing"=>array("AFTER","INSTEAD OF"),"Event"=>array("INSERT","UPDATE","DELETE"),"Type"=>array("AS"),);}function
schemas(){return
get_vals("SELECT name FROM sys.schemas");}function
get_schema(){if($_GET["ns"]!="")return$_GET["ns"];return
get_val("SELECT SCHEMA_NAME()");}function
set_schema($Fh){$_GET["ns"]=$Fh;return
true;}function
create_sql($R,$_a,$si){if(is_view(table_status1($R))){$Mj=view($R);return"CREATE VIEW ".table($R)." AS $Mj[select]";}$n=array();$G=false;foreach(fields($R)as$B=>$m){$X=process_field($m,$m);if($X[6])$G=true;$n[]=implode("",$X);}foreach(indexes($R)as$B=>$v){if(!$G||$v["type"]!="PRIMARY"){$e=array();foreach($v["columns"]as$x=>$X)$e[]=idf_escape($X).($v["descs"][$x]?" DESC":"");$B=idf_escape($B);$n[]=($v["type"]=="INDEX"?"INDEX $B":"CONSTRAINT $B ".($v["type"]=="UNIQUE"?"UNIQUE":"PRIMARY KEY"))." (".implode(", ",$e).")";}}foreach(driver()->checkConstraints($R)as$B=>$Wa)$n[]="CONSTRAINT ".idf_escape($B)." CHECK ($Wa)";return"CREATE TABLE ".table($R)." (\n\t".implode(",\n\t",$n)."\n)";}function
foreign_keys_sql($R){$n=array();foreach(foreign_keys($R)as$jd)$n[]=ltrim(format_foreign_key($jd));return($n?"ALTER TABLE ".table($R)." ADD\n\t".implode(",\n\t",$n).";\n\n":"");}function
truncate_sql($R){return"TRUNCATE TABLE ".table($R);}function
use_sql($Nb,$si=""){return"USE ".idf_escape($Nb);}function
trigger_sql($R){$J="";foreach(triggers($R)as$B=>$hj)$J
.=create_trigger(" ON ".table($R),trigger($B,$R)).";";return$J;}function
convert_field($m){}function
unconvert_field($m,$J){return$J;}function
support($Uc){return
preg_match('~^(check|comment|columns|database|drop_col|dump|indexes|descidx|scheme|sql|table|trigger|view|view_trigger)$~',$Uc);}}class
Adminer{static$instance;var$error='';function
name(){return"<a href='https://www.adminer.org/'".target_blank()." id='h1'><img src='".h(preg_replace("~\\?.*~","",ME)."?file=logo.png&version=5.4.1")."' width='24' height='24' alt='' id='logo'>Adminer</a>";}function
credentials(){return
array(SERVER,$_GET["username"],get_password());}function
connectSsl(){}function
permanentLogin($h=false){return
password_file($h);}function
bruteForceKey(){return$_SERVER["REMOTE_ADDR"];}function
serverName($N){return
h($N);}function
database(){return
DB;}function
databases($hd=true){return
get_databases($hd);}function
pluginsLinks(){}function
operators(){return
driver()->operators;}function
schemas(){return
schemas();}function
queryTimeout(){return
2;}function
afterConnect(){}function
headers(){}function
csp(array$Gb){return$Gb;}function
head($Kb=null){return
true;}function
bodyClass(){echo" adminer";}function
css(){$J=array();foreach(array("","-dark")as$tf){$o="adminer$tf.css";if(file_exists($o)){$Zc=file_get_contents($o);$J["$o?v=".crc32($Zc)]=($tf?"dark":(preg_match('~prefers-color-scheme:\s*dark~',$Zc)?'':'light'));}}return$J;}function
loginForm(){echo"<table class='layout'>\n",adminer()->loginFormField('driver','<tr><th>'.'System'.'<td>',html_select("auth[driver]",SqlDriver::$drivers,DRIVER,"loginDriver(this);")),adminer()->loginFormField('server','<tr><th>'.'Server'.'<td>','<input name="auth[server]" value="'.h(SERVER).'" title="hostname[:port]" placeholder="localhost" autocapitalize="off">'),adminer()->loginFormField('username','<tr><th>'.'Username'.'<td>','<input name="auth[username]" id="username" autofocus value="'.h($_GET["username"]).'" autocomplete="username" autocapitalize="off">'.script("const authDriver = qs('#username').form['auth[driver]']; authDriver && authDriver.onchange();")),adminer()->loginFormField('password','<tr><th>'.'Password'.'<td>','<input type="password" name="auth[password]" autocomplete="current-password">'),adminer()->loginFormField('db','<tr><th>'.'Database'.'<td>','<input name="auth[db]" value="'.h($_GET["db"]).'" autocapitalize="off">'),"</table>\n","<p><input type='submit' value='".'Login'."'>\n",checkbox("auth[permanent]",1,$_COOKIE["adminer_permanent"],'Permanent login')."\n";}function
loginFormField($B,$Hd,$Y){return$Hd.$Y."\n";}function
login($Te,$F){if($F=="")return
sprintf('Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',target_blank());return
true;}function
tableName(array$yi){return
h($yi["Name"]);}function
fieldName(array$m,$dg=0){$U=$m["full_type"];$ob=$m["comment"];return'<span title="'.h($U.($ob!=""?($U?": ":"").$ob:'')).'">'.h($m["field"]).'</span>';}function
selectLinks(array$yi,$O=""){$B=$yi["Name"];echo'<p class="links">';$Re=array("select"=>'Select data');if(support("table")||support("indexes"))$Re["table"]='Show structure';$ye=false;if(support("table")){$ye=is_view($yi);if(!$ye)$Re["create"]='Alter table';elseif(support("view"))$Re["view"]='Alter view';}if($O!==null)$Re["edit"]='New item';foreach($Re
as$x=>$X)echo" <a href='".h(ME)."$x=".urlencode($B).($x=="edit"?$O:"")."'".bold(isset($_GET[$x])).">$X</a>";echo
doc_link(array(JUSH=>driver()->tableHelp($B,$ye)),"?"),"\n";}function
foreignKeys($R){return
foreign_keys($R);}function
backwardKeys($R,$xi){return
array();}function
backwardKeysPrint(array$Da,array$K){}function
selectQuery($H,$oi,$Sc=false){$J="</p>\n";if(!$Sc&&($Qj=driver()->warnings())){$t="warnings";$J=", <a href='#$t'>".'Warnings'."</a>".script("qsl('a').onclick = partial(toggle, '$t');","")."$J<div id='$t' class='hidden'>\n$Qj</div>\n";}return"<p><code class='jush-".JUSH."'>".h(str_replace("\n"," ",$H))."</code> <span class='time'>(".format_time($oi).")</span>".(support("sql")?" <a href='".h(ME)."sql=".urlencode($H)."'>".'Edit'."</a>":"").$J;}function
sqlCommandQuery($H){return
shorten_utf8(trim($H),1000);}function
sqlPrintAfter(){}function
rowDescription($R){return"";}function
rowDescriptions(array$L,array$kd){return$L;}function
selectLink($X,array$m){}function
selectVal($X,$_,array$m,$ng){$J=($X===null?"<i>NULL</i>":(preg_match("~char|binary|boolean~",$m["type"])&&!preg_match("~var~",$m["type"])?"<code>$X</code>":(preg_match('~json~',$m["type"])?"<code class='jush-js'>$X</code>":$X)));if(is_blob($m)&&!is_utf8($X))$J="<i>".lang_format(array('%d byte','%d bytes'),strlen($ng))."</i>";return($_?"<a href='".h($_)."'".(is_url($_)?target_blank():"").">$J</a>":$J);}function
editVal($X,array$m){return$X;}function
config(){return
array();}function
tableStructurePrint(array$n,$yi=null){echo"<div class='scrollable'>\n","<table class='nowrap odds'>\n","<thead><tr><th>".'Column'."<td>".'Type'.(support("comment")?"<td>".'Comment':"")."</thead>\n";$ri=driver()->structuredTypes();foreach($n
as$m){echo"<tr><th>".h($m["field"]);$U=h($m["full_type"]);$c=h($m["collation"]);echo"<td><span title='$c'>".(in_array($U,(array)$ri['User types'])?"<a href='".h(ME.'type='.urlencode($U))."'>$U</a>":$U.($c&&isset($yi["Collation"])&&$c!=$yi["Collation"]?" $c":""))."</span>",($m["null"]?" <i>NULL</i>":""),($m["auto_increment"]?" <i>".'Auto Increment'."</i>":"");$k=h($m["default"]);echo(isset($m["default"])?" <span title='".'Default value'."'>[<b>".($m["generated"]?"<code class='jush-".JUSH."'>$k</code>":$k)."</b>]</span>":""),(support("comment")?"<td>".h($m["comment"]):""),"\n";}echo"</table>\n","</div>\n";}function
tableIndexesPrint(array$w,array$yi){$yg=false;foreach($w
as$B=>$v)$yg|=!!$v["partial"];echo"<table>\n";$Sb=first(driver()->indexAlgorithms($yi));foreach($w
as$B=>$v){ksort($v["columns"]);$Wg=array();foreach($v["columns"]as$x=>$X)$Wg[]="<i>".h($X)."</i>".($v["lengths"][$x]?"(".$v["lengths"][$x].")":"").($v["descs"][$x]?" DESC":"");echo"<tr title='".h($B)."'>","<th>$v[type]".($Sb&&$v['algorithm']!=$Sb?" ($v[algorithm])":""),"<td>".implode(", ",$Wg);if($yg)echo"<td>".($v['partial']?"<code class='jush-".JUSH."'>WHERE ".h($v['partial']):"");echo"\n";}echo"</table>\n";}function
selectColumnsPrint(array$M,array$e){print_fieldset("select",'Select',$M);$s=0;$M[""]=array();foreach($M
as$x=>$X){$X=idx($_GET["columns"],$x,array());$d=select_input(" name='columns[$s][col]'",$e,$X["col"],($x!==""?"selectFieldChange":"selectAddRow"));echo"<div>".(driver()->functions||driver()->grouping?html_select("columns[$s][fun]",array(-1=>"")+array_filter(array('Functions'=>driver()->functions,'Aggregation'=>driver()->grouping)),$X["fun"]).on_help("event.target.value && event.target.value.replace(/ |\$/, '(') + ')'",1).script("qsl('select').onchange = function () { helpClose();".($x!==""?"":" qsl('select, input', this.parentNode).onchange();")." };","")."($d)":$d)."</div>\n";$s++;}echo"</div></fieldset>\n";}function
selectSearchPrint(array$Z,array$e,array$w){print_fieldset("search",'Search',$Z);foreach($w
as$s=>$v){if($v["type"]=="FULLTEXT")echo"<div>(<i>".implode("</i>, <i>",array_map('Adminer\h',$v["columns"]))."</i>) AGAINST"," <input type='search' name='fulltext[$s]' value='".h(idx($_GET["fulltext"],$s))."'>",script("qsl('input').oninput = selectFieldChange;",""),checkbox("boolean[$s]",1,isset($_GET["boolean"][$s]),"BOOL"),"</div>\n";}$Ta="this.parentNode.firstChild.onchange();";foreach(array_merge((array)$_GET["where"],array(array()))as$s=>$X){if(!$X||("$X[col]$X[val]"!=""&&in_array($X["op"],adminer()->operators())))echo"<div>".select_input(" name='where[$s][col]'",$e,$X["col"],($X?"selectFieldChange":"selectAddRow"),"(".'anywhere'.")"),html_select("where[$s][op]",adminer()->operators(),$X["op"],$Ta),"<input type='search' name='where[$s][val]' value='".h($X["val"])."'>",script("mixin(qsl('input'), {oninput: function () { $Ta }, onkeydown: selectSearchKeydown, onsearch: selectSearchSearch});",""),"</div>\n";}echo"</div></fieldset>\n";}function
selectOrderPrint(array$dg,array$e,array$w){print_fieldset("sort",'Sort',$dg);$s=0;foreach((array)$_GET["order"]as$x=>$X){if($X!=""){echo"<div>".select_input(" name='order[$s]'",$e,$X,"selectFieldChange"),checkbox("desc[$s]",1,isset($_GET["desc"][$x]),'descending')."</div>\n";$s++;}}echo"<div>".select_input(" name='order[$s]'",$e,"","selectAddRow"),checkbox("desc[$s]",1,false,'descending')."</div>\n","</div></fieldset>\n";}function
selectLimitPrint($z){echo"<fieldset><legend>".'Limit'."</legend><div>","<input type='number' name='limit' class='size' value='".intval($z)."'>",script("qsl('input').oninput = selectFieldChange;",""),"</div></fieldset>\n";}function
selectLengthPrint($Oi){if($Oi!==null)echo"<fieldset><legend>".'Text length'."</legend><div>","<input type='number' name='text_length' class='size' value='".h($Oi)."'>","</div></fieldset>\n";}function
selectActionPrint(array$w){echo"<fieldset><legend>".'Action'."</legend><div>","<input type='submit' value='".'Select'."'>"," <span id='noindex' title='".'Full table scan'."'></span>","<script".nonce().">\n","const indexColumns = ";$e=array();foreach($w
as$v){$Jb=reset($v["columns"]);if($v["type"]!="FULLTEXT"&&$Jb)$e[$Jb]=1;}$e[""]=1;foreach($e
as$x=>$X)json_row($x);echo";\n","selectFieldChange.call(qs('#form')['select']);\n","</script>\n","</div></fieldset>\n";}function
selectCommandPrint(){return!information_schema(DB);}function
selectImportPrint(){return!information_schema(DB);}function
selectEmailPrint(array$uc,array$e){}function
selectColumnsProcess(array$e,array$w){$M=array();$wd=array();foreach((array)$_GET["columns"]as$x=>$X){if($X["fun"]=="count"||($X["col"]!=""&&(!$X["fun"]||in_array($X["fun"],driver()->functions)||in_array($X["fun"],driver()->grouping)))){$M[$x]=apply_sql_function($X["fun"],($X["col"]!=""?idf_escape($X["col"]):"*"));if(!in_array($X["fun"],driver()->grouping))$wd[]=$M[$x];}}return
array($M,$wd);}function
selectSearchProcess(array$n,array$w){$J=array();foreach($w
as$s=>$v){if($v["type"]=="FULLTEXT"&&idx($_GET["fulltext"],$s)!="")$J[]="MATCH (".implode(", ",array_map('Adminer\idf_escape',$v["columns"])).") AGAINST (".q($_GET["fulltext"][$s]).(isset($_GET["boolean"][$s])?" IN BOOLEAN MODE":"").")";}foreach((array)$_GET["where"]as$x=>$X){$hb=$X["col"];if("$hb$X[val]"!=""&&in_array($X["op"],adminer()->operators())){$sb=array();foreach(($hb!=""?array($hb=>$n[$hb]):$n)as$B=>$m){$Sg="";$rb=" $X[op]";if(preg_match('~IN$~',$X["op"])){$Wd=process_length($X["val"]);$rb
.=" ".($Wd!=""?$Wd:"(NULL)");}elseif($X["op"]=="SQL")$rb=" $X[val]";elseif(preg_match('~^(I?LIKE) %%$~',$X["op"],$A))$rb=" $A[1] ".adminer()->processInput($m,"%$X[val]%");elseif($X["op"]=="FIND_IN_SET"){$Sg="$X[op](".q($X["val"]).", ";$rb=")";}elseif(!preg_match('~NULL$~',$X["op"]))$rb
.=" ".adminer()->processInput($m,$X["val"]);if($hb!=""||(isset($m["privileges"]["where"])&&(preg_match('~^[-\d.'.(preg_match('~IN$~',$X["op"])?',':'').']+$~',$X["val"])||!preg_match('~'.number_type().'|bit~',$m["type"]))&&(!preg_match("~[\x80-\xFF]~",$X["val"])||preg_match('~char|text|enum|set~',$m["type"]))&&(!preg_match('~date|timestamp~',$m["type"])||preg_match('~^\d+-\d+-\d+~',$X["val"]))))$sb[]=$Sg.driver()->convertSearch(idf_escape($B),$X,$m).$rb;}$J[]=(count($sb)==1?$sb[0]:($sb?"(".implode(" OR ",$sb).")":"1 = 0"));}}return$J;}function
selectOrderProcess(array$n,array$w){$J=array();foreach((array)$_GET["order"]as$x=>$X){if($X!="")$J[]=(preg_match('~^((COUNT\(DISTINCT |[A-Z0-9_]+\()(`(?:[^`]|``)+`|"(?:[^"]|"")+")\)|COUNT\(\*\))$~',$X)?$X:idf_escape($X)).(isset($_GET["desc"][$x])?" DESC":"");}return$J;}function
selectLimitProcess(){return(isset($_GET["limit"])?intval($_GET["limit"]):50);}function
selectLengthProcess(){return(isset($_GET["text_length"])?"$_GET[text_length]":"100");}function
selectEmailProcess(array$Z,array$kd){return
false;}function
selectQueryBuild(array$M,array$Z,array$wd,array$dg,$z,$D){return"";}function
messageQuery($H,$Pi,$Sc=false){restart_session();$Jd=&get_session("queries");if(!idx($Jd,$_GET["db"]))$Jd[$_GET["db"]]=array();if(strlen($H)>1e6)$H=preg_replace('~[\x80-\xFF]+$~','',substr($H,0,1e6))."\nâ€¦";$Jd[$_GET["db"]][]=array($H,time(),$Pi);$ki="sql-".count($Jd[$_GET["db"]]);$J="<a href='#$ki' class='toggle'>".'SQL command'."</a> <a href='' class='jsonly copy'>ğŸ—</a>\n";if(!$Sc&&($Qj=driver()->warnings())){$t="warnings-".count($Jd[$_GET["db"]]);$J="<a href='#$t' class='toggle'>".'Warnings'."</a>, $J<div id='$t' class='hidden'>\n$Qj</div>\n";}return" <span class='time'>".@date("H:i:s")."</span>"." $J<div id='$ki' class='hidden'><pre><code class='jush-".JUSH."'>".shorten_utf8($H,1000)."</code></pre>".($Pi?" <span class='time'>($Pi)</span>":'').(support("sql")?'<p><a href="'.h(str_replace("db=".urlencode(DB),"db=".urlencode($_GET["db"]),ME).'sql=&history='.(count($Jd[$_GET["db"]])-1)).'">'.'Edit'.'</a>':'').'</div>';}function
editRowPrint($R,array$n,$K,$wj){}function
editFunctions(array$m){$J=($m["null"]?"NULL/":"");$wj=isset($_GET["select"])||where($_GET);foreach(array(driver()->insertFunctions,driver()->editFunctions)as$x=>$rd){if(!$x||(!isset($_GET["call"])&&$wj)){foreach($rd
as$Gg=>$X){if(!$Gg||preg_match("~$Gg~",$m["type"]))$J
.="/$X";}}if($x&&$rd&&!preg_match('~set|bool~',$m["type"])&&!is_blob($m))$J
.="/SQL";}if($m["auto_increment"]&&!$wj)$J='Auto Increment';return
explode("/",$J);}function
editInput($R,array$m,$ya,$Y){if($m["type"]=="enum")return(isset($_GET["select"])?"<label><input type='radio'$ya value='orig' checked><i>".'original'."</i></label> ":"").enum_input("radio",$ya,$m,$Y,"NULL");return"";}function
editHint($R,array$m,$Y){return"";}function
processInput(array$m,$Y,$r=""){if($r=="SQL")return$Y;$B=$m["field"];$J=q($Y);if(preg_match('~^(now|getdate|uuid)$~',$r))$J="$r()";elseif(preg_match('~^current_(date|timestamp)$~',$r))$J=$r;elseif(preg_match('~^([+-]|\|\|)$~',$r))$J=idf_escape($B)." $r $J";elseif(preg_match('~^[+-] interval$~',$r))$J=idf_escape($B)." $r ".(preg_match("~^(\\d+|'[0-9.: -]') [A-Z_]+\$~i",$Y)&&JUSH!="pgsql"?$Y:$J);elseif(preg_match('~^(addtime|subtime|concat)$~',$r))$J="$r(".idf_escape($B).", $J)";elseif(preg_match('~^(md5|sha1|password|encrypt)$~',$r))$J="$r($J)";return
unconvert_field($m,$J);}function
dumpOutput(){$J=array('text'=>'open','file'=>'save');if(function_exists('gzencode'))$J['gz']='gzip';return$J;}function
dumpFormat(){return(support("dump")?array('sql'=>'SQL'):array())+array('csv'=>'CSV,','csv;'=>'CSV;','tsv'=>'TSV');}function
dumpDatabase($j){}function
dumpTable($R,$si,$ye=0){if($_POST["format"]!="sql"){echo"\xef\xbb\xbf";if($si)dump_csv(array_keys(fields($R)));}else{if($ye==2){$n=array();foreach(fields($R)as$B=>$m)$n[]=idf_escape($B)." $m[full_type]";$h="CREATE TABLE ".table($R)." (".implode(", ",$n).")";}else$h=create_sql($R,$_POST["auto_increment"],$si);set_utf8mb4($h);if($si&&$h){if($si=="DROP+CREATE"||$ye==1)echo"DROP ".($ye==2?"VIEW":"TABLE")." IF EXISTS ".table($R).";\n";if($ye==1)$h=remove_definer($h);echo"$h;\n\n";}}}function
dumpData($R,$si,$H){if($si){$df=(JUSH=="sqlite"?0:1048576);$n=array();$Sd=false;if($_POST["format"]=="sql"){if($si=="TRUNCATE+INSERT")echo
truncate_sql($R).";\n";$n=fields($R);if(JUSH=="mssql"){foreach($n
as$m){if($m["auto_increment"]){echo"SET IDENTITY_INSERT ".table($R)." ON;\n";$Sd=true;break;}}}}$I=connection()->query($H,1);if($I){$ne="";$Na="";$Ce=array();$sd=array();$ui="";$Vc=($R!=''?'fetch_assoc':'fetch_row');$Cb=0;while($K=$I->$Vc()){if(!$Ce){$Ij=array();foreach($K
as$X){$m=$I->fetch_field();if(idx($n[$m->name],'generated')){$sd[$m->name]=true;continue;}$Ce[]=$m->name;$x=idf_escape($m->name);$Ij[]="$x = VALUES($x)";}$ui=($si=="INSERT+UPDATE"?"\nON DUPLICATE KEY UPDATE ".implode(", ",$Ij):"").";\n";}if($_POST["format"]!="sql"){if($si=="table"){dump_csv($Ce);$si="INSERT";}dump_csv($K);}else{if(!$ne)$ne="INSERT INTO ".table($R)." (".implode(", ",array_map('Adminer\idf_escape',$Ce)).") VALUES";foreach($K
as$x=>$X){if($sd[$x]){unset($K[$x]);continue;}$m=$n[$x];$K[$x]=($X!==null?unconvert_field($m,preg_match(number_type(),$m["type"])&&!preg_match('~\[~',$m["full_type"])&&is_numeric($X)?$X:q(($X===false?0:$X))):"NULL");}$Dh=($df?"\n":" ")."(".implode(",\t",$K).")";if(!$Na)$Na=$ne.$Dh;elseif(JUSH=='mssql'?$Cb%1000!=0:strlen($Na)+4+strlen($Dh)+strlen($ui)<$df)$Na
.=",$Dh";else{echo$Na.$ui;$Na=$ne.$Dh;}}$Cb++;}if($Na)echo$Na.$ui;}elseif($_POST["format"]=="sql")echo"-- ".str_replace("\n"," ",connection()->error)."\n";if($Sd)echo"SET IDENTITY_INSERT ".table($R)." OFF;\n";}}function
dumpFilename($Qd){return
friendly_url($Qd!=""?$Qd:(SERVER?:"localhost"));}function
dumpHeaders($Qd,$wf=false){$qg=$_POST["output"];$Nc=(preg_match('~sql~',$_POST["format"])?"sql":($wf?"tar":"csv"));header("Content-Type: ".($qg=="gz"?"application/x-gzip":($Nc=="tar"?"application/x-tar":($Nc=="sql"||$qg!="file"?"text/plain":"text/csv")."; charset=utf-8")));if($qg=="gz"){ob_start(function($Q){return
gzencode($Q);},1e6);}return$Nc;}function
dumpFooter(){if($_POST["format"]=="sql")echo"-- ".gmdate("Y-m-d H:i:s e")."\n";}function
importServerPath(){return"adminer.sql";}function
homepage(){echo'<p class="links">'.($_GET["ns"]==""&&support("database")?'<a href="'.h(ME).'database=">'.'Alter database'."</a>\n":""),(support("scheme")?"<a href='".h(ME)."scheme='>".($_GET["ns"]!=""?'Alter schema':'Create schema')."</a>\n":""),($_GET["ns"]!==""?'<a href="'.h(ME).'schema=">'.'Database schema'."</a>\n":""),(support("privileges")?"<a href='".h(ME)."privileges='>".'Privileges'."</a>\n":"");if($_GET["ns"]!=="")echo(support("routine")?"<a href='#routines'>".'Routines'."</a>\n":""),(support("sequence")?"<a href='#sequences'>".'Sequences'."</a>\n":""),(support("type")?"<a href='#user-types'>".'User types'."</a>\n":""),(support("event")?"<a href='#events'>".'Events'."</a>\n":"");return
true;}function
navigation($sf){echo"<h1>".adminer()->name()." <span class='version'>".VERSION;$Df=$_COOKIE["adminer_version"];echo" <a href='https://www.adminer.org/#download'".target_blank()." id='version'>".(version_compare(VERSION,$Df)<0?h($Df):"")."</a>","</span></h1>\n";if($sf=="auth"){$qg="";foreach((array)$_SESSION["pwds"]as$Kj=>$Wh){foreach($Wh
as$N=>$Fj){$B=h(get_setting("vendor-$Kj-$N")?:get_driver($Kj));foreach($Fj
as$V=>$F){if($F!==null){$Qb=$_SESSION["db"][$Kj][$N][$V];foreach(($Qb?array_keys($Qb):array(""))as$j)$qg
.="<li><a href='".h(auth_url($Kj,$N,$V,$j))."'>($B) ".h("$V@".($N!=""?adminer()->serverName($N):"").($j!=""?" - $j":""))."</a>\n";}}}}if($qg)echo"<ul id='logins'>\n$qg</ul>\n".script("mixin(qs('#logins'), {onmouseover: menuOver, onmouseout: menuOut});");}else{$T=array();if($_GET["ns"]!==""&&!$sf&&DB!=""){connection()->select_db(DB);$T=table_status('',true);}adminer()->syntaxHighlighting($T);adminer()->databasesPrint($sf);$ia=array();if(DB==""||!$sf){if(support("sql")){$ia[]="<a href='".h(ME)."sql='".bold(isset($_GET["sql"])&&!isset($_GET["import"])).">".'SQL command'."</a>";$ia[]="<a href='".h(ME)."import='".bold(isset($_GET["import"])).">".'Import'."</a>";}$ia[]="<a href='".h(ME)."dump=".urlencode(isset($_GET["table"])?$_GET["table"]:$_GET["select"])."' id='dump'".bold(isset($_GET["dump"])).">".'Export'."</a>";}$Xd=$_GET["ns"]!==""&&!$sf&&DB!="";if($Xd)$ia[]='<a href="'.h(ME).'create="'.bold($_GET["create"]==="").">".'Create table'."</a>";echo($ia?"<p class='links'>\n".implode("\n",$ia)."\n":"");if($Xd){if($T)adminer()->tablesPrint($T);else
echo"<p class='message'>".'No tables.'."</p>\n";}}}function
syntaxHighlighting(array$T){echo
script_src(preg_replace("~\\?.*~","",ME)."?file=jush.js&version=5.4.1",true);if(support("sql")){echo"<script".nonce().">\n";if($T){$Re=array();foreach($T
as$R=>$U)$Re[]=preg_quote($R,'/');echo"var jushLinks = { ".JUSH.":";json_row(js_escape(ME).(support("table")?"table":"select").'=$&','/\b('.implode('|',$Re).')\b/g',false);if(support('routine')){foreach(routines()as$K)json_row(js_escape(ME).'function='.urlencode($K["SPECIFIC_NAME"]).'&name=$&','/\b'.preg_quote($K["ROUTINE_NAME"],'/').'(?=["`]?\()/g',false);}json_row('');echo"};\n";foreach(array("bac","bra","sqlite_quo","mssql_bra")as$X)echo"jushLinks.$X = jushLinks.".JUSH.";\n";if(isset($_GET["sql"])||isset($_GET["trigger"])||isset($_GET["check"])){$Ei=array_fill_keys(array_keys($T),array());foreach(driver()->allFields()as$R=>$n){foreach($n
as$m)$Ei[$R][]=$m["field"];}echo"addEventListener('DOMContentLoaded', () => { autocompleter = jush.autocompleteSql('".idf_escape("")."', ".json_encode($Ei)."); });\n";}}echo"</script>\n";}echo
script("syntaxHighlighting('".preg_replace('~^(\d\.?\d).*~s','\1',connection()->server_info)."', '".connection()->flavor."');");}function
databasesPrint($sf){$i=adminer()->databases();if(DB&&$i&&!in_array(DB,$i))array_unshift($i,DB);echo"<form action=''>\n<p id='dbs'>\n";hidden_fields_get();$Ob=script("mixin(qsl('select'), {onmousedown: dbMouseDown, onchange: dbChange});");echo"<label title='".'Database'."'>".'DB'.": ".($i?html_select("db",array(""=>"")+$i,DB).$Ob:"<input name='db' value='".h(DB)."' autocapitalize='off' size='19'>\n")."</label>","<input type='submit' value='".'Use'."'".($i?" class='hidden'":"").">\n";if(support("scheme")){if($sf!="db"&&DB!=""&&connection()->select_db(DB)){echo"<br><label>".'Schema'.": ".html_select("ns",array(""=>"")+adminer()->schemas(),$_GET["ns"])."$Ob</label>";if($_GET["ns"]!="")set_schema($_GET["ns"]);}}foreach(array("import","sql","schema","dump","privileges")as$X){if(isset($_GET[$X])){echo
input_hidden($X);break;}}echo"</p></form>\n";}function
tablesPrint(array$T){echo"<ul id='tables'>".script("mixin(qs('#tables'), {onmouseover: menuOver, onmouseout: menuOut});");foreach($T
as$R=>$P){$R="$R";$B=adminer()->tableName($P);if($B!=""&&!$P["partition"])echo'<li><a href="'.h(ME).'select='.urlencode($R).'"'.bold($_GET["select"]==$R||$_GET["edit"]==$R,"select")." title='".'Select data'."'>".'select'."</a> ",(support("table")||support("indexes")?'<a href="'.h(ME).'table='.urlencode($R).'"'.bold(in_array($R,array($_GET["table"],$_GET["create"],$_GET["indexes"],$_GET["foreign"],$_GET["trigger"],$_GET["check"],$_GET["view"])),(is_view($P)?"view":"structure"))." title='".'Show structure'."'>$B</a>":"<span>$B</span>")."\n";}echo"</ul>\n";}function
processList(){return
process_list();}function
killProcess($t){return
kill_process($t);}}class
Plugins{private
static$append=array('dumpFormat'=>true,'dumpOutput'=>true,'editRowPrint'=>true,'editFunctions'=>true,'config'=>true);var$plugins;var$error='';private$hooks=array();function
__construct($Lg){if($Lg===null){$Lg=array();$Ha="adminer-plugins";if(is_dir($Ha)){foreach(glob("$Ha/*.php")as$o)$Yd=include_once"./$o";}$Id=" href='https://www.adminer.org/plugins/#use'".target_blank();if(file_exists("$Ha.php")){$Yd=include_once"./$Ha.php";if(is_array($Yd)){foreach($Yd
as$Kg)$Lg[get_class($Kg)]=$Kg;}else$this->error
.=sprintf('%s must <a%s>return an array</a>.',"<b>$Ha.php</b>",$Id)."<br>";}foreach(get_declared_classes()as$db){if(!$Lg[$db]&&preg_match('~^Adminer\w~i',$db)){$oh=new
\ReflectionClass($db);$xb=$oh->getConstructor();if($xb&&$xb->getNumberOfRequiredParameters())$this->error
.=sprintf('<a%s>Configure</a> %s in %s.',$Id,"<b>$db</b>","<b>$Ha.php</b>")."<br>";else$Lg[$db]=new$db;}}}$this->plugins=$Lg;$la=new
Adminer;$Lg[]=$la;$oh=new
\ReflectionObject($la);foreach($oh->getMethods()as$qf){foreach($Lg
as$Kg){$B=$qf->getName();if(method_exists($Kg,$B))$this->hooks[$B][]=$Kg;}}}function
__call($B,array$vg){$ua=array();foreach($vg
as$x=>$X)$ua[]=&$vg[$x];$J=null;foreach($this->hooks[$B]as$Kg){$Y=call_user_func_array(array($Kg,$B),$ua);if($Y!==null){if(!self::$append[$B])return$Y;$J=$Y+(array)$J;}}return$J;}}abstract
class
Plugin{protected$translations=array();function
description(){return$this->lang('');}function
screenshot(){return"";}protected
function
lang($u,$Jf=null){$ua=func_get_args();$ua[0]=idx($this->translations[LANG],$u)?:$u;return
call_user_func_array('Adminer\lang_format',$ua);}}Adminer::$instance=(function_exists('adminer_object')?adminer_object():(is_dir("adminer-plugins")||file_exists("adminer-plugins.php")?new
Plugins(null):new
Adminer));SqlDriver::$drivers=array("server"=>"MySQL / MariaDB")+SqlDriver::$drivers;if(!defined('Adminer\DRIVER')){define('Adminer\DRIVER',"server");if(extension_loaded("mysqli")&&$_GET["ext"]!="pdo"){class
Db
extends
\MySQLi{static$instance;var$extension="MySQLi",$flavor='';function
__construct(){parent::init();}function
attach($N,$V,$F){mysqli_report(MYSQLI_REPORT_OFF);list($Md,$Mg)=host_port($N);$ni=adminer()->connectSsl();if($ni)$this->ssl_set($ni['key'],$ni['cert'],$ni['ca'],'','');$J=@$this->real_connect(($N!=""?$Md:ini_get("mysqli.default_host")),($N.$V!=""?$V:ini_get("mysqli.default_user")),($N.$V.$F!=""?$F:ini_get("mysqli.default_pw")),null,(is_numeric($Mg)?intval($Mg):ini_get("mysqli.default_port")),(is_numeric($Mg)?null:$Mg),($ni?($ni['verify']!==false?2048:64):0));$this->options(MYSQLI_OPT_LOCAL_INFILE,0);return($J?'':$this->error);}function
set_charset($Va){if(parent::set_charset($Va))return
true;parent::set_charset('utf8');return$this->query("SET NAMES $Va");}function
next_result(){return
self::more_results()&&parent::next_result();}function
quote($Q){return"'".$this->escape_string($Q)."'";}}}elseif(extension_loaded("mysql")&&!((ini_bool("sql.safe_mode")||ini_bool("mysql.allow_local_infile"))&&extension_loaded("pdo_mysql"))){class
Db
extends
SqlDb{private$link;function
attach($N,$V,$F){if(ini_bool("mysql.allow_local_infile"))return
sprintf('Disable %s or enable %s or %s extensions.',"'mysql.allow_local_infile'","MySQLi","PDO_MySQL");$this->link=@mysql_connect(($N!=""?$N:ini_get("mysql.default_host")),($N.$V!=""?$V:ini_get("mysql.default_user")),($N.$V.$F!=""?$F:ini_get("mysql.default_password")),true,131072);if(!$this->link)return
mysql_error();$this->server_info=mysql_get_server_info($this->link);return'';}function
set_charset($Va){if(function_exists('mysql_set_charset')){if(mysql_set_charset($Va,$this->link))return
true;mysql_set_charset('utf8',$this->link);}return$this->query("SET NAMES $Va");}function
quote($Q){return"'".mysql_real_escape_string($Q,$this->link)."'";}function
select_db($Nb){return
mysql_select_db($Nb,$this->link);}function
query($H,$oj=false){$I=@($oj?mysql_unbuffered_query($H,$this->link):mysql_query($H,$this->link));$this->error="";if(!$I){$this->errno=mysql_errno($this->link);$this->error=mysql_error($this->link);return
false;}if($I===true){$this->affected_rows=mysql_affected_rows($this->link);$this->info=mysql_info($this->link);return
true;}return
new
Result($I);}}class
Result{var$num_rows;private$result;private$offset=0;function
__construct($I){$this->result=$I;$this->num_rows=mysql_num_rows($I);}function
fetch_assoc(){return
mysql_fetch_assoc($this->result);}function
fetch_row(){return
mysql_fetch_row($this->result);}function
fetch_field(){$J=mysql_fetch_field($this->result,$this->offset++);$J->orgtable=$J->table;$J->charsetnr=($J->blob?63:0);return$J;}function
__destruct(){mysql_free_result($this->result);}}}elseif(extension_loaded("pdo_mysql")){class
Db
extends
PdoDb{var$extension="PDO_MySQL";function
attach($N,$V,$F){$bg=array(\PDO::MYSQL_ATTR_LOCAL_INFILE=>false);$ni=adminer()->connectSsl();if($ni){if($ni['key'])$bg[\PDO::MYSQL_ATTR_SSL_KEY]=$ni['key'];if($ni['cert'])$bg[\PDO::MYSQL_ATTR_SSL_CERT]=$ni['cert'];if($ni['ca'])$bg[\PDO::MYSQL_ATTR_SSL_CA]=$ni['ca'];if(isset($ni['verify']))$bg[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]=$ni['verify'];}list($Md,$Mg)=host_port($N);return$this->dsn("mysql:charset=utf8;host=$Md".($Mg?(is_numeric($Mg)?";port=":";unix_socket=").$Mg:""),$V,$F,$bg);}function
set_charset($Va){return$this->query("SET NAMES $Va");}function
select_db($Nb){return$this->query("USE ".idf_escape($Nb));}function
query($H,$oj=false){$this->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,!$oj);return
parent::query($H,$oj);}}}class
Driver
extends
SqlDriver{static$extensions=array("MySQLi","MySQL","PDO_MySQL");static$jush="sql";var$unsigned=array("unsigned","zerofill","unsigned zerofill");var$operators=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","REGEXP","IN","FIND_IN_SET","IS NULL","NOT LIKE","NOT REGEXP","NOT IN","IS NOT NULL","SQL");var$functions=array("char_length","date","from_unixtime","lower","round","floor","ceil","sec_to_time","time_to_sec","upper");var$grouping=array("avg","count","count distinct","group_concat","max","min","sum");static
function
connect($N,$V,$F){$f=parent::connect($N,$V,$F);if(is_string($f)){if(function_exists('iconv')&&!is_utf8($f)&&strlen($Dh=iconv("windows-1250","utf-8",$f))>strlen($f))$f=$Dh;return$f;}$f->set_charset(charset($f));$f->query("SET sql_quote_show_create = 1, autocommit = 1");$f->flavor=(preg_match('~MariaDB~',$f->server_info)?'maria':'mysql');add_driver(DRIVER,($f->flavor=='maria'?"MariaDB":"MySQL"));return$f;}function
__construct(Db$f){parent::__construct($f);$this->types=array('Numbers'=>array("tinyint"=>3,"smallint"=>5,"mediumint"=>8,"int"=>10,"bigint"=>20,"decimal"=>66,"float"=>12,"double"=>21),'Date and time'=>array("date"=>10,"datetime"=>19,"timestamp"=>19,"time"=>10,"year"=>4),'Strings'=>array("char"=>255,"varchar"=>65535,"tinytext"=>255,"text"=>65535,"mediumtext"=>16777215,"longtext"=>4294967295),'Lists'=>array("enum"=>65535,"set"=>64),'Binary'=>array("bit"=>20,"binary"=>255,"varbinary"=>65535,"tinyblob"=>255,"blob"=>65535,"mediumblob"=>16777215,"longblob"=>4294967295),'Geometry'=>array("geometry"=>0,"point"=>0,"linestring"=>0,"polygon"=>0,"multipoint"=>0,"multilinestring"=>0,"multipolygon"=>0,"geometrycollection"=>0),);$this->insertFunctions=array("char"=>"md5/sha1/password/encrypt/uuid","binary"=>"md5/sha1","date|time"=>"now",);$this->editFunctions=array(number_type()=>"+/-","date"=>"+ interval/- interval","time"=>"addtime/subtime","char|text"=>"concat",);if(min_version('5.7.8',10.2,$f))$this->types['Strings']["json"]=4294967295;if(min_version('',10.7,$f)){$this->types['Strings']["uuid"]=128;$this->insertFunctions['uuid']='uuid';}if(min_version(9,'',$f)){$this->types['Numbers']["vector"]=16383;$this->insertFunctions['vector']='string_to_vector';}if(min_version(5.1,'',$f))$this->partitionBy=array("HASH","LINEAR HASH","KEY","LINEAR KEY","RANGE","LIST");if(min_version(5.7,10.2,$f))$this->generated=array("STORED","VIRTUAL");}function
unconvertFunction(array$m){return(preg_match("~binary~",$m["type"])?"<code class='jush-sql'>UNHEX</code>":($m["type"]=="bit"?doc_link(array('sql'=>'bit-value-literals.html'),"<code>b''</code>"):(preg_match("~geometry|point|linestring|polygon~",$m["type"])?"<code class='jush-sql'>GeomFromText</code>":"")));}function
insert($R,array$O){return($O?parent::insert($R,$O):queries("INSERT INTO ".table($R)." ()\nVALUES ()"));}function
insertUpdate($R,array$L,array$G){$e=array_keys(reset($L));$Sg="INSERT INTO ".table($R)." (".implode(", ",$e).") VALUES\n";$Ij=array();foreach($e
as$x)$Ij[$x]="$x = VALUES($x)";$ui="\nON DUPLICATE KEY UPDATE ".implode(", ",$Ij);$Ij=array();$y=0;foreach($L
as$O){$Y="(".implode(", ",$O).")";if($Ij&&(strlen($Sg)+$y+strlen($Y)+strlen($ui)>1e6)){if(!queries($Sg.implode(",\n",$Ij).$ui))return
false;$Ij=array();$y=0;}$Ij[]=$Y;$y+=strlen($Y)+2;}return
queries($Sg.implode(",\n",$Ij).$ui);}function
slowQuery($H,$Qi){if(min_version('5.7.8','10.1.2')){if($this->conn->flavor=='maria')return"SET STATEMENT max_statement_time=$Qi FOR $H";elseif(preg_match('~^(SELECT\b)(.+)~is',$H,$A))return"$A[1] /*+ MAX_EXECUTION_TIME(".($Qi*1000).") */ $A[2]";}}function
convertSearch($u,array$X,array$m){return(preg_match('~char|text|enum|set~',$m["type"])&&!preg_match("~^utf8~",$m["collation"])&&preg_match('~[\x80-\xFF]~',$X['val'])?"CONVERT($u USING ".charset($this->conn).")":$u);}function
warnings(){$I=$this->conn->query("SHOW WARNINGS");if($I&&$I->num_rows){ob_start();print_select_result($I);return
ob_get_clean();}}function
tableHelp($B,$ye=false){$Ve=($this->conn->flavor=='maria');if(information_schema(DB))return
strtolower("information-schema-".($Ve?"$B-table/":str_replace("_","-",$B)."-table.html"));if(DB=="mysql")return($Ve?"mysql$B-table/":"system-schema.html");}function
partitionsInfo($R){$pd="FROM information_schema.PARTITIONS WHERE TABLE_SCHEMA = ".q(DB)." AND TABLE_NAME = ".q($R);$I=$this->conn->query("SELECT PARTITION_METHOD, PARTITION_EXPRESSION, PARTITION_ORDINAL_POSITION $pd ORDER BY PARTITION_ORDINAL_POSITION DESC LIMIT 1");$J=array();list($J["partition_by"],$J["partition"],$J["partitions"])=$I->fetch_row();$Cg=get_key_vals("SELECT PARTITION_NAME, PARTITION_DESCRIPTION $pd AND PARTITION_NAME != '' ORDER BY PARTITION_ORDINAL_POSITION");$J["partition_names"]=array_keys($Cg);$J["partition_values"]=array_values($Cg);return$J;}function
hasCStyleEscapes(){static$Qa;if($Qa===null){$li=get_val("SHOW VARIABLES LIKE 'sql_mode'",1,$this->conn);$Qa=(strpos($li,'NO_BACKSLASH_ESCAPES')===false);}return$Qa;}function
engines(){$J=array();foreach(get_rows("SHOW ENGINES")as$K){if(preg_match("~YES|DEFAULT~",$K["Support"]))$J[]=$K["Engine"];}return$J;}function
indexAlgorithms(array$yi){return(preg_match('~^(MEMORY|NDB)$~',$yi["Engine"])?array("HASH","BTREE"):array());}}function
idf_escape($u){return"`".str_replace("`","``",$u)."`";}function
table($u){return
idf_escape($u);}function
get_databases($hd){$J=get_session("dbs");if($J===null){$H="SELECT SCHEMA_NAME FROM information_schema.SCHEMATA ORDER BY SCHEMA_NAME";$J=($hd?slow_query($H):get_vals($H));restart_session();set_session("dbs",$J);stop_session();}return$J;}function
limit($H,$Z,$z,$C=0,$Rh=" "){return" $H$Z".($z?$Rh."LIMIT $z".($C?" OFFSET $C":""):"");}function
limit1($R,$H,$Z,$Rh="\n"){return
limit($H,$Z,1,0,$Rh);}function
db_collation($j,array$jb){$J=null;$h=get_val("SHOW CREATE DATABASE ".idf_escape($j),1);if(preg_match('~ COLLATE ([^ ]+)~',$h,$A))$J=$A[1];elseif(preg_match('~ CHARACTER SET ([^ ]+)~',$h,$A))$J=$jb[$A[1]][-1];return$J;}function
logged_user(){return
get_val("SELECT USER()");}function
tables_list(){return
get_key_vals("SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME");}function
count_tables(array$i){$J=array();foreach($i
as$j)$J[$j]=count(get_vals("SHOW TABLES IN ".idf_escape($j)));return$J;}function
table_status($B="",$Tc=false){$J=array();foreach(get_rows($Tc?"SELECT TABLE_NAME AS Name, ENGINE AS Engine, TABLE_COMMENT AS Comment FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ".($B!=""?"AND TABLE_NAME = ".q($B):"ORDER BY Name"):"SHOW TABLE STATUS".($B!=""?" LIKE ".q(addcslashes($B,"%_\\")):""))as$K){if($K["Engine"]=="InnoDB")$K["Comment"]=preg_replace('~(?:(.+); )?InnoDB free: .*~','\1',$K["Comment"]);if(!isset($K["Engine"]))$K["Comment"]="";if($B!="")$K["Name"]=$B;$J[$K["Name"]]=$K;}return$J;}function
is_view(array$S){return$S["Engine"]===null;}function
fk_support(array$S){return
preg_match('~InnoDB|IBMDB2I'.(min_version(5.6)?'|NDB':'').'~i',$S["Engine"]);}function
fields($R){$Ve=(connection()->flavor=='maria');$J=array();foreach(get_rows("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ".q($R)." ORDER BY ORDINAL_POSITION")as$K){$m=$K["COLUMN_NAME"];$U=$K["COLUMN_TYPE"];$td=$K["GENERATION_EXPRESSION"];$Qc=$K["EXTRA"];preg_match('~^(VIRTUAL|PERSISTENT|STORED)~',$Qc,$sd);preg_match('~^([^( ]+)(?:\((.+)\))?( unsigned)?( zerofill)?$~',$U,$Ye);$k=$K["COLUMN_DEFAULT"];if($k!=""){$xe=preg_match('~text|json~',$Ye[1]);if(!$Ve&&$xe)$k=preg_replace("~^(_\w+)?('.*')$~",'\2',stripslashes($k));if($Ve||$xe){$k=($k=="NULL"?null:preg_replace_callback("~^'(.*)'$~",function($A){return
stripslashes(str_replace("''","'",$A[1]));},$k));}if(!$Ve&&preg_match('~binary~',$Ye[1])&&preg_match('~^0x(\w*)$~',$k,$A))$k=pack("H*",$A[1]);}$J[$m]=array("field"=>$m,"full_type"=>$U,"type"=>$Ye[1],"length"=>$Ye[2],"unsigned"=>ltrim($Ye[3].$Ye[4]),"default"=>($sd?($Ve?$td:stripslashes($td)):$k),"null"=>($K["IS_NULLABLE"]=="YES"),"auto_increment"=>($Qc=="auto_increment"),"on_update"=>(preg_match('~\bon update (\w+)~i',$Qc,$A)?$A[1]:""),"collation"=>$K["COLLATION_NAME"],"privileges"=>array_flip(explode(",","$K[PRIVILEGES],where,order")),"comment"=>$K["COLUMN_COMMENT"],"primary"=>($K["COLUMN_KEY"]=="PRI"),"generated"=>($sd[1]=="PERSISTENT"?"STORED":$sd[1]),);}return$J;}function
indexes($R,$g=null){$J=array();foreach(get_rows("SHOW INDEX FROM ".table($R),$g)as$K){$B=$K["Key_name"];$J[$B]["type"]=($B=="PRIMARY"?"PRIMARY":($K["Index_type"]=="FULLTEXT"?"FULLTEXT":($K["Non_unique"]?($K["Index_type"]=="SPATIAL"?"SPATIAL":"INDEX"):"UNIQUE")));$J[$B]["columns"][]=$K["Column_name"];$J[$B]["lengths"][]=($K["Index_type"]=="SPATIAL"?null:$K["Sub_part"]);$J[$B]["descs"][]=null;$J[$B]["algorithm"]=$K["Index_type"];}return$J;}function
foreign_keys($R){static$Gg='(?:`(?:[^`]|``)+`|"(?:[^"]|"")+")';$J=array();$Db=get_val("SHOW CREATE TABLE ".table($R),1);if($Db){preg_match_all("~CONSTRAINT ($Gg) FOREIGN KEY ?\\(((?:$Gg,? ?)+)\\) REFERENCES ($Gg)(?:\\.($Gg))? \\(((?:$Gg,? ?)+)\\)(?: ON DELETE (".driver()->onActions."))?(?: ON UPDATE (".driver()->onActions."))?~",$Db,$Ze,PREG_SET_ORDER);foreach($Ze
as$A){preg_match_all("~$Gg~",$A[2],$fi);preg_match_all("~$Gg~",$A[5],$Ii);$J[idf_unescape($A[1])]=array("db"=>idf_unescape($A[4]!=""?$A[3]:$A[4]),"table"=>idf_unescape($A[4]!=""?$A[4]:$A[3]),"source"=>array_map('Adminer\idf_unescape',$fi[0]),"target"=>array_map('Adminer\idf_unescape',$Ii[0]),"on_delete"=>($A[6]?:"RESTRICT"),"on_update"=>($A[7]?:"RESTRICT"),);}}return$J;}function
view($B){return
array("select"=>preg_replace('~^(?:[^`]|`[^`]*`)*\s+AS\s+~isU','',get_val("SHOW CREATE VIEW ".table($B),1)));}function
collations(){$J=array();foreach(get_rows("SHOW COLLATION")as$K){if($K["Default"])$J[$K["Charset"]][-1]=$K["Collation"];else$J[$K["Charset"]][]=$K["Collation"];}ksort($J);foreach($J
as$x=>$X)sort($J[$x]);return$J;}function
information_schema($j){return($j=="information_schema")||(min_version(5.5)&&$j=="performance_schema");}function
error(){return
h(preg_replace('~^You have an error.*syntax to use~U',"Syntax error",connection()->error));}function
create_database($j,$c){return
queries("CREATE DATABASE ".idf_escape($j).($c?" COLLATE ".q($c):""));}function
drop_databases(array$i){$J=apply_queries("DROP DATABASE",$i,'Adminer\idf_escape');restart_session();set_session("dbs",null);return$J;}function
rename_database($B,$c){$J=false;if(create_database($B,$c)){$T=array();$Nj=array();foreach(tables_list()as$R=>$U){if($U=='VIEW')$Nj[]=$R;else$T[]=$R;}$J=(!$T&&!$Nj)||move_tables($T,$Nj,$B);drop_databases($J?array(DB):array());}return$J;}function
auto_increment(){$Aa=" PRIMARY KEY";if($_GET["create"]!=""&&$_POST["auto_increment_col"]){foreach(indexes($_GET["create"])as$v){if(in_array($_POST["fields"][$_POST["auto_increment_col"]]["orig"],$v["columns"],true)){$Aa="";break;}if($v["type"]=="PRIMARY")$Aa=" UNIQUE";}}return" AUTO_INCREMENT$Aa";}function
alter_table($R,$B,array$n,array$jd,$ob,$xc,$c,$_a,$E){$b=array();foreach($n
as$m){if($m[1]){$k=$m[1][3];if(preg_match('~ GENERATED~',$k)){$m[1][3]=(connection()->flavor=='maria'?"":$m[1][2]);$m[1][2]=$k;}$b[]=($R!=""?($m[0]!=""?"CHANGE ".idf_escape($m[0]):"ADD"):" ")." ".implode($m[1]).($R!=""?$m[2]:"");}else$b[]="DROP ".idf_escape($m[0]);}$b=array_merge($b,$jd);$P=($ob!==null?" COMMENT=".q($ob):"").($xc?" ENGINE=".q($xc):"").($c?" COLLATE ".q($c):"").($_a!=""?" AUTO_INCREMENT=$_a":"");if($E){$Cg=array();if($E["partition_by"]=='RANGE'||$E["partition_by"]=='LIST'){foreach($E["partition_names"]as$x=>$X){$Y=$E["partition_values"][$x];$Cg[]="\n  PARTITION ".idf_escape($X)." VALUES ".($E["partition_by"]=='RANGE'?"LESS THAN":"IN").($Y!=""?" ($Y)":" MAXVALUE");}}$P
.="\nPARTITION BY $E[partition_by]($E[partition])";if($Cg)$P
.=" (".implode(",",$Cg)."\n)";elseif($E["partitions"])$P
.=" PARTITIONS ".(+$E["partitions"]);}elseif($E===null)$P
.="\nREMOVE PARTITIONING";if($R=="")return
queries("CREATE TABLE ".table($B)." (\n".implode(",\n",$b)."\n)$P");if($R!=$B)$b[]="RENAME TO ".table($B);if($P)$b[]=ltrim($P);return($b?queries("ALTER TABLE ".table($R)."\n".implode(",\n",$b)):true);}function
alter_indexes($R,$b){$Ua=array();foreach($b
as$X)$Ua[]=($X[2]=="DROP"?"\nDROP INDEX ".idf_escape($X[1]):"\nADD $X[0] ".($X[0]=="PRIMARY"?"KEY ":"").($X[1]!=""?idf_escape($X[1])." ":"")."(".implode(", ",$X[2]).")");return
queries("ALTER TABLE ".table($R).implode(",",$Ua));}function
truncate_tables(array$T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views(array$Nj){return
queries("DROP VIEW ".implode(", ",array_map('Adminer\table',$Nj)));}function
drop_tables(array$T){return
queries("DROP TABLE ".implode(", ",array_map('Adminer\table',$T)));}function
move_tables(array$T,array$Nj,$Ii){$sh=array();foreach($T
as$R)$sh[]=table($R)." TO ".idf_escape($Ii).".".table($R);if(!$sh||queries("RENAME TABLE ".implode(", ",$sh))){$Wb=array();foreach($Nj
as$R)$Wb[table($R)]=view($R);connection()->select_db($Ii);$j=idf_escape(DB);foreach($Wb
as$B=>$Mj){if(!queries("CREATE VIEW $B AS ".str_replace(" $j."," ",$Mj["select"]))||!queries("DROP VIEW $j.$B"))return
false;}return
true;}return
false;}function
copy_tables(array$T,array$Nj,$Ii){queries("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");foreach($T
as$R){$B=($Ii==DB?table("copy_$R"):idf_escape($Ii).".".table($R));if(($_POST["overwrite"]&&!queries("\nDROP TABLE IF EXISTS $B"))||!queries("CREATE TABLE $B LIKE ".table($R))||!queries("INSERT INTO $B SELECT * FROM ".table($R)))return
false;foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")))as$K){$hj=$K["Trigger"];if(!queries("CREATE TRIGGER ".($Ii==DB?idf_escape("copy_$hj"):idf_escape($Ii).".".idf_escape($hj))." $K[Timing] $K[Event] ON $B FOR EACH ROW\n$K[Statement];"))return
false;}}foreach($Nj
as$R){$B=($Ii==DB?table("copy_$R"):idf_escape($Ii).".".table($R));$Mj=view($R);if(($_POST["overwrite"]&&!queries("DROP VIEW IF EXISTS $B"))||!queries("CREATE VIEW $B AS $Mj[select]"))return
false;}return
true;}function
trigger($B,$R){if($B=="")return
array();$L=get_rows("SHOW TRIGGERS WHERE `Trigger` = ".q($B));return
reset($L);}function
triggers($R){$J=array();foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")))as$K)$J[$K["Trigger"]]=array($K["Timing"],$K["Event"]);return$J;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER"),"Event"=>array("INSERT","UPDATE","DELETE"),"Type"=>array("FOR EACH ROW"),);}function
routine($B,$U){$ra=array("bool","boolean","integer","double precision","real","dec","numeric","fixed","national char","national varchar");$gi="(?:\\s|/\\*[\s\S]*?\\*/|(?:#|-- )[^\n]*\n?|--\r?\n)";$zc=driver()->enumLength;$mj="((".implode("|",array_merge(array_keys(driver()->types()),$ra)).")\\b(?:\\s*\\(((?:[^'\")]|$zc)++)\\))?"."\\s*(zerofill\\s*)?(unsigned(?:\\s+zerofill)?)?)(?:\\s*(?:CHARSET|CHARACTER\\s+SET)\\s*['\"]?([^'\"\\s,]+)['\"]?)?(?:\\s*COLLATE\\s*['\"]?[^'\"\\s,]+['\"]?)?";$Gg="$gi*(".($U=="FUNCTION"?"":driver()->inout).")?\\s*(?:`((?:[^`]|``)*)`\\s*|\\b(\\S+)\\s+)$mj";$h=get_val("SHOW CREATE $U ".idf_escape($B),2);preg_match("~\\(((?:$Gg\\s*,?)*)\\)\\s*".($U=="FUNCTION"?"RETURNS\\s+$mj\\s+":"")."(.*)~is",$h,$A);$n=array();preg_match_all("~$Gg\\s*,?~is",$A[1],$Ze,PREG_SET_ORDER);foreach($Ze
as$ug)$n[]=array("field"=>str_replace("``","`",$ug[2]).$ug[3],"type"=>strtolower($ug[5]),"length"=>preg_replace_callback("~$zc~s",'Adminer\normalize_enum',$ug[6]),"unsigned"=>strtolower(preg_replace('~\s+~',' ',trim("$ug[8] $ug[7]"))),"null"=>true,"full_type"=>$ug[4],"inout"=>strtoupper($ug[1]),"collation"=>strtolower($ug[9]),);return
array("fields"=>$n,"comment"=>get_val("SELECT ROUTINE_COMMENT FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_NAME = ".q($B)),)+($U!="FUNCTION"?array("definition"=>$A[11]):array("returns"=>array("type"=>$A[12],"length"=>$A[13],"unsigned"=>$A[15],"collation"=>$A[16]),"definition"=>$A[17],"language"=>"SQL",));}function
routines(){return
get_rows("SELECT SPECIFIC_NAME, ROUTINE_NAME, ROUTINE_TYPE, DTD_IDENTIFIER FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE()");}function
routine_languages(){return
array();}function
routine_id($B,array$K){return
idf_escape($B);}function
last_id($I){return
get_val("SELECT LAST_INSERT_ID()");}function
explain(Db$f,$H){return$f->query("EXPLAIN ".(min_version(5.1)&&!min_version(5.7)?"PARTITIONS ":"").$H);}function
found_rows(array$S,array$Z){return($Z||$S["Engine"]!="InnoDB"?null:$S["Rows"]);}function
create_sql($R,$_a,$si){$J=get_val("SHOW CREATE TABLE ".table($R),1);if(!$_a)$J=preg_replace('~ AUTO_INCREMENT=\d+~','',$J);return$J;}function
truncate_sql($R){return"TRUNCATE ".table($R);}function
use_sql($Nb,$si=""){$B=idf_escape($Nb);$J="";if(preg_match('~CREATE~',$si)&&($h=get_val("SHOW CREATE DATABASE $B",1))){set_utf8mb4($h);if($si=="DROP+CREATE")$J="DROP DATABASE IF EXISTS $B;\n";$J
.="$h;\n";}return$J."USE $B";}function
trigger_sql($R){$J="";foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")),null,"-- ")as$K)$J
.="\nCREATE TRIGGER ".idf_escape($K["Trigger"])." $K[Timing] $K[Event] ON ".table($K["Table"])." FOR EACH ROW\n$K[Statement];;\n";return$J;}function
show_variables(){return
get_rows("SHOW VARIABLES");}function
show_status(){return
get_rows("SHOW STATUS");}function
process_list(){return
get_rows("SHOW FULL PROCESSLIST");}function
convert_field(array$m){if(preg_match("~binary~",$m["type"]))return"HEX(".idf_escape($m["field"]).")";if($m["type"]=="bit")return"BIN(".idf_escape($m["field"])." + 0)";if(preg_match("~geometry|point|linestring|polygon~",$m["type"]))return(min_version(8)?"ST_":"")."AsWKT(".idf_escape($m["field"]).")";}function
unconvert_field(array$m,$J){if(preg_match("~binary~",$m["type"]))$J="UNHEX($J)";if($m["type"]=="bit")$J="CONVERT(b$J, UNSIGNED)";if(preg_match("~geometry|point|linestring|polygon~",$m["type"])){$Sg=(min_version(8)?"ST_":"");$J=$Sg."GeomFromText($J, $Sg"."SRID($m[field]))";}return$J;}function
support($Uc){return
preg_match('~^(comment|columns|copy|database|drop_col|dump|indexes|kill|privileges|move_col|procedure|processlist|routine|sql|status|table|trigger|variables|view'.(min_version(5.1)?'|event':'').(min_version(8)?'|descidx':'').(min_version('8.0.16','10.2.1')?'|check':'').')$~',$Uc);}function
kill_process($t){return
queries("KILL ".number($t));}function
connection_id(){return"SELECT CONNECTION_ID()";}function
max_connections(){return
get_val("SELECT @@max_connections");}function
types(){return
array();}function
type_values($t){return"";}function
schemas(){return
array();}function
get_schema(){return"";}function
set_schema($Fh,$g=null){return
true;}}define('Adminer\JUSH',Driver::$jush);define('Adminer\SERVER',"".$_GET[DRIVER]);define('Adminer\DB',"$_GET[db]");define('Adminer\ME',preg_replace('~\?.*~','',relative_uri()).'?'.(sid()?SID.'&':'').(SERVER!==null?DRIVER."=".urlencode(SERVER).'&':'').($_GET["ext"]?"ext=".urlencode($_GET["ext"]).'&':'').(isset($_GET["username"])?"username=".urlencode($_GET["username"]).'&':'').(DB!=""?'db='.urlencode(DB).'&'.(isset($_GET["ns"])?"ns=".urlencode($_GET["ns"])."&":""):''));function
page_header($Si,$l="",$Ma=array(),$Ti=""){page_headers();if(is_ajax()&&$l){page_messages($l);exit;}if(!ob_get_level())ob_start('ob_gzhandler',4096);$Ui=$Si.($Ti!=""?": $Ti":"");$Vi=strip_tags($Ui.(SERVER!=""&&SERVER!="localhost"?h(" - ".SERVER):"")." - ".adminer()->name());echo'<!DOCTYPE html>
<html lang="en" dir="ltr">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="robots" content="noindex">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>',$Vi,'</title>
<link rel="stylesheet" href="',h(preg_replace("~\\?.*~","",ME)."?file=default.css&version=5.4.1"),'">
';$Hb=adminer()->css();if(is_int(key($Hb)))$Hb=array_fill_keys($Hb,'light');$Ed=in_array('light',$Hb)||in_array('',$Hb);$Cd=in_array('dark',$Hb)||in_array('',$Hb);$Kb=($Ed?($Cd?null:false):($Cd?:null));$jf=" media='(prefers-color-scheme: dark)'";if($Kb!==false)echo"<link rel='stylesheet'".($Kb?"":$jf)." href='".h(preg_replace("~\\?.*~","",ME)."?file=dark.css&version=5.4.1")."'>\n";echo"<meta name='color-scheme' content='".($Kb===null?"light dark":($Kb?"dark":"light"))."'>\n",script_src(preg_replace("~\\?.*~","",ME)."?file=functions.js&version=5.4.1");if(adminer()->head($Kb))echo"<link rel='icon' href='data:image/gif;base64,R0lGODlhEAAQAJEAAAQCBPz+/PwCBAROZCH5BAEAAAAALAAAAAAQABAAAAI2hI+pGO1rmghihiUdvUBnZ3XBQA7f05mOak1RWXrNq5nQWHMKvuoJ37BhVEEfYxQzHjWQ5qIAADs='>\n","<link rel='apple-touch-icon' href='".h(preg_replace("~\\?.*~","",ME)."?file=logo.png&version=5.4.1")."'>\n";foreach($Hb
as$_j=>$tf){$ya=($tf=='dark'&&!$Kb?$jf:($tf=='light'&&$Cd?" media='(prefers-color-scheme: light)'":""));echo"<link rel='stylesheet'$ya href='".h($_j)."'>\n";}echo"\n<body class='".'ltr'." nojs";adminer()->bodyClass();echo"'>\n";$o=get_temp_dir()."/adminer.version";if(!$_COOKIE["adminer_version"]&&function_exists('openssl_verify')&&file_exists($o)&&filemtime($o)+86400>time()){$Lj=unserialize(file_get_contents($o));$ch="-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAwqWOVuF5uw7/+Z70djoK
RlHIZFZPO0uYRezq90+7Amk+FDNd7KkL5eDve+vHRJBLAszF/7XKXe11xwliIsFs
DFWQlsABVZB3oisKCBEuI71J4kPH8dKGEWR9jDHFw3cWmoH3PmqImX6FISWbG3B8
h7FIx3jEaw5ckVPVTeo5JRm/1DZzJxjyDenXvBQ/6o9DgZKeNDgxwKzH+sw9/YCO
jHnq1cFpOIISzARlrHMa/43YfeNRAm/tsBXjSxembBPo7aQZLAWHmaj5+K19H10B
nCpz9Y++cipkVEiKRGih4ZEvjoFysEOdRLj6WiD/uUNky4xGeA6LaJqh5XpkFkcQ
fQIDAQAB
-----END PUBLIC KEY-----
";if(openssl_verify($Lj["version"],base64_decode($Lj["signature"]),$ch)==1)$_COOKIE["adminer_version"]=$Lj["version"];}echo
script("mixin(document.body, {onkeydown: bodyKeydown, onclick: bodyClick".(isset($_COOKIE["adminer_version"])?"":", onload: partial(verifyVersion, '".VERSION."', '".js_escape(ME)."', '".get_token()."')")."});
document.body.classList.replace('nojs', 'js');
const offlineMessage = '".js_escape('You are offline.')."';
const thousandsSeparator = '".js_escape(',')."';"),"<div id='help' class='jush-".JUSH." jsonly hidden'></div>\n",script("mixin(qs('#help'), {onmouseover: () => { helpOpen = 1; }, onmouseout: helpMouseout});"),"<div id='content'>\n","<span id='menuopen' class='jsonly'>".icon("move","","menu","")."</span>".script("qs('#menuopen').onclick = event => { qs('#foot').classList.toggle('foot'); event.stopPropagation(); }");if($Ma!==null){$_=substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1);echo'<p id="breadcrumb"><a href="'.h($_?:".").'">'.get_driver(DRIVER).'</a> Â» ';$_=substr(preg_replace('~\b(db|ns)=[^&]*&~','',ME),0,-1);$N=adminer()->serverName(SERVER);$N=($N!=""?$N:'Server');if($Ma===false)echo"$N\n";else{echo"<a href='".h($_)."' accesskey='1' title='Alt+Shift+1'>$N</a> Â» ";if($_GET["ns"]!=""||(DB!=""&&is_array($Ma)))echo'<a href="'.h($_."&db=".urlencode(DB).(support("scheme")?"&ns=":"")).'">'.h(DB).'</a> Â» ';if(is_array($Ma)){if($_GET["ns"]!="")echo'<a href="'.h(substr(ME,0,-1)).'">'.h($_GET["ns"]).'</a> Â» ';foreach($Ma
as$x=>$X){$Yb=(is_array($X)?$X[1]:h($X));if($Yb!="")echo"<a href='".h(ME."$x=").urlencode(is_array($X)?$X[0]:$X)."'>$Yb</a> Â» ";}}echo"$Si\n";}}echo"<h2>$Ui</h2>\n","<div id='ajaxstatus' class='jsonly hidden'></div>\n";restart_session();page_messages($l);$i=&get_session("dbs");if(DB!=""&&$i&&!in_array(DB,$i,true))$i=null;stop_session();define('Adminer\PAGE_HEADER',1);}function
page_headers(){header("Content-Type: text/html; charset=utf-8");header("Cache-Control: no-cache");header("X-Frame-Options: deny");header("X-XSS-Protection: 0");header("X-Content-Type-Options: nosniff");header("Referrer-Policy: origin-when-cross-origin");foreach(adminer()->csp(csp())as$Gb){$Gd=array();foreach($Gb
as$x=>$X)$Gd[]="$x $X";header("Content-Security-Policy: ".implode("; ",$Gd));}adminer()->headers();}function
csp(){return
array(array("script-src"=>"'self' 'unsafe-inline' 'nonce-".get_nonce()."' 'strict-dynamic'","connect-src"=>"'self'","frame-src"=>"https://www.adminer.org","object-src"=>"'none'","base-uri"=>"'none'","form-action"=>"'self'",),);}function
get_nonce(){static$Ff;if(!$Ff)$Ff=base64_encode(rand_string());return$Ff;}function
page_messages($l){$zj=preg_replace('~^[^?]*~','',$_SERVER["REQUEST_URI"]);$pf=idx($_SESSION["messages"],$zj);if($pf){echo"<div class='message'>".implode("</div>\n<div class='message'>",$pf)."</div>".script("messagesPrint();");unset($_SESSION["messages"][$zj]);}if($l)echo"<div class='error'>$l</div>\n";if(adminer()->error)echo"<div class='error'>".adminer()->error."</div>\n";}function
page_footer($sf=""){echo"</div>\n\n<div id='foot' class='foot'>\n<div id='menu'>\n";adminer()->navigation($sf);echo"</div>\n";if($sf!="auth")echo'<form action="" method="post">
<p class="logout">
<span>',h($_GET["username"])."\n",'</span>
<input type="submit" name="logout" value="Logout" id="logout">
',input_token(),'</form>
';echo"</div>\n\n",script("setupSubmitHighlight(document);");}function
int32($yf){while($yf>=2147483648)$yf-=4294967296;while($yf<=-2147483649)$yf+=4294967296;return(int)$yf;}function
long2str(array$W,$Pj){$Dh='';foreach($W
as$X)$Dh
.=pack('V',$X);if($Pj)return
substr($Dh,0,end($W));return$Dh;}function
str2long($Dh,$Pj){$W=array_values(unpack('V*',str_pad($Dh,4*ceil(strlen($Dh)/4),"\0")));if($Pj)$W[]=strlen($Dh);return$W;}function
xxtea_mx($Wj,$Vj,$vi,$Ae){return
int32((($Wj>>5&0x7FFFFFF)^$Vj<<2)+(($Vj>>3&0x1FFFFFFF)^$Wj<<4))^int32(($vi^$Vj)+($Ae^$Wj));}function
encrypt_string($qi,$x){if($qi=="")return"";$x=array_values(unpack("V*",pack("H*",md5($x))));$W=str2long($qi,true);$yf=count($W)-1;$Wj=$W[$yf];$Vj=$W[0];$dh=floor(6+52/($yf+1));$vi=0;while($dh-->0){$vi=int32($vi+0x9E3779B9);$oc=$vi>>2&3;for($sg=0;$sg<$yf;$sg++){$Vj=$W[$sg+1];$xf=xxtea_mx($Wj,$Vj,$vi,$x[$sg&3^$oc]);$Wj=int32($W[$sg]+$xf);$W[$sg]=$Wj;}$Vj=$W[0];$xf=xxtea_mx($Wj,$Vj,$vi,$x[$sg&3^$oc]);$Wj=int32($W[$yf]+$xf);$W[$yf]=$Wj;}return
long2str($W,false);}function
decrypt_string($qi,$x){if($qi=="")return"";if(!$x)return
false;$x=array_values(unpack("V*",pack("H*",md5($x))));$W=str2long($qi,false);$yf=count($W)-1;$Wj=$W[$yf];$Vj=$W[0];$dh=floor(6+52/($yf+1));$vi=int32($dh*0x9E3779B9);while($vi){$oc=$vi>>2&3;for($sg=$yf;$sg>0;$sg--){$Wj=$W[$sg-1];$xf=xxtea_mx($Wj,$Vj,$vi,$x[$sg&3^$oc]);$Vj=int32($W[$sg]-$xf);$W[$sg]=$Vj;}$Wj=$W[$yf];$xf=xxtea_mx($Wj,$Vj,$vi,$x[$sg&3^$oc]);$Vj=int32($W[0]-$xf);$W[0]=$Vj;$vi=int32($vi-0x9E3779B9);}return
long2str($W,true);}$Ig=array();if($_COOKIE["adminer_permanent"]){foreach(explode(" ",$_COOKIE["adminer_permanent"])as$X){list($x)=explode(":",$X);$Ig[$x]=$X;}}function
add_invalid_login(){$Fa=get_temp_dir()."/adminer.invalid";foreach(glob("$Fa*")?:array($Fa)as$o){$q=file_open_lock($o);if($q)break;}if(!$q)$q=file_open_lock("$Fa-".rand_string());if(!$q)return;$se=unserialize(stream_get_contents($q));$Pi=time();if($se){foreach($se
as$te=>$X){if($X[0]<$Pi)unset($se[$te]);}}$re=&$se[adminer()->bruteForceKey()];if(!$re)$re=array($Pi+30*60,0);$re[1]++;file_write_unlock($q,serialize($se));}function
check_invalid_login(array&$Ig){$se=array();foreach(glob(get_temp_dir()."/adminer.invalid*")as$o){$q=file_open_lock($o);if($q){$se=unserialize(stream_get_contents($q));file_unlock($q);break;}}$re=idx($se,adminer()->bruteForceKey(),array());$Ef=($re[1]>29?$re[0]-time():0);if($Ef>0)auth_error(lang_format(array('Too many unsuccessful logins, try again in %d minute.','Too many unsuccessful logins, try again in %d minutes.'),ceil($Ef/60)),$Ig);}$za=$_POST["auth"];if($za){session_regenerate_id();$Kj=$za["driver"];$N=$za["server"];$V=$za["username"];$F=(string)$za["password"];$j=$za["db"];set_password($Kj,$N,$V,$F);$_SESSION["db"][$Kj][$N][$V][$j]=true;if($za["permanent"]){$x=implode("-",array_map('base64_encode',array($Kj,$N,$V,$j)));$Xg=adminer()->permanentLogin(true);$Ig[$x]="$x:".base64_encode($Xg?encrypt_string($F,$Xg):"");cookie("adminer_permanent",implode(" ",$Ig));}if(count($_POST)==1||DRIVER!=$Kj||SERVER!=$N||$_GET["username"]!==$V||DB!=$j)redirect(auth_url($Kj,$N,$V,$j));}elseif($_POST["logout"]&&(!$_SESSION["token"]||verify_token())){foreach(array("pwds","db","dbs","queries")as$x)set_session($x,null);unset_permanent($Ig);redirect(substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1),'Logout successful.'.' '.'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.');}elseif($Ig&&!$_SESSION["pwds"]){session_regenerate_id();$Xg=adminer()->permanentLogin();foreach($Ig
as$x=>$X){list(,$cb)=explode(":",$X);list($Kj,$N,$V,$j)=array_map('base64_decode',explode("-",$x));set_password($Kj,$N,$V,decrypt_string(base64_decode($cb),$Xg));$_SESSION["db"][$Kj][$N][$V][$j]=true;}}function
unset_permanent(array&$Ig){foreach($Ig
as$x=>$X){list($Kj,$N,$V,$j)=array_map('base64_decode',explode("-",$x));if($Kj==DRIVER&&$N==SERVER&&$V==$_GET["username"]&&$j==DB)unset($Ig[$x]);}cookie("adminer_permanent",implode(" ",$Ig));}function
auth_error($l,array&$Ig){$Xh=session_name();if(isset($_GET["username"])){header("HTTP/1.1 403 Forbidden");if(($_COOKIE[$Xh]||$_GET[$Xh])&&!$_SESSION["token"])$l='Session expired, please login again.';else{restart_session();add_invalid_login();$F=get_password();if($F!==null){if($F===false)$l
.=($l?'<br>':'').sprintf('Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> %s method to make it permanent.',target_blank(),'<code>permanentLogin()</code>');set_password(DRIVER,SERVER,$_GET["username"],null);}unset_permanent($Ig);}}if(!$_COOKIE[$Xh]&&$_GET[$Xh]&&ini_bool("session.use_only_cookies"))$l='Session support must be enabled.';$vg=session_get_cookie_params();cookie("adminer_key",($_COOKIE["adminer_key"]?:rand_string()),$vg["lifetime"]);if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);page_header('Login',$l,null);echo"<form action='' method='post'>\n","<div>";if(hidden_fields($_POST,array("auth")))echo"<p class='message'>".'The action will be performed after successful login with the same credentials.'."\n";echo"</div>\n";adminer()->loginForm();echo"</form>\n";page_footer("auth");exit;}if(isset($_GET["username"])&&!class_exists('Adminer\Db')){unset($_SESSION["pwds"][DRIVER]);unset_permanent($Ig);page_header('No extension',sprintf('None of the supported PHP extensions (%s) are available.',implode(", ",Driver::$extensions)),false);page_footer("auth");exit;}$f='';if(isset($_GET["username"])&&is_string(get_password())){list(,$Mg)=host_port(SERVER);if(preg_match('~^\s*([-+]?\d+)~',$Mg,$A)&&($A[1]<1024||$A[1]>65535))auth_error('Connecting to privileged ports is not allowed.',$Ig);check_invalid_login($Ig);$Fb=adminer()->credentials();$f=Driver::connect($Fb[0],$Fb[1],$Fb[2]);if(is_object($f)){Db::$instance=$f;Driver::$instance=new
Driver($f);if($f->flavor)save_settings(array("vendor-".DRIVER."-".SERVER=>get_driver(DRIVER)));}}$Te=null;if(!is_object($f)||($Te=adminer()->login($_GET["username"],get_password()))!==true){$l=(is_string($f)?nl_br(h($f)):(is_string($Te)?$Te:'Invalid credentials.')).(preg_match('~^ | $~',get_password())?'<br>'.'There is a space in the input password which might be the cause.':'');auth_error($l,$Ig);}if($_POST["logout"]&&$_SESSION["token"]&&!verify_token()){page_header('Logout','Invalid CSRF token. Send the form again.');page_footer("db");exit;}if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);stop_session(true);if($za&&$_POST["token"])$_POST["token"]=get_token();$l='';if($_POST){if(!verify_token()){$ke="max_input_vars";$hf=ini_get($ke);if(extension_loaded("suhosin")){foreach(array("suhosin.request.max_vars","suhosin.post.max_vars")as$x){$X=ini_get($x);if($X&&(!$hf||$X<$hf)){$ke=$x;$hf=$X;}}}$l=(!$_POST["token"]&&$hf?sprintf('Maximum number of allowed fields exceeded. Please increase %s.',"'$ke'"):'Invalid CSRF token. Send the form again.'.' '.'If you did not send this request from Adminer then close this page.');}}elseif($_SERVER["REQUEST_METHOD"]=="POST"){$l=sprintf('Too big POST data. Reduce the data or increase the %s configuration directive.',"'post_max_size'");if(isset($_GET["sql"]))$l
.=' '.'You can upload a big SQL file via FTP and import it from server.';}function
print_select_result($I,$g=null,array$hg=array(),$z=0){$Re=array();$w=array();$e=array();$Ka=array();$nj=array();$J=array();for($s=0;(!$z||$s<$z)&&($K=$I->fetch_row());$s++){if(!$s){echo"<div class='scrollable'>\n","<table class='nowrap odds'>\n","<thead><tr>";for($ze=0;$ze<count($K);$ze++){$m=$I->fetch_field();$B=$m->name;$gg=(isset($m->orgtable)?$m->orgtable:"");$fg=(isset($m->orgname)?$m->orgname:$B);if($hg&&JUSH=="sql")$Re[$ze]=($B=="table"?"table=":($B=="possible_keys"?"indexes=":null));elseif($gg!=""){if(isset($m->table))$J[$m->table]=$gg;if(!isset($w[$gg])){$w[$gg]=array();foreach(indexes($gg,$g)as$v){if($v["type"]=="PRIMARY"){$w[$gg]=array_flip($v["columns"]);break;}}$e[$gg]=$w[$gg];}if(isset($e[$gg][$fg])){unset($e[$gg][$fg]);$w[$gg][$fg]=$ze;$Re[$ze]=$gg;}}if($m->charsetnr==63)$Ka[$ze]=true;$nj[$ze]=$m->type;echo"<th".($gg!=""||$m->name!=$fg?" title='".h(($gg!=""?"$gg.":"").$fg)."'":"").">".h($B).($hg?doc_link(array('sql'=>"explain-output.html#explain_".strtolower($B),'mariadb'=>"explain/#the-columns-in-explain-select",)):"");}echo"</thead>\n";}echo"<tr>";foreach($K
as$x=>$X){$_="";if(isset($Re[$x])&&!$e[$Re[$x]]){if($hg&&JUSH=="sql"){$R=$K[array_search("table=",$Re)];$_=ME.$Re[$x].urlencode($hg[$R]!=""?$hg[$R]:$R);}else{$_=ME."edit=".urlencode($Re[$x]);foreach($w[$Re[$x]]as$hb=>$ze){if($K[$ze]===null){$_="";break;}$_
.="&where".urlencode("[".bracket_escape($hb)."]")."=".urlencode($K[$ze]);}}}elseif(is_url($X))$_=$X;if($X===null)$X="<i>NULL</i>";elseif($Ka[$x]&&!is_utf8($X))$X="<i>".lang_format(array('%d byte','%d bytes'),strlen($X))."</i>";else{$X=h($X);if($nj[$x]==254)$X="<code>$X</code>";}if($_)$X="<a href='".h($_)."'".(is_url($_)?target_blank():'').">$X</a>";echo"<td".($nj[$x]<=9||$nj[$x]==246?" class='number'":"").">$X";}}echo($s?"</table>\n</div>":"<p class='message'>".'No rows.')."\n";return$J;}function
referencable_primary($Ph){$J=array();foreach(table_status('',true)as$_i=>$R){if($_i!=$Ph&&fk_support($R)){foreach(fields($_i)as$m){if($m["primary"]){if($J[$_i]){unset($J[$_i]);break;}$J[$_i]=$m;}}}}return$J;}function
textarea($B,$Y,$L=10,$kb=80){echo"<textarea name='".h($B)."' rows='$L' cols='$kb' class='sqlarea jush-".JUSH."' spellcheck='false' wrap='off'>";if(is_array($Y)){foreach($Y
as$X)echo
h($X[0])."\n\n\n";}else
echo
h($Y);echo"</textarea>";}function
select_input($ya,array$bg,$Y="",$Vf="",$Jg=""){$Hi=($bg?"select":"input");return"<$Hi$ya".($bg?"><option value=''>$Jg".optionlist($bg,$Y,true)."</select>":" size='10' value='".h($Y)."' placeholder='$Jg'>").($Vf?script("qsl('$Hi').onchange = $Vf;",""):"");}function
json_row($x,$X=null,$Fc=true){static$bd=true;if($bd)echo"{";if($x!=""){echo($bd?"":",")."\n\t\"".addcslashes($x,"\r\n\t\"\\/").'": '.($X!==null?($Fc?'"'.addcslashes($X,"\r\n\"\\/").'"':$X):'null');$bd=false;}else{echo"\n}\n";$bd=true;}}function
edit_type($x,array$m,array$jb,array$ld=array(),array$Rc=array()){$U=$m["type"];echo"<td><select name='".h($x)."[type]' class='type' aria-labelledby='label-type'>";if($U&&!array_key_exists($U,driver()->types())&&!isset($ld[$U])&&!in_array($U,$Rc))$Rc[]=$U;$ri=driver()->structuredTypes();if($ld)$ri['Foreign keys']=$ld;echo
optionlist(array_merge($Rc,$ri),$U),"</select><td>","<input name='".h($x)."[length]' value='".h($m["length"])."' size='3'".(!$m["length"]&&preg_match('~var(char|binary)$~',$U)?" class='required'":"")." aria-labelledby='label-length'>","<td class='options'>",($jb?"<input list='collations' name='".h($x)."[collation]'".(preg_match('~(char|text|enum|set)$~',$U)?"":" class='hidden'")." value='".h($m["collation"])."' placeholder='(".'collation'.")'>":''),(driver()->unsigned?"<select name='".h($x)."[unsigned]'".(!$U||preg_match(number_type(),$U)?"":" class='hidden'").'><option>'.optionlist(driver()->unsigned,$m["unsigned"]).'</select>':''),(isset($m['on_update'])?"<select name='".h($x)."[on_update]'".(preg_match('~timestamp|datetime~',$U)?"":" class='hidden'").'>'.optionlist(array(""=>"(".'ON UPDATE'.")","CURRENT_TIMESTAMP"),(preg_match('~^CURRENT_TIMESTAMP~i',$m["on_update"])?"CURRENT_TIMESTAMP":$m["on_update"])).'</select>':''),($ld?"<select name='".h($x)."[on_delete]'".(preg_match("~`~",$U)?"":" class='hidden'")."><option value=''>(".'ON DELETE'.")".optionlist(explode("|",driver()->onActions),$m["on_delete"])."</select> ":" ");}function
process_length($y){$Ac=driver()->enumLength;return(preg_match("~^\\s*\\(?\\s*$Ac(?:\\s*,\\s*$Ac)*+\\s*\\)?\\s*\$~",$y)&&preg_match_all("~$Ac~",$y,$Ze)?"(".implode(",",$Ze[0]).")":preg_replace('~^[0-9].*~','(\0)',preg_replace('~[^-0-9,+()[\]]~','',$y)));}function
process_type(array$m,$ib="COLLATE"){return" $m[type]".process_length($m["length"]).(preg_match(number_type(),$m["type"])&&in_array($m["unsigned"],driver()->unsigned)?" $m[unsigned]":"").(preg_match('~char|text|enum|set~',$m["type"])&&$m["collation"]?" $ib ".(JUSH=="mssql"?$m["collation"]:q($m["collation"])):"");}function
process_field(array$m,array$lj){if($m["on_update"])$m["on_update"]=str_ireplace("current_timestamp()","CURRENT_TIMESTAMP",$m["on_update"]);return
array(idf_escape(trim($m["field"])),process_type($lj),($m["null"]?" NULL":" NOT NULL"),default_value($m),(preg_match('~timestamp|datetime~',$m["type"])&&$m["on_update"]?" ON UPDATE $m[on_update]":""),(support("comment")&&$m["comment"]!=""?" COMMENT ".q($m["comment"]):""),($m["auto_increment"]?auto_increment():null),);}function
default_value(array$m){$k=$m["default"];$sd=$m["generated"];return($k===null?"":(in_array($sd,driver()->generated)?(JUSH=="mssql"?" AS ($k)".($sd=="VIRTUAL"?"":" $sd")."":" GENERATED ALWAYS AS ($k) $sd"):" DEFAULT ".(!preg_match('~^GENERATED ~i',$k)&&(preg_match('~char|binary|text|json|enum|set~',$m["type"])||preg_match('~^(?![a-z])~i',$k))?(JUSH=="sql"&&preg_match('~text|json~',$m["type"])?"(".q($k).")":q($k)):str_ireplace("current_timestamp()","CURRENT_TIMESTAMP",(JUSH=="sqlite"?"($k)":$k)))));}function
type_class($U){foreach(array('char'=>'text','date'=>'time|year','binary'=>'blob','enum'=>'set',)as$x=>$X){if(preg_match("~$x|$X~",$U))return" class='$x'";}}function
edit_fields(array$n,array$jb,$U="TABLE",array$ld=array()){$n=array_values($n);$Tb=(($_POST?$_POST["defaults"]:get_setting("defaults"))?"":" class='hidden'");$pb=(($_POST?$_POST["comments"]:get_setting("comments"))?"":" class='hidden'");echo"<thead><tr>\n",($U=="PROCEDURE"?"<td>":""),"<th id='label-name'>".($U=="TABLE"?'Column name':'Parameter name'),"<td id='label-type'>".'Type'."<textarea id='enum-edit' rows='4' cols='12' wrap='off' style='display: none;'></textarea>".script("qs('#enum-edit').onblur = editingLengthBlur;"),"<td id='label-length'>".'Length',"<td>".'Options';if($U=="TABLE")echo"<td id='label-null'>NULL\n","<td><input type='radio' name='auto_increment_col' value=''><abbr id='label-ai' title='".'Auto Increment'."'>AI</abbr>",doc_link(array('sql'=>"example-auto-increment.html",'mariadb'=>"auto_increment/",'sqlite'=>"autoinc.html",'pgsql'=>"datatype-numeric.html#DATATYPE-SERIAL",'mssql'=>"t-sql/statements/create-table-transact-sql-identity-property",)),"<td id='label-default'$Tb>".'Default value',(support("comment")?"<td id='label-comment'$pb>".'Comment':"");echo"<td>".icon("plus","add[".(support("move_col")?0:count($n))."]","+",'Add next'),"</thead>\n<tbody>\n",script("mixin(qsl('tbody'), {onclick: editingClick, onkeydown: editingKeydown, oninput: editingInput});");foreach($n
as$s=>$m){$s++;$ig=$m[($_POST?"orig":"field")];$ec=(isset($_POST["add"][$s-1])||(isset($m["field"])&&!idx($_POST["drop_col"],$s)))&&(support("drop_col")||$ig=="");echo"<tr".($ec?"":" style='display: none;'").">\n",($U=="PROCEDURE"?"<td>".html_select("fields[$s][inout]",explode("|",driver()->inout),$m["inout"]):"")."<th>";if($ec)echo"<input name='fields[$s][field]' value='".h($m["field"])."' data-maxlength='64' autocapitalize='off' aria-labelledby='label-name'".(isset($_POST["add"][$s-1])?" autofocus":"").">";echo
input_hidden("fields[$s][orig]",$ig);edit_type("fields[$s]",$m,$jb,$ld);if($U=="TABLE")echo"<td>".checkbox("fields[$s][null]",1,$m["null"],"","","block","label-null"),"<td><label class='block'><input type='radio' name='auto_increment_col' value='$s'".($m["auto_increment"]?" checked":"")." aria-labelledby='label-ai'></label>","<td$Tb>".(driver()->generated?html_select("fields[$s][generated]",array_merge(array("","DEFAULT"),driver()->generated),$m["generated"])." ":checkbox("fields[$s][generated]",1,$m["generated"],"","","","label-default")),"<input name='fields[$s][default]' value='".h($m["default"])."' aria-labelledby='label-default'>",(support("comment")?"<td$pb><input name='fields[$s][comment]' value='".h($m["comment"])."' data-maxlength='".(min_version(5.5)?1024:255)."' aria-labelledby='label-comment'>":"");echo"<td>",(support("move_col")?icon("plus","add[$s]","+",'Add next')." ".icon("up","up[$s]","â†‘",'Move up')." ".icon("down","down[$s]","â†“",'Move down')." ":""),($ig==""||support("drop_col")?icon("cross","drop_col[$s]","x",'Remove'):"");}}function
process_fields(array&$n){$C=0;if($_POST["up"]){$Ie=0;foreach($n
as$x=>$m){if(key($_POST["up"])==$x){unset($n[$x]);array_splice($n,$Ie,0,array($m));break;}if(isset($m["field"]))$Ie=$C;$C++;}}elseif($_POST["down"]){$nd=false;foreach($n
as$x=>$m){if(isset($m["field"])&&$nd){unset($n[key($_POST["down"])]);array_splice($n,$C,0,array($nd));break;}if(key($_POST["down"])==$x)$nd=$m;$C++;}}elseif($_POST["add"]){$n=array_values($n);array_splice($n,key($_POST["add"]),0,array(array()));}elseif(!$_POST["drop_col"])return
false;return
true;}function
normalize_enum(array$A){$X=$A[0];return"'".str_replace("'","''",addcslashes(stripcslashes(str_replace($X[0].$X[0],$X[0],substr($X,1,-1))),'\\'))."'";}function
grant($ud,array$Zg,$e,$Sf){if(!$Zg)return
true;if($Zg==array("ALL PRIVILEGES","GRANT OPTION"))return($ud=="GRANT"?queries("$ud ALL PRIVILEGES$Sf WITH GRANT OPTION"):queries("$ud ALL PRIVILEGES$Sf")&&queries("$ud GRANT OPTION$Sf"));return
queries("$ud ".preg_replace('~(GRANT OPTION)\([^)]*\)~','\1',implode("$e, ",$Zg).$e).$Sf);}function
drop_create($ic,$h,$kc,$Li,$mc,$Se,$of,$mf,$nf,$Pf,$Bf){if($_POST["drop"])query_redirect($ic,$Se,$of);elseif($Pf=="")query_redirect($h,$Se,$nf);elseif($Pf!=$Bf){$Eb=queries($h);queries_redirect($Se,$mf,$Eb&&queries($ic));if($Eb)queries($kc);}else
queries_redirect($Se,$mf,queries($Li)&&queries($mc)&&queries($ic)&&queries($h));}function
create_trigger($Sf,array$K){$Ri=" $K[Timing] $K[Event]".(preg_match('~ OF~',$K["Event"])?" $K[Of]":"");return"CREATE TRIGGER ".idf_escape($K["Trigger"]).(JUSH=="mssql"?$Sf.$Ri:$Ri.$Sf).rtrim(" $K[Type]\n$K[Statement]",";").";";}function
create_routine($_h,array$K){$O=array();$n=(array)$K["fields"];ksort($n);foreach($n
as$m){if($m["field"]!="")$O[]=(preg_match("~^(".driver()->inout.")\$~",$m["inout"])?"$m[inout] ":"").idf_escape($m["field"]).process_type($m,"CHARACTER SET");}$Vb=rtrim($K["definition"],";");return"CREATE $_h ".idf_escape(trim($K["name"]))." (".implode(", ",$O).")".($_h=="FUNCTION"?" RETURNS".process_type($K["returns"],"CHARACTER SET"):"").($K["language"]?" LANGUAGE $K[language]":"").(JUSH=="pgsql"?" AS ".q($Vb):"\n$Vb;");}function
remove_definer($H){return
preg_replace('~^([A-Z =]+) DEFINER=`'.preg_replace('~@(.*)~','`@`(%|\1)',logged_user()).'`~','\1',$H);}function
format_foreign_key(array$p){$j=$p["db"];$Gf=$p["ns"];return" FOREIGN KEY (".implode(", ",array_map('Adminer\idf_escape',$p["source"])).") REFERENCES ".($j!=""&&$j!=$_GET["db"]?idf_escape($j).".":"").($Gf!=""&&$Gf!=$_GET["ns"]?idf_escape($Gf).".":"").idf_escape($p["table"])." (".implode(", ",array_map('Adminer\idf_escape',$p["target"])).")".(preg_match("~^(".driver()->onActions.")\$~",$p["on_delete"])?" ON DELETE $p[on_delete]":"").(preg_match("~^(".driver()->onActions.")\$~",$p["on_update"])?" ON UPDATE $p[on_update]":"");}function
tar_file($o,$Wi){$J=pack("a100a8a8a8a12a12",$o,644,0,0,decoct($Wi->size),decoct(time()));$bb=8*32;for($s=0;$s<strlen($J);$s++)$bb+=ord($J[$s]);$J
.=sprintf("%06o",$bb)."\0 ";echo$J,str_repeat("\0",512-strlen($J));$Wi->send();echo
str_repeat("\0",511-($Wi->size+511)%512);}function
doc_link(array$Fg,$Mi="<sup>?</sup>"){$Vh=connection()->server_info;$Lj=preg_replace('~^(\d\.?\d).*~s','\1',$Vh);$Aj=array('sql'=>"https://dev.mysql.com/doc/refman/$Lj/en/",'sqlite'=>"https://www.sqlite.org/",'pgsql'=>"https://www.postgresql.org/docs/".(connection()->flavor=='cockroach'?"current":$Lj)."/",'mssql'=>"https://learn.microsoft.com/en-us/sql/",'oracle'=>"https://www.oracle.com/pls/topic/lookup?ctx=db".preg_replace('~^.* (\d+)\.(\d+)\.\d+\.\d+\.\d+.*~s','\1\2',$Vh)."&id=",);if(connection()->flavor=='maria'){$Aj['sql']="https://mariadb.com/kb/en/";$Fg['sql']=(isset($Fg['mariadb'])?$Fg['mariadb']:str_replace(".html","/",$Fg['sql']));}return($Fg[JUSH]?"<a href='".h($Aj[JUSH].$Fg[JUSH].(JUSH=='mssql'?"?view=sql-server-ver$Lj":""))."'".target_blank().">$Mi</a>":"");}function
db_size($j){if(!connection()->select_db($j))return"?";$J=0;foreach(table_status()as$S)$J+=$S["Data_length"]+$S["Index_length"];return
format_number($J);}function
set_utf8mb4($h){static$O=false;if(!$O&&preg_match('~\butf8mb4~i',$h)){$O=true;echo"SET NAMES ".charset(connection()).";\n\n";}}if(isset($_GET["status"]))$_GET["variables"]=$_GET["status"];if(isset($_GET["import"]))$_GET["sql"]=$_GET["import"];if(!(DB!=""?connection()->select_db(DB):isset($_GET["sql"])||isset($_GET["dump"])||isset($_GET["database"])||isset($_GET["processlist"])||isset($_GET["privileges"])||isset($_GET["user"])||isset($_GET["variables"])||$_GET["script"]=="connect"||$_GET["script"]=="kill")){if(DB!=""||$_GET["refresh"]){restart_session();set_session("dbs",null);}if(DB!=""){header("HTTP/1.1 404 Not Found");page_header('Database'.": ".h(DB),'Invalid database.',true);}else{if($_POST["db"]&&!$l)queries_redirect(substr(ME,0,-1),'Databases have been dropped.',drop_databases($_POST["db"]));page_header('Select database',$l,false);echo"<p class='links'>\n";foreach(array('database'=>'Create database','privileges'=>'Privileges','processlist'=>'Process list','variables'=>'Variables','status'=>'Status',)as$x=>$X){if(support($x))echo"<a href='".h(ME)."$x='>$X</a>\n";}echo"<p>".sprintf('%s version: %s through PHP extension %s',get_driver(DRIVER),"<b>".h(connection()->server_info)."</b>","<b>".connection()->extension."</b>")."\n","<p>".sprintf('Logged as: %s',"<b>".h(logged_user())."</b>")."\n";$i=adminer()->databases();if($i){$Hh=support("scheme");$jb=collations();echo"<form action='' method='post'>\n","<table class='checkable odds'>\n",script("mixin(qsl('table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});"),"<thead><tr>".(support("database")?"<td>":"")."<th>".'Database'.(get_session("dbs")!==null?" - <a href='".h(ME)."refresh=1'>".'Refresh'."</a>":"")."<td>".'Collation'."<td>".'Tables'."<td>".'Size'." - <a href='".h(ME)."dbsize=1'>".'Compute'."</a>".script("qsl('a').onclick = partial(ajaxSetHtml, '".js_escape(ME)."script=connect');","")."</thead>\n";$i=($_GET["dbsize"]?count_tables($i):array_flip($i));foreach($i
as$j=>$T){$zh=h(ME)."db=".urlencode($j);$t=h("Db-".$j);echo"<tr>".(support("database")?"<td>".checkbox("db[]",$j,in_array($j,(array)$_POST["db"]),"","","",$t):""),"<th><a href='$zh' id='$t'>".h($j)."</a>";$c=h(db_collation($j,$jb));echo"<td>".(support("database")?"<a href='$zh".($Hh?"&amp;ns=":"")."&amp;database=' title='".'Alter database'."'>$c</a>":$c),"<td align='right'><a href='$zh&amp;schema=' id='tables-".h($j)."' title='".'Database schema'."'>".($_GET["dbsize"]?$T:"?")."</a>","<td align='right' id='size-".h($j)."'>".($_GET["dbsize"]?db_size($j):"?"),"\n";}echo"</table>\n",(support("database")?"<div class='footer'><div>\n"."<fieldset><legend>".'Selected'." <span id='selected'></span></legend><div>\n".input_hidden("all").script("qsl('input').onclick = function () { selectCount('selected', formChecked(this, /^db/)); };")."<input type='submit' name='drop' value='".'Drop'."'>".confirm()."\n"."</div></fieldset>\n"."</div></div>\n":""),input_token(),"</form>\n",script("tableCheck();");}if(!empty(adminer()->plugins)){echo"<div class='plugins'>\n","<h3>".'Loaded plugins'."</h3>\n<ul>\n";foreach(adminer()->plugins
as$Kg){$Zb=(method_exists($Kg,'description')?$Kg->description():"");if(!$Zb){$oh=new
\ReflectionObject($Kg);if(preg_match('~^/[\s*]+(.+)~',$oh->getDocComment(),$A))$Zb=$A[1];}$Ih=(method_exists($Kg,'screenshot')?$Kg->screenshot():"");echo"<li><b>".get_class($Kg)."</b>".h($Zb?": $Zb":"").($Ih?" (<a href='".h($Ih)."'".target_blank().">".'screenshot'."</a>)":"")."\n";}echo"</ul>\n";adminer()->pluginsLinks();echo"</div>\n";}}page_footer("db");exit;}if(support("scheme")){if(DB!=""&&$_GET["ns"]!==""){if(!isset($_GET["ns"]))redirect(preg_replace('~ns=[^&]*&~','',ME)."ns=".get_schema());if(!set_schema($_GET["ns"])){header("HTTP/1.1 404 Not Found");page_header('Schema'.": ".h($_GET["ns"]),'Invalid schema.',true);page_footer("ns");exit;}}}adminer()->afterConnect();class
TmpFile{private$handler;var$size;function
__construct(){$this->handler=tmpfile();}function
write($zb){$this->size+=strlen($zb);fwrite($this->handler,$zb);}function
send(){fseek($this->handler,0);fpassthru($this->handler);fclose($this->handler);}}if(isset($_GET["select"])&&($_POST["edit"]||$_POST["clone"])&&!$_POST["save"])$_GET["edit"]=$_GET["select"];if(isset($_GET["callf"]))$_GET["call"]=$_GET["callf"];if(isset($_GET["function"]))$_GET["procedure"]=$_GET["function"];if(isset($_GET["download"])){$a=$_GET["download"];$n=fields($a);header("Content-Type: application/octet-stream");header("Content-Disposition: attachment; filename=".friendly_url("$a-".implode("_",$_GET["where"])).".".friendly_url($_GET["field"]));$M=array(idf_escape($_GET["field"]));$I=driver()->select($a,$M,array(where($_GET,$n)),$M);$K=($I?$I->fetch_row():array());echo
driver()->value($K[0],$n[$_GET["field"]]);exit;}elseif(isset($_GET["table"])){$a=$_GET["table"];$n=fields($a);if(!$n)$l=error()?:'No tables.';$S=table_status1($a);$B=adminer()->tableName($S);page_header(($n&&is_view($S)?$S['Engine']=='materialized view'?'Materialized view':'View':'Table').": ".($B!=""?$B:h($a)),$l);$yh=array();foreach($n
as$x=>$m)$yh+=$m["privileges"];adminer()->selectLinks($S,(isset($yh["insert"])||!support("table")?"":null));$ob=$S["Comment"];if($ob!="")echo"<p class='nowrap'>".'Comment'.": ".h($ob)."\n";if($n)adminer()->tableStructurePrint($n,$S);function
tables_links(array$T){echo"<ul>\n";foreach($T
as$R)echo"<li><a href='".h(ME."table=".urlencode($R))."'>".h($R)."</a>";echo"</ul>\n";}$je=driver()->inheritsFrom($a);if($je){echo"<h3>".'Inherits from'."</h3>\n";tables_links($je);}if(support("indexes")&&driver()->supportsIndex($S)){echo"<h3 id='indexes'>".'Indexes'."</h3>\n";$w=indexes($a);if($w)adminer()->tableIndexesPrint($w,$S);echo'<p class="links"><a href="'.h(ME).'indexes='.urlencode($a).'">'.'Alter indexes'."</a>\n";}if(!is_view($S)){if(fk_support($S)){echo"<h3 id='foreign-keys'>".'Foreign keys'."</h3>\n";$ld=foreign_keys($a);if($ld){echo"<table>\n","<thead><tr><th>".'Source'."<td>".'Target'."<td>".'ON DELETE'."<td>".'ON UPDATE'."<td></thead>\n";foreach($ld
as$B=>$p){echo"<tr title='".h($B)."'>","<th><i>".implode("</i>, <i>",array_map('Adminer\h',$p["source"]))."</i>";$_=($p["db"]!=""?preg_replace('~db=[^&]*~',"db=".urlencode($p["db"]),ME):($p["ns"]!=""?preg_replace('~ns=[^&]*~',"ns=".urlencode($p["ns"]),ME):ME));echo"<td><a href='".h($_."table=".urlencode($p["table"]))."'>".($p["db"]!=""&&$p["db"]!=DB?"<b>".h($p["db"])."</b>.":"").($p["ns"]!=""&&$p["ns"]!=$_GET["ns"]?"<b>".h($p["ns"])."</b>.":"").h($p["table"])."</a>","(<i>".implode("</i>, <i>",array_map('Adminer\h',$p["target"]))."</i>)","<td>".h($p["on_delete"]),"<td>".h($p["on_update"]),'<td><a href="'.h(ME.'foreign='.urlencode($a).'&name='.urlencode($B)).'">'.'Alter'.'</a>',"\n";}echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'foreign='.urlencode($a).'">'.'Add foreign key'."</a>\n";}if(support("check")){echo"<h3 id='checks'>".'Checks'."</h3>\n";$Xa=driver()->checkConstraints($a);if($Xa){echo"<table>\n";foreach($Xa
as$x=>$X)echo"<tr title='".h($x)."'>","<td><code class='jush-".JUSH."'>".h($X),"<td><a href='".h(ME.'check='.urlencode($a).'&name='.urlencode($x))."'>".'Alter'."</a>","\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'check='.urlencode($a).'">'.'Create check'."</a>\n";}}if(support(is_view($S)?"view_trigger":"trigger")){echo"<h3 id='triggers'>".'Triggers'."</h3>\n";$kj=triggers($a);if($kj){echo"<table>\n";foreach($kj
as$x=>$X)echo"<tr valign='top'><td>".h($X[0])."<td>".h($X[1])."<th>".h($x)."<td><a href='".h(ME.'trigger='.urlencode($a).'&name='.urlencode($x))."'>".'Alter'."</a>\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'trigger='.urlencode($a).'">'.'Add trigger'."</a>\n";}$ie=driver()->inheritedTables($a);if($ie){echo"<h3 id='partitions'>".'Inherited by'."</h3>\n";$zg=driver()->partitionsInfo($a);if($zg)echo"<p><code class='jush-".JUSH."'>BY ".h("$zg[partition_by]($zg[partition])")."</code>\n";tables_links($ie);}}elseif(isset($_GET["schema"])){page_header('Database schema',"",array(),h(DB.($_GET["ns"]?".$_GET[ns]":"")));$Bi=array();$Ci=array();$ca=($_GET["schema"]?:$_COOKIE["adminer_schema-".str_replace(".","_",DB)]);preg_match_all('~([^:]+):([-0-9.]+)x([-0-9.]+)(_|$)~',$ca,$Ze,PREG_SET_ORDER);foreach($Ze
as$s=>$A){$Bi[$A[1]]=array($A[2],$A[3]);$Ci[]="\n\t'".js_escape($A[1])."': [ $A[2], $A[3] ]";}$Zi=0;$Ga=-1;$Fh=array();$nh=array();$Me=array();$sa=driver()->allFields();foreach(table_status('',true)as$R=>$S){if(is_view($S))continue;$Ng=0;$Fh[$R]["fields"]=array();foreach($sa[$R]as$m){$Ng+=1.25;$m["pos"]=$Ng;$Fh[$R]["fields"][$m["field"]]=$m;}$Fh[$R]["pos"]=($Bi[$R]?:array($Zi,0));foreach(adminer()->foreignKeys($R)as$X){if(!$X["db"]){$Ke=$Ga;if(idx($Bi[$R],1)||idx($Bi[$X["table"]],1))$Ke=min(idx($Bi[$R],1,0),idx($Bi[$X["table"]],1,0))-1;else$Ga-=.1;while($Me[(string)$Ke])$Ke-=.0001;$Fh[$R]["references"][$X["table"]][(string)$Ke]=array($X["source"],$X["target"]);$nh[$X["table"]][$R][(string)$Ke]=$X["target"];$Me[(string)$Ke]=true;}}$Zi=max($Zi,$Fh[$R]["pos"][0]+2.5+$Ng);}echo'<div id="schema" style="height: ',$Zi,'em;">
<script',nonce(),'>
qs(\'#schema\').onselectstart = () => false;
const tablePos = {',implode(",",$Ci)."\n",'};
const em = qs(\'#schema\').offsetHeight / ',$Zi,';
document.onmousemove = schemaMousemove;
document.onmouseup = partialArg(schemaMouseup, \'',js_escape(DB),'\');
</script>
';foreach($Fh
as$B=>$R){echo"<div class='table' style='top: ".$R["pos"][0]."em; left: ".$R["pos"][1]."em;'>",'<a href="'.h(ME).'table='.urlencode($B).'"><b>'.h($B)."</b></a>",script("qsl('div').onmousedown = schemaMousedown;");foreach($R["fields"]as$m){$X='<span'.type_class($m["type"]).' title="'.h($m["type"].($m["length"]?"($m[length])":"").($m["null"]?" NULL":'')).'">'.h($m["field"]).'</span>';echo"<br>".($m["primary"]?"<i>$X</i>":$X);}foreach((array)$R["references"]as$Ji=>$ph){foreach($ph
as$Ke=>$kh){$Le=$Ke-idx($Bi[$B],1);$s=0;foreach($kh[0]as$fi)echo"\n<div class='references' title='".h($Ji)."' id='refs$Ke-".($s++)."' style='left: $Le"."em; top: ".$R["fields"][$fi]["pos"]."em; padding-top: .5em;'>"."<div style='border-top: 1px solid gray; width: ".(-$Le)."em;'></div></div>";}}foreach((array)$nh[$B]as$Ji=>$ph){foreach($ph
as$Ke=>$e){$Le=$Ke-idx($Bi[$B],1);$s=0;foreach($e
as$Ii)echo"\n<div class='references arrow' title='".h($Ji)."' id='refd$Ke-".($s++)."' style='left: $Le"."em; top: ".$R["fields"][$Ii]["pos"]."em;'>"."<div style='height: .5em; border-bottom: 1px solid gray; width: ".(-$Le)."em;'></div>"."</div>";}}echo"\n</div>\n";}foreach($Fh
as$B=>$R){foreach((array)$R["references"]as$Ji=>$ph){foreach($ph
as$Ke=>$kh){$rf=$Zi;$ff=-10;foreach($kh[0]as$x=>$fi){$Og=$R["pos"][0]+$R["fields"][$fi]["pos"];$Pg=$Fh[$Ji]["pos"][0]+$Fh[$Ji]["fields"][$kh[1][$x]]["pos"];$rf=min($rf,$Og,$Pg);$ff=max($ff,$Og,$Pg);}echo"<div class='references' id='refl$Ke' style='left: $Ke"."em; top: $rf"."em; padding: .5em 0;'><div style='border-right: 1px solid gray; margin-top: 1px; height: ".($ff-$rf)."em;'></div></div>\n";}}}echo'</div>
<p class="links"><a href="',h(ME."schema=".urlencode($ca)),'" id="schema-link">Permanent link</a>
';}elseif(isset($_GET["dump"])){$a=$_GET["dump"];if($_POST&&!$l){save_settings(array_intersect_key($_POST,array_flip(array("output","format","db_style","types","routines","events","table_style","auto_increment","triggers","data_style"))),"adminer_export");$T=array_flip((array)$_POST["tables"])+array_flip((array)$_POST["data"]);$Nc=dump_headers((count($T)==1?key($T):DB),(DB==""||count($T)>1));$we=preg_match('~sql~',$_POST["format"]);if($we){echo"-- Adminer ".VERSION." ".get_driver(DRIVER)." ".str_replace("\n"," ",connection()->server_info)." dump\n\n";if(JUSH=="sql"){echo"SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
".($_POST["data_style"]?"SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
":"")."
";connection()->query("SET time_zone = '+00:00'");connection()->query("SET sql_mode = ''");}}$si=$_POST["db_style"];$i=array(DB);if(DB==""){$i=$_POST["databases"];if(is_string($i))$i=explode("\n",rtrim(str_replace("\r","",$i),"\n"));}foreach((array)$i
as$j){adminer()->dumpDatabase($j);if(connection()->select_db($j)){if($we){if($si)echo
use_sql($j,$si).";\n\n";$pg="";if($_POST["types"]){foreach(types()as$t=>$U){$Bc=type_values($t);if($Bc)$pg
.=($si!='DROP+CREATE'?"DROP TYPE IF EXISTS ".idf_escape($U).";;\n":"")."CREATE TYPE ".idf_escape($U)." AS ENUM ($Bc);\n\n";else$pg
.="-- Could not export type $U\n\n";}}if($_POST["routines"]){foreach(routines()as$K){$B=$K["ROUTINE_NAME"];$_h=$K["ROUTINE_TYPE"];$h=create_routine($_h,array("name"=>$B)+routine($K["SPECIFIC_NAME"],$_h));set_utf8mb4($h);$pg
.=($si!='DROP+CREATE'?"DROP $_h IF EXISTS ".idf_escape($B).";;\n":"")."$h;\n\n";}}if($_POST["events"]){foreach(get_rows("SHOW EVENTS",null,"-- ")as$K){$h=remove_definer(get_val("SHOW CREATE EVENT ".idf_escape($K["Name"]),3));set_utf8mb4($h);$pg
.=($si!='DROP+CREATE'?"DROP EVENT IF EXISTS ".idf_escape($K["Name"]).";;\n":"")."$h;;\n\n";}}echo($pg&&JUSH=='sql'?"DELIMITER ;;\n\n$pg"."DELIMITER ;\n\n":$pg);}if($_POST["table_style"]||$_POST["data_style"]){$Nj=array();foreach(table_status('',true)as$B=>$S){$R=(DB==""||in_array($B,(array)$_POST["tables"]));$Lb=(DB==""||in_array($B,(array)$_POST["data"]));if($R||$Lb){$Wi=null;if($Nc=="tar"){$Wi=new
TmpFile;ob_start(array($Wi,'write'),1e5);}adminer()->dumpTable($B,($R?$_POST["table_style"]:""),(is_view($S)?2:0));if(is_view($S))$Nj[]=$B;elseif($Lb){$n=fields($B);adminer()->dumpData($B,$_POST["data_style"],"SELECT *".convert_fields($n,$n)." FROM ".table($B));}if($we&&$_POST["triggers"]&&$R&&($kj=trigger_sql($B)))echo"\nDELIMITER ;;\n$kj\nDELIMITER ;\n";if($Nc=="tar"){ob_end_flush();tar_file((DB!=""?"":"$j/")."$B.csv",$Wi);}elseif($we)echo"\n";}}if(function_exists('Adminer\foreign_keys_sql')){foreach(table_status('',true)as$B=>$S){$R=(DB==""||in_array($B,(array)$_POST["tables"]));if($R&&!is_view($S))echo
foreign_keys_sql($B);}}foreach($Nj
as$Mj)adminer()->dumpTable($Mj,$_POST["table_style"],1);if($Nc=="tar")echo
pack("x512");}}}adminer()->dumpFooter();exit;}page_header('Export',$l,($_GET["export"]!=""?array("table"=>$_GET["export"]):array()),h(DB));echo'
<form action="" method="post">
<table class="layout">
';$Pb=array('','USE','DROP+CREATE','CREATE');$Di=array('','DROP+CREATE','CREATE');$Mb=array('','TRUNCATE+INSERT','INSERT');if(JUSH=="sql")$Mb[]='INSERT+UPDATE';$K=get_settings("adminer_export");if(!$K)$K=array("output"=>"text","format"=>"sql","db_style"=>(DB!=""?"":"CREATE"),"table_style"=>"DROP+CREATE","data_style"=>"INSERT");if(!isset($K["events"])){$K["routines"]=$K["events"]=($_GET["dump"]=="");$K["triggers"]=$K["table_style"];}echo"<tr><th>".'Output'."<td>".html_radios("output",adminer()->dumpOutput(),$K["output"])."\n","<tr><th>".'Format'."<td>".html_radios("format",adminer()->dumpFormat(),$K["format"])."\n",(JUSH=="sqlite"?"":"<tr><th>".'Database'."<td>".html_select('db_style',$Pb,$K["db_style"]).(support("type")?checkbox("types",1,$K["types"],'User types'):"").(support("routine")?checkbox("routines",1,$K["routines"],'Routines'):"").(support("event")?checkbox("events",1,$K["events"],'Events'):"")),"<tr><th>".'Tables'."<td>".html_select('table_style',$Di,$K["table_style"]).checkbox("auto_increment",1,$K["auto_increment"],'Auto Increment').(support("trigger")?checkbox("triggers",1,$K["triggers"],'Triggers'):""),"<tr><th>".'Data'."<td>".html_select('data_style',$Mb,$K["data_style"]),'</table>
<p><input type="submit" value="Export">
',input_token(),'
<table>
',script("qsl('table').onclick = dumpClick;");$Tg=array();if(DB!=""){$Za=($a!=""?"":" checked");echo"<thead><tr>","<th style='text-align: left;'><label class='block'><input type='checkbox' id='check-tables'$Za>".'Tables'."</label>".script("qs('#check-tables').onclick = partial(formCheck, /^tables\\[/);",""),"<th style='text-align: right;'><label class='block'>".'Data'."<input type='checkbox' id='check-data'$Za></label>".script("qs('#check-data').onclick = partial(formCheck, /^data\\[/);",""),"</thead>\n";$Nj="";$Fi=tables_list();foreach($Fi
as$B=>$U){$Sg=preg_replace('~_.*~','',$B);$Za=($a==""||$a==(substr($a,-1)=="%"?"$Sg%":$B));$Wg="<tr><td>".checkbox("tables[]",$B,$Za,$B,"","block");if($U!==null&&!preg_match('~table~i',$U))$Nj
.="$Wg\n";else
echo"$Wg<td align='right'><label class='block'><span id='Rows-".h($B)."'></span>".checkbox("data[]",$B,$Za)."</label>\n";$Tg[$Sg]++;}echo$Nj;if($Fi)echo
script("ajaxSetHtml('".js_escape(ME)."script=db');");}else{echo"<thead><tr><th style='text-align: left;'>","<label class='block'><input type='checkbox' id='check-databases'".($a==""?" checked":"").">".'Database'."</label>",script("qs('#check-databases').onclick = partial(formCheck, /^databases\\[/);",""),"</thead>\n";$i=adminer()->databases();if($i){foreach($i
as$j){if(!information_schema($j)){$Sg=preg_replace('~_.*~','',$j);echo"<tr><td>".checkbox("databases[]",$j,$a==""||$a=="$Sg%",$j,"","block")."\n";$Tg[$Sg]++;}}}else
echo"<tr><td><textarea name='databases' rows='10' cols='20'></textarea>";}echo'</table>
</form>
';$bd=true;foreach($Tg
as$x=>$X){if($x!=""&&$X>1){echo($bd?"<p>":" ")."<a href='".h(ME)."dump=".urlencode("$x%")."'>".h($x)."</a>";$bd=false;}}}elseif(isset($_GET["privileges"])){page_header('Privileges');echo'<p class="links"><a href="'.h(ME).'user=">'.'Create user'."</a>";$I=connection()->query("SELECT User, Host FROM mysql.".(DB==""?"user":"db WHERE ".q(DB)." LIKE Db")." ORDER BY Host, User");$ud=$I;if(!$I)$I=connection()->query("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', 1) AS User, SUBSTRING_INDEX(CURRENT_USER, '@', -1) AS Host");echo"<form action=''><p>\n";hidden_fields_get();echo
input_hidden("db",DB),($ud?"":input_hidden("grant")),"<table class='odds'>\n","<thead><tr><th>".'Username'."<th>".'Server'."<th></thead>\n";while($K=$I->fetch_assoc())echo'<tr><td>'.h($K["User"])."<td>".h($K["Host"]).'<td><a href="'.h(ME.'user='.urlencode($K["User"]).'&host='.urlencode($K["Host"])).'">'.'Edit'."</a>\n";if(!$ud||DB!="")echo"<tr><td><input name='user' autocapitalize='off'><td><input name='host' value='localhost' autocapitalize='off'><td><input type='submit' value='".'Edit'."'>\n";echo"</table>\n","</form>\n";}elseif(isset($_GET["sql"])){if(!$l&&$_POST["export"]){save_settings(array("output"=>$_POST["output"],"format"=>$_POST["format"]),"adminer_import");dump_headers("sql");if($_POST["format"]=="sql")echo"$_POST[query]\n";else{adminer()->dumpTable("","");adminer()->dumpData("","table",$_POST["query"]);adminer()->dumpFooter();}exit;}restart_session();$Kd=&get_session("queries");$Jd=&$Kd[DB];if(!$l&&$_POST["clear"]){$Jd=array();redirect(remove_from_uri("history"));}stop_session();page_header((isset($_GET["import"])?'Import':'SQL command'),$l);$Qe='--'.(JUSH=='sql'?' ':'');if(!$l&&$_POST){$q=false;if(!isset($_GET["import"]))$H=$_POST["query"];elseif($_POST["webfile"]){$ji=adminer()->importServerPath();$q=@fopen((file_exists($ji)?$ji:"compress.zlib://$ji.gz"),"rb");$H=($q?fread($q,1e6):false);}else$H=get_file("sql_file",true,";");if(is_string($H)){if(function_exists('memory_get_usage')&&($kf=ini_bytes("memory_limit"))!="-1")@ini_set("memory_limit",max($kf,strval(2*strlen($H)+memory_get_usage()+8e6)));if($H!=""&&strlen($H)<1e6){$dh=$H.(preg_match("~;[ \t\r\n]*\$~",$H)?"":";");if(!$Jd||first(end($Jd))!=$dh){restart_session();$Jd[]=array($dh,time());set_session("queries",$Kd);stop_session();}}$gi="(?:\\s|/\\*[\s\S]*?\\*/|(?:#|$Qe)[^\n]*\n?|--\r?\n)";$Xb=";";$C=0;$wc=true;$g=connect();if($g&&DB!=""){$g->select_db(DB);if($_GET["ns"]!="")set_schema($_GET["ns"],$g);}$nb=0;$Dc=array();$wg='[\'"'.(JUSH=="sql"?'`#':(JUSH=="sqlite"?'`[':(JUSH=="mssql"?'[':''))).']|/\*|'.$Qe.'|$'.(JUSH=="pgsql"?'|\$([a-zA-Z]\w*)?\$':'');$aj=microtime(true);$ma=get_settings("adminer_import");while($H!=""){if(!$C&&preg_match("~^$gi*+DELIMITER\\s+(\\S+)~i",$H,$A)){$Xb=preg_quote($A[1]);$H=substr($H,strlen($A[0]));}elseif(!$C&&JUSH=='pgsql'&&preg_match("~^($gi*+COPY\\s+)[^;]+\\s+FROM\\s+stdin;~i",$H,$A)){$Xb="\n\\\\\\.\r?\n";$C=strlen($A[0]);}else{preg_match("($Xb\\s*|$wg)",$H,$A,PREG_OFFSET_CAPTURE,$C);list($nd,$Ng)=$A[0];if(!$nd&&$q&&!feof($q))$H
.=fread($q,1e5);else{if(!$nd&&rtrim($H)=="")break;$C=$Ng+strlen($nd);if($nd&&!preg_match("(^$Xb)",$nd)){$Ra=driver()->hasCStyleEscapes()||(JUSH=="pgsql"&&($Ng>0&&strtolower($H[$Ng-1])=="e"));$Gg=($nd=='/*'?'\*/':($nd=='['?']':(preg_match("~^$Qe|^#~",$nd)?"\n":preg_quote($nd).($Ra?'|\\\\.':''))));while(preg_match("($Gg|\$)s",$H,$A,PREG_OFFSET_CAPTURE,$C)){$Dh=$A[0][0];if(!$Dh&&$q&&!feof($q))$H
.=fread($q,1e5);else{$C=$A[0][1]+strlen($Dh);if(!$Dh||$Dh[0]!="\\")break;}}}else{$wc=false;$dh=substr($H,0,$Ng+($Xb[0]=="\n"?3:0));$nb++;$Wg="<pre id='sql-$nb'><code class='jush-".JUSH."'>".adminer()->sqlCommandQuery($dh)."</code></pre>\n";if(JUSH=="sqlite"&&preg_match("~^$gi*+ATTACH\\b~i",$dh,$A)){echo$Wg,"<p class='error'>".'ATTACH queries are not supported.'."\n";$Dc[]=" <a href='#sql-$nb'>$nb</a>";if($_POST["error_stops"])break;}else{if(!$_POST["only_errors"]){echo$Wg;ob_flush();flush();}$oi=microtime(true);if(connection()->multi_query($dh)&&$g&&preg_match("~^$gi*+USE\\b~i",$dh))$g->query($dh);do{$I=connection()->store_result();if(connection()->error){echo($_POST["only_errors"]?$Wg:""),"<p class='error'>".'Error in query'.(connection()->errno?" (".connection()->errno.")":"").": ".error()."\n";$Dc[]=" <a href='#sql-$nb'>$nb</a>";if($_POST["error_stops"])break
2;}else{$Pi=" <span class='time'>(".format_time($oi).")</span>".(strlen($dh)<1000?" <a href='".h(ME)."sql=".urlencode(trim($dh))."'>".'Edit'."</a>":"");$oa=connection()->affected_rows;$Qj=($_POST["only_errors"]?"":driver()->warnings());$Rj="warnings-$nb";if($Qj)$Pi
.=", <a href='#$Rj'>".'Warnings'."</a>".script("qsl('a').onclick = partial(toggle, '$Rj');","");$Lc=null;$hg=null;$Mc="explain-$nb";if(is_object($I)){$z=$_POST["limit"];$hg=print_select_result($I,$g,array(),$z);if(!$_POST["only_errors"]){echo"<form action='' method='post'>\n";$If=$I->num_rows;echo"<p class='sql-footer'>".($If?($z&&$If>$z?sprintf('%d / ',$z):"").lang_format(array('%d row','%d rows'),$If):""),$Pi;if($g&&preg_match("~^($gi|\\()*+SELECT\\b~i",$dh)&&($Lc=explain($g,$dh)))echo", <a href='#$Mc'>Explain</a>".script("qsl('a').onclick = partial(toggle, '$Mc');","");$t="export-$nb";echo", <a href='#$t'>".'Export'."</a>".script("qsl('a').onclick = partial(toggle, '$t');","")."<span id='$t' class='hidden'>: ".html_select("output",adminer()->dumpOutput(),$ma["output"])." ".html_select("format",adminer()->dumpFormat(),$ma["format"]).input_hidden("query",$dh)."<input type='submit' name='export' value='".'Export'."'>".input_token()."</span>\n"."</form>\n";}}else{if(preg_match("~^$gi*+(CREATE|DROP|ALTER)$gi++(DATABASE|SCHEMA)\\b~i",$dh)){restart_session();set_session("dbs",null);stop_session();}if(!$_POST["only_errors"])echo"<p class='message' title='".h(connection()->info)."'>".lang_format(array('Query executed OK, %d row affected.','Query executed OK, %d rows affected.'),$oa)."$Pi\n";}echo($Qj?"<div id='$Rj' class='hidden'>\n$Qj</div>\n":"");if($Lc){echo"<div id='$Mc' class='hidden explain'>\n";print_select_result($Lc,$g,$hg);echo"</div>\n";}}$oi=microtime(true);}while(connection()->next_result());}$H=substr($H,$C);$C=0;}}}}if($wc)echo"<p class='message'>".'No commands to execute.'."\n";elseif($_POST["only_errors"])echo"<p class='message'>".lang_format(array('%d query executed OK.','%d queries executed OK.'),$nb-count($Dc))," <span class='time'>(".format_time($aj).")</span>\n";elseif($Dc&&$nb>1)echo"<p class='error'>".'Error in query'.": ".implode("",$Dc)."\n";}else
echo"<p class='error'>".upload_error($H)."\n";}echo'
<form action="" method="post" enctype="multipart/form-data" id="form">
';$Jc="<input type='submit' value='".'Execute'."' title='Ctrl+Enter'>";if(!isset($_GET["import"])){$dh=$_GET["sql"];if($_POST)$dh=$_POST["query"];elseif($_GET["history"]=="all")$dh=$Jd;elseif($_GET["history"]!="")$dh=idx($Jd[$_GET["history"]],0);echo"<p>";textarea("query",$dh,20);echo
script(($_POST?"":"qs('textarea').focus();\n")."qs('#form').onsubmit = partial(sqlSubmit, qs('#form'), '".js_escape(remove_from_uri("sql|limit|error_stops|only_errors|history"))."');"),"<p>";adminer()->sqlPrintAfter();echo"$Jc\n",'Limit rows'.": <input type='number' name='limit' class='size' value='".h($_POST?$_POST["limit"]:$_GET["limit"])."'>\n";}else{$_d=(extension_loaded("zlib")?"[.gz]":"");echo"<fieldset><legend>".'File upload'."</legend><div>",file_input("SQL$_d: <input type='file' name='sql_file[]' multiple>\n$Jc"),"</div></fieldset>\n";$Vd=adminer()->importServerPath();if($Vd)echo"<fieldset><legend>".'From server'."</legend><div>",sprintf('Webserver file %s',"<code>".h($Vd)."$_d</code>"),' <input type="submit" name="webfile" value="'.'Run file'.'">',"</div></fieldset>\n";echo"<p>";}echo
checkbox("error_stops",1,($_POST?$_POST["error_stops"]:isset($_GET["import"])||$_GET["error_stops"]),'Stop on error')."\n",checkbox("only_errors",1,($_POST?$_POST["only_errors"]:isset($_GET["import"])||$_GET["only_errors"]),'Show only errors')."\n",input_token();if(!isset($_GET["import"])&&$Jd){print_fieldset("history",'History',$_GET["history"]!="");for($X=end($Jd);$X;$X=prev($Jd)){$x=key($Jd);list($dh,$Pi,$rc)=$X;echo'<a href="'.h(ME."sql=&history=$x").'">'.'Edit'."</a>"." <span class='time' title='".@date('Y-m-d',$Pi)."'>".@date("H:i:s",$Pi)."</span>"." <code class='jush-".JUSH."'>".shorten_utf8(ltrim(str_replace("\n"," ",str_replace("\r","",preg_replace("~^(#|$Qe).*~m",'',$dh)))),80,"</code>").($rc?" <span class='time'>($rc)</span>":"")."<br>\n";}echo"<input type='submit' name='clear' value='".'Clear'."'>\n","<a href='".h(ME."sql=&history=all")."'>".'Edit all'."</a>\n","</div></fieldset>\n";}echo'</form>
';}elseif(isset($_GET["edit"])){$a=$_GET["edit"];$n=fields($a);$Z=(isset($_GET["select"])?($_POST["check"]&&count($_POST["check"])==1?where_check($_POST["check"][0],$n):""):where($_GET,$n));$wj=(isset($_GET["select"])?$_POST["edit"]:$Z);foreach($n
as$B=>$m){if(!isset($m["privileges"][$wj?"update":"insert"])||adminer()->fieldName($m)==""||$m["generated"])unset($n[$B]);}if($_POST&&!$l&&!isset($_GET["select"])){$Se=$_POST["referer"];if($_POST["insert"])$Se=($wj?null:$_SERVER["REQUEST_URI"]);elseif(!preg_match('~^.+&select=.+$~',$Se))$Se=ME."select=".urlencode($a);$w=indexes($a);$rj=unique_array($_GET["where"],$w);$gh="\nWHERE $Z";if(isset($_POST["delete"]))queries_redirect($Se,'Item has been deleted.',driver()->delete($a,$gh,$rj?0:1));else{$O=array();foreach($n
as$B=>$m){$X=process_input($m);if($X!==false&&$X!==null)$O[idf_escape($B)]=$X;}if($wj){if(!$O)redirect($Se);queries_redirect($Se,'Item has been updated.',driver()->update($a,$O,$gh,$rj?0:1));if(is_ajax()){page_headers();page_messages($l);exit;}}else{$I=driver()->insert($a,$O);$Je=($I?last_id($I):0);queries_redirect($Se,sprintf('Item%s has been inserted.',($Je?" $Je":"")),$I);}}}$K=null;if($_POST["save"])$K=(array)$_POST["fields"];elseif($Z){$M=array();foreach($n
as$B=>$m){if(isset($m["privileges"]["select"])){$wa=($_POST["clone"]&&$m["auto_increment"]?"''":convert_field($m));$M[]=($wa?"$wa AS ":"").idf_escape($B);}}$K=array();if(!support("table"))$M=array("*");if($M){$I=driver()->select($a,$M,array($Z),$M,array(),(isset($_GET["select"])?2:1));if(!$I)$l=error();else{$K=$I->fetch_assoc();if(!$K)$K=false;}if(isset($_GET["select"])&&(!$K||$I->fetch_assoc()))$K=null;}}if(!support("table")&&!$n){if(!$Z){$I=driver()->select($a,array("*"),array(),array("*"));$K=($I?$I->fetch_assoc():false);if(!$K)$K=array(driver()->primary=>"");}if($K){foreach($K
as$x=>$X){if(!$Z)$K[$x]=null;$n[$x]=array("field"=>$x,"null"=>($x!=driver()->primary),"auto_increment"=>($x==driver()->primary));}}}edit_form($a,$n,$K,$wj,$l);}elseif(isset($_GET["create"])){$a=$_GET["create"];$Ag=driver()->partitionBy;$Dg=($Ag?driver()->partitionsInfo($a):array());$mh=referencable_primary($a);$ld=array();foreach($mh
as$_i=>$m)$ld[str_replace("`","``",$_i)."`".str_replace("`","``",$m["field"])]=$_i;$kg=array();$S=array();if($a!=""){$kg=fields($a);$S=table_status1($a);if(count($S)<2)$l='No tables.';}$K=$_POST;$K["fields"]=(array)$K["fields"];if($K["auto_increment_col"])$K["fields"][$K["auto_increment_col"]]["auto_increment"]=true;if($_POST)save_settings(array("comments"=>$_POST["comments"],"defaults"=>$_POST["defaults"]));if($_POST&&!process_fields($K["fields"])&&!$l){if($_POST["drop"])queries_redirect(substr(ME,0,-1),'Table has been dropped.',drop_tables(array($a)));else{$n=array();$sa=array();$Bj=false;$jd=array();$jg=reset($kg);$qa=" FIRST";foreach($K["fields"]as$x=>$m){$p=$ld[$m["type"]];$lj=($p!==null?$mh[$p]:$m);if($m["field"]!=""){if(!$m["generated"])$m["default"]=null;$bh=process_field($m,$lj);$sa[]=array($m["orig"],$bh,$qa);if(!$jg||$bh!==process_field($jg,$jg)){$n[]=array($m["orig"],$bh,$qa);if($m["orig"]!=""||$qa)$Bj=true;}if($p!==null)$jd[idf_escape($m["field"])]=($a!=""&&JUSH!="sqlite"?"ADD":" ").format_foreign_key(array('table'=>$ld[$m["type"]],'source'=>array($m["field"]),'target'=>array($lj["field"]),'on_delete'=>$m["on_delete"],));$qa=" AFTER ".idf_escape($m["field"]);}elseif($m["orig"]!=""){$Bj=true;$n[]=array($m["orig"]);}if($m["orig"]!=""){$jg=next($kg);if(!$jg)$qa="";}}$E=array();if(in_array($K["partition_by"],$Ag)){foreach($K
as$x=>$X){if(preg_match('~^partition~',$x))$E[$x]=$X;}foreach($E["partition_names"]as$x=>$B){if($B==""){unset($E["partition_names"][$x]);unset($E["partition_values"][$x]);}}$E["partition_names"]=array_values($E["partition_names"]);$E["partition_values"]=array_values($E["partition_values"]);if($E==$Dg)$E=array();}elseif(preg_match("~partitioned~",$S["Create_options"]))$E=null;$lf='Table has been altered.';if($a==""){cookie("adminer_engine",$K["Engine"]);$lf='Table has been created.';}$B=trim($K["name"]);queries_redirect(ME.(support("table")?"table=":"select=").urlencode($B),$lf,alter_table($a,$B,(JUSH=="sqlite"&&($Bj||$jd)?$sa:$n),$jd,($K["Comment"]!=$S["Comment"]?$K["Comment"]:null),($K["Engine"]&&$K["Engine"]!=$S["Engine"]?$K["Engine"]:""),($K["Collation"]&&$K["Collation"]!=$S["Collation"]?$K["Collation"]:""),($K["Auto_increment"]!=""?number($K["Auto_increment"]):""),$E));}}page_header(($a!=""?'Alter table':'Create table'),$l,array("table"=>$a),h($a));if(!$_POST){$nj=driver()->types();$K=array("Engine"=>$_COOKIE["adminer_engine"],"fields"=>array(array("field"=>"","type"=>(isset($nj["int"])?"int":(isset($nj["integer"])?"integer":"")),"on_update"=>"")),"partition_names"=>array(""),);if($a!=""){$K=$S;$K["name"]=$a;$K["fields"]=array();if(!$_GET["auto_increment"])$K["Auto_increment"]="";foreach($kg
as$m){$m["generated"]=$m["generated"]?:(isset($m["default"])?"DEFAULT":"");$K["fields"][]=$m;}if($Ag){$K+=$Dg;$K["partition_names"][]="";$K["partition_values"][]="";}}}$jb=collations();if(is_array(reset($jb)))$jb=call_user_func_array('array_merge',array_values($jb));$yc=driver()->engines();foreach($yc
as$xc){if(!strcasecmp($xc,$K["Engine"])){$K["Engine"]=$xc;break;}}echo'
<form action="" method="post" id="form">
<p>
';if(support("columns")||$a==""){echo'Table name'.": <input name='name'".($a==""&&!$_POST?" autofocus":"")." data-maxlength='64' value='".h($K["name"])."' autocapitalize='off'>\n",($yc?html_select("Engine",array(""=>"(".'engine'.")")+$yc,$K["Engine"]).on_help("event.target.value",1).script("qsl('select').onchange = helpClose;")."\n":"");if($jb)echo"<datalist id='collations'>".optionlist($jb)."</datalist>\n",(preg_match("~sqlite|mssql~",JUSH)?"":"<input list='collations' name='Collation' value='".h($K["Collation"])."' placeholder='(".'collation'.")'>\n");echo"<input type='submit' value='".'Save'."'>\n";}if(support("columns")){echo"<div class='scrollable'>\n","<table id='edit-fields' class='nowrap'>\n";edit_fields($K["fields"],$jb,"TABLE",$ld);echo"</table>\n",script("editFields();"),"</div>\n<p>\n",'Auto Increment'.": <input type='number' name='Auto_increment' class='size' value='".h($K["Auto_increment"])."'>\n",checkbox("defaults",1,($_POST?$_POST["defaults"]:get_setting("defaults")),'Default values',"columnShow(this.checked, 5)","jsonly");$qb=($_POST?$_POST["comments"]:get_setting("comments"));echo(support("comment")?checkbox("comments",1,$qb,'Comment',"editingCommentsClick(this, true);","jsonly").' '.(preg_match('~\n~',$K["Comment"])?"<textarea name='Comment' rows='2' cols='20'".($qb?"":" class='hidden'").">".h($K["Comment"])."</textarea>":'<input name="Comment" value="'.h($K["Comment"]).'" data-maxlength="'.(min_version(5.5)?2048:60).'"'.($qb?"":" class='hidden'").'>'):''),'<p>
<input type="submit" value="Save">
';}echo'
';if($a!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$a));if($Ag&&(JUSH=='sql'||$a=="")){$Bg=preg_match('~RANGE|LIST~',$K["partition_by"]);print_fieldset("partition",'Partition by',$K["partition_by"]);echo"<p>".html_select("partition_by",array_merge(array(""),$Ag),$K["partition_by"]).on_help("event.target.value.replace(/./, 'PARTITION BY \$&')",1).script("qsl('select').onchange = partitionByChange;"),"(<input name='partition' value='".h($K["partition"])."'>)\n",'Partitions'.": <input type='number' name='partitions' class='size".($Bg||!$K["partition_by"]?" hidden":"")."' value='".h($K["partitions"])."'>\n","<table id='partition-table'".($Bg?"":" class='hidden'").">\n","<thead><tr><th>".'Partition name'."<th>".'Values'."</thead>\n";foreach($K["partition_names"]as$x=>$X)echo'<tr>','<td><input name="partition_names[]" value="'.h($X).'" autocapitalize="off">',($x==count($K["partition_names"])-1?script("qsl('input').oninput = partitionNameChange;"):''),'<td><input name="partition_values[]" value="'.h(idx($K["partition_values"],$x)).'">';echo"</table>\n</div></fieldset>\n";}echo
input_token(),'</form>
';}elseif(isset($_GET["indexes"])){$a=$_GET["indexes"];$de=array("PRIMARY","UNIQUE","INDEX");$S=table_status1($a,true);$ae=driver()->indexAlgorithms($S);if(preg_match('~MyISAM|M?aria'.(min_version(5.6,'10.0.5')?'|InnoDB':'').'~i',$S["Engine"]))$de[]="FULLTEXT";if(preg_match('~MyISAM|M?aria'.(min_version(5.7,'10.2.2')?'|InnoDB':'').'~i',$S["Engine"]))$de[]="SPATIAL";$w=indexes($a);$n=fields($a);$G=array();if(JUSH=="mongo"){$G=$w["_id_"];unset($de[0]);unset($w["_id_"]);}$K=$_POST;if($K)save_settings(array("index_options"=>$K["options"]));if($_POST&&!$l&&!$_POST["add"]&&!$_POST["drop_col"]){$b=array();foreach($K["indexes"]as$v){$B=$v["name"];if(in_array($v["type"],$de)){$e=array();$Oe=array();$ac=array();$be=(support("partial_indexes")?$v["partial"]:"");$Zd=(in_array($v["algorithm"],$ae)?$v["algorithm"]:"");$O=array();ksort($v["columns"]);foreach($v["columns"]as$x=>$d){if($d!=""){$y=idx($v["lengths"],$x);$Yb=idx($v["descs"],$x);$O[]=($n[$d]?idf_escape($d):$d).($y?"(".(+$y).")":"").($Yb?" DESC":"");$e[]=$d;$Oe[]=($y?:null);$ac[]=$Yb;}}$Kc=$w[$B];if($Kc){ksort($Kc["columns"]);ksort($Kc["lengths"]);ksort($Kc["descs"]);if($v["type"]==$Kc["type"]&&array_values($Kc["columns"])===$e&&(!$Kc["lengths"]||array_values($Kc["lengths"])===$Oe)&&array_values($Kc["descs"])===$ac&&$Kc["partial"]==$be&&(!$ae||$Kc["algorithm"]==$Zd)){unset($w[$B]);continue;}}if($e)$b[]=array($v["type"],$B,$O,$Zd,$be);}}foreach($w
as$B=>$Kc)$b[]=array($Kc["type"],$B,"DROP");if(!$b)redirect(ME."table=".urlencode($a));queries_redirect(ME."table=".urlencode($a),'Indexes have been altered.',alter_indexes($a,$b));}page_header('Indexes',$l,array("table"=>$a),h($a));$Yc=array_keys($n);if($_POST["add"]){foreach($K["indexes"]as$x=>$v){if($v["columns"][count($v["columns"])]!="")$K["indexes"][$x]["columns"][]="";}$v=end($K["indexes"]);if($v["type"]||array_filter($v["columns"],'strlen'))$K["indexes"][]=array("columns"=>array(1=>""));}if(!$K){foreach($w
as$x=>$v){$w[$x]["name"]=$x;$w[$x]["columns"][]="";}$w[]=array("columns"=>array(1=>""));$K["indexes"]=$w;}$Oe=(JUSH=="sql"||JUSH=="mssql");$ai=($_POST?$_POST["options"]:get_setting("index_options"));echo'
<form action="" method="post">
<div class="scrollable">
<table class="nowrap">
<thead><tr>
<th id="label-type">Index Type
';$Td=" class='idxopts".($ai?"":" hidden")."'";if($ae)echo"<th id='label-algorithm'$Td>".'Algorithm'.doc_link(array('sql'=>'create-index.html#create-index-storage-engine-index-types','mariadb'=>'storage-engine-index-types/','pgsql'=>'indexes-types.html',));echo'<th><input type="submit" class="wayoff">','Columns'.($Oe?"<span$Td> (".'length'.")</span>":"");if($Oe||support("descidx"))echo
checkbox("options",1,$ai,'Options',"indexOptionsShow(this.checked)","jsonly")."\n";echo'<th id="label-name">Name
';if(support("partial_indexes"))echo"<th id='label-condition'$Td>".'Condition';echo'<th><noscript>',icon("plus","add[0]","+",'Add next'),'</noscript>
</thead>
';if($G){echo"<tr><td>PRIMARY<td>";foreach($G["columns"]as$x=>$d)echo
select_input(" disabled",$Yc,$d),"<label><input disabled type='checkbox'>".'descending'."</label> ";echo"<td><td>\n";}$ze=1;foreach($K["indexes"]as$v){if(!$_POST["drop_col"]||$ze!=key($_POST["drop_col"])){echo"<tr><td>".html_select("indexes[$ze][type]",array(-1=>"")+$de,$v["type"],($ze==count($K["indexes"])?"indexesAddRow.call(this);":""),"label-type");if($ae)echo"<td$Td>".html_select("indexes[$ze][algorithm]",array_merge(array(""),$ae),$v['algorithm'],"label-algorithm");echo"<td>";ksort($v["columns"]);$s=1;foreach($v["columns"]as$x=>$d){echo"<span>".select_input(" name='indexes[$ze][columns][$s]' title='".'Column'."'",($n&&($d==""||$n[$d])?array_combine($Yc,$Yc):array()),$d,"partial(".($s==count($v["columns"])?"indexesAddColumn":"indexesChangeColumn").", '".js_escape(JUSH=="sql"?"":$_GET["indexes"]."_")."')"),"<span$Td>",($Oe?"<input type='number' name='indexes[$ze][lengths][$s]' class='size' value='".h(idx($v["lengths"],$x))."' title='".'Length'."'>":""),(support("descidx")?checkbox("indexes[$ze][descs][$s]",1,idx($v["descs"],$x),'descending'):""),"</span> </span>";$s++;}echo"<td><input name='indexes[$ze][name]' value='".h($v["name"])."' autocapitalize='off' aria-labelledby='label-name'>\n";if(support("partial_indexes"))echo"<td$Td><input name='indexes[$ze][partial]' value='".h($v["partial"])."' autocapitalize='off' aria-labelledby='label-condition'>\n";echo"<td>".icon("cross","drop_col[$ze]","x",'Remove').script("qsl('button').onclick = partial(editingRemoveRow, 'indexes\$1[type]');");}$ze++;}echo'</table>
</div>
<p>
<input type="submit" value="Save">
',input_token(),'</form>
';}elseif(isset($_GET["database"])){$K=$_POST;if($_POST&&!$l&&!$_POST["add"]){$B=trim($K["name"]);if($_POST["drop"]){$_GET["db"]="";queries_redirect(remove_from_uri("db|database"),'Database has been dropped.',drop_databases(array(DB)));}elseif(DB!==$B){if(DB!=""){$_GET["db"]=$B;queries_redirect(preg_replace('~\bdb=[^&]*&~','',ME)."db=".urlencode($B),'Database has been renamed.',rename_database($B,$K["collation"]));}else{$i=explode("\n",str_replace("\r","",$B));$ti=true;$Ie="";foreach($i
as$j){if(count($i)==1||$j!=""){if(!create_database($j,$K["collation"]))$ti=false;$Ie=$j;}}restart_session();set_session("dbs",null);queries_redirect(ME."db=".urlencode($Ie),'Database has been created.',$ti);}}else{if(!$K["collation"])redirect(substr(ME,0,-1));query_redirect("ALTER DATABASE ".idf_escape($B).(preg_match('~^[a-z0-9_]+$~i',$K["collation"])?" COLLATE $K[collation]":""),substr(ME,0,-1),'Database has been altered.');}}page_header(DB!=""?'Alter database':'Create database',$l,array(),h(DB));$jb=collations();$B=DB;if($_POST)$B=$K["name"];elseif(DB!="")$K["collation"]=db_collation(DB,$jb);elseif(JUSH=="sql"){foreach(get_vals("SHOW GRANTS")as$ud){if(preg_match('~ ON (`(([^\\\\`]|``|\\\\.)*)%`\.\*)?~',$ud,$A)&&$A[1]){$B=stripcslashes(idf_unescape("`$A[2]`"));break;}}}echo'
<form action="" method="post">
<p>
',($_POST["add"]||strpos($B,"\n")?'<textarea autofocus name="name" rows="10" cols="40">'.h($B).'</textarea><br>':'<input name="name" autofocus value="'.h($B).'" data-maxlength="64" autocapitalize="off">')."\n".($jb?html_select("collation",array(""=>"(".'collation'.")")+$jb,$K["collation"]).doc_link(array('sql'=>"charset-charsets.html",'mariadb'=>"supported-character-sets-and-collations/",'mssql'=>"relational-databases/system-functions/sys-fn-helpcollations-transact-sql",)):""),'<input type="submit" value="Save">
';if(DB!="")echo"<input type='submit' name='drop' value='".'Drop'."'>".confirm(sprintf('Drop %s?',DB))."\n";elseif(!$_POST["add"]&&$_GET["db"]=="")echo
icon("plus","add[0]","+",'Add next')."\n";echo
input_token(),'</form>
';}elseif(isset($_GET["scheme"])){$K=$_POST;if($_POST&&!$l){$_=preg_replace('~ns=[^&]*&~','',ME)."ns=";if($_POST["drop"])query_redirect("DROP SCHEMA ".idf_escape($_GET["ns"]),$_,'Schema has been dropped.');else{$B=trim($K["name"]);$_
.=urlencode($B);if($_GET["ns"]=="")query_redirect("CREATE SCHEMA ".idf_escape($B),$_,'Schema has been created.');elseif($_GET["ns"]!=$B)query_redirect("ALTER SCHEMA ".idf_escape($_GET["ns"])." RENAME TO ".idf_escape($B),$_,'Schema has been altered.');else
redirect($_);}}page_header($_GET["ns"]!=""?'Alter schema':'Create schema',$l);if(!$K)$K["name"]=$_GET["ns"];echo'
<form action="" method="post">
<p><input name="name" autofocus value="',h($K["name"]),'" autocapitalize="off">
<input type="submit" value="Save">
';if($_GET["ns"]!="")echo"<input type='submit' name='drop' value='".'Drop'."'>".confirm(sprintf('Drop %s?',$_GET["ns"]))."\n";echo
input_token(),'</form>
';}elseif(isset($_GET["call"])){$ba=($_GET["name"]?:$_GET["call"]);page_header('Call'.": ".h($ba),$l);$_h=routine($_GET["call"],(isset($_GET["callf"])?"FUNCTION":"PROCEDURE"));$Wd=array();$pg=array();foreach($_h["fields"]as$s=>$m){if(substr($m["inout"],-3)=="OUT"&&JUSH=='sql')$pg[$s]="@".idf_escape($m["field"])." AS ".idf_escape($m["field"]);if(!$m["inout"]||substr($m["inout"],0,2)=="IN")$Wd[]=$s;}if(!$l&&$_POST){$Sa=array();foreach($_h["fields"]as$x=>$m){$X="";if(in_array($x,$Wd)){$X=process_input($m);if($X===false)$X="''";if(isset($pg[$x]))connection()->query("SET @".idf_escape($m["field"])." = $X");}if(isset($pg[$x]))$Sa[]="@".idf_escape($m["field"]);elseif(in_array($x,$Wd))$Sa[]=$X;}$H=(isset($_GET["callf"])?"SELECT ":"CALL ").($_h["returns"]["type"]=="record"?"* FROM ":"").table($ba)."(".implode(", ",$Sa).")";$oi=microtime(true);$I=connection()->multi_query($H);$oa=connection()->affected_rows;echo
adminer()->selectQuery($H,$oi,!$I);if(!$I)echo"<p class='error'>".error()."\n";else{$g=connect();if($g)$g->select_db(DB);do{$I=connection()->store_result();if(is_object($I))print_select_result($I,$g);else
echo"<p class='message'>".lang_format(array('Routine has been called, %d row affected.','Routine has been called, %d rows affected.'),$oa)." <span class='time'>".@date("H:i:s")."</span>\n";}while(connection()->next_result());if($pg)print_select_result(connection()->query("SELECT ".implode(", ",$pg)));}}echo'
<form action="" method="post">
';if($Wd){echo"<table class='layout'>\n";foreach($Wd
as$x){$m=$_h["fields"][$x];$B=$m["field"];echo"<tr><th>".adminer()->fieldName($m);$Y=idx($_POST["fields"],$B);if($Y!=""){if($m["type"]=="set")$Y=implode(",",$Y);}input($m,$Y,idx($_POST["function"],$B,""));echo"\n";}echo"</table>\n";}echo'<p>
<input type="submit" value="Call">
',input_token(),'</form>

<pre>
';function
pre_tr($Dh){return
preg_replace('~^~m','<tr>',preg_replace('~\|~','<td>',preg_replace('~\|$~m',"",rtrim($Dh))));}$R='(\+--[-+]+\+\n)';$K='(\| .* \|\n)';echo
preg_replace_callback("~^$R?$K$R?($K*)$R?~m",function($A){$cd=pre_tr($A[2]);return"<table>\n".($A[1]?"<thead>$cd</thead>\n":$cd).pre_tr($A[4])."\n</table>";},preg_replace('~(\n(    -|mysql)&gt; )(.+)~',"\\1<code class='jush-sql'>\\3</code>",preg_replace('~(.+)\n---+\n~',"<b>\\1</b>\n",h($_h['comment']))));echo'</pre>
';}elseif(isset($_GET["foreign"])){$a=$_GET["foreign"];$B=$_GET["name"];$K=$_POST;if($_POST&&!$l&&!$_POST["add"]&&!$_POST["change"]&&!$_POST["change-js"]){if(!$_POST["drop"]){$K["source"]=array_filter($K["source"],'strlen');ksort($K["source"]);$Ii=array();foreach($K["source"]as$x=>$X)$Ii[$x]=$K["target"][$x];$K["target"]=$Ii;}if(JUSH=="sqlite")$I=recreate_table($a,$a,array(),array(),array(" $B"=>($K["drop"]?"":" ".format_foreign_key($K))));else{$b="ALTER TABLE ".table($a);$I=($B==""||queries("$b DROP ".(JUSH=="sql"?"FOREIGN KEY ":"CONSTRAINT ").idf_escape($B)));if(!$K["drop"])$I=queries("$b ADD".format_foreign_key($K));}queries_redirect(ME."table=".urlencode($a),($K["drop"]?'Foreign key has been dropped.':($B!=""?'Foreign key has been altered.':'Foreign key has been created.')),$I);if(!$K["drop"])$l='Source and target columns must have the same data type, there must be an index on the target columns and referenced data must exist.';}page_header('Foreign key',$l,array("table"=>$a),h($a));if($_POST){ksort($K["source"]);if($_POST["add"])$K["source"][]="";elseif($_POST["change"]||$_POST["change-js"])$K["target"]=array();}elseif($B!=""){$ld=foreign_keys($a);$K=$ld[$B];$K["source"][]="";}else{$K["table"]=$a;$K["source"]=array("");}echo'
<form action="" method="post">
';$fi=array_keys(fields($a));if($K["db"]!="")connection()->select_db($K["db"]);if($K["ns"]!=""){$lg=get_schema();set_schema($K["ns"]);}$lh=array_keys(array_filter(table_status('',true),'Adminer\fk_support'));$Ii=array_keys(fields(in_array($K["table"],$lh)?$K["table"]:reset($lh)));$Vf="this.form['change-js'].value = '1'; this.form.submit();";echo"<p><label>".'Target table'.": ".html_select("table",$lh,$K["table"],$Vf)."</label>\n";if(support("scheme")){$Gh=array_filter(adminer()->schemas(),function($Fh){return!preg_match('~^information_schema$~i',$Fh);});echo"<label>".'Schema'.": ".html_select("ns",$Gh,$K["ns"]!=""?$K["ns"]:$_GET["ns"],$Vf)."</label>";if($K["ns"]!="")set_schema($lg);}elseif(JUSH!="sqlite"){$Qb=array();foreach(adminer()->databases()as$j){if(!information_schema($j))$Qb[]=$j;}echo"<label>".'DB'.": ".html_select("db",$Qb,$K["db"]!=""?$K["db"]:$_GET["db"],$Vf)."</label>";}echo
input_hidden("change-js"),'<noscript><p><input type="submit" name="change" value="Change"></noscript>
<table>
<thead><tr><th id="label-source">Source<th id="label-target">Target</thead>
';$ze=0;foreach($K["source"]as$x=>$X){echo"<tr>","<td>".html_select("source[".(+$x)."]",array(-1=>"")+$fi,$X,($ze==count($K["source"])-1?"foreignAddRow.call(this);":""),"label-source"),"<td>".html_select("target[".(+$x)."]",$Ii,idx($K["target"],$x),"","label-target");$ze++;}echo'</table>
<p>
<label>ON DELETE: ',html_select("on_delete",array(-1=>"")+explode("|",driver()->onActions),$K["on_delete"]),'</label>
<label>ON UPDATE: ',html_select("on_update",array(-1=>"")+explode("|",driver()->onActions),$K["on_update"]),'</label>
',doc_link(array('sql'=>"innodb-foreign-key-constraints.html",'mariadb'=>"foreign-keys/",'pgsql'=>"sql-createtable.html#SQL-CREATETABLE-REFERENCES",'mssql'=>"t-sql/statements/create-table-transact-sql",'oracle'=>"SQLRF01111",)),'<p>
<input type="submit" value="Save">
<noscript><p><input type="submit" name="add" value="Add column"></noscript>
';if($B!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$B));echo
input_token(),'</form>
';}elseif(isset($_GET["view"])){$a=$_GET["view"];$K=$_POST;$mg="VIEW";if(JUSH=="pgsql"&&$a!=""){$P=table_status1($a);$mg=strtoupper($P["Engine"]);}if($_POST&&!$l){$B=trim($K["name"]);$wa=" AS\n$K[select]";$Se=ME."table=".urlencode($B);$lf='View has been altered.';$U=($_POST["materialized"]?"MATERIALIZED VIEW":"VIEW");if(!$_POST["drop"]&&$a==$B&&JUSH!="sqlite"&&$U=="VIEW"&&$mg=="VIEW")query_redirect((JUSH=="mssql"?"ALTER":"CREATE OR REPLACE")." VIEW ".table($B).$wa,$Se,$lf);else{$Ki=$B."_adminer_".uniqid();drop_create("DROP $mg ".table($a),"CREATE $U ".table($B).$wa,"DROP $U ".table($B),"CREATE $U ".table($Ki).$wa,"DROP $U ".table($Ki),($_POST["drop"]?substr(ME,0,-1):$Se),'View has been dropped.',$lf,'View has been created.',$a,$B);}}if(!$_POST&&$a!=""){$K=view($a);$K["name"]=$a;$K["materialized"]=($mg!="VIEW");if(!$l)$l=error();}page_header(($a!=""?'Alter view':'Create view'),$l,array("table"=>$a),h($a));echo'
<form action="" method="post">
<p>Name: <input name="name" value="',h($K["name"]),'" data-maxlength="64" autocapitalize="off">
',(support("materializedview")?" ".checkbox("materialized",1,$K["materialized"],'Materialized view'):""),'<p>';textarea("select",$K["select"]);echo'<p>
<input type="submit" value="Save">
';if($a!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$a));echo
input_token(),'</form>
';}elseif(isset($_GET["event"])){$aa=$_GET["event"];$qe=array("YEAR","QUARTER","MONTH","DAY","HOUR","MINUTE","WEEK","SECOND","YEAR_MONTH","DAY_HOUR","DAY_MINUTE","DAY_SECOND","HOUR_MINUTE","HOUR_SECOND","MINUTE_SECOND");$pi=array("ENABLED"=>"ENABLE","DISABLED"=>"DISABLE","SLAVESIDE_DISABLED"=>"DISABLE ON SLAVE");$K=$_POST;if($_POST&&!$l){if($_POST["drop"])query_redirect("DROP EVENT ".idf_escape($aa),substr(ME,0,-1),'Event has been dropped.');elseif(in_array($K["INTERVAL_FIELD"],$qe)&&isset($pi[$K["STATUS"]])){$Eh="\nON SCHEDULE ".($K["INTERVAL_VALUE"]?"EVERY ".q($K["INTERVAL_VALUE"])." $K[INTERVAL_FIELD]".($K["STARTS"]?" STARTS ".q($K["STARTS"]):"").($K["ENDS"]?" ENDS ".q($K["ENDS"]):""):"AT ".q($K["STARTS"]))." ON COMPLETION".($K["ON_COMPLETION"]?"":" NOT")." PRESERVE";queries_redirect(substr(ME,0,-1),($aa!=""?'Event has been altered.':'Event has been created.'),queries(($aa!=""?"ALTER EVENT ".idf_escape($aa).$Eh.($aa!=$K["EVENT_NAME"]?"\nRENAME TO ".idf_escape($K["EVENT_NAME"]):""):"CREATE EVENT ".idf_escape($K["EVENT_NAME"]).$Eh)."\n".$pi[$K["STATUS"]]." COMMENT ".q($K["EVENT_COMMENT"]).rtrim(" DO\n$K[EVENT_DEFINITION]",";").";"));}}page_header(($aa!=""?'Alter event'.": ".h($aa):'Create event'),$l);if(!$K&&$aa!=""){$L=get_rows("SELECT * FROM information_schema.EVENTS WHERE EVENT_SCHEMA = ".q(DB)." AND EVENT_NAME = ".q($aa));$K=reset($L);}echo'
<form action="" method="post">
<table class="layout">
<tr><th>Name<td><input name="EVENT_NAME" value="',h($K["EVENT_NAME"]),'" data-maxlength="64" autocapitalize="off">
<tr><th title="datetime">Start<td><input name="STARTS" value="',h("$K[EXECUTE_AT]$K[STARTS]"),'">
<tr><th title="datetime">End<td><input name="ENDS" value="',h($K["ENDS"]),'">
<tr><th>Every<td><input type="number" name="INTERVAL_VALUE" value="',h($K["INTERVAL_VALUE"]),'" class="size"> ',html_select("INTERVAL_FIELD",$qe,$K["INTERVAL_FIELD"]),'<tr><th>Status<td>',html_select("STATUS",$pi,$K["STATUS"]),'<tr><th>Comment<td><input name="EVENT_COMMENT" value="',h($K["EVENT_COMMENT"]),'" data-maxlength="64">
<tr><th><td>',checkbox("ON_COMPLETION","PRESERVE",$K["ON_COMPLETION"]=="PRESERVE",'On completion preserve'),'</table>
<p>';textarea("EVENT_DEFINITION",$K["EVENT_DEFINITION"]);echo'<p>
<input type="submit" value="Save">
';if($aa!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$aa));echo
input_token(),'</form>
';}elseif(isset($_GET["procedure"])){$ba=($_GET["name"]?:$_GET["procedure"]);$_h=(isset($_GET["function"])?"FUNCTION":"PROCEDURE");$K=$_POST;$K["fields"]=(array)$K["fields"];if($_POST&&!process_fields($K["fields"])&&!$l){$ig=routine($_GET["procedure"],$_h);$Ki="$K[name]_adminer_".uniqid();foreach($K["fields"]as$x=>$m){if($m["field"]=="")unset($K["fields"][$x]);}drop_create("DROP $_h ".routine_id($ba,$ig),create_routine($_h,$K),"DROP $_h ".routine_id($K["name"],$K),create_routine($_h,array("name"=>$Ki)+$K),"DROP $_h ".routine_id($Ki,$K),substr(ME,0,-1),'Routine has been dropped.','Routine has been altered.','Routine has been created.',$ba,$K["name"]);}page_header(($ba!=""?(isset($_GET["function"])?'Alter function':'Alter procedure').": ".h($ba):(isset($_GET["function"])?'Create function':'Create procedure')),$l);if(!$_POST){if($ba=="")$K["language"]="sql";else{$K=routine($_GET["procedure"],$_h);$K["name"]=$ba;}}$jb=get_vals("SHOW CHARACTER SET");sort($jb);$Ah=routine_languages();echo($jb?"<datalist id='collations'>".optionlist($jb)."</datalist>":""),'
<form action="" method="post" id="form">
<p>Name: <input name="name" value="',h($K["name"]),'" data-maxlength="64" autocapitalize="off">
',($Ah?"<label>".'Language'.": ".html_select("language",$Ah,$K["language"])."</label>\n":""),'<input type="submit" value="Save">
<div class="scrollable">
<table class="nowrap">
';edit_fields($K["fields"],$jb,$_h);if(isset($_GET["function"])){echo"<tr><td>".'Return type';edit_type("returns",(array)$K["returns"],$jb,array(),(JUSH=="pgsql"?array("void","trigger"):array()));}echo'</table>
',script("editFields();"),'</div>
<p>';textarea("definition",$K["definition"],20);echo'<p>
<input type="submit" value="Save">
';if($ba!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$ba));echo
input_token(),'</form>
';}elseif(isset($_GET["sequence"])){$da=$_GET["sequence"];$K=$_POST;if($_POST&&!$l){$_=substr(ME,0,-1);$B=trim($K["name"]);if($_POST["drop"])query_redirect("DROP SEQUENCE ".idf_escape($da),$_,'Sequence has been dropped.');elseif($da=="")query_redirect("CREATE SEQUENCE ".idf_escape($B),$_,'Sequence has been created.');elseif($da!=$B)query_redirect("ALTER SEQUENCE ".idf_escape($da)." RENAME TO ".idf_escape($B),$_,'Sequence has been altered.');else
redirect($_);}page_header($da!=""?'Alter sequence'.": ".h($da):'Create sequence',$l);if(!$K)$K["name"]=$da;echo'
<form action="" method="post">
<p><input name="name" value="',h($K["name"]),'" autocapitalize="off">
<input type="submit" value="Save">
';if($da!="")echo"<input type='submit' name='drop' value='".'Drop'."'>".confirm(sprintf('Drop %s?',$da))."\n";echo
input_token(),'</form>
';}elseif(isset($_GET["type"])){$ea=$_GET["type"];$K=$_POST;if($_POST&&!$l){$_=substr(ME,0,-1);if($_POST["drop"])query_redirect("DROP TYPE ".idf_escape($ea),$_,'Type has been dropped.');else
query_redirect("CREATE TYPE ".idf_escape(trim($K["name"]))." $K[as]",$_,'Type has been created.');}page_header($ea!=""?'Alter type'.": ".h($ea):'Create type',$l);if(!$K)$K["as"]="AS ";echo'
<form action="" method="post">
<p>
';if($ea!=""){$nj=driver()->types();$Bc=type_values($nj[$ea]);if($Bc)echo"<code class='jush-".JUSH."'>ENUM (".h($Bc).")</code>\n<p>";echo"<input type='submit' name='drop' value='".'Drop'."'>".confirm(sprintf('Drop %s?',$ea))."\n";}else{echo'Name'.": <input name='name' value='".h($K['name'])."' autocapitalize='off'>\n",doc_link(array('pgsql'=>"datatype-enum.html",),"?");textarea("as",$K["as"]);echo"<p><input type='submit' value='".'Save'."'>\n";}echo
input_token(),'</form>
';}elseif(isset($_GET["check"])){$a=$_GET["check"];$B=$_GET["name"];$K=$_POST;if($K&&!$l){if(JUSH=="sqlite")$I=recreate_table($a,$a,array(),array(),array(),"",array(),"$B",($K["drop"]?"":$K["clause"]));else{$I=($B==""||queries("ALTER TABLE ".table($a)." DROP CONSTRAINT ".idf_escape($B)));if(!$K["drop"])$I=queries("ALTER TABLE ".table($a)." ADD".($K["name"]!=""?" CONSTRAINT ".idf_escape($K["name"]):"")." CHECK ($K[clause])");}queries_redirect(ME."table=".urlencode($a),($K["drop"]?'Check has been dropped.':($B!=""?'Check has been altered.':'Check has been created.')),$I);}page_header(($B!=""?'Alter check'.": ".h($B):'Create check'),$l,array("table"=>$a));if(!$K){$ab=driver()->checkConstraints($a);$K=array("name"=>$B,"clause"=>$ab[$B]);}echo'
<form action="" method="post">
<p>';if(JUSH!="sqlite")echo'Name'.': <input name="name" value="'.h($K["name"]).'" data-maxlength="64" autocapitalize="off"> ';echo
doc_link(array('sql'=>"create-table-check-constraints.html",'mariadb'=>"constraint/",'pgsql'=>"ddl-constraints.html#DDL-CONSTRAINTS-CHECK-CONSTRAINTS",'mssql'=>"relational-databases/tables/create-check-constraints",'sqlite'=>"lang_createtable.html#check_constraints",),"?"),'<p>';textarea("clause",$K["clause"]);echo'<p><input type="submit" value="Save">
';if($B!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$B));echo
input_token(),'</form>
';}elseif(isset($_GET["trigger"])){$a=$_GET["trigger"];$B="$_GET[name]";$jj=trigger_options();$K=(array)trigger($B,$a)+array("Trigger"=>$a."_bi");if($_POST){if(!$l&&in_array($_POST["Timing"],$jj["Timing"])&&in_array($_POST["Event"],$jj["Event"])&&in_array($_POST["Type"],$jj["Type"])){$Sf=" ON ".table($a);$ic="DROP TRIGGER ".idf_escape($B).(JUSH=="pgsql"?$Sf:"");$Se=ME."table=".urlencode($a);if($_POST["drop"])query_redirect($ic,$Se,'Trigger has been dropped.');else{if($B!="")queries($ic);queries_redirect($Se,($B!=""?'Trigger has been altered.':'Trigger has been created.'),queries(create_trigger($Sf,$_POST)));if($B!="")queries(create_trigger($Sf,$K+array("Type"=>reset($jj["Type"]))));}}$K=$_POST;}page_header(($B!=""?'Alter trigger'.": ".h($B):'Create trigger'),$l,array("table"=>$a));echo'
<form action="" method="post" id="form">
<table class="layout">
<tr><th>Time<td>',html_select("Timing",$jj["Timing"],$K["Timing"],"triggerChange(/^".preg_quote($a,"/")."_[ba][iud]$/, '".js_escape($a)."', this.form);"),'<tr><th>Event<td>',html_select("Event",$jj["Event"],$K["Event"],"this.form['Timing'].onchange();"),(in_array("UPDATE OF",$jj["Event"])?" <input name='Of' value='".h($K["Of"])."' class='hidden'>":""),'<tr><th>Type<td>',html_select("Type",$jj["Type"],$K["Type"]),'</table>
<p>Name: <input name="Trigger" value="',h($K["Trigger"]),'" data-maxlength="64" autocapitalize="off">
',script("qs('#form')['Timing'].onchange();"),'<p>';textarea("Statement",$K["Statement"]);echo'<p>
<input type="submit" value="Save">
';if($B!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$B));echo
input_token(),'</form>
';}elseif(isset($_GET["user"])){$fa=$_GET["user"];$Zg=array(""=>array("All privileges"=>""));foreach(get_rows("SHOW PRIVILEGES")as$K){foreach(explode(",",($K["Privilege"]=="Grant option"?"":$K["Context"]))as$_b)$Zg[$_b][$K["Privilege"]]=$K["Comment"];}$Zg["Server Admin"]+=$Zg["File access on server"];$Zg["Databases"]["Create routine"]=$Zg["Procedures"]["Create routine"];unset($Zg["Procedures"]["Create routine"]);$Zg["Columns"]=array();foreach(array("Select","Insert","Update","References")as$X)$Zg["Columns"][$X]=$Zg["Tables"][$X];unset($Zg["Server Admin"]["Usage"]);foreach($Zg["Tables"]as$x=>$X)unset($Zg["Databases"][$x]);$Af=array();if($_POST){foreach($_POST["objects"]as$x=>$X)$Af[$X]=(array)$Af[$X]+idx($_POST["grants"],$x,array());}$vd=array();$Qf="";if(isset($_GET["host"])&&($I=connection()->query("SHOW GRANTS FOR ".q($fa)."@".q($_GET["host"])))){while($K=$I->fetch_row()){if(preg_match('~GRANT (.*) ON (.*) TO ~',$K[0],$A)&&preg_match_all('~ *([^(,]*[^ ,(])( *\([^)]+\))?~',$A[1],$Ze,PREG_SET_ORDER)){foreach($Ze
as$X){if($X[1]!="USAGE")$vd["$A[2]$X[2]"][$X[1]]=true;if(preg_match('~ WITH GRANT OPTION~',$K[0]))$vd["$A[2]$X[2]"]["GRANT OPTION"]=true;}}if(preg_match("~ IDENTIFIED BY PASSWORD '([^']+)~",$K[0],$A))$Qf=$A[1];}}if($_POST&&!$l){$Rf=(isset($_GET["host"])?q($fa)."@".q($_GET["host"]):"''");if($_POST["drop"])query_redirect("DROP USER $Rf",ME."privileges=",'User has been dropped.');else{$Cf=q($_POST["user"])."@".q($_POST["host"]);$Eg=$_POST["pass"];if($Eg!=''&&!$_POST["hashed"]&&!min_version(8)){$Eg=get_val("SELECT PASSWORD(".q($Eg).")");$l=!$Eg;}$Eb=false;if(!$l){if($Rf!=$Cf){$Eb=queries((min_version(5)?"CREATE USER":"GRANT USAGE ON *.* TO")." $Cf IDENTIFIED BY ".(min_version(8)?"":"PASSWORD ").q($Eg));$l=!$Eb;}elseif($Eg!=$Qf)queries("SET PASSWORD FOR $Cf = ".q($Eg));}if(!$l){$xh=array();foreach($Af
as$Kf=>$ud){if(isset($_GET["grant"]))$ud=array_filter($ud);$ud=array_keys($ud);if(isset($_GET["grant"]))$xh=array_diff(array_keys(array_filter($Af[$Kf],'strlen')),$ud);elseif($Rf==$Cf){$Of=array_keys((array)$vd[$Kf]);$xh=array_diff($Of,$ud);$ud=array_diff($ud,$Of);unset($vd[$Kf]);}if(preg_match('~^(.+)\s*(\(.*\))?$~U',$Kf,$A)&&(!grant("REVOKE",$xh,$A[2]," ON $A[1] FROM $Cf")||!grant("GRANT",$ud,$A[2]," ON $A[1] TO $Cf"))){$l=true;break;}}}if(!$l&&isset($_GET["host"])){if($Rf!=$Cf)queries("DROP USER $Rf");elseif(!isset($_GET["grant"])){foreach($vd
as$Kf=>$xh){if(preg_match('~^(.+)(\(.*\))?$~U',$Kf,$A))grant("REVOKE",array_keys($xh),$A[2]," ON $A[1] FROM $Cf");}}}queries_redirect(ME."privileges=",(isset($_GET["host"])?'User has been altered.':'User has been created.'),!$l);if($Eb)connection()->query("DROP USER $Cf");}}page_header((isset($_GET["host"])?'Username'.": ".h("$fa@$_GET[host]"):'Create user'),$l,array("privileges"=>array('','Privileges')));$K=$_POST;if($K)$vd=$Af;else{$K=$_GET+array("host"=>get_val("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', -1)"));$K["pass"]=$Qf;if($Qf!="")$K["hashed"]=true;$vd[(DB==""||$vd?"":idf_escape(addcslashes(DB,"%_\\"))).".*"]=array();}echo'<form action="" method="post">
<table class="layout">
<tr><th>Server<td><input name="host" data-maxlength="60" value="',h($K["host"]),'" autocapitalize="off">
<tr><th>Username<td><input name="user" data-maxlength="80" value="',h($K["user"]),'" autocapitalize="off">
<tr><th>Password<td><input name="pass" id="pass" value="',h($K["pass"]),'" autocomplete="new-password">
',($K["hashed"]?"":script("typePassword(qs('#pass'));")),(min_version(8)?"":checkbox("hashed",1,$K["hashed"],'Hashed',"typePassword(this.form['pass'], this.checked);")),'</table>

',"<table class='odds'>\n","<thead><tr><th colspan='2'>".'Privileges'.doc_link(array('sql'=>"grant.html#priv_level"));$s=0;foreach($vd
as$Kf=>$ud){echo'<th>'.($Kf!="*.*"?"<input name='objects[$s]' value='".h($Kf)."' size='10' autocapitalize='off'>":input_hidden("objects[$s]","*.*")."*.*");$s++;}echo"</thead>\n";foreach(array(""=>"","Server Admin"=>'Server',"Databases"=>'Database',"Tables"=>'Table',"Columns"=>'Column',"Procedures"=>'Routine',)as$_b=>$Yb){foreach((array)$Zg[$_b]as$Yg=>$ob){echo"<tr><td".($Yb?">$Yb<td":" colspan='2'").' lang="en" title="'.h($ob).'">'.h($Yg);$s=0;foreach($vd
as$Kf=>$ud){$B="'grants[$s][".h(strtoupper($Yg))."]'";$Y=$ud[strtoupper($Yg)];if($_b=="Server Admin"&&$Kf!=(isset($vd["*.*"])?"*.*":".*"))echo"<td>";elseif(isset($_GET["grant"]))echo"<td><select name=$B><option><option value='1'".($Y?" selected":"").">".'Grant'."<option value='0'".($Y=="0"?" selected":"").">".'Revoke'."</select>";else
echo"<td align='center'><label class='block'>","<input type='checkbox' name=$B value='1'".($Y?" checked":"").($Yg=="All privileges"?" id='grants-$s-all'>":">".($Yg=="Grant option"?"":script("qsl('input').onclick = function () { if (this.checked) formUncheck('grants-$s-all'); };"))),"</label>";$s++;}}}echo"</table>\n",'<p>
<input type="submit" value="Save">
';if(isset($_GET["host"]))echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',"$fa@$_GET[host]"));echo
input_token(),'</form>
';}elseif(isset($_GET["processlist"])){if(support("kill")){if($_POST&&!$l){$Ee=0;foreach((array)$_POST["kill"]as$X){if(adminer()->killProcess($X))$Ee++;}queries_redirect(ME."processlist=",lang_format(array('%d process has been killed.','%d processes have been killed.'),$Ee),$Ee||!$_POST["kill"]);}}page_header('Process list',$l);echo'
<form action="" method="post">
<div class="scrollable">
<table class="nowrap checkable odds">
',script("mixin(qsl('table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});");$s=-1;foreach(adminer()->processList()as$s=>$K){if(!$s){echo"<thead><tr lang='en'>".(support("kill")?"<th>":"");foreach($K
as$x=>$X)echo"<th>$x".doc_link(array('sql'=>"show-processlist.html#processlist_".strtolower($x),'pgsql'=>"monitoring-stats.html#PG-STAT-ACTIVITY-VIEW",'oracle'=>"REFRN30223",));echo"</thead>\n";}echo"<tr>".(support("kill")?"<td>".checkbox("kill[]",$K[JUSH=="sql"?"Id":"pid"],0):"");foreach($K
as$x=>$X)echo"<td>".((JUSH=="sql"&&$x=="Info"&&preg_match("~Query|Killed~",$K["Command"])&&$X!="")||(JUSH=="pgsql"&&$x=="current_query"&&$X!="<IDLE>")||(JUSH=="oracle"&&$x=="sql_text"&&$X!="")?"<code class='jush-".JUSH."'>".shorten_utf8($X,100,"</code>").' <a href="'.h(ME.($K["db"]!=""?"db=".urlencode($K["db"])."&":"")."sql=".urlencode($X)).'">'.'Clone'.'</a>':h($X));echo"\n";}echo'</table>
</div>
<p>
';if(support("kill"))echo($s+1)."/".sprintf('%d in total',max_connections()),"<p><input type='submit' value='".'Kill'."'>\n";echo
input_token(),'</form>
',script("tableCheck();");}elseif(isset($_GET["select"])){$a=$_GET["select"];$S=table_status1($a);$w=indexes($a);$n=fields($a);$ld=column_foreign_keys($a);$Mf=$S["Oid"];$na=get_settings("adminer_import");$yh=array();$e=array();$Lh=array();$eg=array();$Oi="";foreach($n
as$x=>$m){$B=adminer()->fieldName($m);$zf=html_entity_decode(strip_tags($B),ENT_QUOTES);if(isset($m["privileges"]["select"])&&$B!=""){$e[$x]=$zf;if(is_shortable($m))$Oi=adminer()->selectLengthProcess();}if(isset($m["privileges"]["where"])&&$B!="")$Lh[$x]=$zf;if(isset($m["privileges"]["order"])&&$B!="")$eg[$x]=$zf;$yh+=$m["privileges"];}list($M,$wd)=adminer()->selectColumnsProcess($e,$w);$M=array_unique($M);$wd=array_unique($wd);$ue=count($wd)<count($M);$Z=adminer()->selectSearchProcess($n,$w);$dg=adminer()->selectOrderProcess($n,$w);$z=adminer()->selectLimitProcess();if($_GET["val"]&&is_ajax()){header("Content-Type: text/plain; charset=utf-8");foreach($_GET["val"]as$sj=>$K){$wa=convert_field($n[key($K)]);$M=array($wa?:idf_escape(key($K)));$Z[]=where_check($sj,$n);$J=driver()->select($a,$M,$Z,$M);if($J)echo
first($J->fetch_row());}exit;}$G=$uj=array();foreach($w
as$v){if($v["type"]=="PRIMARY"){$G=array_flip($v["columns"]);$uj=($M?$G:array());foreach($uj
as$x=>$X){if(in_array(idf_escape($x),$M))unset($uj[$x]);}break;}}if($Mf&&!$G){$G=$uj=array($Mf=>0);$w[]=array("type"=>"PRIMARY","columns"=>array($Mf));}if($_POST&&!$l){$Tj=$Z;if(!$_POST["all"]&&is_array($_POST["check"])){$ab=array();foreach($_POST["check"]as$Wa)$ab[]=where_check($Wa,$n);$Tj[]="((".implode(") OR (",$ab)."))";}$Tj=($Tj?"\nWHERE ".implode(" AND ",$Tj):"");if($_POST["export"]){save_settings(array("output"=>$_POST["output"],"format"=>$_POST["format"]),"adminer_import");dump_headers($a);adminer()->dumpTable($a,"");$pd=($M?implode(", ",$M):"*").convert_fields($e,$n,$M)."\nFROM ".table($a);$yd=($wd&&$ue?"\nGROUP BY ".implode(", ",$wd):"").($dg?"\nORDER BY ".implode(", ",$dg):"");$H="SELECT $pd$Tj$yd";if(is_array($_POST["check"])&&!$G){$qj=array();foreach($_POST["check"]as$X)$qj[]="(SELECT".limit($pd,"\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$n).$yd,1).")";$H=implode(" UNION ALL ",$qj);}adminer()->dumpData($a,"table",$H);adminer()->dumpFooter();exit;}if(!adminer()->selectEmailProcess($Z,$ld)){if($_POST["save"]||$_POST["delete"]){$I=true;$oa=0;$O=array();if(!$_POST["delete"]){foreach($_POST["fields"]as$B=>$X){$X=process_input($n[$B]);if($X!==null&&($_POST["clone"]||$X!==false))$O[idf_escape($B)]=($X!==false?$X:idf_escape($B));}}if($_POST["delete"]||$O){$H=($_POST["clone"]?"INTO ".table($a)." (".implode(", ",array_keys($O)).")\nSELECT ".implode(", ",$O)."\nFROM ".table($a):"");if($_POST["all"]||($G&&is_array($_POST["check"]))||$ue){$I=($_POST["delete"]?driver()->delete($a,$Tj):($_POST["clone"]?queries("INSERT $H$Tj".driver()->insertReturning($a)):driver()->update($a,$O,$Tj)));$oa=connection()->affected_rows;if(is_object($I))$oa+=$I->num_rows;}else{foreach((array)$_POST["check"]as$X){$Sj="\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$n);$I=($_POST["delete"]?driver()->delete($a,$Sj,1):($_POST["clone"]?queries("INSERT".limit1($a,$H,$Sj)):driver()->update($a,$O,$Sj,1)));if(!$I)break;$oa+=connection()->affected_rows;}}}$lf=lang_format(array('%d item has been affected.','%d items have been affected.'),$oa);if($_POST["clone"]&&$I&&$oa==1){$Je=last_id($I);if($Je)$lf=sprintf('Item%s has been inserted.'," $Je");}queries_redirect(remove_from_uri($_POST["all"]&&$_POST["delete"]?"page":""),$lf,$I);if(!$_POST["delete"]){$Qg=(array)$_POST["fields"];edit_form($a,array_intersect_key($n,$Qg),$Qg,!$_POST["clone"],$l);page_footer();exit;}}elseif(!$_POST["import"]){if(!$_POST["val"])$l='Ctrl+click on a value to modify it.';else{$I=true;$oa=0;foreach($_POST["val"]as$sj=>$K){$O=array();foreach($K
as$x=>$X){$x=bracket_escape($x,true);$O[idf_escape($x)]=(preg_match('~char|text~',$n[$x]["type"])||$X!=""?adminer()->processInput($n[$x],$X):"NULL");}$I=driver()->update($a,$O," WHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($sj,$n),($ue||$G?0:1)," ");if(!$I)break;$oa+=connection()->affected_rows;}queries_redirect(remove_from_uri(),lang_format(array('%d item has been affected.','%d items have been affected.'),$oa),$I);}}elseif(!is_string($Zc=get_file("csv_file",true)))$l=upload_error($Zc);elseif(!preg_match('~~u',$Zc))$l='File must be in UTF-8 encoding.';else{save_settings(array("output"=>$na["output"],"format"=>$_POST["separator"]),"adminer_import");$I=true;$kb=array_keys($n);preg_match_all('~(?>"[^"]*"|[^"\r\n]+)+~',$Zc,$Ze);$oa=count($Ze[0]);driver()->begin();$Rh=($_POST["separator"]=="csv"?",":($_POST["separator"]=="tsv"?"\t":";"));$L=array();foreach($Ze[0]as$x=>$X){preg_match_all("~((?>\"[^\"]*\")+|[^$Rh]*)$Rh~",$X.$Rh,$af);if(!$x&&!array_diff($af[1],$kb)){$kb=$af[1];$oa--;}else{$O=array();foreach($af[1]as$s=>$hb)$O[idf_escape($kb[$s])]=($hb==""&&$n[$kb[$s]]["null"]?"NULL":q(preg_match('~^".*"$~s',$hb)?str_replace('""','"',substr($hb,1,-1)):$hb));$L[]=$O;}}$I=(!$L||driver()->insertUpdate($a,$L,$G));if($I)driver()->commit();queries_redirect(remove_from_uri("page"),lang_format(array('%d row has been imported.','%d rows have been imported.'),$oa),$I);driver()->rollback();}}}$_i=adminer()->tableName($S);if(is_ajax()){page_headers();ob_start();}else
page_header('Select'.": $_i",$l);$O=null;if(isset($yh["insert"])||!support("table")){$vg=array();foreach((array)$_GET["where"]as$X){if(isset($ld[$X["col"]])&&count($ld[$X["col"]])==1&&($X["op"]=="="||(!$X["op"]&&(is_array($X["val"])||!preg_match('~[_%]~',$X["val"])))))$vg["set"."[".bracket_escape($X["col"])."]"]=$X["val"];}$O=$vg?"&".http_build_query($vg):"";}adminer()->selectLinks($S,$O);if(!$e&&support("table"))echo"<p class='error'>".'Unable to select the table'.($n?".":": ".error())."\n";else{echo"<form action='' id='form'>\n","<div style='display: none;'>";hidden_fields_get();echo(DB!=""?input_hidden("db",DB).(isset($_GET["ns"])?input_hidden("ns",$_GET["ns"]):""):""),input_hidden("select",$a),"</div>\n";adminer()->selectColumnsPrint($M,$e);adminer()->selectSearchPrint($Z,$Lh,$w);adminer()->selectOrderPrint($dg,$eg,$w);adminer()->selectLimitPrint($z);adminer()->selectLengthPrint($Oi);adminer()->selectActionPrint($w);echo"</form>\n";$D=$_GET["page"];$od=null;if($D=="last"){$od=get_val(count_rows($a,$Z,$ue,$wd));$D=floor(max(0,intval($od)-1)/$z);}$Mh=$M;$xd=$wd;if(!$Mh){$Mh[]="*";$Ab=convert_fields($e,$n,$M);if($Ab)$Mh[]=substr($Ab,2);}foreach($M
as$x=>$X){$m=$n[idf_unescape($X)];if($m&&($wa=convert_field($m)))$Mh[$x]="$wa AS $X";}if(!$ue&&$uj){foreach($uj
as$x=>$X){$Mh[]=idf_escape($x);if($xd)$xd[]=idf_escape($x);}}$I=driver()->select($a,$Mh,$Z,$xd,$dg,$z,$D,true);if(!$I)echo"<p class='error'>".error()."\n";else{if(JUSH=="mssql"&&$D)$I->seek($z*$D);$vc=array();echo"<form action='' method='post' enctype='multipart/form-data'>\n";$L=array();while($K=$I->fetch_assoc()){if($D&&JUSH=="oracle")unset($K["RNUM"]);$L[]=$K;}if($_GET["page"]!="last"&&$z&&$wd&&$ue&&JUSH=="sql")$od=get_val(" SELECT FOUND_ROWS()");if(!$L)echo"<p class='message'>".'No rows.'."\n";else{$Ea=adminer()->backwardKeys($a,$_i);echo"<div class='scrollable'>","<table id='table' class='nowrap checkable odds'>",script("mixin(qs('#table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true), onkeydown: editingKeydown});"),"<thead><tr>".(!$wd&&$M?"":"<td><input type='checkbox' id='all-page' class='jsonly'>".script("qs('#all-page').onclick = partial(formCheck, /check/);","")." <a href='".h($_GET["modify"]?remove_from_uri("modify"):$_SERVER["REQUEST_URI"]."&modify=1")."'>".'Modify'."</a>");$_f=array();$rd=array();reset($M);$ih=1;foreach($L[0]as$x=>$X){if(!isset($uj[$x])){$X=idx($_GET["columns"],key($M))?:array();$m=$n[$M?($X?$X["col"]:current($M)):$x];$B=($m?adminer()->fieldName($m,$ih):($X["fun"]?"*":h($x)));if($B!=""){$ih++;$_f[$x]=$B;$d=idf_escape($x);$Nd=remove_from_uri('(order|desc)[^=]*|page').'&order%5B0%5D='.urlencode($x);$Yb="&desc%5B0%5D=1";echo"<th id='th[".h(bracket_escape($x))."]'>".script("mixin(qsl('th'), {onmouseover: partial(columnMouse), onmouseout: partial(columnMouse, ' hidden')});","");$qd=apply_sql_function($X["fun"],$B);$ei=isset($m["privileges"]["order"])||$qd;echo($ei?"<a href='".h($Nd.($dg[0]==$d||$dg[0]==$x?$Yb:''))."'>$qd</a>":$qd),"<span class='column hidden'>";if($ei)echo"<a href='".h($Nd.$Yb)."' title='".'descending'."' class='text'> â†“</a>";if(!$X["fun"]&&isset($m["privileges"]["where"]))echo'<a href="#fieldset-search" title="'.'Search'.'" class="text jsonly"> =</a>',script("qsl('a').onclick = partial(selectSearch, '".js_escape($x)."');");echo"</span>";}$rd[$x]=$X["fun"];next($M);}}$Oe=array();if($_GET["modify"]){foreach($L
as$K){foreach($K
as$x=>$X)$Oe[$x]=max($Oe[$x],min(40,strlen(utf8_decode($X))));}}echo($Ea?"<th>".'Relations':"")."</thead>\n";if(is_ajax())ob_end_clean();foreach(adminer()->rowDescriptions($L,$ld)as$yf=>$K){$rj=unique_array($L[$yf],$w);if(!$rj){$rj=array();reset($M);foreach($L[$yf]as$x=>$X){if(!preg_match('~^(COUNT|AVG|GROUP_CONCAT|MAX|MIN|SUM)\(~',current($M)))$rj[$x]=$X;next($M);}}$sj="";foreach($rj
as$x=>$X){$m=(array)$n[$x];if((JUSH=="sql"||JUSH=="pgsql")&&preg_match('~char|text|enum|set~',$m["type"])&&strlen($X)>64){$x=(strpos($x,'(')?$x:idf_escape($x));$x="MD5(".(JUSH!='sql'||preg_match("~^utf8~",$m["collation"])?$x:"CONVERT($x USING ".charset(connection()).")").")";$X=md5($X);}$sj
.="&".($X!==null?urlencode("where[".bracket_escape($x)."]")."=".urlencode($X===false?"f":$X):"null%5B%5D=".urlencode($x));}echo"<tr>".(!$wd&&$M?"":"<td>".checkbox("check[]",substr($sj,1),in_array(substr($sj,1),(array)$_POST["check"])).($ue||information_schema(DB)?"":" <a href='".h(ME."edit=".urlencode($a).$sj)."' class='edit'>".'edit'."</a>"));reset($M);foreach($K
as$x=>$X){if(isset($_f[$x])){$d=current($M);$m=(array)$n[$x];$X=driver()->value($X,$m);if($X!=""&&(!isset($vc[$x])||$vc[$x]!=""))$vc[$x]=(is_mail($X)?$_f[$x]:"");$_="";if(is_blob($m)&&$X!="")$_=ME.'download='.urlencode($a).'&field='.urlencode($x).$sj;if(!$_&&$X!==null){foreach((array)$ld[$x]as$p){if(count($ld[$x])==1||end($p["source"])==$x){$_="";foreach($p["source"]as$s=>$fi)$_
.=where_link($s,$p["target"][$s],$L[$yf][$fi]);$_=($p["db"]!=""?preg_replace('~([?&]db=)[^&]+~','\1'.urlencode($p["db"]),ME):ME).'select='.urlencode($p["table"]).$_;if($p["ns"])$_=preg_replace('~([?&]ns=)[^&]+~','\1'.urlencode($p["ns"]),$_);if(count($p["source"])==1)break;}}}if($d=="COUNT(*)"){$_=ME."select=".urlencode($a);$s=0;foreach((array)$_GET["where"]as$W){if(!array_key_exists($W["col"],$rj))$_
.=where_link($s++,$W["col"],$W["val"],$W["op"]);}foreach($rj
as$Ae=>$W)$_
.=where_link($s++,$Ae,$W);}$Od=select_value($X,$_,$m,$Oi);$t=h("val[$sj][".bracket_escape($x)."]");$Rg=idx(idx($_POST["val"],$sj),bracket_escape($x));$qc=!is_array($K[$x])&&is_utf8($Od)&&$L[$yf][$x]==$K[$x]&&!$rd[$x]&&!$m["generated"];$U=(preg_match('~^(AVG|MIN|MAX)\((.+)\)~',$d,$A)?$n[idf_unescape($A[2])]["type"]:$m["type"]);$Mi=preg_match('~text|json|lob~',$U);$ve=preg_match(number_type(),$U)||preg_match('~^(CHAR_LENGTH|ROUND|FLOOR|CEIL|TIME_TO_SEC|COUNT|SUM)\(~',$d);echo"<td id='$t'".($ve&&($X===null||is_numeric(strip_tags($Od))||$U=="money")?" class='number'":"");if(($_GET["modify"]&&$qc&&$X!==null)||$Rg!==null){$Ad=h($Rg!==null?$Rg:$K[$x]);echo">".($Mi?"<textarea name='$t' cols='30' rows='".(substr_count($K[$x],"\n")+1)."'>$Ad</textarea>":"<input name='$t' value='$Ad' size='$Oe[$x]'>");}else{$Ue=strpos($Od,"<i>â€¦</i>");echo" data-text='".($Ue?2:($Mi?1:0))."'".($qc?"":" data-warning='".h('Use edit link to modify this value.')."'").">$Od";}}next($M);}if($Ea)echo"<td>";adminer()->backwardKeysPrint($Ea,$L[$yf]);echo"</tr>\n";}if(is_ajax())exit;echo"</table>\n","</div>\n";}if(!is_ajax()){if($L||$D){$Ic=true;if($_GET["page"]!="last"){if(!$z||(count($L)<$z&&($L||!$D)))$od=($D?$D*$z:0)+count($L);elseif(JUSH!="sql"||!$ue){$od=($ue?false:found_rows($S,$Z));if(intval($od)<max(1e4,2*($D+1)*$z))$od=first(slow_query(count_rows($a,$Z,$ue,$wd)));else$Ic=false;}}$tg=($z&&($od===false||$od>$z||$D));if($tg)echo(($od===false?count($L)+1:$od-$D*$z)>$z?'<p><a href="'.h(remove_from_uri("page")."&page=".($D+1)).'" class="loadmore">'.'Load more data'.'</a>'.script("qsl('a').onclick = partial(selectLoadMore, $z, '".'Loading'."â€¦');",""):''),"\n";echo"<div class='footer'><div>\n";if($tg){$ef=($od===false?$D+(count($L)>=$z?2:1):floor(($od-1)/$z));echo"<fieldset>";if(JUSH!="simpledb"){echo"<legend><a href='".h(remove_from_uri("page"))."'>".'Page'."</a></legend>",script("qsl('a').onclick = function () { pageClick(this.href, +prompt('".'Page'."', '".($D+1)."')); return false; };"),pagination(0,$D).($D>5?" â€¦":"");for($s=max(1,$D-4);$s<min($ef,$D+5);$s++)echo
pagination($s,$D);if($ef>0)echo($D+5<$ef?" â€¦":""),($Ic&&$od!==false?pagination($ef,$D):" <a href='".h(remove_from_uri("page")."&page=last")."' title='~$ef'>".'last'."</a>");}else
echo"<legend>".'Page'."</legend>",pagination(0,$D).($D>1?" â€¦":""),($D?pagination($D,$D):""),($ef>$D?pagination($D+1,$D).($ef>$D+1?" â€¦":""):"");echo"</fieldset>\n";}echo"<fieldset>","<legend>".'Whole result'."</legend>";$fc=($Ic?"":"~ ").$od;$Wf="const checked = formChecked(this, /check/); selectCount('selected', this.checked ? '$fc' : checked); selectCount('selected2', this.checked || !checked ? '$fc' : checked);";echo
checkbox("all",1,0,($od!==false?($Ic?"":"~ ").lang_format(array('%d row','%d rows'),$od):""),$Wf)."\n","</fieldset>\n";if(adminer()->selectCommandPrint())echo'<fieldset',($_GET["modify"]?'':' class="jsonly"'),'><legend>Modify</legend><div>
<input type="submit" value="Save"',($_GET["modify"]?'':' title="'.'Ctrl+click on a value to modify it.'.'"'),'>
</div></fieldset>
<fieldset><legend>Selected <span id="selected"></span></legend><div>
<input type="submit" name="edit" value="Edit">
<input type="submit" name="clone" value="Clone">
<input type="submit" name="delete" value="Delete">',confirm(),'</div></fieldset>
';$md=adminer()->dumpFormat();foreach((array)$_GET["columns"]as$d){if($d["fun"]){unset($md['sql']);break;}}if($md){print_fieldset("export",'Export'." <span id='selected2'></span>");$qg=adminer()->dumpOutput();echo($qg?html_select("output",$qg,$na["output"])." ":""),html_select("format",$md,$na["format"])," <input type='submit' name='export' value='".'Export'."'>\n","</div></fieldset>\n";}adminer()->selectEmailPrint(array_filter($vc,'strlen'),$e);echo"</div></div>\n";}if(adminer()->selectImportPrint())echo"<p>","<a href='#import'>".'Import'."</a>",script("qsl('a').onclick = partial(toggle, 'import');",""),"<span id='import'".($_POST["import"]?"":" class='hidden'").">: ",file_input("<input type='file' name='csv_file'> ".html_select("separator",array("csv"=>"CSV,","csv;"=>"CSV;","tsv"=>"TSV"),$na["format"])." <input type='submit' name='import' value='".'Import'."'>"),"</span>";echo
input_token(),"</form>\n",(!$wd&&$M?"":script("tableCheck();"));}}}if(is_ajax()){ob_end_clean();exit;}}elseif(isset($_GET["variables"])){$P=isset($_GET["status"]);page_header($P?'Status':'Variables');$Jj=($P?show_status():show_variables());if(!$Jj)echo"<p class='message'>".'No rows.'."\n";else{echo"<table>\n";foreach($Jj
as$K){echo"<tr>";$x=array_shift($K);echo"<th><code class='jush-".JUSH.($P?"status":"set")."'>".h($x)."</code>";foreach($K
as$X)echo"<td>".nl_br(h($X));}echo"</table>\n";}}elseif(isset($_GET["script"])){header("Content-Type: text/javascript; charset=utf-8");if($_GET["script"]=="db"){$wi=array("Data_length"=>0,"Index_length"=>0,"Data_free"=>0);foreach(table_status()as$B=>$S){json_row("Comment-$B",h($S["Comment"]));if(!is_view($S)||preg_match('~materialized~i',$S["Engine"])){foreach(array("Engine","Collation")as$x)json_row("$x-$B",h($S[$x]));foreach($wi+array("Auto_increment"=>0,"Rows"=>0)as$x=>$X){if($S[$x]!=""){$X=format_number($S[$x]);if($X>=0)json_row("$x-$B",($x=="Rows"&&$X&&$S["Engine"]==(JUSH=="pgsql"?"table":"InnoDB")?"~ $X":$X));if(isset($wi[$x]))$wi[$x]+=($S["Engine"]!="InnoDB"||$x!="Data_free"?$S[$x]:0);}elseif(array_key_exists($x,$S))json_row("$x-$B","?");}}}foreach($wi
as$x=>$X)json_row("sum-$x",format_number($X));json_row("");}elseif($_GET["script"]=="kill")connection()->query("KILL ".number($_POST["kill"]));else{foreach(count_tables(adminer()->databases())as$j=>$X){json_row("tables-$j",$X);json_row("size-$j",db_size($j));}json_row("");}exit;}else{$Gi=array_merge((array)$_POST["tables"],(array)$_POST["views"]);if($Gi&&!$l&&!$_POST["search"]){$I=true;$lf="";if(JUSH=="sql"&&$_POST["tables"]&&count($_POST["tables"])>1&&($_POST["drop"]||$_POST["truncate"]||$_POST["copy"]))queries("SET foreign_key_checks = 0");if($_POST["truncate"]){if($_POST["tables"])$I=truncate_tables($_POST["tables"]);$lf='Tables have been truncated.';}elseif($_POST["move"]){$I=move_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$lf='Tables have been moved.';}elseif($_POST["copy"]){$I=copy_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$lf='Tables have been copied.';}elseif($_POST["drop"]){if($_POST["views"])$I=drop_views($_POST["views"]);if($I&&$_POST["tables"])$I=drop_tables($_POST["tables"]);$lf='Tables have been dropped.';}elseif(JUSH=="sqlite"&&$_POST["check"]){foreach((array)$_POST["tables"]as$R){foreach(get_rows("PRAGMA integrity_check(".q($R).")")as$K)$lf
.="<b>".h($R)."</b>: ".h($K["integrity_check"])."<br>";}}elseif(JUSH!="sql"){$I=(JUSH=="sqlite"?queries("VACUUM"):apply_queries("VACUUM".($_POST["optimize"]?"":" ANALYZE"),$_POST["tables"]));$lf='Tables have been optimized.';}elseif(!$_POST["tables"])$lf='No tables.';elseif($I=queries(($_POST["optimize"]?"OPTIMIZE":($_POST["check"]?"CHECK":($_POST["repair"]?"REPAIR":"ANALYZE")))." TABLE ".implode(", ",array_map('Adminer\idf_escape',$_POST["tables"])))){while($K=$I->fetch_assoc())$lf
.="<b>".h($K["Table"])."</b>: ".h($K["Msg_text"])."<br>";}queries_redirect(substr(ME,0,-1),$lf,$I);}page_header(($_GET["ns"]==""?'Database'.": ".h(DB):'Schema'.": ".h($_GET["ns"])),$l,true);if(adminer()->homepage()){if($_GET["ns"]!==""){echo"<h3 id='tables-views'>".'Tables and views'."</h3>\n";$Fi=tables_list();if(!$Fi)echo"<p class='message'>".'No tables.'."\n";else{echo"<form action='' method='post'>\n";if(support("table")){echo"<fieldset><legend>".'Search data in tables'." <span id='selected2'></span></legend><div>",html_select("op",adminer()->operators(),idx($_POST,"op",JUSH=="elastic"?"should":"LIKE %%"))," <input type='search' name='query' value='".h($_POST["query"])."'>",script("qsl('input').onkeydown = partialArg(bodyKeydown, 'search');","")," <input type='submit' name='search' value='".'Search'."'>\n","</div></fieldset>\n";if($_POST["search"]&&$_POST["query"]!=""){$_GET["where"][0]["op"]=$_POST["op"];search_tables();}}echo"<div class='scrollable'>\n","<table class='nowrap checkable odds'>\n",script("mixin(qsl('table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});"),'<thead><tr class="wrap">','<td><input id="check-all" type="checkbox" class="jsonly">'.script("qs('#check-all').onclick = partial(formCheck, /^(tables|views)\[/);",""),'<th>'.'Table','<td>'.'Engine'.doc_link(array('sql'=>'storage-engines.html')),'<td>'.'Collation'.doc_link(array('sql'=>'charset-charsets.html','mariadb'=>'supported-character-sets-and-collations/')),'<td>'.'Data Length'.doc_link(array('sql'=>'show-table-status.html','pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT','oracle'=>'REFRN20286')),'<td>'.'Index Length'.doc_link(array('sql'=>'show-table-status.html','pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT')),'<td>'.'Data Free'.doc_link(array('sql'=>'show-table-status.html')),'<td>'.'Auto Increment'.doc_link(array('sql'=>'example-auto-increment.html','mariadb'=>'auto_increment/')),'<td>'.'Rows'.doc_link(array('sql'=>'show-table-status.html','pgsql'=>'catalog-pg-class.html#CATALOG-PG-CLASS','oracle'=>'REFRN20286')),(support("comment")?'<td>'.'Comment'.doc_link(array('sql'=>'show-table-status.html','pgsql'=>'functions-info.html#FUNCTIONS-INFO-COMMENT-TABLE')):''),"</thead>\n";$T=0;foreach($Fi
as$B=>$U){$Mj=($U!==null&&!preg_match('~table|sequence~i',$U));$t=h("Table-".$B);echo'<tr><td>'.checkbox(($Mj?"views[]":"tables[]"),$B,in_array("$B",$Gi,true),"","","",$t),'<th>'.(support("table")||support("indexes")?"<a href='".h(ME)."table=".urlencode($B)."' title='".'Show structure'."' id='$t'>".h($B).'</a>':h($B));if($Mj&&!preg_match('~materialized~i',$U)){$Si='View';echo'<td colspan="6">'.(support("view")?"<a href='".h(ME)."view=".urlencode($B)."' title='".'Alter view'."'>$Si</a>":$Si),'<td align="right"><a href="'.h(ME)."select=".urlencode($B).'" title="'.'Select data'.'">?</a>';}else{foreach(array("Engine"=>array(),"Collation"=>array(),"Data_length"=>array("create",'Alter table'),"Index_length"=>array("indexes",'Alter indexes'),"Data_free"=>array("edit",'New item'),"Auto_increment"=>array("auto_increment=1&create",'Alter table'),"Rows"=>array("select",'Select data'),)as$x=>$_){$t=" id='$x-".h($B)."'";echo($_?"<td align='right'>".(support("table")||$x=="Rows"||(support("indexes")&&$x!="Data_length")?"<a href='".h(ME."$_[0]=").urlencode($B)."'$t title='$_[1]'>?</a>":"<span$t>?</span>"):"<td id='$x-".h($B)."'>");}$T++;}echo(support("comment")?"<td id='Comment-".h($B)."'>":""),"\n";}echo"<tr><td><th>".sprintf('%d in total',count($Fi)),"<td>".h(JUSH=="sql"?get_val("SELECT @@default_storage_engine"):""),"<td>".h(db_collation(DB,collations()));foreach(array("Data_length","Index_length","Data_free")as$x)echo"<td align='right' id='sum-$x'>";echo"\n","</table>\n",script("ajaxSetHtml('".js_escape(ME)."script=db');"),"</div>\n";if(!information_schema(DB)){echo"<div class='footer'><div>\n";$Gj="<input type='submit' value='".'Vacuum'."'> ".on_help("'VACUUM'");$Zf="<input type='submit' name='optimize' value='".'Optimize'."'> ".on_help(JUSH=="sql"?"'OPTIMIZE TABLE'":"'VACUUM OPTIMIZE'");echo"<fieldset><legend>".'Selected'." <span id='selected'></span></legend><div>".(JUSH=="sqlite"?$Gj."<input type='submit' name='check' value='".'Check'."'> ".on_help("'PRAGMA integrity_check'"):(JUSH=="pgsql"?$Gj.$Zf:(JUSH=="sql"?"<input type='submit' value='".'Analyze'."'> ".on_help("'ANALYZE TABLE'").$Zf."<input type='submit' name='check' value='".'Check'."'> ".on_help("'CHECK TABLE'")."<input type='submit' name='repair' value='".'Repair'."'> ".on_help("'REPAIR TABLE'"):"")))."<input type='submit' name='truncate' value='".'Truncate'."'> ".on_help(JUSH=="sqlite"?"'DELETE'":"'TRUNCATE".(JUSH=="pgsql"?"'":" TABLE'")).confirm()."<input type='submit' name='drop' value='".'Drop'."'>".on_help("'DROP TABLE'").confirm()."\n";$i=(support("scheme")?adminer()->schemas():adminer()->databases());echo"</div></fieldset>\n";$Jh="";if(count($i)!=1&&JUSH!="sqlite"){echo"<fieldset><legend>".'Move to other database'." <span id='selected3'></span></legend><div>";$j=(isset($_POST["target"])?$_POST["target"]:(support("scheme")?$_GET["ns"]:DB));echo($i?html_select("target",$i,$j):'<input name="target" value="'.h($j).'" autocapitalize="off">'),"</label> <input type='submit' name='move' value='".'Move'."'>",(support("copy")?" <input type='submit' name='copy' value='".'Copy'."'> ".checkbox("overwrite",1,$_POST["overwrite"],'overwrite'):""),"</div></fieldset>\n";$Jh=" selectCount('selected3', formChecked(this, /^(tables|views)\[/));";}echo"<input type='hidden' name='all' value=''>",script("qsl('input').onclick = function () { selectCount('selected', formChecked(this, /^(tables|views)\[/));".(support("table")?" selectCount('selected2', formChecked(this, /^tables\[/) || $T);":"")."$Jh }"),input_token(),"</div></div>\n";}echo"</form>\n",script("tableCheck();");}echo"<p class='links'><a href='".h(ME)."create='>".'Create table'."</a>\n",(support("view")?"<a href='".h(ME)."view='>".'Create view'."</a>\n":"");if(support("routine")){echo"<h3 id='routines'>".'Routines'."</h3>\n";$Bh=routines();if($Bh){echo"<table class='odds'>\n",'<thead><tr><th>'.'Name'.'<td>'.'Type'.'<td>'.'Return type'."<td></thead>\n";foreach($Bh
as$K){$B=($K["SPECIFIC_NAME"]==$K["ROUTINE_NAME"]?"":"&name=".urlencode($K["ROUTINE_NAME"]));echo'<tr>','<th><a href="'.h(ME.($K["ROUTINE_TYPE"]!="PROCEDURE"?'callf=':'call=').urlencode($K["SPECIFIC_NAME"]).$B).'">'.h($K["ROUTINE_NAME"]).'</a>','<td>'.h($K["ROUTINE_TYPE"]),'<td>'.h($K["DTD_IDENTIFIER"]),'<td><a href="'.h(ME.($K["ROUTINE_TYPE"]!="PROCEDURE"?'function=':'procedure=').urlencode($K["SPECIFIC_NAME"]).$B).'">'.'Alter'."</a>";}echo"</table>\n";}echo'<p class="links">'.(support("procedure")?'<a href="'.h(ME).'procedure=">'.'Create procedure'.'</a>':'').'<a href="'.h(ME).'function=">'.'Create function'."</a>\n";}if(support("sequence")){echo"<h3 id='sequences'>".'Sequences'."</h3>\n";$Uh=get_vals("SELECT sequence_name FROM information_schema.sequences WHERE sequence_schema = current_schema() ORDER BY sequence_name");if($Uh){echo"<table class='odds'>\n","<thead><tr><th>".'Name'."</thead>\n";foreach($Uh
as$X)echo"<tr><th><a href='".h(ME)."sequence=".urlencode($X)."'>".h($X)."</a>\n";echo"</table>\n";}echo"<p class='links'><a href='".h(ME)."sequence='>".'Create sequence'."</a>\n";}if(support("type")){echo"<h3 id='user-types'>".'User types'."</h3>\n";$Ej=types();if($Ej){echo"<table class='odds'>\n","<thead><tr><th>".'Name'."</thead>\n";foreach($Ej
as$X)echo"<tr><th><a href='".h(ME)."type=".urlencode($X)."'>".h($X)."</a>\n";echo"</table>\n";}echo"<p class='links'><a href='".h(ME)."type='>".'Create type'."</a>\n";}if(support("event")){echo"<h3 id='events'>".'Events'."</h3>\n";$L=get_rows("SHOW EVENTS");if($L){echo"<table>\n","<thead><tr><th>".'Name'."<td>".'Schedule'."<td>".'Start'."<td>".'End'."<td></thead>\n";foreach($L
as$K)echo"<tr>","<th>".h($K["Name"]),"<td>".($K["Execute at"]?'At given time'."<td>".$K["Execute at"]:'Every'." ".$K["Interval value"]." ".$K["Interval field"]."<td>$K[Starts]"),"<td>$K[Ends]",'<td><a href="'.h(ME).'event='.urlencode($K["Name"]).'">'.'Alter'.'</a>';echo"</table>\n";$Gc=get_val("SELECT @@event_scheduler");if($Gc&&$Gc!="ON")echo"<p class='error'><code class='jush-sqlset'>event_scheduler</code>: ".h($Gc)."\n";}echo'<p class="links"><a href="'.h(ME).'event=">'.'Create event'."</a>\n";}}}}page_footer();