<?php
class UsuarioController extends BaseController {

    /*  ***************************************************************************************************************************************************  */
    /*  *************************************************** VISUALIZAR Y CREAR ****************************************************************************  */
    /*  ***************************************************************************************************************************************************  */

    public function mostrarUsuarios()
    {
        Session::forget('FiltNombre');
        Session::forget('FiltDNI');
        /*  Busca por nombre y apellido. No necesita ser idéntico. */
        if((Input::has('filtro')) && (input::has('valor'))){
            if (Input::get('filtro') == 'nombre') {
                $usuario = Usuario::where('email', '<>', 'admin@gmail.com')->where(function($query)
                                                                                {
                                                                                    $nombre = Input::get('valor');
                                                                                    $completo = DB::raw('CONCAT(nombre, " ", apellido)');
                                                                                    $query->where('apellido', 'LIKE', '%' . $nombre . '%')
                                                                                          ->orWhere('nombre', 'LIKE', '%' . $nombre . '%')
                                                                                          ->orWhere ($completo, 'LIKE', '%' . $nombre . '%');
                                                                                })
                                                                                ->get();
                $filtnombre = Input::get('valor');
                Session::put('FiltNombre','Está filtrando por los clientes que coincidan con "'.$filtnombre.'".');
            }
            else if (Input::get('filtro') == 'dni') {
                $usuario = Usuario::where('email', '<>', 'admin@gmail.com')->where(function($query)
                                                                                {
                                                                                    $dni = Input::get('valor');
                                                                                    $query->where('dni', '=', $dni);
                                                                                })
                                                                                ->get();
                $filtdni = Input::get('valor');
                Session::put('FiltDNI','Está filtrando por el DNI que coincida con "'.$filtdni.'".');
            }
            else {
                /* Input está vacío. Muestra todos excepto el Admin. */
                $usuario = Usuario::where('email', '<>', 'admin@gmail.com')->get();
            }
        }
        else {
            $usuario = Usuario::where('email', '<>', 'admin@gmail.com')->get();
        }
        return View::make('usuario.lista', array('usuarios' => $usuario));
    }

    public function mostrarUsuariosVigentes()
    {
        Session::forget('FiltNombre');
        Session::forget('FiltDNI');
        /*  Busca por nombre y apellido en vigentes. No necesita ser idéntico. */
        if((Input::has('filtro')) && (input::has('valor'))){
            if (Input::get('filtro') == 'nombre') {
                $usuario = Usuario::where('email', '<>', 'admin@gmail.com')->where('dadoDeBaja', '=', '0')
                                                                           ->where('bloqueado', '=', '0')
                                                                           ->where(function($query)
                                                                                {
                                                                                    $nombre = Input::get('valor');
                                                                                    $completo = DB::raw('CONCAT(nombre, " ", apellido)');
                                                                                    $query->where('apellido', 'LIKE', '%' . $nombre . '%')
                                                                                          ->orWhere('nombre', 'LIKE', '%' . $nombre . '%')
                                                                                          ->orWhere ($completo, 'LIKE', '%' . $nombre . '%');
                                                                                })
                                                                           ->get();
                $filtnombre = Input::get('valor');
                Session::put('FiltNombre','Está filtrando por los clientes que coincidan con "'.$filtnombre.'".');
            }
            else if (Input::get('filtro') == 'dni') {
                $usuario = Usuario::where('email', '<>', 'admin@gmail.com')->where('dadoDeBaja', '=', '0')
                                                                           ->where('bloqueado', '=', '0')
                                                                           ->where(function($query)
                                                                                {
                                                                                    $dni = Input::get('valor');
                                                                                    $query->where('dni', '=', $dni);
                                                                                })
                                                                                ->get();
                $filtdni = Input::get('valor');
                Session::put('FiltDNI','Está filtrando por el DNI que coincida con "'.$filtdni.'".');
            }
            else {
                /* Input está vacío. Muestra todos los vigentes excepto el Admin. */
                $usuario = Usuario::where('email', '<>', 'admin@gmail.com')->where(function($query)
                                                                                        {
                                                                                            $query->where('dadoDeBaja', '=', '0')
                                                                                                  ->where('bloqueado', '=', '0');
                                                                                        })
                                                                                        ->get();
                }
            }
            else {
                $usuario = Usuario::where('email', '<>', 'admin@gmail.com')->where(function($query)
                                                                                        {
                                                                                            $query->where('dadoDeBaja', '=', '0')
                                                                                                  ->where('bloqueado', '=', '0');
                                                                                        })
                                                                                        ->get();
            }
        return View::make('usuario.listaVigentes', array('usuarios' => $usuario));
    }

    
    public function nuevoUsuario()
    {
        if (! Auth::check()){
          $provincias= Provincia::all();
          return View::make('registrarse',['provincias'=>$provincias]);
      }
      else {
          return Redirect::to('/');
      }
    }

