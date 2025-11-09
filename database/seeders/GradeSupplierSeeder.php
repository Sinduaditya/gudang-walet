<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GradeSupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grades = [
            // Gambar 1 - Baris 1
            ['name' => 'MANGKOK', 'description' => 'Grade Mangkok standar'],
            ['name' => 'MANGKOK PUTIH', 'description' => 'Grade Mangkok putih'],
            ['name' => 'MANGKOK KW', 'description' => 'Grade Mangkok KW'],
            ['name' => 'MANGKOK RAMPAS KECIL', 'description' => 'Grade Mangkok rampas kecil'],
            ['name' => 'MANGKOK BULU RAMPAS', 'description' => 'Grade Mangkok bulu rampas'],
            ['name' => 'MANGKOK 2', 'description' => 'Grade Mangkok tipe 2'],
            ['name' => 'MANGKOK BERAS KREM', 'description' => 'Grade Mangkok beras krem'],

            // Gambar 1 - Baris 2
            ['name' => 'MANGKOK BERAS', 'description' => 'Grade Mangkok beras'],
            ['name' => 'MANGKOK PINK', 'description' => 'Grade Mangkok pink'],
            ['name' => 'MANGKOK BB', 'description' => 'Grade Mangkok BB'],
            ['name' => 'MANGKOK OVAL RAMPAS', 'description' => 'Grade Mangkok oval rampas'],
            ['name' => 'MANGKOK OVAL BULU', 'description' => 'Grade Mangkok oval bulu'],
            ['name' => 'MANGKOK BERAS KW', 'description' => 'Grade Mangkok beras KW'],
            ['name' => 'MANGKOK 4', 'description' => 'Grade Mangkok tipe 4'],

            // Gambar 1 - Baris 3
            ['name' => 'MANGKOK 3', 'description' => 'Grade Mangkok tipe 3'],
            ['name' => 'MANGKOK PUTIH BERAS', 'description' => 'Grade Mangkok putih beras'],
            ['name' => 'MANGKOK 1 KREM', 'description' => 'Grade Mangkok 1 krem'],
            ['name' => 'MANGKOK 2 KREM', 'description' => 'Grade Mangkok 2 krem'],
            ['name' => 'MANGKOK KREM', 'description' => 'Grade Mangkok krem'],
            ['name' => 'MANGKOK 2 RAMPAS PUTIH', 'description' => 'Grade Mangkok 2 rampas putih'],
            ['name' => 'MANGKOK 5', 'description' => 'Grade Mangkok tipe 5'],

            // Gambar 1 - Baris 4
            ['name' => 'MANGKOK KUNING', 'description' => 'Grade Mangkok kuning'],
            ['name' => 'MANGKOK/OVAL BS', 'description' => 'Grade Mangkok oval BS'],
            ['name' => 'MANGKOK PLONTOS', 'description' => 'Grade Mangkok plontos'],
            ['name' => 'MANGKOK RAMPAS', 'description' => 'Grade Mangkok rampas'],
            ['name' => 'MANGKOK A/B PUTIH', 'description' => 'Grade Mangkok A/B putih'],
            ['name' => 'MANGKOK KR', 'description' => 'Grade Mangkok KR'],
            ['name' => 'MANGKOK 7', 'description' => 'Grade Mangkok tipe 7'],

            // Gambar 1 - Baris 5
            ['name' => 'MANGKOK 6', 'description' => 'Grade Mangkok tipe 6'],

            // Gambar 2 - OVAL Series
            ['name' => 'OVAL', 'description' => 'Grade Oval standar'],
            ['name' => 'OVAL RAMPAS PUTIH', 'description' => 'Grade Oval rampas putih'],
            ['name' => 'RAMPAS OVAL', 'description' => 'Grade Rampas oval'],
            ['name' => 'OVAL RAMPAS BERAS', 'description' => 'Grade Oval rampas beras'],

            // Gambar 2 - PB
            ['name' => 'PB', 'description' => 'Grade PB'],

            // Gambar 2 - SUDUT Series
            ['name' => 'SUDUT', 'description' => 'Grade Sudut standar'],
            ['name' => 'SUDUT LUNUT', 'description' => 'Grade Sudut lunut'],
            ['name' => 'SUDUT 2', 'description' => 'Grade Sudut tipe 2'],
            ['name' => 'SUDUT/PATAHAN JAWA', 'description' => 'Grade Sudut patahan jawa'],

            // Gambar 2 - SP Series
            ['name' => 'SP', 'description' => 'Grade SP standar'],
            ['name' => 'SP PUTIH', 'description' => 'Grade SP putih'],
            ['name' => 'SPBC', 'description' => 'Grade SPBC'],
            ['name' => 'SP BERAS', 'description' => 'Grade SP beras'],
            ['name' => 'PATAHAN', 'description' => 'Grade Patahan standar'],
            ['name' => 'SP KW', 'description' => 'Grade SP KW'],
            ['name' => 'SP KREM BERAS', 'description' => 'Grade SP krem beras'],
            ['name' => 'SP KREM', 'description' => 'Grade SP krem'],
            ['name' => 'SP LUMUT', 'description' => 'Grade SP lumut'],
            ['name' => 'SP BULU', 'description' => 'Grade SP bulu'],

            // Gambar 2 - 23 Series & PAHATAN
            ['name' => '2/3', 'description' => 'Grade 2/3'],
            ['name' => '2/3 BULU', 'description' => 'Grade 2/3 bulu'],
            ['name' => 'PATAHAN 2/3 KW', 'description' => 'Grade patahan 2/3 KW'],
            ['name' => 'PATAHAN 2/3 BB', 'description' => 'Grade patahan 2/3 BB'],
            ['name' => 'PATAHAN BERAS', 'description' => 'Grade patahan beras'],
            ['name' => 'PATAHAN PENDEK', 'description' => 'Grade patahan pendek'],
            ['name' => 'PATAHAN PLONTOS', 'description' => 'Grade patahan plontos'],

            ['name' => 'PATAHAN 2/3 BERAS', 'description' => 'Grade patahan 2/3 beras'],
            ['name' => 'PATAHAN JAWA', 'description' => 'Grade patahan jawa'],
            ['name' => 'PATAHAN A KREM', 'description' => 'Grade patahan A krem'],
            ['name' => 'PATAHAN KECIL 2/3', 'description' => 'Grade patahan kecil 2/3'],

            // Gambar 3 - KAKIAN Series
            ['name' => 'KAKIAN/CUPING', 'description' => 'Grade Kakian'],
            ['name' => 'KAKI', 'description' => 'Grade Kaki'],
            ['name' => 'BB', 'description' => 'Grade BB'],
            ['name' => 'KW', 'description' => 'Grade KW'],
            ['name' => 'KAKI KUNING', 'description' => 'Grade Kaki kuning'],
            ['name' => 'RAMPAS PUTIH', 'description' => 'Grade Rampas putih'],
            ['name' => 'FLEK', 'description' => 'Grade Flek'],


            // Gambar 3 - KRONIS Series
            ['name' => 'KRONIS', 'description' => 'Grade Kronis'],
            ['name' => 'CONG', 'description' => 'Grade Cong'],
            ['name' => 'BULU CONG', 'description' => 'Grade Bulu cong'],
            ['name' => 'BAKPAO/KRONIS', 'description' => 'Grade Bakpao/Kronis'],
        ];

        $timestamp = Carbon::now();

        foreach ($grades as &$grade) {
            $grade['created_at'] = $timestamp;
            $grade['updated_at'] = $timestamp;
        }

        DB::table('grades_supplier')->insert($grades);

        $this->command->info('✓ ' . count($grades) . ' Grade Supplier seeded successfully!');
    }
}
