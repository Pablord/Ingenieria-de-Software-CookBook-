@extends('admin')

@section('contenido')
<h1>Gestión de Autores</h1>
<h2>Modificar «{{$autor->nombre}}»</h2>
@if($errors->has('nombre'))
<div class="mensajeDeError">
	<p>Error en el nombre ingresado:</p>
	<ul>
		@foreach(($errors->get('nombre')) as $mensajeDeError)
		<li>{{$mensajeDeError}}</li>
		@endforeach
	</ul>
</div>
@endif
<form method="post" action="/admin/autores/{{$autor->id}}/modificar">
	<input name="nombre" placeholder="Solo ingrese letras" value="{{Input::old('nombre',$autor->nombre)}}"/>
	<br/><br/>
	<input type="submit" value="Modificar" title="Modifica este autor" />		
	<a href="/admin/autores/" style="text-decoration:none;">
		<input type="button" value="Cancelar" title="Cancela la operacion"/>
	</a>
</form>
@stop