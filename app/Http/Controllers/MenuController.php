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
        // dd($id_marca);

        if ($id_marca != 1 && $id_marca != 2 && $id_marca != 3 ) {
            $response['status'] = http_response_code();
            $response['status_message'] = "La marca indicada no corresponde a nuestros registros";
            return $response;
        }

        $sucursal = DB::table('sucursal')
        ->where('id','=',$id_sucursal)
        ->where('id_marca','=',$id_marca)
        ->get();

        if (sizeof($sucursal) < 1) {
            $response['status'] = http_response_code();
            $response['status_message'] = "La sucursal indicada, no corresponde a la marca solicitada";
            return $response;
        }

        $id_region = $sucursal[0]->id_region;

        $categorias = DB::table('new_category')
                        ->whereRaw("`id_marca` = $id_marca
                        and  `restriccion_sucursal` not in ($id_sucursal)
                        and `restriccion_region` not in ($id_region) and `activo` = TRUE")
                        ->get();

        if (sizeof($categorias) < 1) {
                $response['status'] = http_response_code();
                $response['status_message'] = "No tenemos categorias de la marca que solicitas";
                return $response;
            }


        // Validaciones
        // Comprobamos si alguna categoría tiene un restricción por horario INICIO
        $categoriasHorarioRestriccion = [];

        foreach ($categorias as $categoria) {
            $restriccion_horario_inicio = $categoria->restriccion_horario_inicio;
            $restriccion_horario_fin = $categoria->restriccion_horario_fin;

            if ($restriccion_horario_inicio != "" && $restriccion_horario_fin != "") {
                if ($currentTime >= $restriccion_horario_inicio && $currentTime <= $restriccion_horario_fin) {
                    array_push($categoriasHorarioRestriccion,$categoria);
                }

            }else{
                array_push($categoriasHorarioRestriccion,$categoria);
            }
        }

        $categoriasFechaDisponibilidad = [];

        foreach ($categoriasHorarioRestriccion as $categoriaChida) {
            $disponibilidad_inicio = $categoriaChida->disponibilidad_inicio;
            $disponibilidad_fin = $categoriaChida->disponibilidad_fin;

            if ($disponibilidad_inicio != "" && $disponibilidad_fin != "") {
                if ($currentDate >= $disponibilidad_inicio && $currentDate <= $disponibilidad_fin) {
                    array_push($categoriasFechaDisponibilidad,$categoriaChida);
                }

            }else{
                array_push($categoriasFechaDisponibilidad,$categoriaChida);
            }
        }



        // Comprobamos si alguna categoría tiene un restricción por horario FIN




        if (sizeof($categoriasFechaDisponibilidad) < 1) {
            $response['status'] = http_response_code();
            $response['status_message'] = "No tenemos categorias de la marca que solicitas";
            return $response;
        }


        // Fin Validaciones

        foreach ($categoriasFechaDisponibilidad as $categoria) {
            $querySub = $this->getSubcategories($categoria->id_categoria, $id_sucursal, $id_region);
            $categoria->subcategorias = $querySub;
        }

        $response['status'] = http_response_code(200);
        $response['categorias'] = $categoriasFechaDisponibilidad;
        $response['sucursal'] = $sucursal[0];

        return $response;
    }


    public function getSubcategories ($id_categoria, $id_sucursal, $id_region){

        $currentTimeDate = Carbon::now();
        $currentTime = $currentTimeDate->toTimeString();
        $currentDate = $currentTimeDate->toDateString();

        $subcategorias = DB::table('new_subcategorie')
                        ->select("*")
                        ->whereRaw("FIND_IN_SET('".$id_categoria."',categorias) and  `restriccion_sucursal` not in ($id_sucursal) and `restriccion_region` not in ($id_region) and `activo` = TRUE")
                        ->get();

        $subcategoriasHorarioRestriccion = [];

        foreach ($subcategorias as $subcategoria) {
            $restriccion_horario_inicio = $subcategoria->restriccion_horario_inicio;
            $restriccion_horario_fin = $subcategoria->restriccion_horario_fin;

            if ($restriccion_horario_inicio != "" && $restriccion_horario_fin != "") {
                if ($currentTime >= $restriccion_horario_inicio && $currentTime <= $restriccion_horario_fin) {
                    array_push($subcategoriasHorarioRestriccion,$subcategoria);
                }

            }else{
                array_push($subcategoriasHorarioRestriccion,$subcategoria);
            }


        }

        $subcategoriasFechaDisponibilidad = [];

        foreach ($subcategoriasHorarioRestriccion as $subcategoriachida) {
            $disponibilidad_inicio = $subcategoriachida->disponibilidad_inicio;
            $disponibilidad_fin = $subcategoriachida->disponibilidad_fin;

            if ($disponibilidad_inicio != "" && $disponibilidad_fin != "") {
                if ($currentDate >= $disponibilidad_inicio && $currentDate <= $disponibilidad_fin) {
                    array_push($subcategoriasFechaDisponibilidad,$subcategoriachida);
                }

            }else{
                array_push($subcategoriasFechaDisponibilidad,$subcategoriachida);
            }
        }

        foreach ($subcategoriasFechaDisponibilidad as $subcategoria) {
            $queryArt = $this->getArticles($subcategoria->id_subcategoria, $id_sucursal, $id_region);
            $subcategoria->articulos = $queryArt;
        }

        return $subcategoriasFechaDisponibilidad;
    }

    public function getArticles ($id_subcategoria, $id_sucursal, $id_region){
        $currentTimeDate = Carbon::now();
        $currentTime = $currentTimeDate->toTimeString();
        $currentDate = $currentTimeDate->toDateString();

        $articles = DB::table('new_article')
                        ->select("*")
                        ->whereRaw("FIND_IN_SET('".$id_subcategoria."',subcategorias) and  `restriccion_sucursal` not in ($id_sucursal) and `restriccion_region` not in ($id_region) and `activo` = TRUE")
                        ->get();

                        $articlesHorarioRestriccion = [];

                        foreach ($articles as $article) {
                            $restriccion_horario_inicio = $article->restriccion_horario_inicio;
                            $restriccion_horario_fin = $article->restriccion_horario_fin;

                            if ($restriccion_horario_inicio != "" && $restriccion_horario_fin != "") {
                                if ($currentTime >= $restriccion_horario_inicio && $currentTime <= $restriccion_horario_fin) {
                                    array_push($articlesHorarioRestriccion,$article);
                                }

                            }else{
                                array_push($articlesHorarioRestriccion,$article);
                            }


                        }

                        $articlesFechaDisponibilidad = [];

                        foreach ($articlesHorarioRestriccion as $articleHorarioRestriccion) {
                            $disponibilidad_inicio = $articleHorarioRestriccion->disponibilidad_inicio;
                            $disponibilidad_fin = $articleHorarioRestriccion->disponibilidad_fin;

                            if ($disponibilidad_inicio != "" && $disponibilidad_fin != "") {
                                if ($currentDate >= $disponibilidad_inicio && $currentDate <= $disponibilidad_fin) {
                                    array_push($articlesFechaDisponibilidad,$articleHorarioRestriccion);
                                }

                            }else{
                                array_push($articlesFechaDisponibilidad,$articleHorarioRestriccion);
                            }
                        }



                        foreach ($articlesFechaDisponibilidad as $article) {
                            $queryExt = $this->getExtras($article->modificadores);
                            $article->opciones_personalizacion = $queryExt;
                        }

                        return $articlesFechaDisponibilidad;
    }

    public function getExtras($id_extra){
        $extras = DB::table('extra')
                    ->select("*")
                    ->whereRaw("FIND_IN_SET(id,'".$id_extra."') and `activo` = TRUE")
                    ->get();

                    return $extras;
    }
}
