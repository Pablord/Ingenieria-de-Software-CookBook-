<?php

class LibroController extends BaseController {

	public function listar()
	{
		//Se ignoran los libros dados de baja logica
		//~ $libros=Libro::all();
		$libros=Libro::disponibles()->get();
		return View::make('libro.libros',['libros'=>$libros]);
	}

	//Muestra el catalogo
	public function mostrarCatalogo()
	{
		//Se ignoran los libros dados de baja logica
		$libros=Libro::disponibles()->get();
		return View::make('catalogo',['libros'=>$libros]);
	}
	//Muestra los detalles de un libro, desde la administración
	public function visualizar($id){
		$libro=Libro::find($id);
		return View::make('libro.visualizar',['libro'=>$libro]);
	}




	public function visualizarDetalles($id){

		$libro=Libro::find($id);
		return View::make('libro.visualizarDetalles',['libro'=>$libro]);
	}


	public function formularioAlta(){
		//Recupero las entidades secundarias, ignorando los valores por 'SIN' usado en caso de no tener ref.
		$idiomas= Idioma::disponibles()->get();
		$editoriales= Editorial::disponibles()->get();
		$etiquetas= Etiqueta::where('id','<>',1)->get();
		$autores= Autor::where('id','<>',1)->get();
		return View::make('libro.crear',['idiomas'=>$idiomas, 'editoriales'=>$editoriales,'etiquetas'=>$etiquetas,'autores'=>$autores]);
	}
	

