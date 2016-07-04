<?php

/**
 * FUNCION PARA CORTAR UN TEXTO Y PONERLE PUNTOS SUSPENSIVOS
 * @param: (string) a modificar, (int) cantidad de caracteres
 * @return: (string)
 */
function cortarTexto($string, $cant){  
	$string = strip_tags($string);
	$string = htmlspecialchars_decode($string);

	if (strlen($string) > $cant){
		if(substr($string,$cant-1,1) != ' '){
			$string = substr($string,'0',$cant);
			$array = explode(' ',$string);
			array_pop($array);
			$new_string = implode(' ',$array);
			
			return $new_string.'...';
		}else{ 
			return substr($string,'0',$cant-1).'...';
		}
	}else{
		return $string;	
	}
} 


/**
 * FUNCION PARA CREAR UN STRING RANDOM
 * @param: (int) cantidad de caracteres
 * @return: (string) random
 */
function createRandom($cantidad=8) { 
    $chars = "!#()$<>abcdefghijkmnopqrstuvwxyz0123456789"; 
    srand((double)microtime()*1000000); 
    $sRandom = ''; 

    for($i=1; $i<=$cantidad; $i++){
        $num = rand(0, count($chars)-1); 
        $tmp = substr($chars, $num, 1); 
        $sRandom = $sRandom . $tmp; 
    } 

    return $sRandom;
} 


/**
 * FUNCION PARA IMPRIMIR EL OBJETO
 * @param: (objeto) objeto a debuguear
 * @return: 
 */
function debugObjeto($objeto){
	echo "<pre>".print_r($objeto, true)."</pre>";
}


/**
 * FUNCION PARA FORMATEAR UNA FECHA A FORMATO Normal
 * @param: (string) fecha, (string) separador
 * @return: (string) fecha
 */
function fecha_en_letras($fecha, $longitud="largo"){
	setlocale(LC_TIME, "es_ES");
	
	if($longitud=="largo"){
		$txt_fecha = ucfirst(strftime('%A %e de %B del %Y', strtotime($fecha)));
	}else{
		$txt_fecha = ucfirst(strftime('%A %e de %B', strtotime($fecha)));
	}
	return $txt_fecha;
}


/**
 * FUNCION PARA FORMATEAR UNA FECHA A FORMATO MySql
 * @param: (string) fecha, (string) separador
 * @return: (string) fecha
 */
function fecha_normal2mysql($fecha, $separador="-"){
	list( $dia, $mes, $anio ) = split('[/.-]', $fecha);
	$fecha = $anio.$separador.$mes.$separador.$dia;
	return $fecha;
}


/**
 * FUNCION PARA FORMATEAR UNA FECHA/HORA A FORMATO MySql
 * @param: (string) fecha y hora, (string) separador de textos, (string) separador de fecha
 * @return: (string) fecha
 */
function fechahora_normal2mysql($fechahora, $separador_txt=" ", $separador_fecha="-"){
	list( $fecha, $hora ) = split(' ', $fechahora);
	
	list( $dia, $mes, $anio ) = split('[/.-]', $fecha);
	$fecha = $anio.$separador_fecha.$mes.$separador_fecha.$dia;
	
	list( $hs, $mins, $segs ) = split(':', $hora);
	$hora = $hs.":".$mins;
	
	return $fecha.$separador_txt.$hora;
}


/**
 * FUNCION PARA FORMATEAR UNA FECHA A FORMATO Normal
 * @param: (string) fecha, (string) separador
 * @return: (string) fecha
 */
function fecha_mysql2normal($fecha, $separador="."){
	list( $anio, $mes, $dia ) = split('[/.-]', $fecha);
	$fecha = $dia.$separador.$mes.$separador.$anio;
	return $fecha;
}


/**
 * FUNCION PARA FORMATEAR UNA FECHA/HORA A FORMATO Normal
 * @param: (string) fecha y hora, (string) separador de textos, (string) separador de fecha
 * @return: (string) fecha
 */
function fechahora_mysql2normal($fechahora, $separador_txt=" ", $separador_fecha=".", $txt_horas=" hs."){
	list( $fecha, $hora ) = split(' ', $fechahora);
	
	list( $anio, $mes, $dia ) = split('[/.-]', $fecha);
	$fecha = $dia.$separador_fecha.$mes.$separador_fecha.$anio;
	
	list( $hs, $mins, $segs ) = split(':', $hora);
	$hora = $hs.":".$mins.$txt_horas;
	
	return $fecha.$separador_txt.$hora;
}


/**
 * FUNCION PARA TRAER LA INFORMACION OEMBED DE UN LINK
 * @param: (string) link
 * @return: (array) aDatos
 */
