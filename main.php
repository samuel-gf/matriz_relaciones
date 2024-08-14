<?php
include('lib.php');

$a = array();
load_personas($a);
load_datos($a, 'relaciones.txt', 'relación');
load_datos($a, 'apegos.txt', 'apego');
for ($i=0; $i<10; $i++) {
	printf("\n\nIteración %d\n\n", $i);
	output_txt($a);
	iteracion($a);
}
#output_html($a, false);
?>
