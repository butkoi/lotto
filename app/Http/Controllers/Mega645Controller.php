<?php
namespace App\Http\Controllers;

use App\Mega645;
use App\Mega655;
use App\MegaOdd;
use App\MegaOdd55;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * ((9 choose 3) * (10 choose 1) * (10 choose 1)  * (10 choose 1)  * (10 choose 0) * (5 choose 0))/(55 choose 6)*100
 *
 * Class Mega645Controller
 * @package App\Http\Controllers
 */
class Mega645Controller extends Controller{
    protected $is655 = false;
    protected $position645 = [
        '12111',
        '11211',
        '11121',
//            '21111',
//            '11220'
    ];
    protected $position655 = [
        '111111'
    ];
    protected $sum645 = [
        105,
        173
    ];

    protected $sum655 = [
        129,
        207
    ];

    protected $dayoutRange55 = [
        5,
        10
    ];

    protected $dayoutRange45 = [
        3,
        8
    ];

    protected $getRequired55 = [
        0 => true,
        1 => true,
        2 => false,
        3 => false
    ];

    protected $getRequired45 = [
        0 => true, //always true
        1 => false, //danh nguoc voi lan cuoi cung
        2 => true, //streak nhieu. Doi khi nao xuat hien 1 lan thi true
        3 => false
    ];
    protected $oldDayouts = [];
    protected $previousJackpots = [];

    public function index(Request $request){
        list($evenPatterns, $numbersPatterns, $positionPatterns, $repeatPatterns, $lowPatterns, $dayouts, $oldDayouts, $positionNumbers, $lastJackpot) = $this->getPatterns($request->get('previous'));
        $oldAveDayouts = array_map(function($item){
            return array_map(function($pattern){
                return array_sum($pattern)/count($pattern);
            }, $item);
        }, $oldDayouts);
        ksort($oldDayouts['numbers']);
        ksort($oldDayouts['positions']);
//        Log::info($oldDayouts);
//        Log::info($oldAveDayouts);
        ksort($numbersPatterns);
        $newEvenPatterns = $this->preparePatterns($evenPatterns);
        $newLowPatterns = $this->preparePatterns($lowPatterns);
        $newNumbersPatterns = $this->preparePatterns($numbersPatterns);
        $newPositionPatterns = $this->preparePatterns($positionPatterns);
        $positionPossibilities = MegaOdd::all();
        $expectedPossibilities = [];
        foreach ($positionPossibilities as $possibility){
            $expectedPossibilities[$possibility->pattern] = $possibility->possibility;
        }
        if ($request->isXmlHttpRequest()) {
            $jackpot = $this->getJackPot($newNumbersPatterns, $repeatPatterns, $lastJackpot, $numbersPatterns);
            sort($jackpot);
            return response(json_encode($jackpot));
        }

        return view('mega')->with(['total' => Mega645::all()->count(), 'newEvenPatterns' => $newEvenPatterns, 'newNumbersPatterns' => $newNumbersPatterns, 'numberPatterns' => $numbersPatterns, 'newPositionPatterns' => $newPositionPatterns, 'positionNumbers' => $positionNumbers, 'expectedPossibilities' => $expectedPossibilities, 'lastJackpot' => $lastJackpot, 'repeatPatterns' => $repeatPatterns, 'newLowPatterns' => $newLowPatterns, 'dayouts' => $dayouts, 'oldAveDayouts' => $oldAveDayouts]);
    }

    public function index655(Request $request){
        $this->is655 = true;
        list($evenPatterns, $numbersPatterns, $positionPatterns, $repeatPatterns, $lowPatterns, $dayouts, $oldDayouts, $positionNumbers, $lastJackpot) = $this->getPatterns($request->get('previous'));
        $oldAveDayouts = array_map(function($item){
            return array_map(function($pattern){
                return array_sum($pattern)/count($pattern);
            }, $item);
        }, $oldDayouts);
        ksort($numbersPatterns);
        $newEvenPatterns = $this->preparePatterns($evenPatterns);
        $newLowPatterns = $this->preparePatterns($lowPatterns);
        $newNumbersPatterns = $this->preparePatterns($numbersPatterns);
        $newPositionPatterns = $this->preparePatterns($positionPatterns);
        $positionPossibilities = MegaOdd55::all();
        $expectedPossibilities = [];
        foreach ($positionPossibilities as $possibility){
            $expectedPossibilities[$possibility->pattern] = $possibility->possibility;
        }
        if ($request->isXmlHttpRequest()) {
            $jackpot = $this->getJackPot($newNumbersPatterns, $repeatPatterns, $lastJackpot, $numbersPatterns);
            sort($jackpot);
            return response(json_encode($jackpot));
        };
        return view('mega')->with(['total' => Mega655::all()->count(),'newEvenPatterns' => $newEvenPatterns, 'newNumbersPatterns' => $newNumbersPatterns, 'numberPatterns' => $numbersPatterns, 'newPositionPatterns' => $newPositionPatterns, 'positionNumbers' => $positionNumbers, 'expectedPossibilities' => $expectedPossibilities, 'lastJackpot' => $lastJackpot, 'repeatPatterns' => $repeatPatterns, 'newLowPatterns' => $newLowPatterns, 'dayouts' => $dayouts, 'oldAveDayouts' => $oldAveDayouts]);
    }

