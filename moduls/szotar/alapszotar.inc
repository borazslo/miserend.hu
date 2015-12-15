<?php

function alapnyelv($txt) {
   switch($txt) {


//Általános
    case 'title' : $tmp = 'VPP - Virtuális Plébánia Portál'; break;
    case "További információk:":  $tmp = ""; break;
    case "Mehet":  $tmp = ""; break;
    case "Letöltés":  $tmp = ""; break;
    case 'top' : $tmp = 'oldal tetejére'; break;

    case '' : $tmp = ''; break;
    case '' : $tmp = ''; break;
    case '' : $tmp = ''; break;
    case '' : $tmp = ''; break;

//Főmenü  (adatbázis alapján)
    case "igenaptár":  $tmp = "Ige- és liturgikus naptár"; break;
    case "visszajelzés":  $tmp = "Visszajelzés"; break;
    case "főoldal":  $tmp = "Főoldal"; break;
    case "magunkról":  $tmp = "Magunkról"; break;
    case "műsoraink":  $tmp = "Műsoraink"; break;
    case "rádióműsor":  $tmp = "Rádióműsor"; break;
    case "hallgatás":  $tmp = "Hallgatás"; break;
    case "cikkek, hírek":  $tmp = "Cikkek, hírek"; break;
    case "hírlevél":  $tmp = "Hírlevél"; break;
    case 'információk': $tmp = 'Információk'; break;
    case 'aktuális műsor': $tmp = 'Aktuális műsor'; break;
    case 'aktuális hírek': $tmp = 'Aktuális hírek'; break;

    //Balmenük
    case "Magunkról":  $tmp = "Magunkról"; break;
    case "Munkatársaink":  $tmp = "Munkatársaink"; break;
    case "Vételi lehetőségek":  $tmp = "Vételi lehetőségek"; break;
    case "":  $tmp = ""; break;
    case "":  $tmp = ""; break;

//Szavazás
    case "Szavazok":  $tmp = "Szavazok"; break;
    case "szavazat":  $tmp = "szavazat"; break;
    case "Eredmény megtekintése":  $tmp = "Eredmény megtekintése"; break;
    case "":  $tmp = ""; break;

//Kereső
    case 'Kulcsszó' : $tmp = 'Kulcsszó'; break;
    case 'Keresés az alábbi kategóriában' : $tmp = 'Keresés az alábbi kategóriában'; break;
    case 'Keresés' : $tmp = 'Keresés'; break;
    case "programok":  $tmp = "programok"; break;
    case "szentek":  $tmp = "szentek"; break;
    case "gondolatok":  $tmp = "gondolatok"; break;
    case "biblia":  $tmp = "biblia"; break;
    case "munkatársak":  $tmp = "munkatársak"; break;


//Színek
    case 'feher' : $tmp = 'fehér'; break;
    case 'piros' : $tmp = 'piros'; break;
    case 'zold' : $tmp = 'zöld'; break;
    case 'lila' : $tmp = 'lila'; break;


//Beosztások
    case "":  $tmp = ""; break;
    case "":  $tmp = ""; break;
    case "":  $tmp = ""; break;
    case "":  $tmp = ""; break;
    case "":  $tmp = ""; break;

//Műsorok (akár címek, alcímek)
    case "Kategória:":  $tmp = "Kategória:"; break;
    case "A műsort készíti:":  $tmp = "A műsort készíti:"; break;
    case "":  $tmp = ""; break;
    case "":  $tmp = ""; break;
    case "":  $tmp = ""; break;

//Szervezeti egységek (szervezetek tábla)
    case "":  $tmp = ""; break;
    case "":  $tmp = ""; break;

  //Sorszámok     
    case "ssz1":  $tmp = "első"; break;
    case "ssz2":  $tmp = "második"; break;
    case "ssz3":  $tmp = "harmadik"; break;
    case "ssz4":  $tmp = "negyedik"; break;
    case "ssz5":  $tmp = "ötödik"; break;
    case "ssz6":  $tmp = "hatodik"; break;
    case "":  $tmp = ""; break;
    case "":  $tmp = ""; break;
    case "":  $tmp = ""; break;
    case "":  $tmp = ""; break;
    case "":  $tmp = ""; break;
    case "":  $tmp = ""; break;
    case "":  $tmp = ""; break;
    case "":  $tmp = ""; break;
    case "":  $tmp = ""; break;
    

//Nyelvek
    case "hu":  $tmp = "magyar"; break;
    case "en":  $tmp = "angol"; break;
    case "de":  $tmp = "német"; break;
    case "fr":  $tmp = "francia"; break;
    case "it":  $tmp = "olasz"; break;
    case "cz":  $tmp = "cseh"; break;
    case "sk":  $tmp = "szlovák"; break;
    case "si":  $tmp = "szlovén"; break;
    case "hr":  $tmp = "horvát"; break;
    case "pl":  $tmp = "lengyel"; break;
    case "ba":  $tmp = "szerb"; break;

//Országok
    case "Ausztria":  $tmp = ""; break;
    case "Belgium":  $tmp = ""; break;
    case "Bulgária":  $tmp = ""; break;
    case "Ciprus":  $tmp = ""; break;
    case "Cseh Köztársaság":  $tmp = ""; break;
    case "Dánia":  $tmp = ""; break;
    case "Észtország":  $tmp = ""; break;
    case "Finnország":  $tmp = ""; break;
    case "Franciaország":  $tmp = ""; break;
    case "Németország":  $tmp = ""; break;
    case "Görögország":  $tmp = ""; break;
    case "Magyarország":  $tmp = ""; break;
    case "Olaszország":  $tmp = ""; break;
    case "Írország":  $tmp = ""; break;
    case "Izland":  $tmp = ""; break;
    case "Lettország":  $tmp = ""; break;
    case "Liechtenstein":  $tmp = ""; break;
    case "Luxemburg":  $tmp = ""; break;
    case "Litvánia":  $tmp = ""; break;
    case "Málta":  $tmp = ""; break;
    case "Hollandia":  $tmp = ""; break;
    case "Norvégia":  $tmp = ""; break;
    case "Lengyelország":  $tmp = ""; break;
    case "Portugália":  $tmp = ""; break;
    case "Románia":  $tmp = ""; break;
    case "Szlovákia":  $tmp = ""; break;
    case "Szlovénia":  $tmp = ""; break;
    case "Spanyolország":  $tmp = ""; break;
    case "Svédország":  $tmp = ""; break;
    case "Egyesült Királyság":  $tmp = ""; break;
    case "Törökország":  $tmp = ""; break;
    case "Svájc":  $tmp = ""; break;
    case "Bosznia-Hercegovina":  $tmp = ""; break;
    case "Amerikai Egyesült Államok (USA)":  $tmp = ""; break;
    case "Kanada":  $tmp = ""; break;
    case "Brazília":  $tmp = ""; break;
    case "Japán":  $tmp = ""; break;
    case "Argentína":  $tmp = ""; break;
    case "Kína":  $tmp = ""; break;
    case "Mongólia":  $tmp = ""; break;
    case "Ausztrália":  $tmp = ""; break;
    case "Mexikó":  $tmp = ""; break;
    case "Egyiptom":  $tmp = ""; break;
    case "Izrael":  $tmp = ""; break;
    case "Ukrajna":  $tmp = ""; break;
    case "Egyéb":  $tmp = ""; break;

//Hónapok
    case "ho1":  $tmp = "január"; break;
    case "ho2":  $tmp = "február"; break;
    case "ho3":  $tmp = "március"; break;
    case "ho4":  $tmp = "április"; break;
    case "ho5":  $tmp = "május"; break;
    case "ho6":  $tmp = "június"; break;
    case "ho7":  $tmp = "július"; break;
    case "ho8":  $tmp = "augusztus"; break;
    case "ho9":  $tmp = "szeptember"; break;
    case "ho01":  $tmp = "január"; break;
    case "ho02":  $tmp = "február"; break;
    case "ho03":  $tmp = "március"; break;
    case "ho04":  $tmp = "április"; break;
    case "ho05":  $tmp = "május"; break;
    case "ho06":  $tmp = "június"; break;
    case "ho07":  $tmp = "július"; break;
    case "ho08":  $tmp = "augusztus"; break;
    case "ho09":  $tmp = "szeptember"; break;
    case "ho10":  $tmp = "október"; break;
    case "ho11":  $tmp = "november"; break;
    case "ho12":  $tmp = "december"; break;

//Napok
    case "hétfő":  $tmp = "hétfő"; break;
    case "kedd":  $tmp = "kedd"; break;
    case "szerda":  $tmp = "szerda"; break;
    case "csütörtök":  $tmp = "csütörtök"; break;
    case "péntek":  $tmp = "péntek"; break;
    case "szombat":  $tmp = "szombat"; break;
    case "vasárnap":  $tmp = "vasárnap"; break;
    
    case "nap1":  $tmp = "hétfő"; break;
    case "nap2":  $tmp = "kedd"; break;
    case "nap3":  $tmp = "szerda"; break;
    case "nap4":  $tmp = "csütörtök"; break;
    case "nap5":  $tmp = "péntek"; break;
    case "nap6":  $tmp = "szombat"; break;
    case "nap0":  $tmp = "vasárnap"; break;

    case "napon1":  $tmp = "hétfőn"; break;
    case "napon2":  $tmp = "kedden"; break;
    case "napon3":  $tmp = "szerdán"; break;
    case "napon4":  $tmp = "csütörtökön"; break;
    case "napon5":  $tmp = "pénteken"; break;
    case "napon6":  $tmp = "szombaton"; break;
    case "napon0":  $tmp = "vasárnap"; break;

    case "n1":  $tmp = "H"; break;
    case "n2":  $tmp = "K"; break;
    case "n3":  $tmp = "Sze"; break;
    case "n4":  $tmp = "CS"; break;
    case "n5":  $tmp = "P"; break;
    case "n6":  $tmp = "Szo"; break;
    case "n0":  $tmp = "V"; break;

    default: $tmp = $txt; break;
    }
    
    if(empty($tmp)) $tmp=$txt;
    
    return $tmp;
}

?>
