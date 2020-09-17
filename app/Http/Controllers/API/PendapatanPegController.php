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
use DOMDocument;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use stdClass;

class PendapatanPegController extends Controller
{

    public function exportTemplatePeg()
    {
        $id_profilp = request()->get('id_profilp');
        $tipe_form = request()->get('tipe_form');
        $nama_file = DB::table('profil_pendapatan')->where('id_profilp', $id_profilp)->value('nama_pendapatan');
        $nama_file = 'Template ' . $nama_file . ' ' . ucfirst($tipe_form) . '.xlsx';
        return (new TemplatePegExport($id_profilp, $tipe_form))->download($nama_file);
    }

    public function exportPendapatanPeg()
    {
        $id_profilp = request()->get('id_profilp');
        $tipe_form = request()->get('tipe_form');
        $bulan_kirim = request()->get('bulan_kirim');
        $nama_file = DB::table('profil_pendapatan')->where('id_profilp', $id_profilp)->value('nama_pendapatan');
        $nama_file =  $nama_file . ' ' . ucfirst($tipe_form) . ' ' . $bulan_kirim . '.xlsx';
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

        if (request()->post('tipe_form') == 'format_personalia') {
            $tipe_form = "detail_personalia";
        }
        if (request()->post('tipe_form') == 'format_keuangan') {
            $tipe_form = 'detail_keuangan';
        }

        $hasil_import = Excel::toArray(new PendapatanPegImport, $file);

        $query = "INSERT INTO pendapatan_pegawai (id_profilp, id_pegawai, nik_pegawai, bulan_kirim, " . $tipe_form . ") VALUES ";

        foreach ($hasil_import as $key => $row) {

            /**
             * unset($row) untuk menghapus header dalam hasil import excel,
             * yang selanjutnya menyisakan row data gaji pegawai (tanpa header)
             */

            $head = $row[0];
            unset($row[0]);
            unset($row[1]);

            foreach ($row as $key2 => $value) {

                $pegawai = new stdClass();
                for ($i = 0; $i <= 2; $i++) {
                    $obj = $head[$i];
                    $pegawai->$obj = $value[$i];
                }

                /**
                 * fungsi loop for diatas untuk mengambil nik pegawai
                 * dan selanjutnya diteruskan kedalam parameter query dibawah
                 */

                $id_pegawai = DB::table('f_data_pegawai')->where('nik_pegawai', $pegawai->NIK)->value('id_pegawai');

                if (!is_null($id_pegawai)) {
                    $data = new stdClass();

                    for ($i = 2; $i <= count($head) - 1; $i++) {
                        $obj = $head[$i];
                        $data->$obj = $value[$i];
                    }

                    $data = json_encode($data);

                    /**
                     * jika tidak ada id_profilp dan bulan kirim, maka lakukan insert
                     * jika ada id_profilp dan bulan kirim, maka lakukan update sesuai dengan tipe form
                     */

                    $query .= '(' . $id_profilp . ', \'' . $id_pegawai . '\', \'' . $pegawai->NIK . '\', TO_DATE(\'' . $bulan_kirim . '\', \'MM-YYYY\'), \'' . $data . '\')';

                    if ($key2 <= count($row)) {
                        $query .= ', ';
                    }
                }
            }
        }

        $query .= ' ON CONFLICT ON CONSTRAINT pendapatan_pegawai_ukey DO UPDATE SET ' . $tipe_form . ' = excluded.' . $tipe_form . '';

        DB::select($query);

        return response()->json(["status" => "success"], 201);
    }

