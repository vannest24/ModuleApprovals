<?php

namespace App\Enum;

enum Status: string
{
    case PENDIENTE = 'Pendiente';
    case APROBADA = 'Aprobada';
    case RECHAZADA = 'Rechazada';
}
