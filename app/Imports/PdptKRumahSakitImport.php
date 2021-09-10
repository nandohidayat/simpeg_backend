<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use stdClass;

class PdptKRumahSakitImport implements ToCollection
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

        $keuangan = DB::table('pendapatan_pegawai')
            ->select('id_pegawai', 'keuangan')
            ->get();

        $query = DB::raw("UPDATE pendapatan_pegawai AS pp SET keuangan = c.keuangan FROM ( VALUES ");

        $i = 0;

        foreach ($rows as $row) {
            $format = new stdClass();

            foreach ($position as $k => $v) {
                if ((int)$v->sheet === 4) {
                    $format->$k = $row[$v->column] ?? 0;
                }
            }

            $nik = (int) $format->{"NIK RUMAH SAKIT"};
            $jml = (int) $format->{"JUMLAH ANGSURAN"};

            if ($nik < 5 || $jml === 0) {
                continue;
            }

            $id_pegawai = DB::table('f_data_pegawai as dp')
                ->whereRaw('dp.nik_pegawai = \'' . sprintf("%05d", (int) $nik) . '\'')
                ->select('id_pegawai')
                ->first()
                ->id_pegawai;

            $item = null;
            foreach ($keuangan as $struct) {
                if ($id_pegawai === $struct->id_pegawai) {
                    $item = $struct;
                    break;
                }
            }

            $format = (object) array_merge((array) json_decode($item->keuangan), (array) $format);

            $query .= '(\'' . $id_pegawai . '\',\'' . json_encode($format, JSON_HEX_APOS) . '\'::json), ';
        }

        $query = rtrim($query, ", ");
        $query .= " ) AS c ( id_pegawai, keuangan ) WHERE c.id_pegawai = pp.id_pegawai";

        DB::select($query);
    }
}
