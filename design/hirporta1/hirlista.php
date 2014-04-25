						
<?php

function hirlista_formazo($hir_listaT) {
	if(!empty($hir_listaT['lead'])) {
	$html_kod="				<div id=\"hirzart$hir_listaT[hid]$hir_listaT[mi]\" class=\"listazart\">
								<a title=\"Olvasd el a bevezetõt!\" OnClick=\"javascript:document.getElementById('hirnyit$hir_listaT[hid]$hir_listaT[mi]').style.display='block';document.getElementById('hirzart$hir_listaT[hid]$hir_listaT[mi]').style.display='none';\"><div class=\"plussz\"></div></a>
								<li class=\"linklistazart\"><a href=\"$hir_listaT[link]\" title=\"Hír megnyitása\" class=\"linklistazart\">$hir_listaT[cim]</a> <span class=\"datum\">$hir_listaT[idopont]</span></li>
							</div>
							
							<div id=\"hirnyit$hir_listaT[hid]$hir_listaT[mi]\" class=\"listanyit\">
								<a title=\"Bevezetõt becsuk\"  OnClick=\"javascript:document.getElementById('hirnyit$hir_listaT[hid]$hir_listaT[mi]').style.display='none';document.getElementById('hirzart$hir_listaT[hid]$hir_listaT[mi]').style.display='block';\"><div class=\"minusz\"></div></a>
								<li class=\"linklistazart\"><a href=\"$hir_listaT[link]\" title=\"Hír megnyitása\" class=\"linklistanyit\">$hir_listaT[cim]</a> <span class=\"datum\">$hir_listaT[idopont]</span></li>
								<div class=\"hirlead\">$hir_listaT[lead]
								<a href=\"$hir_listaT[link]\"><img src=\"img/tovabb2.gif\" width=\"10\" height=\"5\" align=\"absmiddle\" title=\"Hír megnyitása\" border=\"0\"></a>
								</div>
							</div>";
	}
	else {
	$html_kod="				<div class=\"listazart\">
								<div class=\"minusz\"></div>
								<li class=\"linklistazart\"><a href=\"$hir_listaT[link]\" title=\"Hír megnyitása\" class=\"linklistazart\">$hir_listaT[cim]</a> <span class=\"datum\">$hir_listaT[idopont]</span></li>
							</div>";							
	}

	return $html_kod;
}

?>