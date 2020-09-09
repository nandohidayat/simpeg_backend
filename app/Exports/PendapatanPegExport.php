<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use stdClass;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PendapatanPegExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    function __construct($id_profilp, $tipe_form, $bulan_kirim)
    {
        $this->id_profilp = $id_profilp;
        if ($tipe_form == 'format_personalia') {
            $this->tipe_form = "pg.detail_personalia";
        }
        if ($tipe_form == 'format_keuangan') {
            $this->tipe_form = 'pg.detail_keuangan';
        }
        $this->bulan_kirim = $bulan_kirim;
    }

    public function query()
    {

        $query = DB::table('pendapatan_pegawai as pg')
            ->leftJoin('f_data_pegawai as dg', 'pg.id_pegawai', '=', 'dg.id_pegawai')
            ->select('dg.nik_pegawai', 'dg.nm_pegawai', $this->tipe_form . ' AS detail_pendapatan',)
            ->whereRaw("pg.id_profilp = '" . $this->id_profilp . "'")
            ->whereRaw("to_char(pg.bulan_kirim, 'MM-YYYY') = '" . $this->bulan_kirim . "'")
            ->orderBy('dg.nik_pegawai');
        return $query;
    }

    public function map($data): array
    {
        if (!is_null($data->detail_pendapatan)) {
            $d = array_values(get_object_vars(json_decode($data->detail_pendapatan)));
        } else {
            $d['status'] = "empty";
        }
        $custom = [];
        array_push($custom, $data->nik_pegawai);
        array_push($custom, $data->nm_pegawai);
        foreach ($d as $e) {
            array_push($custom, $e);
        }
        return [
            $custom
        ];
    }

    public function headings(): array
    {
        $topheader = [];
        $header = [];
        $mainheader = [];

        $query = DB::table('pendapatan_pegawai as pg')
            ->select($this->tipe_form . ' AS detail_pendapatan')
            ->whereRaw("pg.id_profilp = '" . $this->id_profilp . "'")
            ->whereRaw("to_char(pg.bulan_kirim, 'MM-YYYY') = '" . $this->bulan_kirim . "'")
            ->first();
        if (!is_null($query)) {
            $myheader =  array_keys(get_object_vars(json_decode($query->detail_pendapatan)));

            //ini untuk membuat header sesuai dengan profil pendapatan
            // $profil = M_ProfilPendapatan::where('nama_pendapatan', 'Gaji Pegawai')
            // ->get();
            // foreach($profil as $key => $value)
            // {
            //     $d = json_decode($value->format_form);
            //     foreach ($d as $key => $value1)
            //     {
            //         $value1 = (array)$value1;
            //         $new = array($key => $key) + $value1;
            //         foreach($new as $key2 => $value2)
            //         {
            //             array_push($topheader, $key2);
            //             array_push($header, $value2);
            //         }
            //     }
            // }

            foreach ($myheader as $key => $value) {
                array_push($topheader, $value);
                array_push($header, $value);
            }
            array_unshift($topheader, 'NIK', 'Nama');
            array_unshift($header, 'NIK', 'Nama');
            array_push($mainheader, $topheader);
            array_push($mainheader, $header);
        }
        return $mainheader;
    }
}