	public function alta(){
		$validador= Validator::make(Input::all(),Libro::reglasDeValidacion());
		
		if($validador->fails()){
			return Redirect::back()->withErrors($validador)->withInput();
		}
		else{
			
			//contiene los posibles msj de errores
			$errores=[];
			
			//Validación Manual Requerida: Análisis de alternativas

			// Relación 1 a 1: Idioma/Editorial
			// -> Debido a q puede enviarse otro sin checkbox activado, pudiendo mal interpretarse.
			// Campo	Otro	Check	Acción
			// --------------------------------
			// Si		SI		SI		ERROR
			// Si		SI		NO		cargar Campo
			// Si		NO		SI		ERROR
			// Si		NO		NO		cargar Campo
			// NO		SI		SI		cargar Otro
			// NO		SI		NO		ERROR
			// NO		NO		SI		ERROR
			// NO		NO		NO		ERROR
			//Conclusión= Campo se carga solo cuando  esta presente y no el checkbox. Otro solo cuando esta presente junto con el check
		
			//idioma: debe haberse definido al menos uno. Sino otro se tomará en cuenta despues.
			//Si  NO se definió (Otro y Checkbox Activo) ni (Idioma con  Checbox activo). 
		
			if(!( ( Input::has('idioma-otro') && Input::has('idioma-checkbox') ) || ( Input::has('idioma') && !(Input::has('idioma-checkbox')) ) )){			
				$errores['idioma']='Debe seleccionar un idioma para el libro. Utilice la lista o bien defina uno nuevo.';
			}		
			
			
			//editorial: idem idioma
			if(!( ( Input::has('editorial-otro') && Input::has('editorial-checkbox') ) || ( Input::has('editorial') && !(Input::has('editorial-checkbox')) ) )){			
				$errores['editorial']='Debe seleccionar una editorial para el libro. Utilice la lista o bien defina una nueva.';
				//~ $errores['editorial']='Debe seleccionar una editorial para el libro. Utilice la lista o bien defina una nueva. ###DEBUG: Editorial='. ((Input::has('editorial'))?'SI':'NO'). ', Checkbox='. ((Input::has('editorial-checkbox'))?'SI':'NO'). ' y Otro='. ((Input::has('editorial-otro'))?'SI':'NO');
			}
			
			
			// Relación 1 a N: Etiqueta/Autor			
			// Campo	Otro	Check	Acción
			// --------------------------------
			// Si		SI		SI		Cargar Campo y Otro
			// Si		SI		NO		cargar Campo
			// Si		NO		SI		ERROR
			// Si		NO		NO		cargar Campo
			// NO		SI		SI		cargar Otro
			// NO		SI		NO		ERROR
			// NO		NO		SI		ERROR
			// NO		NO		NO		ERROR
			//Conclusión= Campo se carga cuando  esta presente. Otro solo cuando esta presente junto con el check.
			
			//etiquetas: Al menos 1 de los dos campos debe estar completado.
			//El Otro solo soporta 1 etiqueta y debe haberse activado el checkbox. 	
			if( !( (Input::has('etiqueta-otro') && Input::has('etiqueta-checkbox')) || (Input::has('etiqueta')) ) ){
				$errores['etiquetas']='Debe seleccionar una o mas etiquetas de la lista, o bien defina una nueva.';
				//~ $errores['etiquetas']='Debe seleccionar una o mas etiquetas de la lista, o bien defina una nueva. ###DEBUG: Etiqueta='. ((Input::has('etiqueta'))?'SI':'NO'). ', Checkbox='. ((Input::has('etiqueta-checkbox'))?'SI':'NO'). ' y Otro='. ((Input::has('etiqueta-otro'))?'SI':'NO');
			}
			
			//autores: idem etiquetas
			if( !( (Input::has('autor-otro') && Input::has('autor-checkbox')) || (Input::has('autor')) ) ){
				$errores['autores']='Debe seleccionar uno o mas autores de la lista, o bien defina uno nuevo.';
			}
			
			
			//Chequeo de archivos: subida correcta			
			if ( !( (Input::file('tapa')->isValid()) && (Input::file('indice')->isValid()) ) ){
				$errores['archivos']='Los archivos seleccionados se han cargado de forma erronea. Vuelva a cargarlos.';
			}
			
			//Si hubo errores lo devuelvo al form
			if(count($errores)>0){
				return Redirect::back()->withErrors($errores)->withInput();
			}
			else{
				//Se carga un array con los datos definitivos
				//Basicos:
				$datos=Input::only('isbn','precio');
				$datos['título']=Input::get('titulo');
				$datos['añoEdición']=Input::get('anoDeEdicion');
				$datos['hojas']=Input::get('cantidadDeHojas');				
				
				//Claves foraneas:
				if(Input::has('idioma-checkbox')){
					//Se crea el nuevo idioma..
					$idioma=Idioma::create(['nombre'=>Input::get('idioma-otro')]);
					$datos['idioma_id']=$idioma->id;
				}
				else{
					$datos['idioma_id']= (int) Input::get('idioma');
				}
				
				
				if(Input::has('editorial-checkbox')){					
					$editorial=Editorial::create(['nombre'=>Input::get('editorial-otro')]);
					$datos['editorial_id']=$editorial->id;				
				}
				else{
					$datos['editorial_id']=(int) Input::get('editorial');
				}
				
				//Creo el libro con la info básica:
				$libro= Libro::create($datos);				
				
				
				
				// Se asocia a las Entidad NaN:
				//Autor:
				if(Input::has('autor')){
					//uso Sync para agregar los id a la tabla libroautor
					$libro->autores()->sync(Input::get('autor'));
				}
				if(Input::has('autor-checkbox')){					
					$autor=Autor::create(['nombre'=>Input::get('autor-otro')]);
					$libro->autores()->attach($autor->id);
				}


				//Etiqueta:
				if(Input::has('etiqueta')){
					//uso Sync para agregar los id a la tabla libroautor
					$libro->etiquetas()->sync(Input::get('etiqueta'));
				}

				if(Input::has('etiqueta-checkbox')){					
					$etiqueta=Etiqueta::create(['nombre'=>Input::get('etiqueta-otro')]);
					$libro->etiquetas()->attach($etiqueta->id);
				}
				
				//Se colocan los archivos: Se deberia chequear q salio bn
				Libro::ubicarArchivos($libro,Input::file('tapa'),Input::file('indice'));

				return Redirect::to('/admin/libros');
			}
			
		}
	}

	public function formularioModificacion($id){
		//ToDo: proteger este método
		$libro=Libro::find($id);		
		
		$autoresFiltrados=$libro->with(['autores' => function($query){
			$query->whereNotIn(array_pluck($libro->autores,'id'));
		}]);
		return View::make('libro.modificar',['libro'=>$libro, 'idiomas'=>Idioma::disponibles()->get(), 'editoriales'=>Editorial::disponibles()->get()]);	
	}

