<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    public function menuFactory($id_marca){
        $response = array();
        $categorias = DB::table('new_category')
                        ->where('id_marca','=',$id_marca)
                        ->get();

        foreach ($categorias as $categoria) {
            $querySub = $this->getSubcategories($categoria->id_categoria);
            $categoria->subcategorias = $querySub;
        }

        $response['categorias'] = $categorias;

        return $response;
    }

    public function getSubcategories ($id_categoria){
        $subcategorias = DB::table('new_subcategorie')
                        ->select("*")
                        ->whereRaw("FIND_IN_SET('".$id_categoria."',categorias)")
                        ->get();

                        foreach ($subcategorias as $subcategoria) {
                            $queryArt = $this->getArticles($subcategoria->id_subcategoria);
                            $subcategoria->articulos = $queryArt;
                        }

                        return $subcategorias;
    }

    public function getArticles ($id_subcategoria){
        $articles = DB::table('new_article')
                        ->select("*")
                        ->whereRaw("FIND_IN_SET('".$id_subcategoria."',subcategorias)")
                        ->get();

                        foreach ($articles as $article) {
                            $queryExt = $this->getExtras($article->modificadores);
                            $article->opciones_personalizacion = $queryExt;
                        }

                        return $articles;
    }

    public function getExtras($id_extra){
        $extras = DB::table('extra')
                    ->select("*")
                    ->whereRaw("FIND_IN_SET(id,'".$id_extra."')")
                    ->get();

                    return $extras;
    }
}
