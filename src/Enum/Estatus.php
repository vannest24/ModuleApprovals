<?php

namespace App\Enum;

enum Estatus: string
{
    case ABIERTA = 'Abierta';
    case APROBADA = 'Aprobada';
    case RECHAZADA = 'Rechazada';
}
