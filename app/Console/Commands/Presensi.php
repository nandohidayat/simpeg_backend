<?php

namespace App\Console\Commands;

use App\Presensi as AppPresensi;
use Exception;
use Illuminate\Console\Command;

class Presensi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:presensi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get all the presensi boys';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    function Parse_Data($data, $p1, $p2)
    {
        $data = " " . $data;
        $hasil = "";

        $awal = strpos($data, $p1);

        if ($awal != "") {
            $akhir = strpos(strstr($data, $p1), $p2);

            if ($akhir != "") {
                $hasil = substr($data, $awal + strlen($p1), $akhir - strlen($p1));
            }
        }

        return $hasil;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ip_key = array('192.168.0.251' => '12345', '192.168.0.252' => '0', '192.168.0.229' => '0', '192.168.0.253' => '0');

        foreach ($ip_key as $ip => $key) {
            try {
                $Connect = fsockopen($ip, "80", $errno, $errstr, 1);
            } catch (Exception $e) {
                echo $e->getMessage();
            }

            if ($Connect) {
                $soap_request = "<GetAttLog><ArgComKey xsi:type=\"xsd:integer\">" . $key . "</ArgComKey><Arg><PIN xsi:type=\"xsd:integer\">All</PIN></Arg></GetAttLog>";
                $newLine = "\r\n";

                fputs($Connect, "POST /iWsService HTTP/1.0" . $newLine);
                fputs($Connect, "Content-Type: text/xml" . $newLine);
                fputs($Connect, "Content-Length: " . strlen($soap_request) . $newLine . $newLine);
                fputs($Connect, $soap_request . $newLine);
                $buffer = "";

                while ($Response = fgets($Connect, 1024)) {
                    $buffer = $buffer . $Response;
                }

                $buffer = $this->Parse_Data($buffer, "<GetAttLogResponse>", "</GetAttLogResponse>");
                $buffer = explode("\r\n", $buffer);

                for ($a = 0; $a < count($buffer); $a++) {
                    $data = $this->Parse_Data($buffer[$a], "<Row>", "</Row>");
                    $PIN = $this->Parse_Data($data, "<PIN>", "</PIN>");
                    $DateTime = $this->Parse_Data($data, "<DateTime>", "</DateTime>");
                    $Verified = $this->Parse_Data($data, "<Verified>", "</Verified>");
                    $Status = $this->Parse_Data($data, "<Status>", "</Status>");

                    if ($PIN != 0) {
                        $arr = ['pin' => $PIN, 'datetime' => $DateTime, 'verified' => $Verified, 'status' => $Status, 'source' => $ip];
                        AppPresensi::updateOrCreate($arr, $arr);
                    }
                }
            }
        }
    }
}
