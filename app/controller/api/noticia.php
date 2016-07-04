<?php

$r = new RESTRouter();
$r->setBasePath("/api/noticia");

$r->map("GET","/:num",function(Array $parts, Array $req, RESTRouter $r) {
    $r->returnSuccess(Noticias::findById($parts[0]));
});

$r->run();