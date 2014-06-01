<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function()
{
	return View::make('hello');
});

// URL de pruebas para saber si tenemos la BD bn conectada.
Route::get('/test', function()
{
	$admin= Usuario::first();
	return	"Existen ".Usuario::count()." usuarios en la BD ;).<br/>El 1ro  se llama «".$admin['nombre']."» y su mail es «".$admin['email']."».<br/>¿La contraseña es \"admin\"? ".(Hash::check('admin',$admin['contraseña'])? 'SI :)':'No :('.var_dump($admin));
});


//Gestion de Libros
Route::get('/admin/libros',['uses'=>'LibroController@listar']);
Route::get('/admin/libros/crear',['uses'=>'LibroController@formularioAlta']);
Route::post('/admin/libros/crear',['uses'=>'LibroController@alta']);
Route::get('/admin/libros/{id}',['uses'=>'LibroController@visualizar']);
Route::get('/admin/libros/{id}/modificar',['uses'=>'LibroController@formularioModificacion']);
Route::post('/admin/libros/{id}/modificar',['uses'=>'LibroController@modificacion']);
Route::get('/admin/libros/{id}/borrar',['uses'=>'LibroController@baja']);
Route::get('/admin/libros/{id}/agotado',['uses'=>'LibroController@marcarComoAgotado']);

//Gestion de Editorial
Route::get('/admin/editoriales',['uses'=>'EditorialController@listar']);
Route::get('/admin/editoriales/crear',['uses'=>'EditorialController@formularioAlta']);
Route::post('/admin/editoriales/crear',['uses'=>'EditorialController@alta']);
Route::get('/admin/editoriales/{id}/modificar',['uses'=>'EditorialController@formularioModificacion']);
Route::post('/admin/editoriales/{id}/modificar',['uses'=>'EditorialController@modificacion']);
Route::get('/admin/editoriales/{id}/borrar',['uses'=>'EditorialController@baja']);

//Gestion de Idioma
Route::get('/admin/idiomas',['uses'=>'IdiomaController@listar']);
Route::get('/admin/idiomas/crear',['uses'=>'IdiomaController@formularioAlta']);
Route::post('/admin/idiomas/crear',['uses'=>'IdiomaController@alta']);
Route::get('/admin/idiomas/{id}/modificar',['uses'=>'IdiomaController@formularioModificacion']);
Route::post('/admin/idiomas/{id}/modificar',['uses'=>'IdiomaController@modificacion']);
Route::get('/admin/idiomas/{id}/borrar',['uses'=>'IdiomaController@baja']);


// Agregue algo de etiquetas, chequear el post que no rompa nada

Route::get('/admin/etiquetas', array('uses' => 'EtiquetasController@mostrarEtiquetas'));
Route::get('/admin/etiquetas/crear',['uses'=>'EtiquetasController@formularioAlta']);
Route::post('/admin/etiquetas/crear',['uses'=>'EtiquetasController@altaEtiqueta']);


//Gestión de Usuarios para admin. Nuevo/Crear: funciones de prueba
Route::get('/admin/usuarios', array('uses' => 'UsuarioController@mostrarUsuarios'));
Route::get('/admin/usuarios/nuevo', array('uses' => 'UsuarioController@nuevoUsuario'));
Route::post('/admin/usuarios/crear', array('uses' => 'UsuarioController@crearUsuario'));
Route::get('/admin/usuarios/{id}', array('uses'=>'UsuarioController@verUsuario'));
Route::get('/admin/usuarios/{id}/bloquear',['uses'=>'UsuarioController@bloquearUsuario']);
Route::get('/admin/usuarios/{id}/modificar',['uses'=>'UsuarioController@modificarDatos']);
Route::post('/admin/usuarios/{id}/modificar',['uses'=>'UsuarioController@modificarUsuario']);


?>
