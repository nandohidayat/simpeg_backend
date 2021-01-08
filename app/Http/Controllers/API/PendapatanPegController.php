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
use App\Imports\PendapatanImport;
use Maatwebsite\Excel\Facades\Excel;
use DOMDocument;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use stdClass;
use Config;

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
        Excel::import(new PendapatanImport(request()->bulan, request()->profil, request()->tipe), request()->file('file'));

        return response()->json(["status" => "success"], 200);
    }

    public function buatEmail(Request $request)
    {
        $id_pegawai = $request->post('id_pegawai') ?? 'empty';

        $query = DB::table('pendapatan_pegawai as pg')
            ->leftJoin('profil_pendapatan as pp', 'pp.id_profilp', '=', 'pg.id_profilp')
            ->leftJoin('f_data_pegawai as dg', 'pg.id_pegawai', '=', 'dg.id_pegawai')
            ->select('pg.id_pegawai', 'pp.nama_pendapatan', 'pg.bulan', 'dg.email_pegawai')
            ->whereRaw("dg.email_pegawai NOT LIKE ''");
        if ($id_pegawai != 'empty') {
            $query->whereRaw("dg.id_pegawai = '" . $id_pegawai . "'");
        }

        $data = $query->get();

        if ($data->isEmpty()) {
            return response()->json(["status" => "error", "message" => "Data tidak ditemukan, pastikan id pegawai benar dan email tidak kosong."], 404);
        } else {
            foreach ($data as $value) {
                $date = new Carbon($value->bulan);

                DB::table('kirim_email')->insert([
                    'penerima_email'    => $value->email_pegawai,
                    'subjek_email'      => $value->nama_pendapatan . ' ' . $date->locale('id_ID')->isoFormat('MMMM Y'),
                    'id_pengirim'     => auth()->user()->id_pegawai,
                    'id_pegawai'     => $value->id_pegawai,
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
            ->leftJoin('pendapatan_pegawai as pg', 'ke.id_pegawai', '=', 'pg.id_pegawai')
            ->leftJoin('profil_pendapatan as pp', 'pp.id_profilp', '=', 'pg.id_profilp')
            ->leftJoin('f_data_pegawai as fdp', 'fdp.id_pegawai', '=', 'ke.id_pegawai')
            ->select('ke.id_email', 'ke.subjek_email', 'ke.penerima_email', 'pp.view', 'fdp.nm_pegawai', 'pg.*')
            ->whereRaw('sendingdatetime is null')
            ->whereRaw("status_email is null")
            /**
             * untuk debug where clause email pegawai di disable
             */
            ->whereRaw("fdp.email_pegawai not ilike ''")
            ->limit(5)
            ->get();

        foreach ($data as $key => $value) {
            $value->personalia = json_decode($value->personalia);
            $value->keuangan = json_decode($value->keuangan);
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
        $mail = new PHPMailer(true);
        try {
            //Server settings
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                          // Enable verbose debug output
            $mail->isSMTP();
            $mail->SMTPOptions = array('ssl' => array('verify_peer_name' => false));     // Send using SMTP
            $mail->Host       = gethostbyname(config('constant.mail_host'));               // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                         // Enable SMTP authentication
            $mail->Username   = config('constant.mail_username');                     // SMTP username
            $mail->Password   = config('constant.mail_password');                                  // SMTP password
            $mail->SMTPSecure = "tls";
            $mail->Port       = config('constant.mail_port');

            //Recipients
            $mail->setFrom(config('constant.mail_from'), 'RS Roemani Muhammadiyah');
            $mail->addAddress($data->penerima_email, $data->nm_pegawai);     // Add a recipient
            // $mail->addAddress('laksitakusumaw@gmail.com', $data->nm_pegawai);

            $email_body = view($data->view, ['data' => $data]);
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
            ->whereRaw("to_char(bulan_kirim, 'MM-YYYY') = '11-2020'")
            ->whereRaw("pg.id_pendapatan = '15276'")
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
            $tipe = 'personalia';
        } else if (request()->tipe === 'format_keuangan') {
            $tipe = 'keuangan';
        }

        $profil = DB::table('pendapatan_pegawai as pp')
            ->leftJoin('f_data_pegawai as dp', 'dp.id_pegawai', '=', 'pp.id_pegawai')
            ->select('' . $tipe . '', 'dp.nik_pegawai as nik', 'dp.nm_pegawai as nama')
            ->orderBy('nik')
            ->get();

        $format = DB::table('profil_pendapatan')
            ->where('id_profilp', request()->profil)
            ->select(request()->tipe)
            ->first()
            ->{'' . request()->tipe . ''};

        $data = [];
        $header = [];

        $obj = new stdClass;
        $obj->value = 'NIK';
        $obj->text = 'NIK';
        $obj->width = '100';
        $obj->divider = true;
        array_push($header, $obj);

        $obj = new stdClass;
        $obj->value = 'NAMA';
        $obj->text = 'NAMA';
        $obj->width = '250';
        $obj->divider = true;
        array_push($header, $obj);

        foreach (json_decode($format) as $key => $value) {
            if (strpos($key, 'NIK') !== false || strpos($key, 'NAMA') !== false) {
                continue;
            }

            $obj = new stdClass;
            $obj->value = $key;
            $obj->text = $key;

            $obj->width = '160';
            $obj->divider = true;
            $obj->filterable = false;

            array_push($header, $obj);
        }

        if (count($profil) > 0) {
            if (isset($profil[0]->$tipe)) {
                foreach ($profil as $p) {
                    $obj = new stdClass;

                    foreach (json_decode($p->$tipe) as $k => $v) {
                        if ($k !== "NIK" && strpos($k, 'NIK') !== false) {
                            continue;
                        }

                        if (strpos($k, 'PROSEN') !== false) {
                            $obj->$k = '' . $v . ' %';
                        } else if ($k !== "NIK" && is_int($v)) {
                            $obj->$k = 'Rp ' . number_format($v, 2, ',', '.') . '';
                        } else {
                            $obj->$k = $v;
                        }
                    }

                    array_push($data, $obj);
                }
            }
        }

        return response()->json(["status" => "success", "data" => ['pendapatan' => $data, 'header' => $header]], 200);
    }
}