    public function registrarUsuario()
    {

        $validador= Validator::make(Input::all(),Usuario::reglasDeValidacion());

        if($validador->fails()){
            return Redirect::back()->withInput()->withErrors($validador);
        }
        else{

         $user = new Usuario;
         $user->nombre = Input::get('nombre');
         $user->apellido = Input::get('apellido');
         $user->email = Input::get('email');
         $user->dni = Input::get('dni');
         $user->provincia_id = Input::get('provincia');
         $user->localidad = Input::get('localidad');
         $user->dirección = Input::get('dirección');
         $user->teléfono = Input::get('teléfono');
         $user->contraseña = Hash::make(Input::get('contraseña'));
         $user->save();


         $userdata = array(
              'email'      => Input::get('email'),
              'password'      => Input::get('contraseña')
               );

        if (Auth::attempt($userdata)) {
                return Redirect::to('/');
            }
         }

    }
 
     
  
     
    public function verUsuario($id)
    {
         $usuario = Usuario::find($id);
         if (($usuario) && (! $usuario->esAdmin)){
            return View::make('usuario.ver', array('usuario' => $usuario));
         }
         else {
            return Redirect::to('/admin/usuarios');
         }
    }
    


    /*  ***************************************************************************************************************************************************  */
    /*  *************************************************** FUNCIONES DE TESTING - DESACTIVADAS ***********************************************************  */
    /*  ***************************************************************************************************************************************************  */
/*
    public function nuevoTestUsuario()
    {

        $provincias= Provincia::all();
        return View::make('usuario.crear',['provincias'=>$provincias]);
    }
  
   
        public function crearUsuario()
    {

        $validador= Validator::make(Input::all(),Usuario::reglasDeValidacion());

        if($validador->fails()){
            return Redirect::back()->withInput()->withErrors($validador);
        }
        else{

         $user = new Usuario;
         $user->nombre = Input::get('nombre');
         $user->apellido = Input::get('apellido');
         $user->email = Input::get('email');
         $user->dni = Input::get('dni');
         $user->contraseña = Hash::make(Input::get('contraseña'));
         $user->provincia_id = Input::get('provincia');
         $user->dirección = Input::get('dirección');
         $user->localidad = Input::get('localidad');
         $user->teléfono = Input::get('teléfono');
         $user->save();

         return Redirect::to('/admin/usuarios');
         }
    }
    */

    public function bloquearUsuario($id)
    {
        $usuario=Usuario::find($id);
        if($usuario) {
            if($usuario->id != 1) {
             $usuario->bloqueado=!($usuario->bloqueado);
             $usuario->save();
            }
        }
     /* return Redirect::to('/admin/usuarios#area'); */
        return Redirect::back();
    }
/*
    public function modificarDatos($id)
    {
        $provincias= Provincia::all();
        $usuario=Usuario::find($id);
        return View::make('usuario.modificar',['usuario'=>$usuario, 'provincias'=>$provincias]);
    }

    public function modificarUsuario($id)
    {

        $validador= Validator::make(Input::all(),Usuario::reglasDeValidacionModAdmin());

        if($validador->fails()){

            return Redirect::back()->withInput(Input::except('contraseña'))->withErrors($validador);
        }
        else{

            $usuario=Usuario::find($id);

            if ($usuario->nombre != Input::get('nombre')) {
                $usuario->nombre=Input::get('nombre');
            }
            if ($usuario->apellido != Input::get('apellido')){
                $usuario->apellido=Input::get('apellido');
            }
            
            if ($usuario->email != Input::get('email') and (sizeof(Usuario::where('email','=',Input::get('email'))->get()) <= 0 )){
                $usuario->email=Input::get('email');
            }
            else {
                
                if ($usuario->email == Input::get('email')) {
                }
              
                else {
                return Redirect::back()->withInput(Input::except('contraseña'))->withErrors(['-> El email ingresado ya se encuentra en la base de datos']);
                }
            }

           
            if ($usuario->dni != Input::get('dni') and (sizeof(Usuario::where('dni','=',Input::get('dni'))->get()) <= 0 )){
                $usuario->dni=Input::get('dni');
            }
            else {
               
                if ($usuario->dni == Input::get('dni')) {
                }
                
                else {
                return Redirect::back()->withInput(Input::except('contraseña'))->withErrors(['-> El DNI ingresado ya se encuentra en la base de datos']);
                }
            }

            $usuario->provincia_id = Input::get('provincia');
            $usuario->localidad = Input::get('localidad');
            $usuario->dirección = Input::get('dirección');
            $usuario->teléfono = Input::get('teléfono');

            if ( ($usuario->contraseña != Input::get('contraseña')) AND (Input::get('contraseña') != null) ){
                $usuario->contraseña = Hash::make(Input::get('contraseña'));
            }

            $usuario->save();
            
            return Redirect::to('/admin/usuarios/');
          }
    }
*/
    /*  ***************************************************************************************************************************************************  */
    /*  ************************************************************* PERFIL ******************************************************************************  */
    /*  ***************************************************************************************************************************************************  */

