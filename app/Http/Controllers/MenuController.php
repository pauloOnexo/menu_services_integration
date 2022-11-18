<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    public function menuFactory(){
        $response = array();
        $categorias = DB::table('new_category')
                        ->where('id_marca','=','1')
                        ->get();
        $subcategorias = DB::table('new_subcategorie')->get();
        $articulos = DB::table('new_article')->get();
        $queryCategories = DB::select(DB::raw("select distinct (case when a.id_categoria=47 then 0 else a.id_categoria end) orden, a.id_categoria id_categoria, nombre_categoria,a.id_marca,a.imagen_categoria
        from new_category a join Categoria_Marca b on b.id_categoria = a.id_categoria
                                            where 1=1 and activo = 1  and b.id_marca=1 and a.nombre_categoria not like '%en casa%' order by orden"));
        $response['categorias'] = $categorias;
        // $response['subcategorias'] = $subcategorias;
        // $response['articulos'] = $articulos;
        // $response['query'] = $query;

        return $response;
    }
}
