<?php

namespace App\Exceptions;

use RuntimeException;

class JadwalBentrokException extends RuntimeException
{
    /** @param array<int, array{jenis:string,pesan:string,ids:array,tanggal_list:array}> $konflik */
    public function __construct(public array $konflik)
    {
        parent::__construct('Jadwal bertabrakan.');
    }
}
