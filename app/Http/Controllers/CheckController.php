<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Place;
use App\Models\Check;
use App\Models\History;
use App\Models\Employee;
use DateTime;

class CheckController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    //

	function postCheck(Request $Request, $place){
				
	#validamos los tokens recibidos.
	#A travez de la url recibimos la empresa.
	#Por medio de post el token del usuario.	
	
#Inicializamos

	$token = $Request->get('token');
	$idCompany = 0;
	
#Ubicamos el lugar
	
	#Verificamos que sea correcta la lectura.
	$place = Place::whereToken($place)->first();

#Verificamos	
	if(empty($place))
	{
		//En caso de que no exista coincidencia todo muere.
		$idCompany = 0;
		
	}else{
		
#Verificamos la distancia		

		$latitude = $place->latitude;
		$longitude = $place->longitude;
		$distancia = $this->compararDistancia($latitude, $longitude, $Request->get('latitud'), $Request->get('longitud'));
		
		#Si la distancia es menor a 200 Metros
		if($distancia > 200)
			return response()
            ->json(['status' => 'error', 'message' => 'No se encuentra en la ubicación establecida']);
		else
			$idCompany = $place->company_id;
	}
	
#ubicamos al empleado
	$employee = Employee::whereCompanyId($idCompany)->whereToken($token)->first();
	
#Retornamos error en caso de una dispariedad.
	if(empty($employee))
		
		return response()
            ->json(['status' => 'error', 'message' => 'No se pudo registrar el chequeo']);
	
#Verificamos que no haya chequeado en los ultimos 5 minutos.
	$ultimoCheck = Check::whereEmployeeId($employee->id)
				->wherePlaceId($place->id)
				->whereCompanyId($idCompany)
				->orderBy('id', 'desc')
				->first();
				
#Verificamos la diferencia de tiempo.	
if(!empty($ultimoCheck))
{
	$ultimoCheckDate = $ultimoCheck->created_at;
	$now = new DateTime();
	$diff = $ultimoCheckDate->diff($now);
	$min = $diff->i;
	if($min<5)
	{
		return response()
            ->json(['status' => 'error', 'message' => 'Ya se ha chequeado, debe esperar 5 minutos.']);
	}
	
}
#Si todo va Ok registramos el chequeo.
	$check = new Check();	
	$check->employee_id = $employee->id;
	$check->place_id = $place->id;
	$check->company_id = $idCompany;
	$check->save();

#Registrado el chequeo dejamos el registro en la tabla de historial.
	$history = History::whereEmployeeId($employee->id)
				->whereYear('day', date('Y'))
				->whereMonth('day',date('m'))
				->whereDay('day',date('d'))
				->first();
	
#Si es el primer chequeo del dia se registra.			
	if(empty($history))
		$history = new History();	
	
	$history->employee_id = $employee->id;
	$history->place_id = 1;
	$history->company_id = $idCompany;
	$history->day = date('Y-m-d');
	$checks = array();
	
	#En caso de que existan chequeos anteriores.
	if(!empty($history->checks))
		$checks = $history->checks;
	
	array_push($checks, date('H:i'));
	$history->checks = $checks;
	$history->save();	
	
	
#Respuesta.
	return response()
            ->json(['status' => 'ok', 'message' => 'Checkeo registrado con exito.']);
	}
	
 
#Funciones privadas del controlador.
	
	private function compararDistancia($latitude1, $longitude1, $latitude2, $longitude2) {
		
		if (($latitude1 == $latitude2) && ($longitude1 == $longitude2))
				return 0; // misma distancia.
			
		$p1 = deg2rad($latitude1);
		$p2 = deg2rad($latitude2);
		$dp = deg2rad($latitude2 - $latitude1);
		$dl = deg2rad($longitude2 - $longitude1);
		$a = (sin($dp/2) * sin($dp/2)) + (cos($p1) * cos($p2) * sin($dl/2) * sin($dl/2));
		$c = 2 * atan2(sqrt($a),sqrt(1-$a));
		$r = 6371008; // Radio de la tierr
		$d = $r * $c;
		
		return $d; // Distancia en metros.
}
}
