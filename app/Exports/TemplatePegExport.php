<?php
namespace App\Exports;

use DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TemplatePegExport implements FromQuery, WithHeadings, ShouldAutoSize
{
    use Exportable;

    function __construct($id_profilp,$tipe_form) {
        $this->id_profilp = $id_profilp;
        $this->tipe_form = $tipe_form;
    }

    public function query()
    {
        $query = DB::table('f_data_pegawai as dg')
        ->select('dg.nik_pegawai', 'dg.nm_pegawai')
        ->where('is_active', true)
        ->orderBy('nik_pegawai');
        return $query;
    }

    public function headings(): array
    {
        $profil = DB::table('profil_pendapatan')
        ->select('id_profilp','nama_pendapatan',$this->tipe_form.' AS format_pendapatan','format_total')
        ->where('id_profilp', $this->id_profilp)
        ->get();
        $topheader = [];
        $header = [];
        $mainheader = [];
        // dd($profil);
        // var_dump($profil);
        foreach($profil as $key => $value)
        {
            $d = json_decode($value->format_pendapatan);
            foreach ($d as $key => $value1)
            {
                $value1 = (array)$value1;
                $new = array($key => $key) + $value1;
                foreach($new as $key2 => $value2)
                {
                    array_push($topheader, $key2);
                    array_push($header, $value2);
                }
            }
            // sementara di off kan
            // $total = json_decode($value->format_total);
            // foreach ($total as $key => $value2)
            // {
            //     $value2 = (array)$value2;
            //     $new = array($key => $key) + $value2;
            //     foreach($new as $key2 => $value2)
            //     {
            //         array_push($topheader, $key2);
            //         array_push($header, $value2);
            //     }
            // }
            
        }
        array_unshift($topheader,'NIK','Nama');
        array_unshift($header,'NIK','Nama');
        array_push($mainheader, $topheader);
        array_push($mainheader, $header);
        return $mainheader;
    }
}

?>