<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PHPUnit\Util\Json;

class MenuController extends Controller
{

    public function menuFactory($id_marca, $id_sucursal){
        $currentTimeDate = Carbon::now();
        $currentTime = $currentTimeDate->toTimeString();
        $currentDate = $currentTimeDate->toDateString();

        // dd($currentDate);

        $response = array();

        $sucursal = DB::table('sucursal')
                    ->where('id','=',$id_sucursal)
                    ->where('id_marca','=',$id_marca)
                    ->get();


        $id_region = $sucursal[0]->id_region;

        $categorias = DB::table('new_category')
                        ->whereRaw("`id_marca` = $id_marca
                        and  `restriccion_sucursal` not in ($id_sucursal)
                        and `restriccion_region` not in ($id_region)")
                        ->get();


        // Validaciones
        // Comprobamos si alguna categoría tiene un restricción por horario INICIO
        $categoriasChidas = [];

        foreach ($categorias as $categoria) {
            $restriccion_horario_inicio = $categoria->restriccion_horario_inicio;
            $restriccion_horario_fin = $categoria->restriccion_horario_fin;

            if ($restriccion_horario_inicio != "" && $restriccion_horario_fin != "") {
                if ($currentTime >= $restriccion_horario_inicio && $currentTime <= $restriccion_horario_fin) {
                    array_push($categoriasChidas,$categoria);
                }

            }else{
                array_push($categoriasChidas,$categoria);
            }
        }

        $categoriasMasChidas = [];

        foreach ($categoriasChidas as $categoriaChida) {
            $disponibilidad_inicio = $categoriaChida->disponibilidad_inicio;
            $disponibilidad_fin = $categoriaChida->disponibilidad_fin;

            if ($disponibilidad_inicio != "" && $disponibilidad_fin != "") {
                if ($currentDate >= $disponibilidad_inicio && $currentDate <= $disponibilidad_fin) {
                    array_push($categoriasMasChidas,$categoriaChida);
                }

            }else{
                array_push($categoriasMasChidas,$categoriaChida);
            }
        }



        // Comprobamos si alguna categoría tiene un restricción por horario FIN


        if ($id_marca =! 1 || $id_marca =! 2 || $id_marca =! 3 ) {
            $response['status'] = http_response_code();
            $response['status_message'] = "La marca indicada no corresponde a nuestros registros";
            return $response;
        }

        if (sizeof($sucursal) < 1) {
            $response['status'] = http_response_code();
            $response['status_message'] = "La sucursal indicada, no corresponde a la marca solicitada";
            return $response;
        }

        if (sizeof($categoriasMasChidas) < 1) {
            $response['status'] = http_response_code();
            $response['status_message'] = "No tenemos categorias de la marca que solicitas";
            return $response;
        }


        // Fin Validaciones

        foreach ($categoriasMasChidas as $categoria) {
            $querySub = $this->getSubcategories($categoria->id_categoria, $id_sucursal, $id_region);
            $categoria->subcategorias = $querySub;
        }

        $response['status'] = http_response_code(200);
        $response['categorias'] = $categoriasMasChidas;
        $response['sucursal'] = $sucursal[0];

        return $response;
    }


    public function getSubcategories ($id_categoria, $id_sucursal, $id_region){

        $currentTimeDate = Carbon::now();
        $currentTime = $currentTimeDate->toTimeString();
        $currentDate = $currentTimeDate->toDateString();

        $subcategorias = DB::table('new_subcategorie')
                        ->select("*")
                        ->whereRaw("FIND_IN_SET('".$id_categoria."',categorias) and  `restriccion_sucursal` not in ($id_sucursal) and `restriccion_region` not in ($id_region)")
                        ->get();

        $subcategoriasChidas = [];

        foreach ($subcategorias as $subcategoria) {
            $restriccion_horario_inicio = $subcategoria->restriccion_horario_inicio;
            $restriccion_horario_fin = $subcategoria->restriccion_horario_fin;

            if ($restriccion_horario_inicio != "" && $restriccion_horario_fin != "") {
                if ($currentTime >= $restriccion_horario_inicio && $currentTime <= $restriccion_horario_fin) {
                    array_push($subcategoriasChidas,$subcategoria);
                }

            }else{
                array_push($subcategoriasChidas,$subcategoria);
            }


        }

        $subcategoriasMasChidas = [];

        foreach ($subcategoriasChidas as $subcategoriachida) {
            $disponibilidad_inicio = $subcategoriachida->disponibilidad_inicio;
            $disponibilidad_fin = $subcategoriachida->disponibilidad_fin;

            if ($disponibilidad_inicio != "" && $disponibilidad_fin != "") {
                if ($currentDate >= $disponibilidad_inicio && $currentDate <= $disponibilidad_fin) {
                    array_push($subcategoriasMasChidas,$subcategoriachida);
                }

            }else{
                array_push($subcategoriasMasChidas,$subcategoriachida);
            }
        }

        foreach ($subcategoriasMasChidas as $subcategoria) {
            $queryArt = $this->getArticles($subcategoria->id_subcategoria);
            $subcategoria->articulos = $queryArt;
        }

        return $subcategoriasMasChidas;
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
