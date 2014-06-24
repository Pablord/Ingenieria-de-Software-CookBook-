@extends('admin')

@section('menuActivo')
menuActivo='pedidos'
@stop

@section('contenido')
<h2>Pedidos vigentes ({{count($pedidos)}}):</h2>

{{-- ToDo: Funciones varias (comprobante, estado, etc). --}}

@if (count($pedidos) >= 1)
	{{-- "Declara" una variable contador --}}
	@if ($cont = '1') @endif
	@foreach($pedidos as $pedido)
		<h3>Pedido {{$cont}}</h3>
		<table width="100%">
			<tr>
				<td width="8%"><strong>Fecha:</strong> {{$pedido->fecha}}</td>
				<td width="10%"><strong>Cliente:</strong> <a href="/admin/usuarios/{{ $pedido->usuario->id }}/ver" title="Ver datos">{{$pedido->usuario->nombre}} {{$pedido->usuario->apellido}}</a>
				@if (($pedido->usuario->bloqueado) OR ($pedido->usuario->dadoDeBaja))
					<strong><font color="red"><span class="tooltip" title="El usuario ha sido bloqueado o dado de baja. Usted deberá cambiar el estado del pedido manualmente.">- Inactivo [?]</span></font></strong>
				@endif
				</td>
				<td width="8%"><strong>Estado:</strong> @if($pedido->estado == "p")
												           Pendiente
														@endif   
											            @if($pedido->estado == "e")
												            Enviado
											            @endif
														@if($pedido->estado == "f")
														    Finalizado
														@endif
				</td>
				<td width="30%"><strong>Funciones:</strong> <a href="/admin/pedidos/{{ $pedido->id }}/ver" title="Visualizar datos del pedido">Ver Detalle</a> | <a href="/404" title="Visualizar comprobante">Ver Comprobante</a> | 
				@if ($pedido->estado == "p")
					<a href="/pedidos/{{ $pedido->id }}/cambiar" title="Cambiar estado">Cambiar a Enviado</a></td>
				@else
				   @if($pedido->estado == "e")
					<a href="/pedidos/{{ $pedido->id }}/cambiar" title="Cambiar estado" onclick="return confirm('¿Le ha llegado el pedido al cliente? Si no es así, aguarde una confirmación antes de cambiar el estado.\r\n Esta operación no se puede deshacer.')">Cambiar a Finalizado</a></td>
				   @endif
				@endif
			</tr>
		</table>
		<h2></h2>
		{{-- "Incrementa" la variable contador --}}
		@if ($cont = $cont+1) @endif
	@endforeach
<h2>Funciones:</h2>
Pedidos ordenados por fechas ascendentes. <a href="/admin/pedidos/ordenD" title="Cambiar orden">Cambiar a orden descendente.</a></td></br>
<form method="get" action="/admin/pedidos/">
  <select name="filtro" style="padding:2px;width:90px;display:inline;">
        <option value="nombre">Nombre</option>
        <option value="estado">Estado</option>
  </select>
  <input name="valor" size="25" value=""/>
  <input value="Buscar" type="submit"/> <span class="tooltip" title="El estado a buscar debe ser exacto, no así el nombre o apellido.">[?]</span>
</form>
* Al buscar por Estado: </br>
Ingrese 'p' para mostrar todos los pedidos pendientes.</br>
Ingrese 'e' para mostrar todos los pedidos enviados.</br>
Ingrese 'f' para mostrar todos los pedidos finalizados.</br>

@else
	<div class="mensaje mensaje-notificacion">No hay pedidos que gestionar o no se encontraron en la búsqueda. </br>
	<a href="/admin/pedidos/" title="Regresar a la gestión de los pedidos">Haga click aquí para regresar a la gestión de los pedidos.</a></div>
@endif
@stop


