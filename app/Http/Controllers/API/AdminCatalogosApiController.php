<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminCatalogosApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $id_sucursal = $request->id_sucursal;
        // $data = $request->data;
        $response = [];

        // Comienzo del proceso de inserción de información del menú digital

        // Se obtienen las categorias de la data recibida
        $categorias = $request->data['categorias'];

        // Se itera cada una de las categorias y se obtienen su propiedades, estas son insertadas como nuevas filas en la tablas, en caso de existir, se actualizan
        foreach ($categorias as $categoria) {

            try {
                $subcategorias = $categoria['subcategorias'];
                DB::table('new_category_injected')->upsert(
                    [
                        'id_categoria'=> $categoria['id_categoria'],
                        'nombre_categoria' => $categoria['nombre_categoria'],
                        'descripcion_categoria' => $categoria['descripcion_categoria'],
                        'imagen_categoria' => $categoria['imagen_categoria'],
                        'restriccion_sucursal' => $categoria['restriccion_sucursal'],
                        'restriccion_horario_inicio' => $categoria['restriccion_horario_inicio'],
                        'restriccion_horario_fin' => $categoria['restriccion_horario_fin'],
                        'disponibilidad_inicio' => $categoria['disponibilidad_inicio'],
                        'disponibilidad_fin' => $categoria['disponibilidad_fin'],
                    ],[
                        'id_categoria',
                        'nombre_categoria',
                        'descripcion_categoria',
                        'imagen_categoria',
                        'restriccion_sucursal',
                        'restriccion_horario_inicio',
                        'restriccion_horario_fin',
                        'disponibilidad_inicio',
                        'disponibilidad_fin'
                    ]);

                $insertSub = $this->mapSubcategories($subcategorias, $categoria['id_categoria']);
                    // dd($insertSub);
                if ($insertSub['status_code'] != 200) {
                    return $insertSub;
                }

            } catch (Exception $e) {
                $response['status'] = 'BAD REQUEST';
                $response['status_msg'] = 'Hay un error con la data enviada, verificar información';
                $response['error_code'] = $e->getCode();
                $response['error_description'] = $e->getMessage();
                return json_encode($response);
            }



            // if (!$query) {
            //     $response['status'] = 'BAD REQUEST';
            //     $response['status_msg'] = 'Ocurrió un error al insertar los registros';
            //     return json_encode($response);
            // }
        }





        //
        $response['status'] = 'OK';
        $response['status_msg'] = 'Se insertaron correctamente los registros';
        return json_encode($response);
    }

    public function mapSubcategories($subcategorias, $id_categoria){
        $response = [];
        // Se itera cada una de las categorias y se obtienen su propiedades, estas son insertadas como nuevas filas en la tablas, en caso de existir, se actualizan
        try {
            foreach ($subcategorias as $subcategoria) {

                $id_subcategoria = $subcategoria['id_subcategoria'];
                $oldCategoriesInSub = DB::table('new_subcategorie_injected')->where('id_subcategoria','=',$id_subcategoria)->pluck('categorias')->first();
                $oldCategoriesInSub = strval($oldCategoriesInSub);

                // dd($oldCategoriesInSub);


                if (str_contains($oldCategoriesInSub,strval($id_categoria))) {
                    // dd('Existe');
                }else{
                    $newCategoriesValue = [];
                    if ($oldCategoriesInSub != "") {
                        array_push($newCategoriesValue,$oldCategoriesInSub,$id_categoria);
                        // dd($newCategoriesValue);
                    }
                    array_push($newCategoriesValue,$id_categoria);
                    // dd($newCategoriesValue);
                    $categoriesString = implode(",",$newCategoriesValue);
                    DB::table('new_subcategorie_injected')->where('id_subcategoria','=',$id_subcategoria)->update(['categorias' => $categoriesString]);

                }

                $articles = $subcategoria['articulos'];
                // $categorias = implode(",",$id_categoria);
                DB::table('new_subcategorie_injected')->upsert(
                    [
                        'id_subcategoria'=> $id_subcategoria,
                        'nombre_subcategoria' => $subcategoria['nombre_subcategoria'],
                        // 'descripcion_subcategoria' => $subcategoria['descripcion_subcategoria'],
                        'restriccion_sucursal' => $subcategoria['restriccion_sucursal'],
                        'restriccion_horario_inicio' => $subcategoria['restriccion_horario_inicio'],
                        'restriccion_horario_fin' => $subcategoria['restriccion_horario_fin'],
                        'disponibilidad_inicio' => $subcategoria['disponibilidad_inicio'],
                        'disponibilidad_fin' => $subcategoria['disponibilidad_fin'],
                        // 'categorias' => $id_categoria
                    ],
                    [
                        'id_subcategoria',
                        'nombre_subcategoria',
                        'restriccion_sucursal',
                        'restriccion_horario_inicio',
                        'restriccion_horario_fin',
                        'disponibilidad_inicio',
                        'disponibilidad_fin',
                        // 'categorias'

                    ]);
                    $insertArt = $this->mapArticles($articles);
                    if ($insertArt['status_code'] != 200) {
                        return $insertArt;
                    }

        }
        } catch (Exception $e) {
            $response['status_code'] = 500;
            $response['status'] = 'BAD REQUEST';
                $response['status_msg'] = 'Hay un error con la data enviada en las subcategorias, verificar información';
                $response['error_code'] = $e->getCode();
                $response['error_description'] = $e->getMessage();
                return $response;
        }

        $response['status_code'] = 200;
        $response['status'] = 'OK';
        $response['status_msg'] = 'Subcategorias insertadas correctamente';
        return $response;
    }

    public function mapArticles($articles){
        // dd($articles);
        $response = [];
        // Se itera cada una de los articulos y se obtienen su propiedades, estas son insertadas como nuevas filas en la tablas, en caso de existir, se actualizan
        try {
            foreach ($articles as $article) {

                $modificadores = $article['modificadores'];
                if (sizeof($modificadores)) {
                    $insertMod = $this->mapModificators($modificadores);
                    if ($insertMod['status_code'] != 200) {
                        return $insertMod;
                    }

                    $ids_modificadores = $insertMod['ids_modificadores'];
                }else{
                    $ids_modificadores = "";

                }

                DB::table('new_articles_injected')->upsert(
                    [
                        'id_articulo'=> $article['id_articulo'],
                        'sku' => $article['sku'],
                        'nombre_articulo' => $article['nombre_articulo'],
                        'cantidad' => $article['cantidad'],
                        'unidad_medida' => $article['unidad_medida'],
                        'descripcion' => $article['descripcion'],
                        'extracto_logo' => $article['extracto_logo'],
                        'id_experiencia' => $article['id_experiencia'],
                        'imagen_articulo' => $article['imagen_articulo'],
                        'prioridad_menu' => $article['prioridad_menu'],
                        'precio' => $article['precio'],
                        'restriccion_sucursal' => $article['restriccion_sucursal'],
                        'restriccion_horario_inicio' => $article['restriccion_horario_inicio'],
                        'restriccion_horario_fin' => $article['restriccion_horario_fin'],
                        'disponibilidad_inicio' => $article['disponibilidad_inicio'],
                        'disponibilidad_fin' => $article['disponibilidad_fin'],
                        'modificadores' => $ids_modificadores,
                        'activo' => 1,
                    ],
                    [
                        'id_articulo',
                        'sku',
                        'nombre_articulo',
                        'cantidad',
                        'unidad_medida',
                        'descripcion',
                        'extracto_logo',
                        'id_experiencia',
                        'imagen_articulo',
                        'prioridad_menu',
                        'precio',
                        'restriccion_sucursal',
                        'restriccion_horario_inicio',
                        'restriccion_horario_fin',
                        'disponibilidad_inicio',
                        'disponibilidad_fin',
                        'activo'
                    ]
                );


        }
        } catch (Exception $e) {
            $response['status_code'] = 500;
            $response['status'] = 'BAD REQUEST';
                $response['status_msg'] = 'Hay un error con la data enviada en los articulos, verificar información';
                $response['error_code'] = $e->getCode();
                $response['error_description'] = $e->getMessage();
                return $response;
        }

        $response['status_code'] = 200;
        $response['status'] = 'OK';
        $response['status_msg'] = 'Articulos insertadas correctamente';
        return $response;
    }

    public function mapModificators($modificadores){

        $arrayIds = [];
        try {
            foreach ($modificadores as $modificador) {
            array_push($arrayIds,$modificador['id_modificador']);

            $opciones_modificador = $modificador['opciones'];

            if (sizeof($opciones_modificador)) {
                $insertOpt = $this->mapModificatorsOptions($opciones_modificador);
                if ($insertOpt['status_code'] != 200) {
                    return $insertOpt;
                }

                $ids_opciones_modificador = $insertOpt['ids_opciones_modificador'];
            }else{
                $ids_opciones_modificador = "";

            }
                DB::table('modificadores')->upsert([
                    'id_modificador' => $modificador['id_modificador'],
                    'nombre_modificador' => $modificador['nombre_modificador'],
                    'prioridad' => $modificador['prioridad'],
                    'opciones' => $ids_opciones_modificador
                ],[
                    'id_modificador',
                    'nombre_modificador',
                    'prioridad',
                    'opciones'
                ]);


            }
        } catch (Exception $e) {
            $response['status_code'] = 500;
            $response['status'] = 'BAD REQUEST';
                $response['status_msg'] = 'Hay un error con la data enviada en los modificadores, verificar información';
                $response['error_code'] = $e->getCode();
                $response['error_description'] = $e->getMessage();
                return $response;
        }

        // dd();
        $response['ids_modificadores'] = implode(',',$arrayIds);
        $response['status_code'] = 200;
        $response['status'] = 'OK';
        $response['status_msg'] = 'Modificadores insertadas correctamente';
        return $response;

    }

    public function mapModificatorsOptions($opciones_modificador){

        $arrayIds = [];
        try {
            foreach ($opciones_modificador as $opcion_modificador) {
            array_push($arrayIds,$opcion_modificador['id_opcion_modificador']);

                DB::table('opciones_modificador')->upsert([
                    'id_opcion_modificador' => $opcion_modificador['id_opcion_modificador'],
                    'nombre_opcion_modificador' => $opcion_modificador['nombre_opcion_modificador'],
                    'modificador_trigger' => $opcion_modificador['modificador_trigger'],
                    'precio_extra' => $opcion_modificador['precio_extra'],
                    'cantidad_personalizacion' => $opcion_modificador['cantidad_personalizacion'],
                    'unidad_personalizacion' => $opcion_modificador['unidad_personalizacion'],
                ],[
                    'id_opcion_modificador',
                    'nombre_opcion_modificador',
                    'modificador_trigger',
                    'precio_extra',
                    'cantidad_personalizacion',
                    'unidad_personalizacion',
                ]);
            }
        } catch (Exception $e) {
            $response['status_code'] = 500;
            $response['status'] = 'BAD REQUEST';
                $response['status_msg'] = 'Hay un error con la data enviada en los opciones de modificador, verificar información';
                $response['error_code'] = $e->getCode();
                $response['error_description'] = $e->getMessage();
                return $response;
        }

        $response['ids_opciones_modificador'] = implode(',',$arrayIds);
        $response['status_code'] = 200;
        $response['status'] = 'OK';
        $response['status_msg'] = 'Opciones de modificadores insertadas correctamente';
        return $response;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
