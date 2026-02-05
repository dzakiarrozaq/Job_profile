<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MasterResponsibilityImport;

class MasterResponsibilitySeeder extends Seeder
{
    public function run()
    {
        // PENTING: Pastikan file CSV sudah ditaruh di storage/app/master_csv/
        
        // Format: [Nama File, Grade Name, Tipe]
        $files = [
            ['Master Tanggung Jawab Generic.xlsx - BAND 1 STR.csv', '1', 'structural'],
            ['Master Tanggung Jawab Generic.xlsx - BAND 2 STR.csv', '2', 'structural'],
            ['Master Tanggung Jawab Generic.xlsx - BAND 2 FSL.csv', '2', 'functional'],
            ['Master Tanggung Jawab Generic.xlsx - BAND 3 STR.csv', '3', 'structural'],
            ['Master Tanggung Jawab Generic.xlsx - BAND 3 FSL.csv', '3', 'functional'],
            ['Master Tanggung Jawab Generic.xlsx - BAND 4 STR.csv', '4', 'structural'],
            ['Master Tanggung Jawab Generic.xlsx - BAND 4 FSL.csv', '4', 'functional'],
            ['Master Tanggung Jawab Generic.xlsx - BAND 5.csv',     '5', 'general'], // Band 5 biasanya general
        ];

        foreach ($files as $file) {
            $path = storage_path('app/master_csv/' . $file[0]);
            
            if (file_exists($path)) {
                $this->command->info("Importing: " . $file[0]);
                Excel::import(new MasterResponsibilityImport($file[1], $file[2]), $path);
            } else {
                $this->command->error("File not found: " . $file[0]);
            }
        }
    }
}