function getLinkOembed($link){
	$api_key = "1ed287301e0111e1ae874040d3dc5c07";
	$api_url = "http://api.embed.ly/1/oembed?key=".$api_key."&url=".$link;	
	$result = file_get_contents($api_url);
	$result_xml = json_decode($result,true);
	
	switch($result_xml["type"]){
		case "video":
		case "rich":	
		case "photo":
			$aDatos = array("type" => $result_xml["type"],
							"title" => $result_xml["title"],
							"description" => $result_xml["description"],
							"thumbnail" => $result_xml["thumbnail_url"],
							"url" => $result_xml["url"],
							"html" => $result_xml["html"],
							"width" => $result_xml["width"], 
							"height" => $result_xml["height"] );
			break;
			
		case "link":
		case "error":
		default:
			$aDatos = array("type" => "link",
							"title" => $result_xml["title"],
							"description" => $result_xml["description"],
							"thumbnail" => $result_xml["thumbnail_url"],
							"url" => $result_xml["url"],
							"html" => $result_xml["html"],
							"width" => $result_xml["thumbnail_width"],
							"height" => $result_xml["thumbnail_height"] );
			break;
		
	}
	
	return $aDatos;
}


/**
 * FUNCION PARA ARMAR LOS LINKS DE PAGINADO
 * @param: (objeto) conexion, (int) pagina actual, (int) cantidad de registros, (string) query sql, (string) criterio
 * @return: (string) botonera de paginacion
 */
function getPaginacionAdmin($conexion, $pagina, $cant_registros, $sql, $criterio){
	//traigo la cantidad
	$sql_cantidad = "SELECT COUNT(*) cantidad FROM ($sql) C";
	$rs_cantidad = $conexion->Execute($sql_cantidad);	
	$total_registros = $rs_cantidad->Fields("cantidad");
			
	$cant_paginas = 15;
	$total_paginas = ceil($total_registros / $cant_registros);
	$inicio = ($pagina - 1) * $cant_registros;
	
	//Valido que no se acceda a una pagina que no exista 
	if($pagina>$total_paginas && $total_registros>0){
		header("Location: ?p=$total_paginas"); 
	}

	//Calculo las p�ginas a mostrar
	$intervalo = ceil (($cant_paginas/2)-1);
	$pag_desde = $pagina - $intervalo;
	$pag_hasta = $pagina + $intervalo;

	if($pag_desde < 1){
		$pag_hasta -= ($pag_desde - 1);
		$pag_desde = 1;
	}

	if($pag_hasta > $total_paginas){
		$pag_desde -= ($pag_hasta - $total_paginas);
		$pag_hasta = $total_paginas;
		if($pag_desde < 1){
			$pag_desde = 1;
		}
	}

	//Armo la botonera con los indices de paginacion
	$btn_paginacion = '';
	if ($total_paginas > 1){
		$btn_paginacion = '<ul class="pagination">'."\n";
		
		if($pag_desde>1){
			$btn_paginacion .= '<li class="previous"><a href="?p=' . ($pag_desde-1) . $criterio . '">...</a></li>'."\n";
		}
		
		for ($i=$pag_desde;$i<=$pag_hasta;$i++){
		   if ($pagina == $i){
			  $btn_paginacion .= '<li class="active">' . $pagina . '</li>'."\n";
		   }else{
			  $btn_paginacion .= '<li><a href="?p='. $i . $criterio .'">' . $i . '</a></li>'."\n";
		   }
		}
		
		if($pag_hasta!=$total_paginas){
			$btn_paginacion .= '<li class="next"><a href="?p=' . ($pag_hasta+1) . $criterio . '">...</a></li>'."\n";
		}
		
		$btn_paginacion .= '</ul>'."\n";	
	}
		
	return $btn_paginacion;
}


/**
 * FUNCION PARA ARMAR LOS LINKS DE PAGINADO
 * @param: (objeto) conexion, (int) pagina actual, (int) cantidad de registros, (string) query sql, (string) criterio
 * @return: (string) botonera de paginacion
 */
function getPaginacionFront($conexion, $pagina, $cant_registros, $sql, $url=""){
	//traigo la cantidad
	$sql_cantidad = "SELECT COUNT(*) cantidad FROM ($sql) C";
	$rs_cantidad = $conexion->Execute($sql_cantidad);
	$total_registros = $rs_cantidad->Fields("cantidad");
		
	$total_paginas = ceil($total_registros / $cant_registros);
	$inicio = ($pagina - 1) * $cant_registros;

	//Valido que no se acceda a una pagina que no exista
	if($pagina>$total_paginas && $total_registros>0){
		header("Location: ".$url."/".$total_paginas);
	}
	
	//Calculo las p�ginas a mostrar
	$pag_ant = $pagina - 1;
	$pag_pos = $pagina + 1;
	
	if($pag_ant < 1){
		$pag_ant = 1;
	}
	
	if($pag_pos > $total_paginas){
		$pag_hasta = $total_paginas;
		if($pag_ant < 1){
			$pag_ant = 1;
		}
	}

	//Armo la botonera con los indices de paginacion
	$btn_paginacion = '';
	if ($total_paginas > 1){
		
		if($pagina>1){
			$btn_paginacion .= '<span id="anterior">&laquo; <a href="' . $url . '/' . $pag_ant . '">Anterior</a></span>';
		}

		if($pagina!=$total_paginas){
			$btn_paginacion .= '<span id="siguiente"><a href="' . $url . '/' . $pag_pos . '">Siguiente</a> &raquo;</span>';
		}

		$btn_paginacion .= '<br><br>';
	}

	return $btn_paginacion;
}


