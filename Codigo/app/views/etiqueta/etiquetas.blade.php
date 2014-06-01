@extends('admin')

@section('contenido')
<h1>Gestion de etiquetas:</h1>
<h2>Etiquetas <span title="Cantidad de etiquetas en el Sistema">({{count($etiquetas)}})</span>:</h2>
<table width="50%" border="1">
	<tr>
		<th>Nombre</th>
		<th>Operaciones</th>
	</tr>
@foreach($etiquetas as $etiqueta )
{{
	'<tr>
		<td>'.$etiqueta->nombre.'</td>
		<td><a href="/admin/etiquetas/'. $etiqueta->id. '/modificar" title="Modificar esta etiqueta">Modificar</a> 
			<a href="/admin/etiquetas/'. $etiqueta->id. '/borrar" title="Borrar esta etiqueta" onclick="return confirm(\'¿Ud está seguro que desea eliminar la etiqueta \n«'. $etiqueta->nombre .'» ?\')">Eliminar</a>
		</td>
	</tr>'
	
}}
@endforeach
	
</table>	


<h2>Operaciones</h2>
<a href="/admin/etiquetas/crear" title="Agregue una nueva etiqueta al sistema">Agregar</a> 
<a href="/admin/" title="Vuelve al panel de Administración">Volver</a> 

@stop