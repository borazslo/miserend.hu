<?php
include 'load.php';

switch ($_GET['q']) {
    case 'FromMassEmpty':
    	$form = formMass($_POST['period'],$_POST['count']);
    	echo $twig->render('admin_form_mass.html', $form);  
        break;
    case 'FromPeriodEmpty':
    	echo formPeriod($_POST['period']);
        break;
    case 'label3':
        //code to be executed if n=label3;
        break;
    default:
        //code to be executed if n is different from all labels;
}



?>