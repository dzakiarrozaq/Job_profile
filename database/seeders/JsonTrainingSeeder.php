<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Training;
use Illuminate\Support\Facades\File;

class JsonTrainingSeeder extends Seeder
{
    public function run()
    {
        // 1. Ambil path file JSON
        $jsonPath = database_path('data/courses_en.json');

        // 2. Cek apakah file ada
        if (!File::exists($jsonPath)) {
            $this->command->error("File tidak ditemukan di: $jsonPath");
            return;
        }

        // 3. Baca dan Decode JSON
        $json = File::get($jsonPath);
        $courses = json_decode($json, true); // true = ubah jadi array asosiatif

        // 4. Looping data
        foreach ($courses as $item) {
            
            // --- LOGIKA MAPPING (Sesuaikan key di sini dengan JSON Anda) ---
            
            // A. Mapping Title
            // Cek apakah key-nya 'course_title', 'title', atau 'courseName' di JSON Anda
            $title = $item['name'] ?? $item['title'] ?? 'No Title';

            // B. Mapping Description & URL
            // Jika ada URL, kita masukkan ke deskripsi atau buat kolom baru
            $url = $item['url'] ?? '#';
            $descText = "Pelatihan online interaktif. Pelajari selengkapnya di: " . $url;

            // C. Mapping Difficulty/Level
            // Dataset Kaggle biasanya punya value: 'All Levels', 'Beginner Level', dll.
            // Kita ubah supaya cocok dengan filter aplikasi Anda (Beginner, Intermediate, Advanced)
            $jsonLevel = $item['level'] ?? 'Beginner';
            $difficulty = 'Beginner'; // Default
            
            if (stripos($jsonLevel, 'Intermediate') !== false) $difficulty = 'Intermediate';
            if (stripos($jsonLevel, 'Expert') !== false || stripos($jsonLevel, 'Advanced') !== false) $difficulty = 'Advanced';

            // D. Mapping Category
            // Dataset biasanya punya 'subject'. Kita petakan ke kategori aplikasi Anda.
            $jsonSubject = $item['subject'] ?? 'General';
            $category = 'Technical'; // Default

            if (stripos($jsonSubject, 'Business') !== false || stripos($jsonSubject, 'Finance') !== false) $category = 'Leadership';
            if (stripos($jsonSubject, 'Communication') !== false) $category = 'Soft Skills';
            if (stripos($jsonSubject, 'Web') !== false || stripos($jsonSubject, 'Code') !== false) $category = 'Technical';

            // E. Mapping Harga
            // Kadang harga di JSON bentuknya string "$100" atau angka. Kita bersihkan.
            $rawPrice = $item['price'] ?? 0;
            // Hapus simbol mata uang jika ada, konversi ke Rupiah (asumsi kurs 15.000)
            $cleanPrice = (float) preg_replace('/[^0-9.]/', '', $rawPrice);
            $costInRupiah = $cleanPrice > 0 ? $cleanPrice * 15000 : 0; 

            // --- SIMPAN KE DATABASE ---
            Training::create([
                'title'       => substr($title, 0, 255),
                'description' => substr($descText, 0, 500), // Batasi panjang
                'provider'    => 'Udemy / Coursera', // Bisa dihardcode atau ambil dari JSON
                'type'        => 'external',
                'method'      => 'Online',
                'cost'        => $costInRupiah,
                'status'      => 'approved',
                
                // Field Filter Penting
                'difficulty'  => $difficulty,
                'category'    => $category,
                
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        $this->command->info("Berhasil mengimpor " . count($courses) . " pelatihan dari JSON!");
    }
}