    public function formularioPerfil()
    {
        if (Auth::user()->esAdmin != 1) {
            $provincias= Provincia::all();
            /* Pedidos no finalizados del usuario, para advertirlo si desea darse de baja. */
            $pedidos = Pedido::where('usuario_id', '=', Auth::user()->id)->where('estado', '!=', 'f')->get();
            return View::make('usuario.perfil',['provincias'=>$provincias, 'pedidos'=>$pedidos]);
        }
        else {
            return View::make('usuario.adminPerfil');
        }
    }

    public function modificarPerfil()
    {

        $validador= Validator::make(Input::all(),Usuario::reglasDeValidacionMod());

        if($validador->fails()){

            return Redirect::back()->withInput(Input::except('contraseña'))->withErrors($validador);
        }
        else{


            if (Auth::user()->nombre != Input::get('nombre')) {
                Auth::user()->nombre=Input::get('nombre');
            }
            if (Auth::user()->apellido != Input::get('apellido')){
                Auth::user()->apellido=Input::get('apellido');
            }
            /*Si el email es diferente al suyo y no existe en la base de datos, se graban los cambios.*/
            if (Auth::user()->email != Input::get('email') and (sizeof(Usuario::where('email','=',Input::get('email'))->get()) <= 0 )){
                Auth::user()->email=Input::get('email');
            }
            else {
                /*Si el email es igual al suyo, no realiza cambios.*/
                if (Auth::user()->email == Input::get('email')) {
                }
                /*Si el email es diferente, pero existe en la base de datos, se le informa del error.*/
                else {
                return Redirect::back()->withInput(Input::except('contraseña'))->withErrors(['-> El email ingresado ya se encuentra en la base de datos']);
                }
            }

            /*Si el dni es diferente al suyo y no existe en la base de datos, se graban los cambios.*/
            if (Auth::user()->dni != Input::get('dni') and (sizeof(Usuario::where('dni','=',Input::get('dni'))->get()) <= 0 )){
                Auth::user()->dni=Input::get('dni');
            }
            else {
                /*Si el dni es igual al suyo, no realiza cambios.*/
                if (Auth::user()->dni == Input::get('dni')) {
                }
                /*Si el dni es diferente, pero existe en la base de datos, se le informa del error.*/
                else {
                return Redirect::back()->withInput(Input::except('contraseña'))->withErrors(['-> El DNI ingresado ya se encuentra en la base de datos']);
                }
            }



            if ( (Auth::user()->contraseña != Input::get('contraseña')) AND (Input::get('contraseña') != null) ){
                Auth::user()->contraseña = Hash::make(Input::get('contraseña'));
            }

            Auth::user()->provincia_id = Input::get('provincia');
            Auth::user()->localidad=Input::get('localidad');
            Auth::user()->dirección=Input::get('dirección');
            Auth::user()->teléfono=Input::get('teléfono');

            Auth::user()->save();
            
            return Redirect::to('/');
          }
    }

    public function darBaja()
    {
        if (Auth::user()->esAdmin != 1) {
            Auth::user()->dadoDeBaja = 1;
            Auth::user()->save();
            return Redirect::to('/logout');
        }
        else {
            return Redirect::to('/');
        }
    }


