<?php
namespace App\Http\Controllers;

use App\Mega655;

class Mega655Controller extends Controller{
    public function index(){
        list($evenPatterns, $numbersPatterns, $positionPatterns) = $this->getPatterns();
        $newEvenPatterns = $this->preparePatterns($evenPatterns);
        $newNumbersPatterns = $this->preparePatterns($numbersPatterns);
        $newPositionPatterns = $this->preparePatterns($positionPatterns);
        return view('mega')->with(['newEvenPatterns' => $newEvenPatterns, 'newNumbersPatterns' => $newNumbersPatterns, 'newPositionPatterns' => $newPositionPatterns]);
    }

    private function getPatterns(){
        $jackpotNumbers = Mega655::all()->pluck('jackpot_numbers');
        $evenPatterns = [];
        $positionPatterns = [];
        $numbersPatterns = [];
        foreach ($jackpotNumbers as $numbers){
            $zero = 0;
            $one = 0;
            $two = 0;
            $three = 0;
            $four = 0;
            $five = 0;
            $even = 0;
            $numberArray = str_split($numbers, 2);
            foreach ($numberArray as $number){
                if ($number%2 === 0 ){
                    $even++;
                }
                if ($number[0] == 0){
                    $zero++;
                }
                if ($number[0] == 1){
                    $one++;
                }
                if ($number[0] == 2){
                    $two++;
                }
                if ($number[0] == 3){
                    $three++;
                }
                if ($number[0] == 4){
                    $four++;
                }
                if ($number[0] == 5){
                    $five++;
                }
                $numbersPatterns[$number] = !empty($numbersPatterns[$number]) ? $numbersPatterns[$number]+1 : 1;
            }
            $positionIndex = $zero . $one . $two . $three . $four . $five;
            $positionPatterns[$positionIndex] = !empty($positionPatterns[$positionIndex]) ? $positionPatterns[$positionIndex]+1 : 1;
            $evenPatterns[$even] = !empty($evenPatterns[$even]) ? $evenPatterns[$even]+1 : 1;
        }
        arsort($evenPatterns);
        arsort($numbersPatterns);
        arsort($positionPatterns);
        return [$evenPatterns, $numbersPatterns, $positionPatterns];
    }

    public function preparePatterns($patterns){
        $total = array_sum($patterns);
        $preparedPatterns = array_map(function ($value) use ($total){
            return number_format($value/$total*100,2 );
        }, $patterns);
        $average = array_sum($preparedPatterns)/count($patterns);

        $newPreparedPatterns = array_filter($preparedPatterns, function ($value) use ($average){
            return ((float)$value) >= $average;
        });

        return $newPreparedPatterns;
    }
}