<?php

use Illuminate\Database\Seeder;

class Mega655Seeder extends Seeder
{
    const MAX = 55;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $numbers = [
            0 => 9,
            1 => 10,
            2 => 10,
            3 => 10,
            4 => 10,
            5 => 6,
        ];
        $generated = [];
        $toSave =[];
        $fact = gmp_strval(gmp_fact(self::MAX));
        $combinationTotal = $fact/(gmp_strval(gmp_fact(self::MAX - 6)) * gmp_strval(gmp_fact(6)));
        for ($i = 0; $i < 2000000; $i++){
            $pattern = [
                random_int(0, 6),
                random_int(0, 6),
                random_int(0, 6),
                random_int(0, 6),
                random_int(0, 6),
                random_int(0, 6),
            ];
            $sum = 1;
            if (array_sum($pattern) == 6 && !in_array($pattern, $generated)){
                $generated[] = $pattern;
                foreach ($pattern as $key => $item){
                    $sum *= gmp_strval(gmp_fact($numbers[$key]))/(gmp_strval(gmp_fact($numbers[$key] - $item)) * gmp_strval(gmp_fact($item)));
                }
                $combination = number_format($sum/$combinationTotal*100, 4);
                $toSave[] = [
                    'pattern' => implode('',$pattern),
                    'possibility' => $combination
                ];
            }
        }
        \App\MegaOdd55::insert($toSave);
    }
}