    public function buatEmail(Request $request)
    {
        if (empty($request->id_profilp)) {
            return response()->json(["status" => "error", 'message' => 'Profil nya dipilih dulu'], 501);
        }

        $id_profilp = $request->post('id_profilp');
        $bulan_kirim = $request->post('bulan_kirim');
        $id_pegawai = $request->post('id_pegawai') ?? 'empty';
        $query = DB::table('pendapatan_pegawai as pg')
            ->leftJoin('profil_pendapatan as pp', 'pp.id_profilp', '=', 'pg.id_profilp')
            ->leftJoin('f_data_pegawai as dg', 'pg.id_pegawai', '=', 'dg.id_pegawai')
            ->leftJoin('f_department as d', 'd.id_dept', '=', DB::raw('ANY(dg.id_dept)'))
            ->select('pg.id_pendapatan', 'pg.bulan_kirim', 'pp.nama_pendapatan', DB::raw("to_char(pg.bulan_kirim, 'MM-YYYY') AS bulan_kirim2"), 'dg.nm_pegawai', DB::raw("string_agg(d.nm_dept,':') AS nm_dept"), 'dg.email_pegawai')
            ->where("pg.id_profilp", $id_profilp)
            ->whereRaw("to_char(pg.bulan_kirim, 'MM-YYYY') = '" . $bulan_kirim . "'")
            ->whereRaw("dg.email_pegawai NOT LIKE ''");
        if ($id_pegawai != 'empty') {
            $query->whereRaw("dg.id_pegawai = '" . $id_pegawai . "'");
        }
        $query->groupBy('pg.id_pendapatan', 'dg.nm_pegawai', 'dg.email_pegawai', 'pp.nama_pendapatan');
        $data = $query->get();

        if ($data->isEmpty()) {
            return response()->json(["status" => "failed", "message" => "Data tidak ditemukan, pastikan id pegawai benar dan email tidak kosong."], 404);
        } else {
            foreach ($data as $key => $value) {
                DB::table('kirim_email')->insert([
                    'penerima_email'    => $value->email_pegawai,
                    'subjek_email'      => $value->nama_pendapatan . ' ' . $this->bulan_indo($value->bulan_kirim2),
                    'id_pendapatan'     => $value->id_pendapatan,
                    'insertintodb'      => 'NOW()'
                ]);
            }
            //Membuat cron job
            $output = shell_exec('sudo crontab -l -u www-data | grep -i "http://localhost/php74/simpeg_testing/simpeg_backend/public/api/email/kirim"');
            if (is_null($output)) {
                $cron = shell_exec('(sudo crontab -u www-data -l ; echo "* * * * * wget http://localhost/php74/simpeg_testing/simpeg_backend/public/api/email/kirim") | sudo crontab -u www-data -');
            }
            return response()->json(["status" => "success"], 201);
        }
    }

    public function kirimEmail(Request $request)
    {
        $data = DB::table('kirim_email as ke')
            ->leftJoin('pendapatan_pegawai as pg', 'ke.id_pendapatan', '=', 'pg.id_pendapatan')
            // TODO: kenapa di join?
            ->leftJoin('profil_pendapatan as pp', 'pp.id_profilp', '=', 'pg.id_profilp')
            ->leftJoin('f_data_pegawai as dg', 'pg.id_pegawai', '=', 'dg.id_pegawai')
            ->leftJoin('f_department as d', 'd.id_dept', '=', DB::raw('ANY(dg.id_dept)'))
            ->select('ke.id_email', 'ke.subjek_email', 'pg.*', 'pp.nama_pendapatan', DB::raw("to_char(pg.bulan_kirim, 'MM-YYYY') AS bulan_kirim2"), 'dg.nm_pegawai', DB::raw("string_agg(d.nm_dept,':') AS nm_dept"), 'dg.email_pegawai')
            ->whereRaw('sendingdatetime is null')
            ->whereRaw("status_email is null")
            /**
             * untuk debug where clause email pegawai di disable
             */
            ->whereRaw("dg.email_pegawai not ilike ''")
            ->groupBy('pg.id_pendapatan', 'dg.nm_pegawai', 'dg.email_pegawai', 'ke.id_email', 'pp.nama_pendapatan')
            ->limit(5)
            ->get();

        // var_dump($data);
        $arr_template = [];
        foreach ($data as $key => $value) {
            $template_total = DB::table('profil_pendapatan')
                ->where('id_profilp', $value->id_profilp)
                ->value('format_total');
            $personalia = json_decode($value->detail_personalia, true);
            $keuangan = json_decode($value->detail_keuangan, true);
            $total_hitung = 0;
            foreach ($personalia as $key => $total_perso) {
                if (strpos($key, 'P:') !== false) {
                    $total_hitung += (int)$total_perso;
                }
                if (strpos($key, 'M:') !== false) {
                    $total_hitung -= (int)$total_perso;
                }
            }
            foreach ($keuangan as $key => $total_keu) {
                if (strpos($key, 'P:') !== false) {
                    $total_hitung += (int)$total_keu;
                }
                if (strpos($key, 'M:') !== false) {
                    $total_hitung -= (int)$total_keu;
                }
            }
            $template_total2 = json_decode($template_total, true);

            foreach ($template_total2 as $key => $total_temp) {
                $arr_template[$key] = null;
                foreach ($total_temp as $key2 => $value2) {
                    if (strpos($key2, 'T:') !== false) {
                        $arr_template[$key2] = $total_hitung;
                    }
                }
            }

            $template_total2 = json_encode($arr_template);
            DB::table('pendapatan_pegawai')
                ->where('id_profilp', $value->id_profilp)
                ->whereRaw("to_char(bulan_kirim, 'MM-YYYY') = '" . $value->bulan_kirim2 . "'")
                ->where('id_pegawai', $value->id_pegawai)
                ->update(['detail_total' => $template_total2]);
            $this->kirimSlip($value);
        }

        if (!isset($data[0])) {
            // tidak ada antrian email, hapus cron job
            $output = shell_exec('sudo crontab -l -u www-data | grep -i "curl http://localhost/php74/simpeg_testing/simpeg_backend/public/api/email/kirim"');
            if (!is_null($output)) {
                $remove_cron = shell_exec("sudo crontab -u www-data -l | grep -v 'curl http://localhost/php74/simpeg_testing/simpeg_backend/public/api/email/kirim' | crontab -u www-data -");
            }
        }
    }

