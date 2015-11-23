<?php

function alapnyelv($txt) {
   switch($txt) {


//Általános
    case 'title' : $tmp = 'MKR - Magyar Katolikus Rádió'; break;
    case "További információk:":  $tmp = ""; break;
    case "Mehet":  $tmp = ""; break;
    case "Letöltés":  $tmp = ""; break;

//Főmenü  (adatbázis alapján)
    case "igenaptár":  $tmp = "Ige- és liturgikus naptár"; break;
    case "visszajelzés":  $tmp = "Visszajelzés"; break;
    case "főoldal":  $tmp = "Főoldal"; break;
    case "magunkról":  $tmp = "Magunkról"; break;
    case "műsoraink":  $tmp = "Műsoraink"; break;
    case "rádióműsor":  $tmp = "Rádióműsor"; break;
    case "hallgatás":  $tmp = "Hallgatás"; break;
    case "hírek":  $tmp = "Nachrichten"; break;
    case "hírlevél":  $tmp = "Hírlevél"; break;

//Modulok menüpontjai
  //     #29 Imalánc
    case "imalánc információk":  $tmp = ""; break;
    case "püspöki köszöntő":  $tmp = ""; break;
    case "a kezdeményezésről":  $tmp = ""; break;
    case "hogyan épül fel?":  $tmp = ""; break;
    case "az imádság formája":  $tmp = ""; break;
    case "felajánló ima":  $tmp = ""; break;

  //     #35 Letöltés (Zarándok-imalánc)
    case "letölthető anyagok":  $tmp = ""; break;
    case "imalánc-szórólapok":  $tmp = ""; break;
    case "imalánc-körlevél":  $tmp = ""; break;

  //     #27 zarándok
    case "zarándok oldal":  $tmp = ""; break;
    case "bemutatkozás":  $tmp = ""; break;
    case "kegyhelyek":  $tmp = ""; break;
    case "üzenetek":  $tmp = ""; break;
    case "engesztelés":  $tmp = ""; break;

  //     #32 testvéroldalak
    case "testvéroldalak":  $tmp = ""; break;
    case "KeKT":  $tmp = ""; break;
    case "Zarándok":  $tmp = ""; break;
    case "imperfectum":  $tmp = ""; break;
    case "Virtuális Plébánia":  $tmp = ""; break;
    
  //     #36 imádkozók
    case "imádkozók":  $tmp = ""; break;
    case "közösségek":  $tmp = ""; break;
    case "vélemények":  $tmp = ""; break;

  //     #
    case "":  $tmp = ""; break;
    case "":  $tmp = ""; break;
    case "":  $tmp = ""; break;
    case "":  $tmp = ""; break;
    case "":  $tmp = ""; break;
    case "":  $tmp = ""; break;
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
    case "január":  $tmp = ""; break;
    case "február":  $tmp = ""; break;
    case "március":  $tmp = ""; break;
    case "április":  $tmp = ""; break;
    case "május":  $tmp = ""; break;
    case "június":  $tmp = ""; break;
    case "július":  $tmp = ""; break;
    case "augusztus":  $tmp = ""; break;
    case "szeptember":  $tmp = ""; break;
    case "október":  $tmp = ""; break;
    case "november":  $tmp = ""; break;
    case "december":  $tmp = ""; break;

    case "ho1":  $tmp = ""; break;
    case "ho2":  $tmp = ""; break;
    case "ho3":  $tmp = ""; break;
    case "ho4":  $tmp = ""; break;
    case "ho5":  $tmp = ""; break;
    case "ho6":  $tmp = ""; break;
    case "ho7":  $tmp = ""; break;
    case "ho8":  $tmp = ""; break;
    case "ho9":  $tmp = ""; break;
    case "ho10":  $tmp = ""; break;
    case "ho11":  $tmp = ""; break;
    case "ho12":  $tmp = ""; break;

//Napok
    case "hétfő":  $tmp = ""; break;
    case "kedd":  $tmp = ""; break;
    case "szerda":  $tmp = ""; break;
    case "csütörtök":  $tmp = ""; break;
    case "péntek":  $tmp = ""; break;
    case "szombat":  $tmp = ""; break;
    case "vasárnap":  $tmp = ""; break;

    default: $tmp = $txt; break;
    }
    
    if(empty($tmp)) $tmp=$txt;
    
    return $tmp;
}

?>
