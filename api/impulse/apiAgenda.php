<?php
require_once('API.class.php');
require_once('defines.php');
require_once('db.class.php'); // ignored by git
session_start();

ini_set( 'date.timezone', 'America/New_York');
/**
* Class with all methods to gather info from the DB
*/
class apiAgenda extends API{
	
	private
		$result,
		$_date,
		$centro,
		$conn,
		
		$user,
		$consts,
		
		$OhOhMsg = 'Oh, oh. Algo salio mal, por favor intenta de nuevo';
	
	/*
	* Public class constructor.
	* Calls parent constructor which 
	* contains all API base methods
	*/
	public function __construct($request, $origin) {
		parent::__construct($request);
		$this->validateLoggedUser();
		$this->_date = new DateTime('today');
		
		$this->consts = new APIConstants();
		
		$this->conn = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);
		
		if (!$this->conn) {
			echo "Error: No se pudo conectar a MySQL." . PHP_EOL;
			echo "errno de depuración: " . mysqli_connect_errno() . PHP_EOL;
			echo "error de depuración: " . mysqli_connect_error() . PHP_EOL;
			die();
		}
		$this->conn->set_charset("utf8");
		$this->result = array();
	}
	
	private function validateLoggedUser(){
		$this->user = array(
			'user_id'		=> '318',
			'client_id'		=> '132',
			'type'			=> '3',
			'nickname'		=> 'prueba 1',
			'username'		=> 'prueba 1',
			'email'			=> '3@makrosoft.com'
		);
	}
	
	private function restartSQL(){
		if($this->conn){
			$this->conn->close();
		}
		$this->conn = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);
	}
	
	/*
	* Executes a Query and returns an Assoc Array with the query result.
	*/
	private function runQueryToArray($sql){
		$result = array();
		$res = $this->conn->query($sql);
		
		if(!$res){
			return array(
				'Error' => $this->conn->error
			);
		}
			
		while($_res = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
			$result[] = $_res;
		}
		$res->free();
		return $result;
	}
	
	private function returnMessage($success = false, $message = '', $error = '', $linea = 0){
		return array(
				'success' 	=> ($success) ? 'true' : 'false',
				'message' 	=> $message,
				'error'		=> $error,
				'line'		=> $linea
			);
	}
	
	/* Save a log */
	private function saveToLog($args, $req, $line){
		return true;
	}
	
	
	/*
	* /getCabinas/CABINA_ID
	*/
	protected function getCabinas($args, $req){
		if(!isset($args[0]) || !is_numeric($args[0])){
			$this->saveToLog($args, $req, __LINE__);
			return $this->returnMessage(false, $this->OhOhMsg, 'Invalid input.', __LINE__);
		}
		
		$query = "SELECT 
			CENTRO_CABINA_DETALLE_ID as ID, 
			NOMBRE_CABINA as Nombre 
			FROM c_centros_cabinas_detalles 
			WHERE CENTRO_ID =". $args[0];
		return $this->runQueryToArray($query);
	}
	
	/*
	* 
	* @URI: /getDetalleCliente/ClienteID
	*/
	protected function getDetalleCliente($args, $req){
		if(!is_numeric($args[0]))
			return array(
				'Error' => 'Invalid input'
			);
		$id = $args[0];
		$sql = "SELECT * FROM c_clientes WHERE CLIENTE_ID = ".$id;
		return $this->runQueryToArray($sql);
	}

	/* If a user can schedule a service by service type */
	protected function userCanByType($args, $req){
		if(!isset($req['type']) || empty($req['type'])){
			$this->saveToLog($args, $req, __LINE__);
			return $this->returnMessage(false, $this->OhOhMsg, 'Please provide a service type id', __LINE__);
		}
		
		if(!isset($req['user']) || empty($req['user'])){
			$this->saveToLog($args, $req, __LINE__);
			return $this->returnMessage(false, $this->OhOhMsg, 'Please provide a client_id', __LINE__);
		}
		
		$client_id = (is_numeric($req['user'])) ? $req['user'] : 0;
		
		if($client_id == 0){
			$this->saveToLog($args, $req, __LINE__);
			return $this->returnMessage(false, $this->OhOhMsg, 'Invalid client_id', __LINE__);
		}
		
		$sids = '';
		$stype = $this->consts->getServiveType($req['type']);
		if($stype == '20'){
			$sids = ' (20)';
		}else if($stype == '21'){
			$sids = ' (20, 21)';
		}else if ($stype == '19'){
			$sids = ' (19, 20, 21)';			
		}
		
		if($sids == ''){
			$this->saveToLog($args, $req, __LINE__);
			return $this->returnMessage(false, $this->OhOhMsg, 'Please provide a service type id', __LINE__);
		}
		
		$query = "SELECT a.VENTA_ID, a.FECHA FROM `vta_p_ventas` AS a 
				LEFT JOIN `vta_p_ventas_detalle` as b ON a.VENTA_ID = b.VENTA_ID 
				WHERE a.CLIENTE_ID = '".$client_id."' AND b.SECCION_ID IN ". $sids . "
				AND b.CANTIDAD > IFNULL(b.CANTIDAD_AGENDADOS, 0)";
		$res = $this->runQueryToArray($query);
		if(count($res) > 0){
			return array(
				'success' 	=> 'true',
				'message' 	=> '',
				'error'		=> '',
				'line'		=> '',
				'obj' 		=> $res
			);
		}else{
			$message = "Lo sentimos, no cuenta con este tipo de servicio disponible.";
			return $this->returnMessage(false, $message, 'User has no service available', __LINE__);
		}
	}
	
	/*
	*
	*/
	protected function validateCode($args, $req){
		if(!isset($req['code']) || empty($req['code']) || !is_numeric($req['code'])){
			$this->saveToLog($args, $req, __LINE__);
			return $this->returnMessage(false, $this->OhOhMsg, 'Invalid input', __LINE__);
		}
		
		if(!isset($req['id']) || empty($req['id']) || !is_numeric($req['id'])){
			$this->saveToLog($args, $req, __LINE__);
			return $this->returnMessage(false, $this->OhOhMsg, 'Invalid input', __LINE__);
		}
		
		$query = "SELECT id, DESCRIPCION_CITA as code, start, end 
			FROM agn_p_citas
			WHERE STATUS_CITA = '1'
			AND id = '". $req['id'] ."'
			AND DESCRIPCION_CITA = '" . $req['code'] . "'";
		
		//echo $query;	
		$res = $this->runQueryToArray($query);
		if(count($res) == 0){
			$this->saveToLog($args, $req, __LINE__);
			return $this->returnMessage(false, $this->OhOhMsg, 'Could not find code', __LINE__);
		}else{
			return $this->addToSemiSesion($args, $res[0]);
		}
	}
	
	/*
	*
	*/
	private function usersOnSession($id){
		$query = "SELECT id, NOMBRE, APELLIDO_PATERNO, APELLIDO_MATERNO FROM agn_p_citas WHERE
			STATUS_CITA = 1
			AND cbn = (SELECT cbn FROM agn_p_citas WHERE id = '".$id."')
			AND start = (SELECT start FROM agn_p_citas WHERE id = '".$id."')";
		return array(
			'success' 	=> 'true',
			'obj' => $this->runQueryToArray($query)
		);
	}
	
	/*
	* 
	*/
	protected function getGroupSessions($args, $req){
		if(!is_numeric($req['day'])){
			return $this->returnMessage(false, $this->OhOhMsg, 'Invalid input', __LINE__);
		}
		$day 	= $req['day'];
		$date 	= $req['date'];
		$cbn 	= $req['cbn'];
		
		$query = "SELECT id, start, end, title, limite
			FROM c_cabina_itinerario WHERE day = '". $day . "' 
			AND active = 1";
			
		
		$res = $this->runQueryToArray($query);
		foreach($res as &$r){
			$query2 = "SELECT COUNT(*) as total   
				FROM agn_p_citas WHERE
				cbn = '".$cbn."' 
				AND start = '". $date ." ". $r['start'] . "'";
			$res2 = $this->runQueryToArray($query2);
			if(count($res2)){
				$r['available'] = intval($r['limite']) - intval($res2[0]['total']);
				$r['taken'] = $res2[0]['total'];
			}
		}
		return array(
			'success' 	=> 'true',
			'obj'		=> $res
		);
	}
	
	/*
	*
	*/
	protected function userIsOnGroup($args, $req){
		if(!isset($req['cbn']) || !is_numeric($req['cbn'])){
			$this->saveToLog($args, $req, __LINE__);
			return $this->returnMessage(false, $this->OhOhMsg, 'Invalid input.', __LINE__);
		}
		
		$usuarioId 		= $this->user['user_id']; //1 //(isset($_SESSION['TE_user_id']) && !empty($_SESSION['TE_user_id'])) ? $_SESSION['TE_user_id'] : 0;
		$client_id 		= $this->user['client_id'];
		$start = mysqli_real_escape_string($this->conn, $req['start']);
		
		$query = "SELECT id FROM agn_p_citas WHERE 
			STATUS_CITA = 1 
			AND USUARIO_ID = '".$client_id."' 
			AND cbn = '".$req['cbn']."' 
			AND start = '".$start."'";
		
		$validated = $this->runQueryToArray($query);
		if(count($validated)){
			return $this->returnMessage(true, 'Ya estas anotado en esta sesión', '', __LINE__);
		}else{
			return $this->returnMessage(false, '', 'user is not on session', __LINE__);
		}
	}
	
	
	/*
	*
	*/
	protected function userIsOnSession($args, $req){
		if(!isset($args[0]) || !is_numeric($args[0])){
			$this->saveToLog($args, $req, __LINE__);
			return $this->returnMessage(false, $this->OhOhMsg, 'Invalid input.', __LINE__);
		}
		
		$id = $args[0];
		
		$usuarioId 		= $this->user['user_id']; //1 //(isset($_SESSION['TE_user_id']) && !empty($_SESSION['TE_user_id'])) ? $_SESSION['TE_user_id'] : 0;
		$client_id 		= $this->user['client_id'];
		
		$query = "SELECT id FROM agn_p_citas WHERE
			STATUS_CITA = 1
			AND USUARIO_ID = '".$usuarioId."' 
			AND cbn = (SELECT cbn FROM agn_p_citas WHERE id = '".$id."')
			AND start = (SELECT start FROM agn_p_citas WHERE id = '".$id."')";
		$validated = $this->runQueryToArray($query);
		if(count($validated)){
			return $this->usersOnSession($id);
		}else{
			return $this->returnMessage(false, '', 'user is not on session', __LINE__);
		}
	}
	
	
	/*
	*
	*/
	private function addToSemiSesion($args, $req){

		$userCan = $this->userCanByType($args, array('type' => '26', 'user' => $this->user['client_id']));
		if($userCan['success'] != 'true'){
			return $this->returnMessage(false, 'Servicio no disponible', 'User has no service available', __LINE__);
		}
		
		$usuarioId 		= $this->user['user_id']; //1 //(isset($_SESSION['TE_user_id']) && !empty($_SESSION['TE_user_id'])) ? $_SESSION['TE_user_id'] : 0;
		$client_id 		= $this->user['client_id'];
		
		$cliente = $this->getDetalleCliente(array($client_id), array());
		
		$statusCita 	= 1;
		$title 			= $this->getTitleBytype('26');
		$nombre 		= $cliente[0]['NOMBRE'];
		$ApPaterno		= $cliente[0]['APELLIDO_PATERNO'];
		$ApMaterno		= $cliente[0]['APELLIDO_MATERNO'];
		$dniNie			= $cliente[0]['DNI_NIE'];
		$eMail			= $cliente[0]['EMAIL'];
		$telContacto	= $cliente[0]['TELEFONO_1'];
		
		$start 			= $req['start'];
		$end 			= $req['end'];
		$duracionCita	= gmdate("H:i:s", 30*60);
		
		$fechaVenta 	= $userCan['obj'][0]['FECHA'];
		$vtaID 			= $userCan['obj'][0]['VENTA_ID'];
		
		// TODO: Get this dinamically
		$cabina 		= '26';
		$seccionID		= $this->consts->getServiveType('26');
		$descCita		= 'Added as guest';
		
		// if($seccionID == '21'){
			// $descCita = $this->getSemiCode();
		// }
		
		$dtA = new DateTime($start);
		$dtB = new DateTime();

		if ( $dtA < $dtB ) {
			return array(
				'success' => false,
				'message' => 'No se pueden agendar citas en fecha/hora pasadas.'
			);
		}
		
		
		$sql = "INSERT INTO agn_p_citas
				(title,USUARIO_ID,start,end,DURACION_CITA,STATUS_CITA,FECHA_VENTA,CENTRO_ID,CLIENTE_ID,NOMBRE,
				APELLIDO_PATERNO,APELLIDO_MATERNO,DNI_NIE,EMAIL,TEL_CONTACTO,CENTRO_CABINA_DETALLE_ID,cbn,DESCRIPCION_CITA,ID_EMPLEADO,
				NOMBRE_EMPLEADO) 
				VALUES('".$title."', '".$usuarioId."', '".$start."', '".$end."', '".$duracionCita."', '".$statusCita."', '".$fechaVenta."', '1', '".$client_id."', '".$nombre."', '".$ApPaterno."', '".$ApMaterno."', '".$dniNie."', '".$eMail."', '".$telContacto."', '".$cabina."', '".$cabina."', '".$descCita."', '', '')";
				
		$this->res = $this->conn->query($sql);
		if($this->res){
			$citaID = $this->conn->insert_id;
			$this->restartSQL();
		}else{
			return array(
				'success' => false,
				'message' => 'Ocurrio un error al guardar la cita, por favor intente de nuevo.'
			);
		}		
		
				
		$sql2 = "INSERT INTO agn_p_citas_detalles (id, VENTA_ID, SECCION_ID, ARTICULO_ID, CABINA_ID, CITA_ESTATUS_ID, DURACION_SERVICIO,
		OBSERVACIONES) VALUES('".$citaID."', '".$vtaID."', '".$seccionID."', '', '".$cabina."', '".$statusCita."', '".$duracionCita."', '')";

		$this->res = $this->conn->query($sql2);
		if($this->res){
			$sql3 = "UPDATE vta_p_ventas_detalle SET CANTIDAD = CANTIDAD - 1 WHERE VENTA_ID = ". $vtaID;
			$this->conn->query($sql3);
			return array(
				'success' 	=> true,
				'type'	  	=> $seccionID,
				'code'		=> $descCita
			);
		}else{
			return array(
				'success' => false,
				'message' => 'Ocurrio un error al guardar la cita, por favor intente de nuevo.',
				'error' =>  $this->conn->error
			);
		}
	}
	
	
	/*
	* @URI: /getDetalleDeVenta/VENTA_DETALLE_ID
	*/
	protected function getDetalleDeVenta($args, $req){
		if(!is_numeric($args[0]))
			return array(
				'Error' => 'Invalid input'
			);
		$vtaID = $args[0];
		$sql = "SELECT a.VENTA_ID,a.FECHA,a.NUMERO_VENTA,a.CENTRO_ID,a.CLIENTE_ID,a.TOTAL,a.OBSERVACIONES, a.ADEUDO,
				b.SECCION_ID,b.FAMILIA_ID,b.SUB_FAMILIA_ID,b.CODIGO_ID,b.CANTIDAD,b.IMPORTE, b.DESCRIPCION, b.VENTA_DETALLE_ID 
				FROM vta_p_ventas AS a 
				INNER JOIN vta_p_ventas_detalle AS b ON a.VENTA_ID = b.VENTA_ID 
				WHERE b.VENTA_DETALLE_ID = ".$vtaID;
		return $this->runQueryToArray($sql);
	}
	
	/*
	* Get details from a given 'Cabina' ID, including appointments
	*/
	protected function getCabinaDetails($args, $req){
		if(!isset($args[0]) || !is_numeric($args[0])){
			$this->saveToLog($args, $req, __LINE__);
			return $this->returnMessage(false, $this->OhOhMsg, 'Invalid input.', __LINE__);
		}
		$year 	= (isset($args[1]) && is_numeric($args[1])) ? $args[1] : '2010';
		$month 	= (isset($args[2]) && is_numeric($args[2])) ? $args[2] : '01';
		$day 	= (isset($args[3]) && is_numeric($args[3])) ? $args[3] : '01';
		
		$start = $year . '-' . $month . '-' . $day . ' 00:00:00'; 
		$end = $year . '-' . $month . '-' . $day . ' 23:59:59'; 
		
		$cabina_id = $args[0];
		$query = "SELECT 
			CENTRO_CABINA_DETALLE_ID as ID, 
			NOMBRE_CABINA as Nombre, 
			APERTURA as Apertura, 
			CIERRE as Cierre 
			FROM c_centros_cabinas_detalles
			WHERE CENTRO_CABINA_DETALLE_ID = ". $cabina_id;
			
		$query2 = "SELECT 
			COUNT(*) as total, id, title, USUARIO_ID as user_id, start, end, DURACION_CITA, CLIENTE_ID as client_id
			FROM agn_p_citas
			WHERE CENTRO_CABINA_DETALLE_ID = ". $cabina_id ." 
			AND start > '". $start ."' AND end < '". $end ."'
			AND STATUS_CITA != 3
			GROUP BY start";
		return array(
			'Details' => $this->runQueryToArray($query),
			'Appnmts' => $this->runQueryToArray($query2)
		);
	}
	
	/*
	*
	*/
	private function getTitleBytype($type){
		if($type == '24'){
			return 'Sesion Privada';
		}
		
		if($type == '26'){
			return 'Sesion Grupal privada';
		}
		
		return '';
	}
	
	/*
	*
	*/
	private function getSemiCode(){
		return rand(1234, 9999);
	}
	
	/*
	*
	*/
	private function getParticipantsCount($args, $req){
		
	}
	
	/*
	* 
	*/
	protected function addSesion($args, $req){
		if(!isset($req['type']) || empty($req['type'])){
			$this->saveToLog($args, $req, __LINE__);
			return $this->returnMessage(false, $this->OhOhMsg, 'Please provide a service type id', __LINE__);
		}
		
		$userCan = $this->userCanByType($args, $req);
		if($userCan['success'] != 'true'){
			return $this->returnMessage(false, 'Servicio no disponible', 'User has no service available', __LINE__);
		}
		
		//$vta = $req['VENTA_DETALLE_ID'];
		$usuarioId 		= (is_numeric($req['user'])) ? $req['user'] : 0; //1 //(isset($_SESSION['TE_user_id']) && !empty($_SESSION['TE_user_id'])) ? $_SESSION['TE_user_id'] : 0;
		$client_id 		= (is_numeric($req['user'])) ? $req['user'] : 0;
		
		$cliente = $this->getDetalleCliente(array($client_id), array());
		//$detalle = $this->getDetalleDeVenta(array($vta), array());
		
		$statusCita 	= 1;
		$title 			= $this->getTitleBytype($req['type']);
		$nombre 		= $cliente[0]['NOMBRE'];
		$ApPaterno		= $cliente[0]['APELLIDO_PATERNO'];
		$ApMaterno		= $cliente[0]['APELLIDO_MATERNO'];
		$dniNie			= $cliente[0]['DNI_NIE'];
		$eMail			= $cliente[0]['EMAIL'];
		$telContacto	= $cliente[0]['TELEFONO_1'];
		
		$start 			= (isset($req['start']) && !empty($req['start'])) ? $req['start'] : false;
		$end 			= (isset($req['end']) && !empty($req['end'])) ? $req['end'] : false;		
		$duracionCita	= gmdate("H:i:s", 30*60);
		
		$fechaVenta 	= $userCan['obj'][0]['FECHA'];
		$vtaID 			= $userCan['obj'][0]['VENTA_ID'];
		
		// TODO: Get this dinamically
		$cabina 		= $req['type'];
		$seccionID		= $this->consts->getServiveType($req['type']);
		$descCita		= '';
		
		if($seccionID == '21'){
			$descCita = $this->getSemiCode();
		}
		
		$dtA = new DateTime($start);
		$dtB = new DateTime();

		if ( $dtA < $dtB ) {
			return array(
				'success' => false,
				'message' => 'No se pueden agendar citas en fecha/hora pasadas.'
			);
		}
		
		
		$sql = "INSERT INTO agn_p_citas
				(title,USUARIO_ID,start,end,DURACION_CITA,STATUS_CITA,FECHA_VENTA,CENTRO_ID,CLIENTE_ID,NOMBRE,
				APELLIDO_PATERNO,APELLIDO_MATERNO,DNI_NIE,EMAIL,TEL_CONTACTO,CENTRO_CABINA_DETALLE_ID,cbn,DESCRIPCION_CITA,ID_EMPLEADO,
				NOMBRE_EMPLEADO) 
				VALUES('".$title."', '".$usuarioId."', '".$start."', '".$end."', '".$duracionCita."', '".$statusCita."', '".$fechaVenta."', '1', '".$client_id."', '".$nombre."', '".$ApPaterno."', '".$ApMaterno."', '".$dniNie."', '".$eMail."', '".$telContacto."', '".$cabina."', '".$cabina."', '".$descCita."', '', '')";
				
		$this->res = $this->conn->query($sql);
		if($this->res){
			$citaID = $this->conn->insert_id;
			$this->restartSQL();
		}else{
			return array(
				'success' => false,
				'message' => 'Ocurrio un error al guardar la cita, por favor intente de nuevo.'
			);
		}		
		
				
		$sql2 = "INSERT INTO agn_p_citas_detalles (id, VENTA_ID, SECCION_ID, ARTICULO_ID, CABINA_ID, CITA_ESTATUS_ID, DURACION_SERVICIO,
		OBSERVACIONES) VALUES('".$citaID."', '".$vtaID."', '".$seccionID."', '', '".$cabina."', '".$statusCita."', '".$duracionCita."', '')";

		$this->res = $this->conn->query($sql2);
		if($this->res){
			$sql3 = "UPDATE vta_p_ventas_detalle SET CANTIDAD = CANTIDAD - 1 WHERE VENTA_ID = ". $vtaID;
			$this->conn->query($sql3);
			return array(
				'success' 	=> true,
				'type'	  	=> $seccionID,
				'code'		=> $descCita
			);
		}else{
			return array(
				'success' => false,
				'message' => 'Ocurrio un error al guardar la cita, por favor intente de nuevo.',
				'error' =>  $this->conn->error
			);
		}
	}
}


if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
    $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
}

try {
    $res = new apiAgenda($_REQUEST['request'], $_SERVER['HTTP_ORIGIN']);
    echo $res->processAPI();
} catch (Exception $e) {
    echo json_encode(Array('error' => $e->getMessage()));
}