<?php


function load_personas(&$a){
	$s = file_get_contents('personas.txt');
	$lineas = explode("\n", $s);
	$a['_'] = array();
	$a['_']['personas'] = array();
	foreach($lineas as $linea){
		if ($linea == "") continue;
		$a[trim($linea)] = array();
		array_push($a['_']['personas'], trim($linea));
	}
}

function load_datos(&$a, $file, $dato){
	$s = file_get_contents($file);
	$lineas = explode("\n", $s);
	$personas = $a['_']['personas'];
	foreach($lineas as $linea) {
		if ($linea == "") continue;

		$campos = preg_split('/\t+/', $linea);
		$de = trim($campos[0]);
		for ($i=1; $i<sizeof($campos); $i++) {
			$campo = $campos[$i];
			$hacia = $personas[$i-1];
			#echo("$de $hacia $campo\n");
			#if ($campo == "") $campo = 0;			# Si es un campo vacío toma el valor por defecto
			$a[$de][$hacia][$dato] = trim($campo);
		}
	}
}

# Ejecutar los apegos sobre los datos existentes en $a
function iteracion(&$a){
	$personas = $a['_']['personas'];
	for($i=0; $i<sizeof($personas); $i++){
		$de = $personas[$i];
		for($j=0; $j<sizeof($personas); $j++){
			$hacia = $personas[$j];
			$rel = $a[$de][$hacia]['relación'];		# Relación
			$apego = $a[$de][$hacia]['apego'];		# Apego
			$rel += get_delta($rel, $apego);		# Aplica el alogritmo
			$a[$de][$hacia]['relación'] = $rel;		# Guarda los cambios
		}
	}
}

# Obtiene el delta (la variación) en la relación dependiendo del tipo de apego
function get_delta($rel, $apego){
	$delta = 0;
	switch($apego){
	case '+':		# Cada vez me cae mejor
		$delta = +1;
		break;
	case '++':		# Le sigo como líder
		$delta = +2;
		break;
	case '-':		# Cada vez me cae peor
		$delta = -1;
		break;
	case '--':		# Enemigo
		$delta = -2;
		break;
	case 'r1':	# Estable pero aleatorio entre -1 y +1
		$delta = rand(-1, 1);
		break;
	case 'o':	# Estoy olvidando a esta persona
		if ($rel > 0) $delta = -1;
		if ($rel < 0) $delta = +1;
		break;
	default: # Se está enfirando la relación
		if ($rel > 0) $delta = -1;
		if ($rel < 0) $delta = +1;
		break;
	}
	return $delta;
}

# Una persona influye en otra
function influye($de, $hacia, $apego){
	$delta = get_delta($a[$de][$hacia]['relación'], $apego);
	$a[$de][$hacia]['relación'] += $delta;
}

function get_array_valores($max){
	$valores[0] = 0;
	$pos = 1;
	for ($i=1; $i<=$max; $i++){
		for ($j=1; $j<=$i; $j++){
			$valores[$pos] = $i;
			$valores[-$pos] = -$i;
			$pos++;
		}
	}
	ksort($valores);
	return $valores;
}

function output_html($a, $raw=false){
	$valores = get_array_valores(20);	# Los valores 1:1, 2:3, 3:6, 4:10, 5:15, 6:21 ... el valor el el primer número de la pareja
	echo("<!DOCTYPE html>\n<html lang='es' prefix='og: http://ogp.me/ns#'>\n<head>\n");
	echo("<link rel='stylesheet' type='text/css' href='css/reset2.css'>\n");
	echo("<link rel='stylesheet' type='text/css' href='css/mobile.css'>\n");
	echo("</head>\n<body>\n");
	echo("<table>\n\t<tr><th></th>");
	$personas = $a['_']['personas'];
	foreach($personas as $persona){
		echo('<th>'.$persona.'</th>'); 
	}
	echo("</tr>\n");
	for ($i=0; $i<sizeof($personas); $i++){
		$de = $personas[$i];
		echo("\t<tr><th>".$de.'</th>');
		for ($j=0; $j<sizeof($personas); $j++){
			$hacia = $personas[$j];
			if ($raw) {		# Dato tal cual está almacenado
				echo('<td>'.$a[$de][$hacia]['relación'].'</td>');
			} else {
				$relacion = $a[$de][$hacia]['relación'];
				echo('<td>'.$valores[$relacion].'</td>');
			}
		}
		echo("</tr>\n");
	}
	echo("</table>\n");
	echo("</body>\n</html>");
}

# Salida para un fichero de datos
function output_txt($a, $raw=false){
	$personas = $a['_']['personas'];
	$valores = get_array_valores(20);	# Los valores 1:1, 2:3, 3:6, 4:10, 5:15, 6:21 ... el valor el el primer número de la pareja

	for ($i=0; $i<sizeof($personas); $i++){
		$de = $personas[$i];
		#echo("$de\t");
		printf("%-20s\t", $de);
		for ($j=0; $j<sizeof($personas)-1; $j++){
			$hacia = $personas[$j];
			if ($raw){
				printf("%4d\t", $a[$de][$hacia]['relación']);
			} else {
				$relacion = $a[$de][$hacia]['relación'];
				printf("%4d\t", $valores[$relacion]);
			}
		}
		$hacia = $personas[sizeof($personas)-1];
		printf("%4d\n", $a[$de][$hacia]['relación']);
	}
}

?>
