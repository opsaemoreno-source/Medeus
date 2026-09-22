<?php

namespace App\Services\Evolok;

class EvolokComprasMapper
{
    public const COLUMNS = 'GUID,Attributes,ProfileBrand,visitor_country,email_address,first_name,last_name,PurchaseID,ProductName,ProductDisplayName,PaymentPlanName';

    public static function map(array $item): array
    {
        $rows = [];
        $idUsuario = $item['GUID'] ?? null;

        if (!isset($item['Purchases']) || !is_array($item['Purchases'])) {
            return $rows;
        }

        foreach ($item['Purchases'] as $purchase) {
            $orderItem = $purchase['orderItem'] ?? [];
            $product   = $orderItem['product'] ?? [];
            $plan      = $orderItem['paymentPlan'] ?? [];
            $freq      = $plan['frequency'] ?? [];
            $currency  = $plan['currency'] ?? [];
            $discount  = $plan['discount'] ?? [];
            $sub       = $purchase['subscription'] ?? [];

            $rows[] = [
                'idUsuario'              => $idUsuario,
                'idCompra'               => $purchase['id'] ?? null,

                'idProducto'             => $product['id'] ?? null,
                'nombreProducto'         => $product['name'] ?? null,
                'nombreProductoDisplay'  => $product['displayName'] ?? null,
                'tipoProducto'           => $product['type'] ?? null,
                'tipoProducto2'          => $product['productType'] ?? null,
                'fechaCreacion'          => EvolokDates::transform($product['createdDate'] ?? null),
                'fechaModificacion'      => EvolokDates::transform($product['lastModifiedDate'] ?? null),

                'idPlanPago'             => $plan['id'] ?? null,
                'nombrePlanPago'         => $plan['name'] ?? null,
                'nombrePlanPagoDisplay'  => $plan['displayName'] ?? null,

                'idFrecuencia'           => $freq['id'] ?? null,
                'noDias'                 => $freq['numberOfDays'] ?? null,
                'tipoPeriodo'            => $freq['periodType'] ?? null,
                'nombreFrecuencia'       => $freq['displayName'] ?? null,

                'precio'                 => $plan['price'] ?? null,
                'precioFinal'            => $plan['finalPrice'] ?? null,

                'idMoneda'               => $currency['id'] ?? null,

                'idDescuento'            => $discount['id'] ?? null,
                'tipoDescuento'          => $discount['type'] ?? null,
                'valorDescuento'         => $discount['value'] ?? null,
                'codigosPromocion'       => isset($plan['promoCodes']) ? json_encode($plan['promoCodes']) : null,

                'cantidad'               => $orderItem['quantity'] ?? null,
                'fechaInicioEntrega'     => EvolokDates::transform($purchase['startDeliveryDate'] ?? null),

                'fechaInicioSuscripcion' => EvolokDates::transform($sub['startDate'] ?? null),
                'fechaFinalSuscripcion'  => EvolokDates::transform($sub['endDate'] ?? null),

                'tipoPago'               => $purchase['paymentType'] ?? null,
                'tipoOrden'              => $purchase['orderType'] ?? null,
                'marca'                  => $purchase['brand'] ?? null,
                'canal'                  => $purchase['channel'] ?? null,
                'estado'                 => $purchase['status'] ?? null,
                'ultimaFechaPago'        => EvolokDates::transform($purchase['lastPaymentDate'] ?? null),
                'proximaFechaPago'       => EvolokDates::transform($purchase['nextPaymentDate'] ?? null),
                'fechaPedidoCancelacion' => EvolokDates::transform($purchase['cancelationRequestDate'] ?? null),
            ];
        }

        return $rows;
    }
}
