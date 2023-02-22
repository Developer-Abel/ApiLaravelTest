<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacturaController extends Controller{
    
    function getfacturaAll(){
        $listado = DB::select('SELECT 
                facturas.id,
                facturas.codigo AS factura,
                facturas.fecha,
                (SELECT DISTINCT codigo FROM pedidos WHERE id = pedido.pedido_id ) AS pedido,
                total AS monto,
                (SELECT DISTINCT razon_social FROM clientes WHERE id = pedido.cliente_id) AS razon_social
            FROM facturas 
            INNER JOIN (
                SELECT cliente_id, pedido_id, SUM(subtotal) AS total FROM (
                    SELECT 
                        pedidos_items.pedido_id, 
                        pedidos.cliente_id,
                        (SUM(pedidos_items.cantidad) * SUM(articulos.precio)) AS subtotal
                    FROM pedidos_items
                    INNER JOIN articulos ON articulos.id = pedidos_items.articulo_id
                    INNER JOIN pedidos ON pedidos.id = pedidos_items.pedido_id
                    GROUP BY pedidos_items.id
                ) subtotal
                GROUP BY pedido_id,cliente_id
            ) pedido ON pedido.pedido_id = facturas.pedido_id');

        return $listado;
    }

    function addPedido(Request $request){
        $clienteid =  $request->clienteid;
        $codigo    =  $request->codigo;
        $fecha     =  $request->fecha;

        $articulo_id = $request->articulo_id;
        $cantidad    = $request->cantidad;

        $insertPedido = DB::table('pedidos')->insert([
                'cliente_id' => $clienteid,
                'codigo'    => $codigo,
                'fecha'     => $fecha
            ]);
        if ($insertPedido) {
            $SelectId = DB::SELECT('SELECT MAX(id) AS id FROM pedidos');
            $pedido_id= $SelectId[0]->id;
            $insertDetalle = DB::table('pedidos_items')->insert([
                'pedido_id'   => $pedido_id,
                'articulo_id' => $articulo_id,
                'cantidad'    => $cantidad
            ]);
            if ($insertDetalle) {
                return ["message" => "success", "errorid" => 0];
            }
        }

        return ["message" => "error", "errorid" => 100];
    }
    function addFactura(Request $request){
        $pedido_id = $request->pedido_id;
        $codigo    = $request->codigo;
        $fecha     = $request->fecha;

        $insertfactura = DB::table('facturas')->insert([
            'codigo'    => $codigo,
            'pedido_id' => $pedido_id,
            'fecha'     => $fecha
        ]);
        if ($insertfactura) {
            $SelectId = DB::SELECT('SELECT MAX(id) AS id FROM facturas');
            $factura_id= $SelectId[0]->id;

            $Select = DB::SELECT('SELECT articulo_id, cantidad FROM pedidos_items where id = ?', [$pedido_id]);
            $articulo_id = $Select[0]->articulo_id;
            $cantidad    = $Select[0]->cantidad;

            $insertfacturadet = DB::table('facturas_items')->insert([
                'factura_id'   => $factura_id,
                'articulo_id'  => $articulo_id,
                'cantidad'     => $cantidad
            ]);
            if ($insertfacturadet) {
                return ["message" => "success", "errorid" => 0];
            }
        }
        return ["message" => "error", "errorid" => 100];
    }

    function findfactura(Request $request){
        $codigo = $request->codigo;
        $Select = DB::SELECT('SELECT * FROM facturas where codigo = ?', [$codigo]);
        $id_factura = $Select[0]->id;

        $listado = DB::select('SELECT 
                facturas.id,
                facturas.codigo AS factura,
                facturas.fecha,
                (SELECT DISTINCT codigo FROM pedidos WHERE id = pedido.pedido_id ) AS pedido,
                total AS monto,
                (SELECT DISTINCT razon_social FROM clientes WHERE id = pedido.cliente_id) AS razon_social
            FROM facturas 
            INNER JOIN (
                SELECT cliente_id, pedido_id, SUM(subtotal) AS total FROM (
                    SELECT 
                        pedidos_items.pedido_id, 
                        pedidos.cliente_id,
                        (SUM(pedidos_items.cantidad) * SUM(articulos.precio)) AS subtotal
                    FROM pedidos_items
                    INNER JOIN articulos ON articulos.id = pedidos_items.articulo_id
                    INNER JOIN pedidos ON pedidos.id = pedidos_items.pedido_id
                    GROUP BY pedidos_items.id
                ) subtotal
                GROUP BY pedido_id,cliente_id
            ) pedido ON pedido.pedido_id = facturas.pedido_id
            WHERE facturas.id = ?', [$id_factura]);
        return $listado;
    }
}