/**
 * FUNCION QUE DEVUELVE UN CUADRO DE MENSAJE
 * @param: (string) tipo de mensaje
 * @return: (string) mensaje
 */
function getMsg($tipo_msg){
	switch ($tipo_msg) {
		case "error":
			$mensaje = '<div id="msg_error" class="response-msg error ui-corner-all">
						  <span>Error!</span>
						  Ha ocurrido un problema al guardar los datos. Vuelva a intentarlo m&aacute;s tarde.
						</div>';
			break;
		
		case "add-ok":
			$mensaje = '<div class="response-msg success ui-corner-all">
						  <span>Todo OK!</span>
						  Los datos se han agregado correctamente.
						</div>';
			break;

		case "edit-ok":
			$mensaje = '<div class="response-msg success ui-corner-all">
						  <span>Todo OK!</span>
						  Los datos se han guardado correctamente.
						</div>';
			break;

		case "del-ok":
			$mensaje = '<div class="response-msg success ui-corner-all">
						  <span>Todo OK!</span>
						  El registro se ha borrado correctamente.
						</div>';
			break;

		default:
			$mensaje = '<div class="response-msg notice ui-corner-all">
						  <span>Atenci&oacute;n!</span>'.$tipo_msg.'</div>';
			break;
	}

	return $mensaje;
}


/**
 * FUNCION PARA OBTENER LA IP REAL DE USUARIO
 * @param: 
 * @return: (string) ip
 */
function getRealIP() {
    $ip = '';
	$ip = (!empty($_SERVER['REMOTE_ADDR']))?$_SERVER['REMOTE_ADDR'] :((!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) ? $_SERVER['HTTP_X_FORWARDED_FOR']: @getenv('REMOTE_ADDR'));
	if(isset($_SERVER['HTTP_CLIENT_IP']))
	$ip = $_SERVER['HTTP_CLIENT_IP'];
	return htmlspecialchars($ip,ENT_QUOTES);
}


/**
 * FUNCION PARA TRANSFORMAR DE bytes A OTRAS UNIDADES
 * @param: (number) tamaño en bytes
 * @return: (string) tamaño formateado
 */
function getSize($size){
	$filesizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
    return $size ? round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i] : '0 Bytes';
}


/**
 * FUNCTION PARA IR GUARDANDO LOS LINKS EN UN ARRAY 
 * @param: (string) url
 * @return: 
 */
function replace_links($url) {
	global $aLinks;
	if(!in_array($url[2], $aLinks) && $url[2]!="" && $url[2]!="#"){
		array_push($aLinks, $url[2]);
	}
}


/**
 * FUNCION PARA TRASNFORMAR TEXTO A URL
 * @param: (string) texto a formatear, (boolean) codificado con utf8
 * @return: (string) texto formateado
 */
function text2url($string, $utf8_decode=false) {
	if($utf8_decode){
		$string = utf8_decode($string);
	}else{
		$string = html_entity_decode($string);
	}
	
	$string = trim($string);
   	$string = strtolower($string);
	$string = strtr($string, utf8_decode("áãéíóúñÁÉÍÓÚÑ&Çç"), "aaeiounaeiounycc");
   	$string = trim(ereg_replace("[^ A-Za-z0-9_]", "-", $string)); 
	$string = ereg_replace("[ \t\n\r]+", "-", $string);
   	$string = ereg_replace("[ -]+", "-", $string);

	return $string; 
}


/**
 * FUNCION PARA DEVOLVER LA DIFERENCIA ENTRE DOS FECHAS
 * @param: (string) fecha1 y fecha2
 * @return: (array) 
 */
function time_difference($date1, $date2){
	$date1 = is_int($date1) ? $date1 : strtotime($date1);
	$date2 = is_int($date2) ? $date2 : strtotime($date2);
   
	if (($date1 !== false) && ($date2 !== false)) {
		if ($date2 >= $date1) {
			$diff = ($date2 - $date1);
		   
			if ($days = intval((floor($diff / 86400))))
				$diff %= 86400;
			if ($hours = intval((floor($diff / 3600))))
				$diff %= 3600;
			if ($minutes = intval((floor($diff / 60))))
				$diff %= 60;
		   
			return array("dias" => $days, 
						 "horas" => $hours, 
						 "minutos" => $minutes, 
						 "segundos" => intval($diff), 
						 "error" => 0);
		}
	}
   
	return array("error" => 1);
}



?>