	public function modificacion($id){
		//Se descompuso la funcionalidad en 5 secciones: info,autores,etiquetas,tapa e indice		
		if(Input::has('modificar')){
			switch (Input::get('modificar')) {
				case 'info':		$this->modificarInfo($id,Input::all());
									break;
				case 'autores':		$this->modificarInfo($id,Input::all());
									break;
				case 'etiquetas':	$this->modificarInfo($id,Input::all());
									break;
				case 'tapa':		$this->modificarInfo($id,Input::all());
									break;
				case 'indice':		$this->modificarInfo($id,Input::all());
									break;				
				default: 			return Redirect::back()->withErrors('Lo que intentas modificar no es válido');
			}		
			return Redirect::to('/admin/libros/'.$id.'/modificar#'.Input::get('modificar'));
		}
		else{
			return Redirect::back()->withErrors('Error en el envío del formulario o este no es válido.');
		}
	}
	
	
	public function baja($id){
		//ToDo: Proteger este metodo
		
		//Criterio: Si el libro no fue vendido, se puede elimar completamente del Sistema, incluyendo archivos.
		// Sino se da de baja lógica, puiendose conservar o no sus archivos.
		//To-Do: Ex
		
		$libro= Libro::find($id);
		
		$libro->dadoDeBaja=true;
		$libro->save();
		//return Redirect::back();
		return Redirect::to('/admin/libros#area');
	}
	
	
	/*Otras Operaciones*/
	
	public function marcarComoAgotado($id){
		//ToDo: Proteger este metodo
		$libro=Libro::find($id);
		$libro->agotado=!($libro->agotado);
		$libro->save();
		//return Redirect::back();
		return Redirect::to('/admin/libros#area');
	}
	
	
	// Funciones de utilidad/privadas	
	//
	
	protected function modificarInfo($id,$datos){
		$reglasDeValidacion=[
			'isbn'=>['required','numeric','digits_between:10,13'],
			'titulo'=>['required','regex:/[a-zñÑáéíóú 0-9]+/i','min:2','max:64'],
			'editorial'=>['exists:editorial,id','required_without:editorial-otro'],
			'editorial-otro'=>['regex:/[a-zñ ]+/i','max:64','min:5','unique:editorial,nombre,1','required_without:editorial'],
			'idioma'=>['exists:idioma,id','required_without:idioma-otro'],
			'idioma-otro'=>['regex:/[a-zñÑáéíóú ]+/i','max:16','min:5','unique:idioma,nombre,1','required_without:idioma'],
			'anoDeEdicion'=>['required','numeric','between:1900,2014'],
			'hojas'=>['required','numeric','between:10,9999'],
			'precio'=>['required','regex:/^[0-9]{1,4}([.][0-9]{1,2})?$/i']
		];
		
		$validador= Validator::make($datos,$reglasDeValidacion);
		if($validador->fails()){
			return Redirect::to('/admin/libros/'.$id.'/modificar#info')->withErrors($validador);
		}
		else{
			//Actualizo el libro. LLeno un array cn los Key
			$datos=Input::only('isbn','hojas','precio');
			$datos['título']=Input::get('titulo');
			$datos['añoEdición']=Input::get('anoDeEdicion');
			
			//Resuelvo la situacion delas relaciones 1 a N:
			//Idioma:
			if(Input::has('idioma-checkbox')){
				$idiomaNuevo=Idioma::create(['nombre'=>Input::get('idioma-otro')]);
				$datos['idioma_id']=$idiomaNuevo->id;
			}
			else{
				$datos['idioma_id']=Input::get('idioma');
			}
			
			
			//Editorial:
			if(Input::has('editorial-checkbox')){
				$editorialNueva=editorial::create(['nombre'=>Input::get('editorial-otro')]);
				$datos['editorial_id']=$editorialNueva->id;
			}
			else{
				$datos['editorial_id']=Input::get('editorial');
			}
			
			//Actualizo el libro afectado:
			Libro::find($id)->fill($datos)->save();
		}
	}

	protected function modificarAutores($id,$datos){
		$reglasDeValidacion=[
			'autor'=>['array','exists:autor,id',],
			'autor-otro'=>['regex:/[a-zñÑáéíóú ]+/i','max:64','min:5','required_without:autor', 'unique:autor,nombre']
		];
		
		$validador= Validator::make($datos,$reglasDeValidacion);
		if($validador->fails()){
			return Redirect::to('/admin/libros/'.$id.'/modificar#info')->withErrors($validador);
		}
		else{
			//Actualizo el libro. LLeno un array cn los Key
			return;
		}
	}	
}

?>
