<?php

namespace App\Console\Commands;

use App\AksesUser;
use App\SIMDataPegawai;
use App\SIMDepartment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PublishAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'publish:access';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishing Access to All Users';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // 15 ADMIN
        // 16 Kepala Bagian
        // 18 Karyawan

        // u-661 Nando

        // 5 Edit Jadwal
        $data = DB::table('akses_users')->where('id_akses', 5)->get();

        foreach ($data as $d) {
            if ($d->id_pegawai !== 'u-661') {
                DB::table('users')->insert(['id_pegawai' => $d->id_pegawai, 'id_group' => $d->status ? 16 : 18]);
            }
        }
    }
}
