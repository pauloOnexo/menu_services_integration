<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AUTH\LoginController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\MenuController;
use Illuminate\Http\Request;

class MenuApiController extends Controller
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
        $token = $request->bearerToken();

        $checkUser = (new LoginController)->buscar_token_client($token);

        if ($checkUser) {
            $marca = $request->id_marca;
            $sucursal = $request->id_sucursal;
            $response = (new MenuController)->menuFactory($marca, $sucursal);
            return $response;
        }else{
            $response = [];
            $response['status'] = 'BAD REQUEST';
            $response['status_msg'] = 'Token invalido';
            return json_encode($response);
        }

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
