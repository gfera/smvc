<?php

$sitemap = array();

$sitemap['api'] = "Api";
$sitemap['api/'] = "Api";
$sitemap['api/:*'] = "Api";

$sitemap['default_controller'] = "Front/verHome";
$sitemap['contacto'] = "Front/verContacto";
$sitemap['institucional'] = "Front/verInstitucional";
$sitemap['noticias'] = "Front/verNoticias";
$sitemap['noticias/:*'] = "Front/verNoticias";
$sitemap['eventos'] = "Front/verNoticias";
$sitemap['eventos/:*'] = "Front/verNoticias";

$sitemap['productos'] = "Front/verCategorias";
$sitemap['productos/'] = "Front/verCategorias";

$sitemap['productos/p/:num'] = "Front/verProducto";
$sitemap['productos/:*'] = "Front/verCategoria";

$sitemap['makeModels'] = "Front/makeModels";


$sitemap['buscar/:*'] = "Front/buscar";


//}
$sitemap['404'] = "Errores/e404";
$sitemap['500'] = 'Errores/e500';