    public function formularioAdminPerfil()
    {
        return View::make('usuario.adminPerfil');
    }

    public function modificarAdminPerfil()
    {

        $validador= Validator::make(Input::all(),Usuario::reglasDeValidacionAdmin());

        if($validador->fails()){

            return Redirect::back()->withErrors($validador);
        }
        else{
            if ( (Auth::user()->contraseña != Input::get('contraseña')) AND (Input::get('contraseña') != null) ){
                Auth::user()->contraseña = Hash::make(Input::get('contraseña'));
            }

            Auth::user()->save();
            
            return Redirect::to('/');
          }
    }


    public function verPedidos()
    {
        if (Auth::user()->esAdmin != 1) {
            /* Devuelve los pedidos del usuario autenticado. Ignora los que están Finalizados.  */
            $pedidos = Pedido::where('usuario_id', '=', Auth::user()->id)->where('estado', '!=', 'f')->get();
            return View::make('usuario.pedidos',['pedidos'=>$pedidos]);
        }
        else {
            return Redirect::to('/');
        }
    }


    public function detallePedido($id)
    {
        $pedido = Pedido::find($id);
        /* Si el pedido existe. Si el pedido no está Finalizado (protege URL). */
        if (($pedido) && ($pedido->estado != "f")) {
            /* Lo muestra si el usuario no es admin. Si el pedido pertenece al usuario (protege URL). */
            if ((Auth::user()->esAdmin != 1) && ($pedido->usuario_id == Auth::user()->id))
            {
                return View::make('usuario.detallePedido',['pedido'=>$pedido]);
            }
            else {
                return Redirect::to('/404');
            }
        }
        else {
            return Redirect::to('/404');
        }
    }

    public function cambiarEstado($id)
    {
        $pedido = Pedido::find($id);
        /* Lo cambia si el usuario no es admin. Si el pedido existe. Si el pedido pertenece al usuario (protege URL). Si ha sido Enviado (protege URL). */
        if ((Auth::user()->esAdmin != 1) && ($pedido) && ($pedido->usuario_id == Auth::user()->id) && ($pedido->estado == "e")) {
            $pedido->estado = "f";
            $pedido->save();
            return Redirect::to('/pedidos');
        }
        else {
            /* El admin lo quiere cambiar. Si el pedido existe. */
            if ((Auth::user()->esAdmin) && ($pedido)) {
                /* De Pendiente a Enviado. */
                if ((Auth::user()->esAdmin) && ($pedido->estado == "p")) {
                    $pedido->estado = "e";
                    $pedido->save();
                    return Redirect::to('/admin/pedidos');
                }
                /* De Enviado a Finalizado. */
                else if ((Auth::user()->esAdmin) && ($pedido->estado == "e")) {
                    $pedido->estado = "f";
                    $pedido->save();
                    return Redirect::to('/admin/pedidos');
                }
            }
            else {
                return Redirect::to('/404');  
            }
          return Redirect::to('/404');  
        }
    }