    public function kirimSlip($data)
    {
        $data = json_encode($data);
        //Cek string untuk validasi XML

        $data = strip_tags(preg_replace("/&(?!#?[a-z0-9]+;)/", "&amp;", $data));

        $data = json_decode($data);
        // $data = $data[0];
        // var_dump($data[0]->nama_pendapata);
        // var_dump($data);echo '<br>';echo '<br>';
        $nama_slip = $data->nama_pendapatan . '<br>' . $this->bulan_indo($data->bulan_kirim2);

        $tr = "<tr>";
        $tr .= "<td align='left' width='50%'>NIK</td>";
        $tr .= "<td>: " . $data->nik_pegawai . "</td>";
        $tr .= "</tr>";

        $tr .= "<tr>";
        $tr .= "<td align='left' width='50%'>Nama Pegawai</td>";
        $tr .= "<td>: " . $data->nm_pegawai . "</td>";
        $tr .= "</tr>";

        $tr .= "<tr>";
        $tr .= "<td align='left' width='50%'>Bagian</td>";
        $tr .= "<td>: " . $data->nm_dept . "</td>";
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
        for ($i = 0; $i <= count($keys) - 1; $i++) {
            if (strpos($keys[$i], 'N:') !== false) {
                $keys[$i] = str_replace("N:", "", $keys[$i]);
                $kontens[$i] = $this->rupiah($kontens[$i]);
            }
            if (strpos($keys[$i], 'P:') !== false) {
                $keys[$i] = str_replace("P:", "", $keys[$i]);
            }
            if (strpos($keys[$i], 'M:') !== false) {
                $keys[$i] = str_replace("M:", "", $keys[$i]);
            }
            if (is_null($kontens[$i]) && strpos($keys[$i], 'K:') !== false) {
                $keys[$i] = '';
            } elseif (is_null($kontens[$i]) && strpos($keys[$i], 'H:') !== false) {
                $keys[$i] = str_replace("H:", "", $keys[$i]);
                $tr .= "<tr>";
                $tr .= "<td colspan='2' style='text-align: center; border: 1px solid black ;'>" . $keys[$i] . "</td>";
                $tr .= "</tr>";
            } else {
                if (strpos($keys[$i], 'T:') !== false) {
                    $keys[$i] = str_replace("T:", "", $keys[$i]);
                    $tr .= "<tr>";
                    $tr .= "<td align='left' width='50%'>" . $keys[$i] . "</td>";
                    $tr .= "<td style='font-weight: bold; font-size: 25px;'>: " . $kontens[$i] . "</td>";
                    $tr .= "</tr>";
                } elseif (strpos($keys[$i], 'K:') === false) {
                    $tr .= "<tr>";
                    $tr .= "<td align='left' width='50%'>" . $keys[$i] . "</td>";
                    $tr .= "<td>: " . $kontens[$i] . "</td>";
                    $tr .= "</tr>";
                }
            }
        }

        $dom = new DOMDocument;
        $dom->loadXml('<html><body/></html>');
        $body = $dom->documentElement->firstChild;

        $template = $dom->createDocumentFragment();
        $template->appendXML($tr);
        $body->appendChild($template);

        $mail = new PHPMailer(true);
        try {
            //Server settings
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
            $mail->isSMTP();
            $mail->SMTPOptions = array('ssl' => array('verify_peer_name' => false));                                  // Send using SMTP
            $mail->Host       = gethostbyname('smtp.gmail.com');        // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                         // Enable SMTP authentication
            $mail->Username   = 'rsroemaniv2@gmail.com';                     // SMTP username
            $mail->Password   = 'rsroemanII';                               // SMTP password
            $mail->SMTPSecure = "tls";
            $mail->Port       = 587;

            //Recipients
            $mail->setFrom('rsroemaniv2@gmail.com', 'RS Roemani Muhammadiyah');
            // $mail->addAddress($data->email_pegawai, $data->nm_pegawai);     // Add a recipient
            $mail->addAddress('mattborgic@gmail.com', 'Nando Bruh');

            $email_body = view('template_email', ['nama_slip' => $nama_slip, 'table' => $dom->saveXml()]);
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
                ->update(['status_email' => "Mailer Error: {" . $mail->ErrorInfo . "}"]);
            // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }

    public function testTemplate()
    {
        $data = DB::table('pendapatan_pegawai as pg')
            ->leftJoin('profil_pendapatan as pp', 'pp.id_profilp', '=', 'pg.id_profilp')
            ->leftJoin('f_data_pegawai as dg', 'pg.id_pegawai', '=', 'dg.id_pegawai')
            ->leftJoin('f_department as d', 'd.id_dept', '=', DB::raw('ANY(dg.id_dept)'))
            ->select('pg.*', 'pp.nama_pendapatan', DB::raw("to_char(pg.bulan_kirim, 'MM-YYYY') AS bulan_kirim2"), 'dg.nm_pegawai', DB::raw("string_agg(d.nm_dept,':') AS nm_dept"), 'dg.no_rekening')
            ->where("pg.id_profilp", '1')
            ->whereRaw("to_char(bulan_kirim, 'MM-YYYY') = '03-2020'")
            ->whereRaw("pg.id_pendapatan = '13556'")
            ->groupBy('pg.id_pendapatan', 'dg.nm_pegawai', 'dg.no_rekening', 'dg.nik_pegawai', 'pp.nama_pendapatan')
            ->orderBy('dg.nik_pegawai', 'asc')
            ->get();
        $data = json_encode($data);
        //Cek string untuk validasi XML

        $data = strip_tags(preg_replace("/&(?!#?[a-z0-9]+;)/", "&amp;", $data));

        $data = json_decode($data);
        $data = $data[0];
        $nama_slip = $data->nama_pendapatan . '<br>' . $this->bulan_indo($data->bulan_kirim2);

        $tr = "<tr>";
        $tr .= "<td align='left' width='50%'>NIK</td>";
        $tr .= "<td>: " . $data->nik_pegawai . "</td>";
        $tr .= "</tr>";

        $tr .= "<tr>";
        $tr .= "<td align='left' width='50%'>Nama Pegawai</td>";
        $tr .= "<td>: " . $data->nm_pegawai . "</td>";
        $tr .= "</tr>";

        $tr .= "<tr>";
        $tr .= "<td align='left' width='50%'>Bagian</td>";
        $tr .= "<td>: " . $data->nm_dept . "</td>";
        $tr .= "</tr>";
        $key_perso = array_keys(get_object_vars(json_decode($data->detail_personalia)));
        $konten_perso = array_values(get_object_vars(json_decode($data->detail_personalia)));

        $key_keu = array_keys(get_object_vars(json_decode($data->detail_keuangan)));
        $konten_keu = array_values(get_object_vars(json_decode($data->detail_keuangan)));

        $key_total = array_keys(get_object_vars(json_decode($data->detail_total)));
        $konten_total = array_values(get_object_vars(json_decode($data->detail_total)));

        $keys = array_merge($key_perso, $key_keu, $key_total);
        $kontens = array_merge($konten_perso, $konten_keu, $konten_total);
        for ($i = 0; $i <= count($keys) - 1; $i++) {
            if (strpos($keys[$i], 'N:') !== false) {
                $keys[$i] = str_replace("N:", "", $keys[$i]);
                $kontens[$i] = $this->rupiah($kontens[$i]);
            }
            if (strpos($keys[$i], 'P:') !== false) {
                $keys[$i] = str_replace("P:", "", $keys[$i]);
            }
            if (strpos($keys[$i], 'M:') !== false) {
                $keys[$i] = str_replace("M:", "", $keys[$i]);
            }
            if (is_null($kontens[$i]) && strpos($keys[$i], 'K:') !== false) {
                $keys[$i] = '';
            } elseif (is_null($kontens[$i]) && strpos($keys[$i], 'H:') !== false) {
                $keys[$i] = str_replace("H:", "", $keys[$i]);
                $tr .= "<tr>";
                $tr .= "<td colspan='2' style='text-align: center; border: 1px solid black ;'>" . $keys[$i] . "</td>";
                $tr .= "</tr>";
            } else {
                if (strpos($keys[$i], 'T:') !== false) {
                    $keys[$i] = str_replace("T:", "", $keys[$i]);
                    $tr .= "<tr>";
                    $tr .= "<td align='left' width='50%'>" . $keys[$i] . "</td>";
                    $tr .= "<td style='font-weight: bold; font-size: 25px;'>: " . $kontens[$i] . "</td>";
                    $tr .= "</tr>";
                } elseif (strpos($keys[$i], 'K:') === false) {
                    $tr .= "<tr>";
                    $tr .= "<td align='left' width='50%'>" . $keys[$i] . "</td>";
                    $tr .= "<td>: " . $kontens[$i] . "</td>";
                    $tr .= "</tr>";
                }
            }
        }

        // just some setup
        $dom = new DOMDocument;
        $dom->loadXml('<html><body/></html>');
        $body = $dom->documentElement->firstChild;

        $template = $dom->createDocumentFragment();
        $template->appendXML($tr);
        $body->appendChild($template);

        return view('template_email', ['nama_slip' => $nama_slip, 'table' => $dom->saveXml()]);
    }

    public function rupiah($angka)
    {
        $hasil_rupiah = "Rp " . number_format($angka, 2, ',', '.');
        return $hasil_rupiah;
    }

    public function bulan_indo($tanggal)
    {
        $bulan = array(
            1 =>   'Januari',
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

        $tgl_indo = $bulan[(int)$split[0]] . ' ' . $split[1];
        return $tgl_indo;
    }

    public function getProfil()
    {
        $data = DB::table('profil_pendapatan')->select('id_profilp as value', 'nama_pendapatan as text')->orderBy('nama_pendapatan')->get();

        return response()->json(["status" => "success", "data" => $data], 200);
    }

    public function getPendapatan()
    {
        setlocale(LC_MONETARY, 'id_ID');

        $tipe = null;
        if (request()->tipe === 'format_personalia') {
            $tipe = 'detail_personalia';
        } else if (request()->tipe === 'format_keuangan') {
            $tipe = 'detail_keuangan';
        }

        $profil = DB::table('pendapatan_pegawai as pp')
            ->where('pp.id_profilp', request()->profil)
            ->whereRaw("to_char(pp.bulan_kirim, 'YYYY-MM') = '" . request()->date . "'")
            ->leftJoin('f_data_pegawai as dp', 'dp.id_pegawai', '=', 'pp.id_pegawai')
            ->select('' . $tipe . '', 'dp.nik_pegawai as nik', 'dp.nm_pegawai as nama')
            ->orderBy('nik')
            ->get();

        $data = [];
        $header = [];

        if (count($profil) > 0) {
            $obj = new stdClass;
            $obj->value = 'nik';
            $obj->text = 'NIK';
            $obj->width = '100';
            $obj->divider = true;
            array_push($header, $obj);

            $obj = new stdClass;
            $obj->value = 'nama';
            $obj->text = 'Nama';
            $obj->width = '250';
            $obj->divider = true;
            array_push($header, $obj);

            if (isset($profil[0]->$tipe)) {

                foreach (json_decode($profil[0]->$tipe) as $key => $value) {
                    $obj = new stdClass;
                    $obj->value = $key;

                    $temp = explode(':', $key);
                    $obj->text = end($temp);

                    $obj->width = '160';
                    $obj->divider = true;

                    array_push($header, $obj);
                }

                foreach ($profil as $p) {
                    $obj = new stdClass;

                    foreach (json_decode($p->$tipe) as $k => $v) {
                        if (strpos($k, 'N:') !== false) {
                            $obj->$k = 'Rp ' . number_format($v, 2, ',', '.') . '';
                        } else {
                            $obj->$k = $v;
                        }
                    }

                    unset($p->$tipe);

                    $temp = (object) array_merge((array)$p, (array)$obj);
                    array_push($data, $temp);
                }
            }
        }

        return response()->json(["status" => "success", "data" => ['data' => $data, 'header' => $header]], 200);
    }
}
