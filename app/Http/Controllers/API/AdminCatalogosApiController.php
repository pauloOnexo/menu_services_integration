<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
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
        $categorias = $request->data['categorias'];

        foreach ($categorias as $categoria) {
            try {
                $query = DB::table('new_category_injected')->updateOrInsert(
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
                    ]);
            } catch (Exception $e) {
                $response['status'] = 'BAD REQUEST';
                $response['status_msg'] = 'Hay un error con la data enviada, verificar información';
                $response['error_code'] = $e->getCode();
                $response['error_description'] = $e->getMessage();
                return json_encode($response);
            }

            if (!$query) {
                $response['status'] = 'BAD REQUEST';
                $response['status_msg'] = 'Ocurrió un error al insertar los registros';
                return json_encode($response);
            }
        }

        $response['status'] = 'OK';
        $response['status_msg'] = 'Se insertaron correctamente los registros';
        return json_encode($response);
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
