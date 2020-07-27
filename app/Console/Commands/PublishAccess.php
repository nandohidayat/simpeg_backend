<?php

namespace App\Console\Commands;

use App\AksesUser;
use App\SIMDataPegawai;
use App\SIMDepartment;
use Illuminate\Console\Command;

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
        $data = SIMDataPegawai::all();
        $kepala = SIMDepartment::pluck('kepala_dept');
        foreach ($data as $d) {
            // AksesUser::updateOrCreate(['id_akses' => 2, 'id_pegawai' => $d->id_pegawai], ['status' => true]);
            if (!in_array($d->id_pegawai, (array) $kepala)) {
                AksesUser::updateOrCreate(['id_akses' => 5, 'id_pegawai' => $d->id_pegawai], ['status' => false]);
            }
        }
    }
}
