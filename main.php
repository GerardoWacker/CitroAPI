<?php

/*

CitroAPI v2.0. Creado por Gerardo Wacker, desde la Ciudad Autónoma de Buenos Aires.
Recode general empezado el 19 de noviembre de 2019.
http://www.stcl.ga

*/

// Iniciar los headers y el user-agent
ini_set("user_agent","CitroAPI/2.0\n");

// Especificar URL
$_ = "links.html";

// Establecer un array con los links ya visitados.
$sitios_visitados = array();
$visitando = array();

// Crear directorio cache
mkdir("cache");

// Función para guardar los sitios en html
function guardar_sitio($url, $extension) {
    // Reemplazar objetos para poder guardar el sitio en un directorio correcto
	$resulturl = str_replace("\n", "", $url);
	$aaaa = str_replace("http://", "", $resulturl);
	$aaaaa = str_replace("?", "", $aaaa);
	$aaa = str_replace("https://", "", $aaaaa);
    
    // Crear carpeta de cache si no existe
	mkdir("cache/$aaa", 0777, true);
	file_put_contents("cache/$aaa/index.$extension", file_get_contents("$resulturl"));
	print "Guardado $resulturl\n";
}

// Funcion principal para seguir links.
function seguir_links($url) {

    // Crear un nuevo Documento DOM.
    $documento = new DOMDocument();
    // Cargar la URL correspondiente.
    $documento->loadHTML(file_get_contents($url, false, stream_context_create(array('http'=>array('method'=>"GET", 'headers'=>"CitroAPI/2.0\n")))));

    // Obtener todos los links, imágenes, y los link tags.
    $links = $documento->getElementsByTagName("a");
    $images = $documento->getElementsByTagName("img");
    $linktags = $documento->getElementsByTagName("link");

    // Para los links
    foreach ($links as $link) {
        // Obtener href del link.
        $l = $link->getAttribute("href");

        // MUCHO parsing para solo obtener links.
        if (substr($l, 0, 1) == "/" && substr($l, 0, 2) != "//") {
			$l = parse_url($url)["scheme"]."://".parse_url($url)["host"].$l;
		} else if (substr($l, 0, 2) == "//") {
			$l = parse_url($url)["scheme"].":".$l;
		} else if (substr($l, 0, 2) == "./") {
			$l = parse_url($url)["scheme"]."://".parse_url($url)["host"].dirname(parse_url($url)["path"]).substr($l, 1);
		} else if (substr($l, 0, 1) == "#") {
			$l = parse_url($url)["scheme"]."://".parse_url($url)["host"].parse_url($url)["path"].$l;
		} else if (substr($l, 0, 3) == "../") {
			$l = parse_url($url)["scheme"]."://".parse_url($url)["host"]."/".$l;
		} else if (substr($l, 0, 11) == "javascript:") {
			continue;
		} else if (substr($l, 0, 5) != "https" && substr($l, 0, 4) != "http") {
			$l = parse_url($url)["scheme"]."://".parse_url($url)["host"]."/".$l;
        }
        
        // Si el link no está en el array
        if (!in_array($l, $sitios_visitados)) {
            $sitios_visitados[] = $l;
            $visitando[] = $l;
            guardar_sitio($l, "html");
        }

    }

    // Para todas las imagenes
    foreach ($images as $img) {

        $l = $img->getAttribute("src");
        
        // Si el link no está en el array
        if (!in_array($l, $sitios_visitados)) {
            $sitios_visitados[] = $l;
            $visitando[] = $l;
            guardar_sitio($l, "png");
        }
    }

    // Para todas los css
    foreach ($linktags as $linktag) {

        if($linktag->getAttribute("rel") == "stylesheet") {
            $l = $linktag->getAttribute("href");

            // Si el link no está en el array
            if (!in_array($l, $sitios_visitados)) {
                $sitios_visitados[] = $l;
                $visitando[] = $l;
                guardar_sitio($l, "css");
            }
        }
    }
    
    array_shift($visitando);
	foreach ($visitando as $sitio) {
		seguir_links($sitio);
	}

}

seguir_links($_);

?>