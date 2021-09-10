<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use stdClass;

class PdptPersonaliaImport implements ToCollection
{
    protected $bulan, $profil;

    function __construct($bulan, $profil)
    {
        $this->bulan = $bulan;
        $this->profil = $profil;
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        $position = DB::table('profil_pendapatan')
            ->where('id_profilp', $this->profil)
            ->select('format_personalia')
            ->first()
            ->format_personalia;

        $position = json_decode($position);

        $query = DB::raw("INSERT INTO pendapatan_pegawai (id_pegawai, id_profilp, bulan, personalia) VALUES ");

        foreach ($rows as $row) {
            $format = new stdClass();

            foreach ($position as $k => $v) {
                $format->$k = $row[$v->column] ?? null;
            }

            $nik = (int) explode('.', $format->NIK)[0];
            if ($nik < 5) {
                continue;
            }

            $id_pegawai = DB::table('f_data_pegawai as dp')
                ->whereRaw('dp.nik_pegawai = \'' . sprintf("%05d", (int) $nik) . '\'')
                ->select('id_pegawai')
                ->first()
                ->id_pegawai;

            $query .= '(\'' . $id_pegawai . '\', \'' . $this->profil . '\', \'' . Carbon::create($this->bulan) . '\',\'' . json_encode($format, JSON_HEX_APOS) . '\'), ';
        }

        $query = rtrim($query, ", ");

        DB::select('DELETE FROM pendapatan_pegawai');
        DB::select($query);
    }
}
