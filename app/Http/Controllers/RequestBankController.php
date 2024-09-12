<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RequestBankController extends Controller
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

    public function cuentas(){

        $data = array();
        $cliente = DB::table("cliente")->get();
        echo $data[] = json_encode($cliente);
    }

    public function procesar_deposito(Request $request, $uid){
        $data = array();
        $cliente = DB::table("cliente")->where("uid",$uid)->first();
        if (empty($cliente)){
            $data = array(
                    'code' => '403',
                    'msg' => 'Cliente no existe'
            );
            echo json_encode($data);
            exit();
        }
        
        $saldo_actual = $cliente->saldo;
        $saldo_total = $saldo_actual + $request['monto'];
        DB::table("cliente")->where("uid",$uid)->update(['saldo' => $saldo_total]);

        DB::table("historial_transaccion")->insert(
            ['uid' => uniqid(),
            'id_cliente' => $cliente->id,
            'tipo' => 'depósito',
            'monto' => $request['monto']
            ,'comision' => 0
            ]
        );

        $data = array(
            'code' => '200',
            'msg' => 'Deposito realizado con exito.'
        );

        echo json_encode($data);

    }

    public function procesar_retiro(Request $request, $uid){
        $data = array();
        $cliente = DB::table("cliente")->where("uid",$uid)->first();
        if (empty($cliente)){
            $data = array(
                    'code' => '403',
                    'msg' => 'Cliente no existe'
            );
            echo json_encode($data);
            exit();
        }
        
        $saldo_actual = $cliente->saldo;



        if($cliente->tipo_cuenta == 'CuentaEstandar'){
            if($saldo_actual < 100){
                $data = array(
                    'code' => '403',
                    'msg' => 'Saldo mínimo debe ser mayor a $100.'
            );
            echo json_encode($data);
            exit();
            }


            $comision_retiro = ($request['monto'] * 2) / 100;
        }else{
            $comision_retiro = 0;
        }

        $monto_total = $request['monto'] + $comision_retiro;

        if($saldo_actual < $monto_total){
            $data = array(
                'code' => '403',
                'msg' => 'No cuenta con saldo suficiente para realizar retiro.'
        );
        echo json_encode($data);
        exit();
        }
        
        $saldo_total = $saldo_actual - $monto_total;
        DB::table("cliente")->where("uid",$uid)->update(['saldo' => $saldo_total]);

        DB::table("historial_transaccion")->insert(
            ['uid' => uniqid(),
            'id_cliente' => $cliente->id,
            'tipo' => 'retiro',
            'monto' => $request['monto'],
            'comision' => $comision_retiro
            ]
        );

        $data = array(
            'code' => '200',
            'msg' => 'Retiro realizado con exito.'
        );

        echo json_encode($data);

    }

    public function procesar_transferencia(Request $request, $uid){
        $data = array();
        $cliente = DB::table("cliente")->where("uid",$uid)->first();
        if (empty($cliente)){
            $data = array(
                    'code' => '403',
                    'msg' => 'Cliente no existe.'
            );
            echo json_encode($data);
            exit();
        }

        $cliente_dest = DB::table("cliente")->where("uid",$request['cuentaDestinoId'])->first();
        if (empty($cliente_dest)){
            $data = array(
                    'code' => '403',
                    'msg' => 'Cliente destino no existe.'
            );
            echo json_encode($data);
            exit();
        }

        $comision_transferencia = 0;

        if($cliente->tipo_cuenta == 'CuentaEstandar')
            $comision_transferencia = ($request['monto'] * 1) / 100;

        $monto_total = $request['monto'] + $comision_transferencia;

        if($cliente->saldo < $monto_total){
            $data = array(
                'code' => '403',
                'msg' => 'No cuenta con saldo suficiente para realizar transacción.'
        );
        echo json_encode($data);
        exit();
        }


        //Debitar el monto a la cuenta del cliente
        $saldo_actual = $cliente->saldo;
        $saldo_total = $saldo_actual - $monto_total;
        DB::table("cliente")->where("uid",$cliente->uid)->update(['saldo' => $saldo_total]);


        //Recargar el dinero a la cuenta destino
        $saldo_actual_dest = $cliente_dest->saldo;
        $saldo_total_dest = $saldo_actual_dest + $request['monto'];
        DB::table("cliente")->where("uid",$cliente_dest->uid)->update(['saldo' => $saldo_total_dest]);


        DB::table("historial_transaccion")->insert(
            ['uid' => uniqid(),
            'id_cliente' => $cliente->id,
            'tipo' => 'transferencia',
            'monto' => $request['monto'],
            'id_cliente_destino' => $cliente_dest->id,
            'comision' => $comision_transferencia
            ]
        );

        $data = array(
            'code' => '200',
            'msg' => 'Transferencia realizado con exito.'
        );

        echo json_encode($data);

    }
    
    public function ver_detalle_cuenta($uid){

        $data = array();
        $cliente = DB::table("cliente")->where('uid',$uid)->first();

        if(empty($cliente)){
            $data = array(
                'code' => '403',
                'msg' => 'Cliente no existe.'
            );
        }


        $historial = DB::table("historial_transaccion")->where('id_cliente',$cliente->id)->get();

        $data[] = array(
                    'id' => $cliente->id,
                    'saldo' => $cliente->saldo,
                    'historialTransacciones' => $historial
                );

         echo json_encode($data);
    }

}