    public function verPedidosAdmin()
    {
	   
	   Session::forget('FiltNombre');//Reinicializa el mensaje que se envia a cuando se aplica algun filtro.
	   Session::forget('FiltEstado');
	   if((Input::has('filtro')) && (input::has('valor'))){
            if (Input::get('filtro') == 'nombre') 
			{
                 $pedidos = Pedido::where('estado', '!=', 'f')->join('usuario', 'usuario.id', '=', 'pedido.usuario_id')
                                                              ->where(function($query)
                                                                {
                                                                  $nombre = Input::get('valor');
                                                                  $completo = DB::raw('CONCAT(nombre, " ", apellido)');
                                                                  $query->where('apellido', 'LIKE', '%' . $nombre . '%')
                                                                        ->orWhere('nombre', 'LIKE', '%' . $nombre . '%')
                                                                        ->orWhere ($completo, 'LIKE', '%' . $nombre . '%');
                                                                })->orderBy('fecha', 'ASC')->select('pedido.*', 'usuario_id')->get();
				 $nombreComp = Input::get('valor');
                 Session::put('FiltNombre','Estás filtrando por los clientes que coincidan con "'.$nombreComp.'".');
            }
            else if (Input::get('filtro') == 'estado' )
                 {
                   if((Input::get('valor')== 'f')||(Input::get('valor')== 'p')||(Input::get('valor')== 'e')
                       ||(Input::get('valor')== 'F')||(Input::get('valor')== 'P')||(Input::get('valor')== 'E'))
				   {
                        $pedidos = Pedido::where('usuario_id', '<>', '1')->where(function($query)
                                                                                    {
                                                                                    $est = Input::get('valor');
                                                                                    $query->where('estado', '=', $est);
                                                                                    })->orderBy('fecha', 'ASC')->get();
			            $estado = Input::get('valor');
						if($estado == 'f')
						   $estado = 'finalizados';
                        if($estado == 'p')
						   $estado = 'pendientes';
                        if($estado == 'e')
						   $estado = 'enviados';  						   
						Session::put('FiltEstado','Estás filtrando por los pedidos '.$estado.'.');
					}
					else  //se ingreso un caracter no valido.Se muestra que no hay pedidos en ese estado.
                         $pedidos = null;				
                 }
                 else 
				 {
                   //Input está vacío. Muestra todos los pedidos menos los finalizados. 
                   $pedidos = Pedido::where('estado', '!=', 'f')->orderBy('fecha', 'ASC')->get();
                 }
        }
        else {
            $pedidos = Pedido::where('estado', '!=', 'f')->orderBy('fecha', 'ASC')->get();
        }
		
        return View::make('usuario.pedidosAdmin', array('pedidos' => $pedidos));
		
	}	 
	 public function verPedidosAdminOrdDesc()//Metodo completamente similar al sup, solo cambia el orden de las fechas.
    {
	   Session::forget('FiltNombre');//Reinicializa el mensaje que se envia a cuando se aplica algun filtro.
	   Session::forget('FiltEstado');
	   if((Input::has('filtro')) && (input::has('valor'))){
            if (Input::get('filtro') == 'nombre') 
			{
			  $pedidos = Pedido::where('estado', '!=', 'f')->join('usuario', 'usuario.id', '=', 'pedido.usuario_id')
                                                           ->where(function($query)
                                                            {
                                                              $nombre = Input::get('valor');
                                                              $completo = DB::raw('CONCAT(nombre, " ", apellido)');
                                                              $query->where('apellido', 'LIKE', '%' . $nombre . '%')
                                                                    ->orWhere('nombre', 'LIKE', '%' . $nombre . '%')
                                                                    ->orWhere ($completo, 'LIKE', '%' . $nombre . '%');
                                                            })->orderBy('fecha', 'DESC')->select('pedido.*', 'usuario_id')->get();
              $nombreComp = Input::get('valor');
              Session::put('FiltNombre','Estás filtrando por los clientes que coincidan con "'.$nombreComp.'".');			  
            }
            else if (Input::get('filtro') == 'estado' )
			    {
			       if((Input::get('valor')== 'f')||(Input::get('valor')== 'p')||(Input::get('valor')== 'e')
                       ||(Input::get('valor')== 'F')||(Input::get('valor')== 'P')||(Input::get('valor')== 'E'))
				    {
                        $pedidos = Pedido::where('usuario_id', '<>', '1')->where(function($query)
                                                                          {
                                                                            $est = Input::get('valor');
                                                                            $query->where('estado', '=', $est);
                                                                          })->orderBy('fecha', 'DESC')->get();
					    $estado = Input::get('valor');
						if($estado == 'f')
						   $estado = 'finalizados';
                        if($estado == 'p')
						   $estado = 'pendientes';
                        if($estado == 'e')
						   $estado = 'enviados';  						   
						Session::put('FiltEstado','Estás filtrando por los pedidos '.$estado.'.');	
				    }
					else  //se ingreso un caracter no valido.Se muestran mensaje que no se encuentran pedidos.
                         $pedidos = null;					
                }
            else {
                // Input está vacío. Muestra todos excepto el Admin. 
                $pedidos = Pedido::where('estado', '!=', 'f')->orderBy('fecha', 'DESC')->get();
            }
        }
        else {
            $pedidos = Pedido::where('estado', '!=', 'f')->orderBy('fecha', 'DESC')->get();
        }
		
        return View::make('usuario.pedidosAdminDesc', array('pedidos' => $pedidos));
	}	
		
