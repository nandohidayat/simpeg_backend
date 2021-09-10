<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use stdClass;

class PdptKeuanganImport implements ToCollection
{
    protected $profil;

    function __construct($profil)
    {
        $this->profil = $profil;
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        $position = DB::table('profil_pendapatan')
            ->where('id_profilp', $this->profil)
            ->select('format_keuangan')
            ->first()
            ->format_keuangan;

        $position = json_decode($position);

        $query = DB::raw("INSERT INTO pendapatan_pegawai (id_pegawai, keuangan) VALUES ");

        $i = 0;

        foreach ($rows as $row) {
            $format = new stdClass();

            foreach ($position as $k => $v) {
                if ((int)$v->sheet === 1) {
                    $format->$k = $row[$v->column] ?? 0;
                } else {
                    $format->$k = 0;
                }
            }

            $nik = (int) $format->NIK;
            $jml = (int) $format->{"JUMLAH DITERIMA"};

            if ($nik < 5 || $jml === 0) {
                continue;
            }

            $id_pegawai = DB::table('f_data_pegawai as dp')
                ->whereRaw('dp.nik_pegawai = \'' . sprintf("%05d", (int) $nik) . '\'')
                ->select('id_pegawai')
                ->first()
                ->id_pegawai;

            $query .= '(\'' . $id_pegawai . '\',\'' . json_encode($format, JSON_HEX_APOS) . '\'), ';
        }

        $query = rtrim($query, ", ");
        $query .= " ON CONFLICT ON CONSTRAINT pendapatan_pegawai_pkey DO UPDATE SET keuangan = excluded.keuangan";

        DB::select($query);
    }
}
