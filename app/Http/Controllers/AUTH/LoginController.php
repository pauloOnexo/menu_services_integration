<?php

namespace App\Http\Controllers\AUTH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{

    function validar_datos_cliente(Request $request)
	{
		$arrayRespuesta = array();
		$clientUser = $request->client_user;
        $clientPass = $request->client_pass;


		$validateUser = $this->validar_cliente($clientUser,$clientPass);
		if(count($validateUser)>0){
			//GENERAR TOKEN
			$token = "";
			$token = bin2hex(random_bytes(64));
			$addTokenSession = $this->new_client_session($validateUser[0]->id,$token);
			if ($addTokenSession) {
                $arrayRespuesta["status"] = "OK";
			    $arrayRespuesta["status_msg"] = "Usuario correcto";
			    $arrayRespuesta["token"] = $token;
            }else{
                $arrayRespuesta["status"] = "BAD REQUEST";
			    $arrayRespuesta["status_msg"] = "Error al registrar token";
            }
			// $arrayRespuesta["datosUser"] = $validateUser[0]->id;
		}else{

                $arrayRespuesta["status"] = "BAD REQUEST";
                $arrayRespuesta["status_msg"] = "Usuario o contraseña incorrecta";
				// $arrayRespuesta["token"] = '';
                $response = json_encode($arrayRespuesta);
            }

		$response = json_encode($arrayRespuesta);
		return $response;
	}

    // Se validan las credenciales para aplicaciones externas
	public function validar_cliente($clientUser, $clientPass){
        // dd($request->all());

        $client_User = $clientUser;
        $client_Pass = sha1($clientPass);
		// $query = $this->db->get_where('client', array('client_user' => $clientUser,'client_pass'=> $clientPass));
        $query = DB::table('client')->where('client_user','=',$client_User)->where('client_pass','=',$client_Pass)->get();

		return $query;
	}

    // Inserción de nuevo token validado
	public function new_client_session($id_client,$token){

		// $this->db->insert('auth_client',array('client_id' => $id_client, 'token' => $token));
        $query = DB::table('auth_client')->insert(['client_id' => $id_client, 'token' => $token]);
        if ($query) {
            return true;
        }else{

            return false;
        }

	}

    public function checkToken(Request $request){
        $token = $request->bearerToken();

       $response =  $this->buscar_token_client($token);

       return $response;
    }
    // Busqueda de token existente
	public function buscar_token_client($token){
		// $query = $this->db->query("select token from auth_client where token = '$token' limit 1");
        $query = DB::table('auth_client')->where('token','=',$token)->get();

        if (count($query) > 0) {
            return true;
        }else{

            return false;
        }

	}


}