    public function detallePedidoAdmin($id)
    {
        $pedido = Pedido::find($id);
        /* Lo muestra si el usuario no es admin. Si el pedido existe. Si el pedido pertenece al usuario (protege URL). Si el pedido no está Finalizado (protege URL).*/
        if ($pedido) {
            return View::make('usuario.detallePedidoAdmin',['pedido'=>$pedido]);
        }
        else {
            return Redirect::to('/404');
        }
    }
	public function comprobanteUsuario($id)
	{
	    $pedido = Pedido::find($id);
        /* Si el pedido existe. Si el pedido no está Finalizado (protege URL). */
        if (($pedido) && ($pedido->estado != "f")) {
            /* Lo muestra si el usuario no es admin. Si el pedido pertenece al usuario (protege URL). */
            if ((Auth::user()->esAdmin != 1) && ($pedido->usuario_id == Auth::user()->id))
            {
			   // Session::put('notificacionComprobante','El comprobante se ha enviado a la cola de impresión.');
                return View::make('usuario.comprobantePedidoUsuario',['pedido'=>$pedido]);
            }
            else {
                return Redirect::to('/404');
            }
        }
        else {
            return Redirect::to('/404');
        }
	}
	
	public function comprobanteAdmin($id)
	{
	    $pedido = Pedido::find($id);
        /* Si el pedido existe.*/
        if ($pedido) {
            /* Lo muestra si el usuario es admin.*/
            if (Auth::user()->esAdmin == 1)
            {
			   // Session::put('notificacionComprobante','El comprobante se ha enviado a la cola de impresión.');
                return View::make('usuario.comprobantePedidoAdmin',['pedido'=>$pedido]);
            }
            else {
                return Redirect::to('/404');
            }
        }
        else {
            return Redirect::to('/404');
        }
	}

	public function comprobanteAdminDesc($id)
	{
	    $pedido = Pedido::find($id);
        /* Si el pedido existe.*/
        if ($pedido) {
            /* Lo muestra si el usuario es admin.*/
            if (Auth::user()->esAdmin == 1)
            {
			   // Session::put('notificacionComprobante','El comprobante se ha enviado a la cola de impresión.');
                return View::make('usuario.comprobantePedidoAdminDesc',['pedido'=>$pedido]);
            }
            else {
                return Redirect::to('/404');
            }
        }
        else {
            return Redirect::to('/404');
        }
	}

    public function verAyuda()
    {
        return View::make('ayuda');
    }

    public function verAyudaAdmin()
    {
        if (Auth::user()->esAdmin == 1) {
            return View::make('ayudaAdmin');
        }
        else {
            return Redirect::to('/');
        }
    }
	//
	public function mostrarReportes()
	{
	  $reporte = null;
	  $reglas=['reporte'=>['required'],'desde'=>['required'],'hasta'=>['required']];
	  if(Input::has('reporte'))
	  {
	     $validador = Validator::make(Input::all(),$reglas);
		 if($validador->fails())
		 {
		    if(Input::get('valor')==''){
			  Session::put('rep','Seleccione un tipo de reporte.');  
			}
            return Redirect::back()->withInput()->withErrors($validador);
         }
         else
		 {
		   if(Input::get('valor')==''){
			  Session::put('rep','Seleccione un tipo de reporte.');  
			}

           // ******* Reporte de Usuarios ******* //
	       if(Input::get('valor')=='CantUs')
		   {
		     $fecDesde = Input::get('desde');
			 $fecHasta = Input::get('hasta');
		     $reporte = Usuario::whereBetween('created_at',[$fecDesde,$fecHasta])->whereNotIn('id',[1])->orderBy('created_at','ASC')->select('nombre','apellido','created_at')->get();
			 if(count($reporte)== 0)
			   Session::put('sinRes','No hay resultados para las fechas ingresadas.');
			 Session::put('repUserReg','reporte de usuarios registrados'); // Esto nunca se muestra pero sirve para que la view sepa que reporte debe mostrar(con su formato de tabla especifico).   
		   }

           // ******* Reporte de Libros ******* //
		   if(Input::get('valor')=='VenLib') {
             $fecDesde = Input::get('desde');
             $fecHasta = Input::get('hasta');
             $reporte = Pedido::whereBetween('fecha',[$fecDesde,$fecHasta])->select(array('*', DB::raw('SUM(libropedido.cantidad) as cant')))
                                                                           ->join('libropedido', 'libropedido.pedido_id', '=', 'pedido.id')
                                                                           ->join('libro', 'libro.id', '=', 'libropedido.libro_id')
                                                                           ->groupBy('libropedido.libro_id')
                                                                           ->orderBy('cant', 'DESC')
                                                                           ->get();

             if(count($reporte)== 0) {
               Session::put('sinRes','No hay resultados para las fechas ingresadas.');
             }
             Session::put('repLibrVen','reporte de libros vendidos');   
		   }

           // ******* Reporte de Pedidos ******* //
           if(Input::get('valor')=='ListaPedidos') {
             $fecDesde = Input::get('desde');
             $fecHasta = Input::get('hasta');
             $reporte = Pedido::whereBetween('fecha',[$fecDesde,$fecHasta])->join('usuario', 'usuario.id', '=', 'pedido.usuario_id')
                                                                           ->orderBy('fecha', 'ASC')
                                                                           ->select('pedido.*', 'usuario_id')
                                                                           ->get();
             if(count($reporte)== 0) {
               Session::put('sinRes','No hay resultados para las fechas ingresadas.');
             }
             Session::put('repPedidos','reporte de libros vendidos');   
           }
		 }
	  }	  
	  return View::make('usuario.reportes',['datosReporte'=>$reporte]);
	}
	
