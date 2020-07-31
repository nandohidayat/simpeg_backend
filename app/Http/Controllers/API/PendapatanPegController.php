<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Route;
use App\Imports\PendapatanPegImport;
use App\Exports\PendapatanPegExport;
use App\Exports\TemplatePegExport;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use stdClass;
use DOMDocument;
// use App\Helper\Myhelper;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PendapatanPegController extends Controller
{
    
    public function exportTemplatePeg()
    {
        $id_profilp = request()->get('id_profilp');
        $tipe_form = request()->get('tipe_form');
        $nama_file = DB::table('profil_pendapatan')->where('id_profilp', $id_profilp)->value('nama_pendapatan');
        $nama_file = 'Template '.$nama_file.' '.ucfirst($tipe_form).'.xlsx';
        return (new TemplatePegExport($id_profilp, $tipe_form))->download($nama_file);
    }

    public function exportPendapatanPeg()
    {
        $id_profilp = request()->get('id_profilp');
        $tipe_form = request()->get('tipe_form');
        $bulan_kirim = request()->get('bulan_kirim');
        $nama_file = DB::table('profil_pendapatan')->where('id_profilp', $id_profilp)->value('nama_pendapatan');
        $nama_file =  $nama_file.' '.ucfirst($tipe_form).' '.$bulan_kirim.'.xlsx';
        return (new PendapatanPegExport($id_profilp, $tipe_form, $bulan_kirim))->download($nama_file);
    }

    public function importPendapatanPeg() 
    {
        /**
         * 1. Saat upload file excel ada pilihan personalia / keuangan
         * 2. Saat upload ada proses pengecekan apakah bulan dan tipe profil sudah ada di tabel
         * 3. Jika sudah ada datanya dilakukan update
         * 4. Jika belum dilakukan proses insert
         */

        $file = request()->file('file');
        $id_profilp = request()->post('id_profilp');
        $bulan_kirim = request()->post('bulan_kirim');
        if(request()->post('tipe_form') == 'personalia') {
            $tipe_form = "detail_personalia";
        }
        if(request()->post('tipe_form') == 'keuangan') {
            $tipe_form = 'detail_keuangan';
        }
        $time=time();
        $hasil_import = Excel::toArray(new PendapatanPegImport, $file);
        $template_total = DB::table('profil_pendapatan')
        ->where('id_profilp', $id_profilp)
        ->value('format_total');
        $arr_template = [];
        foreach($hasil_import as $key => $row)
        {
            /**
             * unset($row) untuk menghapus header dalam hasil import excel, 
             * yang selanjutnya menyisakan row data gaji pegawai (tanpa header)
             */
            $head = $row[0];
            unset($row[0]);
            unset($row[1]);

            
            foreach($row as $key2 => $value)
            {
                $pegawai = new stdClass();
                for ($i = 0; $i <= 2; $i++) 
                {
                    $obj = $head[$i];
                    $pegawai->$obj = $value[$i];
                } 
                /**
                 * fungsi loop for diatas untuk mengambil nik pegawai 
                 * dan selanjutnya diteruskan kedalam parameter query dibawah
                 */
                $pegawai = get_object_vars($pegawai);
                $id_pegawai = DB::table('f_data_pegawai')->where('nik_pegawai', $pegawai['NIK'])->value('id_pegawai');
                if(!is_null($id_pegawai))
                {
                    $data = new stdClass();
                    for ($i = 2; $i <= count($head)-1; $i++) 
                    {
                        $obj = $head[$i];
                        $data->$obj = $value[$i];
                    } 
                    $data = json_encode($data);
                    // echo $data;
                    // die();
                    
                    /**
                     * jika tidak ada id_profilp dan bulan kirim, maka lakukan insert
                     * jika ada id_profilp dan bulan kirim, maka lakukan update sesuai dengan tipe form
                     */

                    $cek_data = DB::table('pendapatan_pegawai')
                    ->where('nik_pegawai', $pegawai['NIK'])
                    ->where('id_profilp', $id_profilp)
                    ->whereRaw("to_char(bulan_kirim, 'MM-YYYY') = '".$bulan_kirim."'")
                    ->value('id_pegawai');
                    
                    if(is_null($cek_data)) 
                    {
                        DB::insert(DB::raw("INSERT INTO pendapatan_pegawai (id_profilp, id_pegawai, nik_pegawai, bulan_kirim, ".$tipe_form.") VALUES
                            ('".$id_profilp."', '".$id_pegawai."', '".$pegawai['NIK']."', TO_DATE('".$bulan_kirim."', 'MM-YYYY'), '".$data."')"));
                    }else 
                    {
                        DB::table('pendapatan_pegawai')
                        ->where('id_profilp', $id_profilp)
                        ->whereRaw("to_char(bulan_kirim, 'MM-YYYY') = '".$bulan_kirim."'")
                        ->where('id_pegawai', $id_pegawai)
                        ->update([$tipe_form => $data]);
                    }
                    $data = DB::table('pendapatan_pegawai')
                    ->select('detail_personalia', 'detail_keuangan')
                    ->where('nik_pegawai', $pegawai['NIK'])
                    ->where('id_profilp', $id_profilp)
                    ->whereRaw("to_char(bulan_kirim, 'MM-YYYY') = '".$bulan_kirim."'")
                    ->first();
                    $personalia = json_decode($data->detail_personalia,true);
                    $keuangan = json_decode($data->detail_keuangan,true);
                    $total_hitung = 0;
                    foreach($personalia as $key =>$total)
                    {
                        if(strpos($key, 'P:') !== false)
                        {
                            $total_hitung += (int)$total;
                        }
                        if(strpos($key, 'M:') !== false)
                        {
                            $total_hitung -= (int)$total;
                        }
                    }
                    foreach($keuangan as $key =>$total)
                    {
                        if(strpos($key, 'P:') !== false)
                        {
                            $total_hitung += (int)$total;
                        }
                        if(strpos($key, 'M:') !== false)
                        {
                            $total_hitung -= (int)$total;
                        }
                    }
                    $template_total2 = json_decode($template_total,true);
                    
                    foreach($template_total2 as $key => $total)
                    {
                        
                        $arr_template[$key] = null;
                        foreach($total as $key2 => $value)
                        {
                            if(strpos($key2, 'T:') !== false)
                            {
                                $arr_template[$key2] = $total_hitung;
                            }
                        }
                    }
                    $template_total2 = json_encode($arr_template);
                    DB::table('pendapatan_pegawai')
                        ->where('id_profilp', $id_profilp)
                        ->whereRaw("to_char(bulan_kirim, 'MM-YYYY') = '".$bulan_kirim."'")
                        ->where('id_pegawai', $id_pegawai)
                        ->update(['detail_total' => $template_total2]);
                } 
            }
        }
        // return response()->json(["status" => "success"], 201);
    }


    public function buatEmailSimultan(Request $request) 
    {
        $id_profilp = $request->post('id_profilp');
        $bulan_premi = $request->post('dateunggah');
        $data = DB::table('pendapatan_pegawai as pg')
            ->leftJoin('profil_pendapatan as pp', 'pp.id_profilp' , '=', 'pg.id_profilp')
            ->leftJoin('data_pegawai as dg', 'pg.id_pegawai', '=', 'dg.id_pegawai')
            ->leftJoin('department as d', 'd.id_dept', '=',DB::raw('ANY(dg.id_dept)'))
            ->select('pg.id_pendapatan','pg.bulan_kirim', 'pp.nama_pendapatan', DB::raw("to_char(pg.bulan_kirim, 'MM-YYYY') AS bulan_kirim2"), 'dg.nm_pegawai', DB::raw("string_agg(d.nm_dept,':') AS nm_dept"), 'dg.email_pegawai')
            ->where("pg.id_profilp", $id_profilp)
            ->whereRaw("to_char(pg.bulan_kirim, 'MM-YYYY') = '".$bulan_premi."'")
            ->whereRaw("dg.email_pegawai NOT LIKE ''")
            ->groupBy('pg.id_pendapatan','dg.nm_pegawai','dg.email_pegawai','pp.nama_pendapatan')
            ->get();
        $helper = new Myhelper();
        foreach ($data as $key => $value)
        {
            DB::table('kirim_email')->insert([
                'penerima_email'    => $value->email_pegawai,
                'subjek_email'      => $value->nama_pendapatan.' '.$this->bulan_indo($value->bulan_kirim2),
                'id_pendapatan'     => $value->id_pendapatan,
                'insertintodb'      => 'NOW()'              
                ]);
        }
        //Membuat cron job
        $output = shell_exec('sudo crontab -l -u www-data | grep -i "wget http://localhost/simpeg2/kirim_email"');
        if(is_null($output))
        {
            $cron = shell_exec('(sudo crontab -u www-data -l ; echo "* * * * * wget http://localhost/simpeg2/kirim_email") | sudo crontab -u www-data -');
        }
        return response()->json(["status" => "success"], 201);
    }

    public function kirimEmail(Request $request) 
    {
        $data = DB::table('kirim_email as ke')
            ->leftJoin('pendapatan_pegawai as pg', 'ke.id_pendapatan', '=', 'pg.id_pendapatan')
            ->leftJoin('profil_pendapatan as pp', 'pp.id_profilp' , '=', 'pg.id_profilp')
            ->leftJoin('f_data_pegawai as dg', 'pg.id_pegawai', '=', 'dg.id_pegawai')
            ->leftJoin('f_department as d', 'd.id_dept', '=',DB::raw('ANY(dg.id_dept)'))
            ->select('ke.id_email', 'ke.subjek_email', 'pg.*', 'pp.nama_pendapatan', DB::raw("to_char(pg.bulan_kirim, 'MM-YYYY') AS bulan_kirim2"), 'dg.nm_pegawai', DB::raw("string_agg(d.nm_dept,':') AS nm_dept"), 'dg.email_pegawai')
            ->whereRaw('sendingdatetime is null')
            ->whereRaw("status_email is null")
            ->groupBy('pg.id_pendapatan','dg.nm_pegawai','dg.email_pegawai','ke.id_email','pp.nama_pendapatan')
            ->limit(5)
            ->get();
            // var_dump($data);
            foreach ($data as $key => $value)
            {
               $this->kirimSlip($value); 
            }

            if(!isset($data[0]))
            {
                /**
                 * Konfig wget di crontab diganti menggunakan fitur Console laravel
                 */
                // tidak ada antrian email, hapus cron job
                $output = shell_exec('sudo crontab -l -u www-data | grep -i "wget http://localhost/simpeg2/kirim_email"');
                if(!is_null($output))
                {
                    $remove_cron = shell_exec("sudo crontab -u www-data -l | grep -v 'wget http://localhost/simpeg2/kirim_email' | crontab -u www-data -");
                }
            }
    }

    public function kirimSlip($data){
        $data= json_encode($data);
        //Cek string untuk validasi XML
        
        $data = strip_tags(preg_replace("/&(?!#?[a-z0-9]+;)/", "&amp;",$data));
        
        $data = json_decode($data);
        $data = $data[0];
        // var_dump($data[0]->nama_pendapata);
        // var_dump($data);echo '<br>';echo '<br>';
        $nama_slip = $data->nama_pendapatan.'<br>'.$this->bulan_indo($data->bulan_kirim2);
        
        $tr = "<tr>";
        $tr .= "<td align='left' width='50%'>NIK</td>";
        $tr .= "<td>: ".$data->nik_pegawai."</td>";
        $tr .= "</tr>";

        $tr .= "<tr>";
        $tr .= "<td align='left' width='50%'>Nama Pegawai</td>";
        $tr .= "<td>: ".$data->nm_pegawai."</td>";
        $tr .= "</tr>";

        $tr .= "<tr>";
        $tr .= "<td align='left' width='50%'>Bagian</td>";
        $tr .= "<td>: ".$data->nm_dept."</td>";
        $tr .= "</tr>";
        $key_perso = array_keys(get_object_vars(json_decode($data->detail_personalia)));
        $konten_perso = array_values(get_object_vars(json_decode($data->detail_personalia)));

        $key_keu = array_keys(get_object_vars(json_decode($data->detail_keuangan)));
        $konten_keu = array_values(get_object_vars(json_decode($data->detail_keuangan)));

        $key_total = array_keys(get_object_vars(json_decode($data->detail_total)));
        $konten_total = array_values(get_object_vars(json_decode($data->detail_total)));
        
        $keys = array_merge($key_perso, $key_keu, $key_total);
        $kontens = array_merge($konten_perso, $konten_keu, $konten_total);
        // dd($keys);
        for ($i = 0; $i <= count($keys)-1; $i++) 
        {
            if(strpos($keys[$i], 'N:') !== false)
            {
                $keys[$i] = str_replace("N:", "", $keys[$i]);
                $kontens[$i] = $this->rupiah($kontens[$i]);
            }
            if(strpos($keys[$i], 'P:') !== false)
            {
                $keys[$i] = str_replace("P:", "", $keys[$i]);
            }
            if(strpos($keys[$i], 'M:') !== false)
            {
                $keys[$i] = str_replace("M:", "", $keys[$i]);
            }
            if(is_null($kontens[$i]) && strpos($keys[$i], 'K:') !== false)
            {
                $keys[$i] = '';
            }elseif(is_null($kontens[$i]) && strpos($keys[$i], 'H:') !== false)
            {
                $keys[$i] = str_replace("H:", "", $keys[$i]);
                $tr .= "<tr>";
                $tr .= "<td colspan='2' style='text-align: center; border: 1px solid black ;'>".$keys[$i]."</td>";
                $tr .= "</tr>";
            }else
            {
                if(strpos($keys[$i], 'T:') !== false)
                {
                    $keys[$i] = str_replace("T:", "", $keys[$i]);
                    $tr .= "<tr>";
                    $tr .= "<td align='left' width='50%'>".$keys[$i]."</td>";
                    $tr .= "<td style='font-weight: bold; font-size: 25px;'>: ".$kontens[$i]."</td>";
                    $tr .= "</tr>";
                }elseif(strpos($keys[$i], 'K:') === false)
                {
                    $tr .= "<tr>";
                    $tr .= "<td align='left' width='50%'>".$keys[$i]."</td>";
                    $tr .= "<td>: ".$kontens[$i]."</td>";
                    $tr .= "</tr>";
                }
            }
        } 
        
        // just some setup
        $dom = new DOMDocument;
        $dom->loadXml('<html><body/></html>');
        $body = $dom->documentElement->firstChild;

        // this is the part you are looking for    
        $template = $dom->createDocumentFragment();
        $template->appendXML($tr);
        $body->appendChild($template);

        $mail = new PHPMailer(true);
        try {
            //Server settings
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
            $mail->isSMTP();          
            $mail->SMTPOptions = array('ssl' => array('verify_peer_name'=> false));                                  // Send using SMTP
            $mail->Host       = gethostbyname('smtp.gmail.com');        // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                         // Enable SMTP authentication
            $mail->Username   = 'rsroemaniv2@gmail.com';                     // SMTP username
            $mail->Password   = 'rsroemanII';                               // SMTP password
            $mail->SMTPSecure = "tls";         
            $mail->Port       = 587;                                  

            //Recipients
            $mail->setFrom('rsroemaniv2@gmail.com', 'RS Roemani Muhammadiyah');
            // $mail->addAddress($data->email_pegawai, $data->nm_pegawai);     // Add a recipient
            $mail->addAddress('mibrahimua@yahoo.com', 'M Ibrahim U Albab');   

            $email_body = view('template_email', ['nama_slip' => $nama_slip,'table' => $dom->saveXml()]);
            $mail->isHTML(true);                               
            $mail->Subject = $data->subjek_email;
            $mail->Body    = $email_body;
            //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
        
            $mail->send();
            DB::table('kirim_email')
            ->where('id_email', $data->id_email)
            ->update(['sendingdatetime' => 'NOW()', 'status_email' => 'OK']);
            
        } catch (Exception $e) {
            DB::table('kirim_email')
            ->where('id_email', $data->id_email)
            ->update(['status_email' => "Mailer Error: {".$mail->ErrorInfo."}"]);
            // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }

    

    public function testTemplate()
    {
        $data = DB::table('pendapatan_pegawai as pg')
        ->leftJoin('profil_pendapatan as pp', 'pp.id_profilp' , '=', 'pg.id_profilp')
        ->leftJoin('f_data_pegawai as dg', 'pg.id_pegawai', '=', 'dg.id_pegawai')
        ->leftJoin('f_department as d', 'd.id_dept', '=',DB::raw('ANY(dg.id_dept)'))
        ->select('pg.*', 'pp.nama_pendapatan',DB::raw("to_char(pg.bulan_kirim, 'MM-YYYY') AS bulan_kirim2"), 'dg.nm_pegawai', DB::raw("string_agg(d.nm_dept,':') AS nm_dept"), 'dg.no_rekening')
        ->where("pg.id_profilp", '1')
        ->whereRaw("to_char(bulan_kirim, 'MM-YYYY') = '03-2020'")
        ->whereRaw("pg.id_pendapatan = '13556'")
        ->groupBy('pg.id_pendapatan','dg.nm_pegawai','dg.no_rekening','dg.nik_pegawai', 'pp.nama_pendapatan')
        ->orderBy('dg.nik_pegawai', 'asc')
        ->get();
        $data= json_encode($data);
        //Cek string untuk validasi XML
        
        $data = strip_tags(preg_replace("/&(?!#?[a-z0-9]+;)/", "&amp;",$data));
        
        $data = json_decode($data);
        $data = $data[0];
        // var_dump($data[0]->nama_pendapata);
        // var_dump($data);echo '<br>';echo '<br>';
        $nama_slip = $data->nama_pendapatan.'<br>'.$this->bulan_indo($data->bulan_kirim2);
        
        $tr = "<tr>";
        $tr .= "<td align='left' width='50%'>NIK</td>";
        $tr .= "<td>: ".$data->nik_pegawai."</td>";
        $tr .= "</tr>";

        $tr .= "<tr>";
        $tr .= "<td align='left' width='50%'>Nama Pegawai</td>";
        $tr .= "<td>: ".$data->nm_pegawai."</td>";
        $tr .= "</tr>";

        $tr .= "<tr>";
        $tr .= "<td align='left' width='50%'>Bagian</td>";
        $tr .= "<td>: ".$data->nm_dept."</td>";
        $tr .= "</tr>";
        $key_perso = array_keys(get_object_vars(json_decode($data->detail_personalia)));
        $konten_perso = array_values(get_object_vars(json_decode($data->detail_personalia)));

        $key_keu = array_keys(get_object_vars(json_decode($data->detail_keuangan)));
        $konten_keu = array_values(get_object_vars(json_decode($data->detail_keuangan)));

        $key_total = array_keys(get_object_vars(json_decode($data->detail_total)));
        $konten_total = array_values(get_object_vars(json_decode($data->detail_total)));
        
        $keys = array_merge($key_perso, $key_keu, $key_total);
        $kontens = array_merge($konten_perso, $konten_keu, $konten_total);
        // dd($keys);
        for ($i = 0; $i <= count($keys)-1; $i++) 
        {
            if(strpos($keys[$i], 'N:') !== false)
            {
                $keys[$i] = str_replace("N:", "", $keys[$i]);
                $kontens[$i] = $this->rupiah($kontens[$i]);
            }
            if(strpos($keys[$i], 'P:') !== false)
            {
                $keys[$i] = str_replace("P:", "", $keys[$i]);
            }
            if(strpos($keys[$i], 'M:') !== false)
            {
                $keys[$i] = str_replace("M:", "", $keys[$i]);
            }
            if(is_null($kontens[$i]) && strpos($keys[$i], 'K:') !== false)
            {
                $keys[$i] = '';
            }elseif(is_null($kontens[$i]) && strpos($keys[$i], 'H:') !== false)
            {
                $keys[$i] = str_replace("H:", "", $keys[$i]);
                $tr .= "<tr>";
                $tr .= "<td colspan='2' style='text-align: center; border: 1px solid black ;'>".$keys[$i]."</td>";
                $tr .= "</tr>";
            }else
            {
                if(strpos($keys[$i], 'T:') !== false)
                {
                    $keys[$i] = str_replace("T:", "", $keys[$i]);
                    $tr .= "<tr>";
                    $tr .= "<td align='left' width='50%'>".$keys[$i]."</td>";
                    $tr .= "<td style='font-weight: bold; font-size: 25px;'>: ".$kontens[$i]."</td>";
                    $tr .= "</tr>";
                }elseif(strpos($keys[$i], 'K:') === false)
                {
                    $tr .= "<tr>";
                    $tr .= "<td align='left' width='50%'>".$keys[$i]."</td>";
                    $tr .= "<td>: ".$kontens[$i]."</td>";
                    $tr .= "</tr>";
                }
            }
        } 
        
        // just some setup
        $dom = new DOMDocument;
        $dom->loadXml('<html><body/></html>');
        $body = $dom->documentElement->firstChild;

        // this is the part you are looking for    
        $template = $dom->createDocumentFragment();
        $template->appendXML($tr);
        $body->appendChild($template);

        return view('template_email', ['nama_slip' => $nama_slip,'table' => $dom->saveXml()]);
    }

    public function rupiah($angka)
    {
        $hasil_rupiah = "Rp " . number_format($angka,2,',','.');
        return $hasil_rupiah;
    }

    public function bulan_indo($tanggal)
    {
        $bulan = array (1 =>   'Januari',
                    'Februari',
                    'Maret',
                    'April',
                    'Mei',
                    'Juni',
                    'Juli',
                    'Agustus',
                    'September',
                    'Oktober',
                    'November',
                    'Desember'
                );
        $split    = explode('-', $tanggal);
        
        $tgl_indo = $bulan[ (int)$split[0] ] . ' ' . $split[1];
        return $tgl_indo;
    }
}