<?php 
class EtiquetasController extends BaseController 
{
 
    /**
     * Mustra la lista con todas los etiquetas
     */
    public function mostrarEtiquetas()
    {
        $etiquetas= Etiqueta::where('id','<>',1)->get();// me qedo con todos menos con el <id> "Sin etiqueta"	        
        return View::make('etiqueta.etiquetas', array('etiquetas' => $etiquetas));
        
        // El método make de la clase View indica cual vista vamos a mostrar al usuario 
        //y también pasa como parámetro los datos que queramos pasar a la vista. 
        // En este caso le estamos pasando un array con todas las etiquetas
    }
	
	// Muestra las etiquetas de un libro pasado por parametro
	public function mostrarEtiquetasDeX($id)
	{
	    $libro = Libro::find($id);
	    $etiquetas = $libro->etiquetas()->get();
		$libroX = $libro->get();//libroX se quedaria con el libro para tener los 2 id para luego quitar una etiqueta (nose como pasarla a la vista)
		return View::make('etiqueta.etiquetasDeX',array('etiquetasDeX' => $etiquetas));//
	}
	

	public function formularioAlta(){
		return View::make('etiqueta.crear'); //
	}
	public function altaEtiqueta()
	{
		// llamamos a la función de agregar etiqueta en el modelo y le pasamos los datos del formulario 
        $respuesta = Etiqueta::agregarEtiqueta(Input::all());
        
        // Dependiendo de la respuesta del modelo 
        // retornamos los mensajes de error con los datos viejos del formulario 
        // o un mensaje de éxito de la operación 
        if ($respuesta['error'] == true){
            return Redirect::back()->withErrors($respuesta['mensaje'] )->withInput();
        }else{
            return Redirect::to('/admin/etiquetas')->with('mensaje', $respuesta['mensaje']);
        }
    }
	public function modificacionEtiqueta($id){
	$etiqueta=Etiqueta::find($id);
	if ($etiqueta->nombre != Input::get('nombre')){	
		$validador= Validator::make(Input::all(),Etiqueta::reglasDeValidacion());
		
		if($validador->fails()){
			//~ return Redirect::to('/admin/idiomas/'.$id.'/modificar/')->withErrors($validador)->withInput();
			return Redirect::back()->withErrors($validador)->withInput();
		}
		else{
			$etiqueta->nombre=Input::get('nombre');
			$etiqueta->save();
         	return Redirect::to('/admin/etiquetas/');
		}
	}
    else
       return Redirect::to('/admin/etiquetas'); 
	}
			
	public function formularioModificacionEtiqueta($id){
		//ToDo: Proteger este metodo 
		$etiqueta=Etiqueta::find($id);
		return View::make('etiqueta.modificar',['etiqueta'=>$etiqueta]);
	}		
 
}
?>