	public function exportarRepCantUs()
	{
	  $fecDesde = Input::get('fechaDesde');
	  $fecHasta = Input::get('fechaHasta');
	  $table = Usuario::whereBetween('created_at',[$fecDesde,$fecHasta])->whereNotIn('id',[1])
	                                                                    ->orderBy('created_at','ASC')
																		->select('nombre','apellido','created_at')
																		->get();
      $output= 'Usuarios registrados entre '.$fecDesde.' y '.$fecHasta."\r\n";
      $output.= "\r\n";	  
	  $output.='"Nombre ", "Apellido", "Fecha de registro"'."\r\n";
      foreach ($table as $row) {
         $columnas=$row->toArray();
         $columnas['created_at']=date('d/m/Y', strtotime($columnas['created_at']));
         $output.=  '"'.implode('", "',$columnas)."\"\r\n";
      }
	  $output.= "\r\n";
	  $output.= 'Total de usuarios registrados: '.count($table);
      $headers = array(
         'Content-Type' => 'text/csv',
         'Content-Disposition' => 'attachment; filename="Reporte de Usuarios Registrados.csv"');
      return Response::make(rtrim($output, "\n"), 200, $headers);
	}
	public function exportarRepLibrosVendidos()
	{
	  $fecDesde = Input::get('fechaDesde');
	  $fecHasta = Input::get('fechaHasta');
	  $table = Pedido::whereBetween('fecha',[$fecDesde,$fecHasta])->select(array('isbn','título', DB::raw('SUM(libropedido.cantidad) as cant')))
                                                                    ->join('libropedido', 'libropedido.pedido_id', '=', 'pedido.id')
                                                                    ->join('libro', 'libro.id', '=', 'libropedido.libro_id')
                                                                    ->groupBy('libropedido.libro_id')
                                                                    ->orderBy('cant', 'DESC')
																	//->select()
                                                                    ->get();
	  $output= 'Libros vendidos entre '.$fecDesde.' y '.$fecHasta."\r\n";
      $output.= "\r\n";	  
	  $output.='"ISBN ", "Titulo", "Cantidad de Ventas"'."\r\n";
	  $tot = 0 ;
      foreach ($table as $row) {
         $columnas=$row->toArray();
         //$columnas['created_at']=date('d/m/Y h:i:s', strtotime($columnas['created_at']));
		 $tot = $tot + $columnas['cant']; 
         $output.=  '"'.implode('", "',$columnas)."\"\r\n";
      }
	  $output.= "\r\n";
	  $output.= 'Total de libros vendidos: '.$tot;
      $headers = array(
         'Content-Type' => 'text/csv',
         'Content-Disposition' => 'attachment; filename="Reporte de libros vendidos.csv"');
      return Response::make(rtrim($output, "\n"), 200, $headers);
		//return $table;
	}
    public function exportarPedidos()
    {
	  $fecDesde = Input::get('fechaDesde');
      $fecHasta = Input::get('fechaHasta');
      $table = Pedido::whereBetween('fecha',[$fecDesde,$fecHasta])->join('usuario', 'usuario.id', '=', 'pedido.usuario_id')
                                                                    ->orderBy('fecha', 'ASC')
                                           							->select('pedido.id','pedido.created_at','usuario.nombre','usuario.apellido','pedido.monto')
                                                                    ->get();
	  $output= 'Pedidos realizados entre '.$fecDesde.' y '.$fecHasta."\r\n";
      $output.= "\r\n";	  
	  $output.='"Numero ", "Fecha", "Nombre del cliente","Apellido del cliente","Monto"'."\r\n";
	  $montoTot = 0;
      foreach ($table as $row) {
         $columnas=$row->toArray();
         $columnas['created_at']=date('d/m/Y', strtotime($columnas['created_at']));
		 $montoTot = $montoTot + $columnas['monto'];
         $output.=  '"'.implode('", "',$columnas)."\"\r\n";
      }
	  $output.= "\r\n";
	  $output.= 'Total de Pedidos realizados: '.count($table);$output.= "\r\n";
	  $output.= 'Monto total recaudado: $'.$montoTot;
      $headers = array(
         'Content-Type' => 'text/csv',
         'Content-Disposition' => 'attachment; filename="Reporte de Pedidos.csv"');
      return Response::make(rtrim($output, "\n"), 200, $headers);
	}	
	//Experimental !!!
	public function exportarBD(){
		//Solucion indep del SO y "flexible" a cambios de Esquema de la BD. Recomandada para usar con MySQL.
		//Consigo los nombres de las tablas de la BD.
		$consulta=DB::select('Show tables') ;
		$tablas=array_fetch($consulta,'Tables_in_cookbook');

		//Por cada una consigo sus campos (para el futuro inser into..)
		$tablasConCampos=[];
		foreach($tablas as $tabla){
			$consulta=DB::select('Describe '.$tabla) ;
			$tablasConCampos[$tabla]=array_fetch($consulta,'Field');
		}

		//Encabezado sin chequeo de FK y otras compatibilidades...
		$scriptDeSQL="-- Backup completo generado por Cookbook\n-- Fecha: ".date('d/m/Y H:i:s')."\n-- Aclaración: este Script de SQL es solo compatible con el Gestor de BD MySQL.\n\n";
		$scriptDeSQL.="SET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\nSET time_zone = \"+00:00\";\n\n";
		$scriptDeSQL.="CREATE DATABASE IF NOT EXISTS `cookbook` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;\nUSE `cookbook`;\n\n";

		//Genero el Esquema de cada tabla y simulo el Insert Into..
		foreach($tablasConCampos as $tabla => $campos){
			$scriptDeSQL.="/*	Esquema de la tabla $tabla	*/\n";
			$scriptDeSQL.=str_replace('CREATE TABLE','CREATE TABLE IF NOT EXISTS',array_fetch(DB::select('Show create table '.$tabla),'Create Table')[0]).';';
			$scriptDeSQL.="\n\n";
		
			$registros=DB::select('select * from '.$tabla);

			//Solo si hay registros...se define el insert into
			if(count($registros)){
				$scriptDeSQL.='Insert into `'.$tabla.'` (`'.implode('`, `',$campos)."`) values \n";
				
				//Genero las lineas de inserción..
				$cantidad=count($registros);
				foreach($registros as $registro){
					//Los datos separados por coma, rodeados por comillas simples. MySQL hace la conv de los String a Numeros
					//cuando sea necesario. Se colocan los Null
					$datos="('".implode("','",array_flatten($registro))."')".(($cantidad===1)?';':',');
					$datos=str_replace("''","Null",$datos);
					$scriptDeSQL.=$datos."\n";
					$cantidad--;
				}
				
				$scriptDeSQL.="\n\n";
			}
		}

		//Reactivo las FK
		$scriptDeSQL.='SET FOREIGN_KEY_CHECKS=1;';
		
		
		//Genero la respuesta: un archivo para descargar con nombre «Cookbook - Backup YYYY-MM-DD.sql»
		$respuesta= Response::make($scriptDeSQL, 200);
		$respuesta->header('Content-Type', 'application/x-sql');
		$respuesta->header('Content-Disposition','attachment; filename="Cookbook - Backup '.date('Y-m-d h\h\si\m').'.sql"');
		//Se calcula el tamaño en funcion de la cantidad de caracteres q hay, si bien es
		//multibyte, strlen devuelve la cant de bytes ;).
		$respuesta->header('Content-Length',strlen($scriptDeSQL));
		return $respuesta;
	}
}
?>