    public function store(Request $request){
        $mega645 = new Mega645();
        $mega645->fill($request->input());
        $mega645->save();
        return response('success');
    }

    public function store655(Request $request){
        $mega645 = new Mega655();
        $mega645->fill($request->input());
        $mega645->save();
        return response('success');
    }

    private function getPatterns($previous = 0){
        if ($this->is655) {
            $jackpotNumbers = Mega655::all()->sortBy('date_roll');
        } else {
            $jackpotNumbers = Mega645::all()->sortBy('date_roll');
        }
        $jackpotNumbers = $jackpotNumbers->slice(0, $jackpotNumbers->count() - $previous);
        $evenPatterns = [];
        $lowPatterns = [];
        $positionPatterns = [];
        $numbersPatterns = [];
        $positionNumbers = [];
        $previousJackpot = [1 => [], 2 => [], 3 => []];
        $repeatPatterns = [1 => [], 2 => [], 3 => []];
        $dayouts = [
            'numbers' => array_fill_keys(range(1, $this->is655 ? 55 : 45), 0),
            'positions' => array_fill_keys($this->is655 ? MegaOdd55::all()->pluck('pattern')->toArray() : MegaOdd::all()->pluck('pattern')->toArray(), -1)
        ];

        $oldDayouts = [
            'numbers' => [],
            'positions' => []
        ];
        $total = 0;
        foreach ($jackpotNumbers as $numbersRaw){
            $total++;
            array_walk($dayouts, function (&$pattern){
                array_walk($pattern, function (&$item){
                    $item++;
                });
            });
            $numbers = $numbersRaw->jackpot_numbers;
            $zero = 0;
            $one = 0;
            $two = 0;
            $three = 0;
            $four = 0;
            $five = 0;
            $even = 0;
            $low = 0;
            $numberArray = str_split($numbers, 2);
            $output = [];
            foreach ($numberArray as $number){
                if ($number%2 === 0 ){
                    $even++;
                }
                if ($number%10 < 5){
                    $low++;
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

                $value = !empty($numbersPatterns[$number]) ? $numbersPatterns[$number] : 0;
                $numberInt = (int)$number;
                if (!isset($oldDayouts['numbers'][$numberInt])){
                    $oldDayouts['numbers'][$numberInt] = [];
                }
                $oldDayouts['numbers'][$numberInt][] = $dayouts['numbers'][$numberInt] - 1;
                $output[$numbers]['numbers'][$numberInt] = [
                    'dayouts' => $dayouts['numbers'][$numberInt] - 1,
                    'value' => ($total - 1 != 0) ? $value . " (" . number_format($value/($total-1)*100, 2) . ")" : 0,
                    'repeat' => "" . ((!empty($repeatPatterns[1][$number]) && !empty($numbersPatterns[$number])) ? $repeatPatterns[1][$number] . " (" . number_format($repeatPatterns[1][$number]/$numbersPatterns[$number]*100, 2) . ")" : 0)
                        . " - " . ((!empty($repeatPatterns[2][$number]) && !empty($numbersPatterns[$number])) ? $repeatPatterns[2][$number] . " (" . number_format($repeatPatterns[2][$number]/$numbersPatterns[$number]*100, 2) . ")" : 0)
                        . " - " . ((!empty($repeatPatterns[3][$number]) && !empty($numbersPatterns[$number])) ? $repeatPatterns[3][$number] . " (" . number_format($repeatPatterns[3][$number]/$numbersPatterns[$number]*100, 2) . ")" : 0)

                ];
                $dayouts['numbers'][$numberInt] = 0;
                $numbersPatterns[$number] = !empty($numbersPatterns[$number]) ? $numbersPatterns[$number]+1 : 1;
                for ($i = 1; $i <= 3; $i++) {
                    if (in_array($number, $previousJackpot[$i])) {
                        $repeatPatterns[$i][$number] = !empty($repeatPatterns[$i][$number]) ? $repeatPatterns[$i][$number] + 1 : 1;
                        break;
                    }
                }
            }
            $positionIndex = $zero . $one . $two . $three . $four;
            if ($this->is655) {
                $positionIndex .= $five;
            }
            if (!isset($oldDayouts['positions'][$positionIndex])) {
                $oldDayouts['positions'][$positionIndex] = [];
            }

            $positionPatterns[$positionIndex] = !empty($positionPatterns[$positionIndex]) ? $positionPatterns[$positionIndex]+1 : 1;
            $oldDayouts['positions'][$positionIndex][] = $dayouts['positions'][$positionIndex] - 1;
            $output[$numbers]['position'] = [
                'pattern' => $positionIndex,
                'dayouts' => $dayouts['positions'][$positionIndex],
                'value' => ($total-1 != 0) ? $positionPatterns[$positionIndex]-1 . " (" . number_format(($positionPatterns[$positionIndex]-1)/($total-1)*100, 2) . ")" : 0
            ];
            $outputDayoutTotal = 0;
            foreach ($output[$numbers]['numbers'] as $outputNumber) {
                $outputDayoutTotal += $outputNumber['dayouts'];
            }
            $output[$numbers]['ave_dayout'] = $outputDayoutTotal/6;
            $dayouts['positions'][$positionIndex] = 0;
            $positionNumbers[$positionIndex][] = implode(' ', $numberArray) . ' - ' . Carbon::parse($numbersRaw->date_roll)->format('d/m/Y');
            $evenPatterns[$even] = !empty($evenPatterns[$even]) ? $evenPatterns[$even]+1 : 1;
            $lowPatterns[$low] = !empty($lowPatterns[$low]) ? $lowPatterns[$low]+1 : 1;
            $previousJackpot[6] = $previousJackpot[5] ?? [];
            $previousJackpot[5] = $previousJackpot[4] ?? [];
            $previousJackpot[4] = $previousJackpot[3] ?? [];
            $previousJackpot[3] = $previousJackpot[2] ?? [];
            $previousJackpot[2] = $previousJackpot[1] ?? [];
            $previousJackpot[1] = $numberArray;
//            Log::info($output);
        }
        array_walk($oldDayouts, function (&$item, $itemKey) use ($dayouts){
            array_walk($item, function (&$pattern, $patternKey) use ($dayouts, $itemKey){
                $pattern = array_merge($pattern, [$dayouts[$itemKey][$patternKey]]);
            });
        });
        arsort($evenPatterns);
        arsort($numbersPatterns);
        arsort($positionPatterns);
        $this->oldDayouts = $oldDayouts;
        $this->previousJackpots = $previousJackpot;
        return [$evenPatterns, $numbersPatterns, $positionPatterns, $repeatPatterns, $lowPatterns, $dayouts, $oldDayouts, $positionNumbers, $previousJackpot];
    }

    public function preparePatterns($patterns, $ave = false){
        $total = array_sum($patterns);
        $preparedPatterns = array_map(function ($value) use ($total){
            return number_format($value/$total*100,3);
        }, $patterns);

        if ($ave) {
            $average = array_sum($preparedPatterns)/count($patterns);
            $preparedPatterns = array_filter($preparedPatterns, function ($value) use ($average){
                return ((float)$value) >= $average;
            });
        }
        return $preparedPatterns;
    }

    public function _getJackpot($prefilledJackpot){
        $success = false;
        while (!$success) {
            if (count($prefilledJackpot) > 2){
                $jackpot = array_random($prefilledJackpot, 2);
            } else {
                $jackpot = $prefilledJackpot;
            }

            while (count($jackpot) < 6) {
                $jackpot[] = sprintf("%02d", random_int(1, $this->is655 ? 55 : 45));
            }

            if (
                $this->checkUnique($jackpot) &&
                $this->checkEven($jackpot) &&
                $this->checkLow($jackpot) &&
                $this->checkSum($jackpot) &&
                $this->checkRequiredGet($jackpot) &&
                $this->checkDayouts($jackpot) &&
                $this->checkPosition($jackpot)
            ) {
                $success = true;
            }
        }
        return $jackpot;
    }

    public function getJackpot($numberPatterns, $repeatPatterns, $previousJackpot, $valueNumberPatterns){
        $jackpot = [];

        foreach ($previousJackpot as $index => $oldJackpot){
            if ($index > 3) {
                break;
            }
            foreach ($repeatPatterns[$index] as $pattern => $value){
                if (
                    in_array($pattern, $oldJackpot)
                    && (empty($previousJackpot[$index*2]) || !in_array($pattern, $previousJackpot[$index*2]))
                    && ($value/$valueNumberPatterns[$pattern]*100 > 10)
                ){
                    if (!in_array((string)$pattern, $jackpot)) {
                        $jackpot[] = (string)$pattern;
                    }
                }
            }
        }

        return $this->_getJackpot($jackpot, $numberPatterns);
    }

    public function checkUnique($jackpot)
    {
        $unique = array_unique($jackpot);
        return count($unique) == 6;
    }

    public function checkEven($jackpot){
        $even = 0;
        foreach ($jackpot as $number){
            if ($number % 2 == 0){
                $even++;
            }
        }
        return in_array($even, [2,3,4]);
    }

    public function checkLow($jackpot){
        $low = 0;
        foreach ($jackpot as $number){
            if ($number%10 < 5) {
                $low++;
            }
        }
        return $low == 3 || $low == 4;
    }

    public function checkRequiredGet($jackpot)
    {
        $zero = $this->is655 ? $this->getRequired55[0]: $this->getRequired45[0];
        $one = $this->is655 ? $this->getRequired55[1]: $this->getRequired45[1];
        $two = $this->is655 ? $this->getRequired55[2]: $this->getRequired45[2];
        $three = $this->is655 ? $this->getRequired55[3]: $this->getRequired45[3];
        if ($zero) {
            if (count(array_intersect($jackpot, $this->previousJackpots[1])) == 0){
                return false;
            }
        }
        if ($one) {
            if (count(array_intersect($jackpot, $this->previousJackpots[2])) == 0){
                return false;
            }
        }
        if ($two) {
            if (count(array_intersect($jackpot, $this->previousJackpots[3])) == 0){
                return false;
            }
        }
        if ($three) {
            if (count(array_intersect($jackpot, $this->previousJackpots[4])) == 0){
                return false;
            }
        }
        return true;
    }

    public function checkDayouts($jackpot){
        $total = 0;
        foreach ($jackpot as $number){
            $total += last($this->oldDayouts['numbers'][(int)$number]);
        }
        $ave = $total/6;
        $dayoutRanges = $this->is655 ? $this->dayoutRange55 : $this->dayoutRange45;
        return $dayoutRanges[0] <= $ave && $ave < $dayoutRanges[1];
    }

    public function checkSum($jackpot){
        $sum = array_sum($jackpot);
        $sumRestrict = $this->is655 ? $this->sum655 : $this->sum645;
        return $sumRestrict[0] <= $sum && $sum <= $sumRestrict[1];
    }

    public function checkPosition($jackpot){
        $positionPatterns = $this->is655 ? $this->position655 : $this->position645;
        $zero = 0;
        $one = 0;
        $two = 0;
        $three = 0;
        $four = 0;
        $five = 0;
        foreach ($jackpot as $number){
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
        }
        $positionIndex = $zero . $one . $two . $three . $four;
        if ($this->is655) {
            $positionIndex .= $five;
        }
        if (in_array($positionIndex, $positionPatterns)){
            return true;
        }
        return false;
    }

    public function test(Request $request)
    {
        $numbers = [];
        $this->is655 = $request->get('655', false);

        while(count($numbers) < 1000) {
            if ($this->is655) {
                $number = [
                    sprintf("%02d", random_int(1, 55)),
                    sprintf("%02d", random_int(1, 55)),
                    sprintf("%02d", random_int(1, 55)),
                    sprintf("%02d", random_int(1, 55)),
                    sprintf("%02d", random_int(1, 55)),
                    sprintf("%02d", random_int(1, 55)),
                    sprintf("%02d", random_int(1, 55)),
                ];
            } else {
                $number = [
                    sprintf("%02d", random_int(1, 45)),
                    sprintf("%02d", random_int(1, 45)),
                    sprintf("%02d", random_int(1, 45)),
                    sprintf("%02d", random_int(1, 45)),
                    sprintf("%02d", random_int(1, 45)),
                    sprintf("%02d", random_int(1, 45)),
                ];
            }

            $number = array_unique($number);
            if (count($number) == 6) {
                $numbers[] = $number;
            }
        }
        list($evenPatterns, $numbersPatterns, $positionPatterns, $repeatPatterns, $lowPatterns, $positionNumbers, $lastJackpot, $sumPatterns) = $this->_getPatterns($numbers);
        ksort($numbersPatterns);
        $newEvenPatterns = $this->preparePatterns($evenPatterns);
        $newLowPatterns = $this->preparePatterns($lowPatterns);
        $newNumbersPatterns = $this->preparePatterns($numbersPatterns);
        $newPositionPatterns = $this->preparePatterns($positionPatterns);
        $positionPossibilities = MegaOdd::all();
        $expectedPossibilities = [];
        foreach ($positionPossibilities as $possibility){
            $expectedPossibilities[$possibility->pattern] = $possibility->possibility;
        }
        return view('mega_test')->with([
            'newEvenPatterns' => $newEvenPatterns,
            'newNumbersPatterns' => $newNumbersPatterns,
            'numberPatterns' => $numbersPatterns,
            'newPositionPatterns' => $newPositionPatterns,
            'positionNumbers' => $positionNumbers,
            'expectedPossibilities' => $expectedPossibilities,
            'lastJackpot' => $lastJackpot,
            'repeatPatterns' => $repeatPatterns,
            'newLowPatterns' => $newLowPatterns,
            'sumPatterns' => $sumPatterns
        ]);
    }

    public function _getPatterns($numbersToTal)
    {
        $evenPatterns = [];
        $lowPatterns = [];
        $positionPatterns = [];
        $numbersPatterns = [];
        $positionNumbers = [];
        $previousJackpot = [1 => [], 2 => [], 3 => []];
        $repeatPatterns = [1 => [], 2 => [], 3 => []];
        $sumPatterns = [];
        foreach ($numbersToTal as $numberArray){
            $zero = 0;
            $one = 0;
            $two = 0;
            $three = 0;
            $four = 0;
            $five = 0;
            $even = 0;
            $low = 0;
            $sum = 0;
            foreach ($numberArray as $number){
                if ($number%2 === 0 ){
                    $even++;
                }
                if ($number%10 < 5){
                    $low++;
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
                for ($i = 1; $i <= 3; $i++) {
                    if (in_array($number, $previousJackpot[$i])) {
                        $repeatPatterns[$i][$number] = !empty($repeatPatterns[$i][$number]) ? $repeatPatterns[$i][$number] + 1 : 1;
                        break;
                    }
                }
                $sum += $number;
            }
            $positionIndex = $zero . $one . $two . $three . $four;
            if ($this->is655) {
                $positionIndex .= $five;
            }
            $positionPatterns[$positionIndex] = !empty($positionPatterns[$positionIndex]) ? $positionPatterns[$positionIndex]+1 : 1;
            $positionNumbers[$positionIndex][] = implode(' ', $numberArray). ' - ' .$even;
            $evenPatterns[$even] = !empty($evenPatterns[$even]) ? $evenPatterns[$even]+1 : 1;
            $lowPatterns[$low] = !empty($lowPatterns[$low]) ? $lowPatterns[$low]+1 : 1;
            $sumRestrict = $this->is655 ? $this->sum655 : $this->sum645;
            if ($sum < $sumRestrict[0]) {
                $sumPatterns['lower'] = !empty($sumPatterns['lower']) ? $sumPatterns['lower']+1 : 1;
            }
            if ($sumRestrict[0] <= $sum && $sum <= $sumRestrict[1]) {
                $sumPatterns['between'] = !empty($sumPatterns['between']) ? $sumPatterns['between']+1 : 1;
            }
            if ($sum > $sumRestrict[1]) {
                $sumPatterns['higher'] = !empty($sumPatterns['higher']) ? $sumPatterns['higher']+1 : 1;
            }
            $previousJackpot[6] = $previousJackpot[5] ?? [];
            $previousJackpot[5] = $previousJackpot[4] ?? [];
            $previousJackpot[4] = $previousJackpot[3] ?? [];
            $previousJackpot[3] = $previousJackpot[2] ?? [];
            $previousJackpot[2] = $previousJackpot[1] ?? [];
            $previousJackpot[1] = $numberArray;

        }
        arsort($evenPatterns);
        arsort($numbersPatterns);
        arsort($positionPatterns);
        arsort($lowPatterns);
        arsort($sumPatterns);
        return [$evenPatterns, $numbersPatterns, $positionPatterns, $repeatPatterns, $lowPatterns, $positionNumbers, $previousJackpot, $sumPatterns];
    }
}
