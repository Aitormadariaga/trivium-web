<?php

namespace App\Enum;

enum EstadoBackup: string
{
    case MODIFICADO = 'Modificado';
    //   ↑             ↑
    //   Nombre        Valor que se guarda en MariaDB
    //   en PHP        y se envía en el JSON

    case ELIMINADO  = 'Eliminado';
}