<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PendapatanImport implements WithMultipleSheets
{
    protected $bulan, $profil, $type;

    function __construct($bulan, $profil, $type)
    {
        $this->bulan = $bulan;
        $this->profil = $profil;
        $this->type = $type;
    }

    public function sheets(): array
    {
        if ($this->type === 'format_personalia') {
            return [
                new PdptPersonaliaImport($this->bulan, $this->profil)
            ];
        } else if ($this->type === 'format_keuangan') {
            return [];
        }

        return [];
    }
}
