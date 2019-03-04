<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <link rel="stylesheet" href="css/mega.css">
        <link rel="stylesheet" href="http://github.hubspot.com/odometer/themes/odometer-theme-car.css" />
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <script src="https://code.jquery.com/jquery-3.3.1.min.js"  crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        <script src="http://github.hubspot.com/odometer/odometer.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    </head>
<body>
    <div class="container">
        <div style="display: flex; justify-content: center;align-items: center; flex-direction: column">
            @foreach($lastJackpot as $key => $jackpot)
                <div style="margin-top:20px">
                    @foreach($jackpot as $key => $number)
                        <span style="display: inline !important;" class="jackpot-number">{{$number}}</span>
                    @endForeach
                </div>
            @endForeach
        </div>
        <hr/>
        <div style="display: flex;">
            <div style="flex:1">
                <form id="new-jackpot">
                    <div class="form-group">
                        <label>Date</label>
                        <input id="date_roll" name="date_roll" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Jackpot number</label>
                        <input name="jackpot_numbers" class="form-control">
                    </div>
                    <div id="success" class="alert-success alert" style="display: none">Save successfully!</div>
                    <button id="newJackpotBtn" class="btn btn-primary">Save</button>
                </form>
            </div>
            <div style="display: flex; align-items: center; margin-left: 50px; flex: 2;" class="col-6">
                <button style="flex:1" id="rollJackpot" class="btn btn-danger align-items-center">Give Me JACKPOT!</button>
                <div id="jackpot-container" style="display: flex; flex: 3; justify-content: center">
                    @foreach($lastJackpot[1] as $key => $number)
                        <div id="number{{$key}}" class="jackpot-number">{{$number}}</div>
                    @endForeach
                </div>
            </div>
        </div>
        <hr/>

        <div class="row">
            {{--<div class="col-2">--}}
                {{--<table class="table">--}}
                    {{--<thead>--}}
                        {{--<tr>--}}
                            {{--<th colspan="2">Even pattern</th>--}}
                        {{--</tr>--}}
                        {{--<tr>--}}
                            {{--<th>Pattern</th>--}}
                            {{--<th>Value</th>--}}
                        {{--</tr>--}}
                    {{--</thead>--}}
                    {{--<tbody>--}}
                        {{--@foreach($newEvenPatterns as $pattern => $value)--}}
                        {{--<tr>--}}
                            {{--<td>{{$pattern}}</td>--}}
                            {{--<td>{{$value}}</td>--}}
                        {{--</tr>--}}
                        {{--@endforeach--}}
                    {{--</tbody>--}}

                {{--</table>--}}
            {{--</div>--}}
            {{--<div class="col-2">--}}
                {{--<table class="table">--}}
                    {{--<thead>--}}
                    {{--<tr>--}}
                        {{--<th colspan="2">Low pattern</th>--}}
                    {{--</tr>--}}
                    {{--<tr>--}}
                        {{--<th>Pattern</th>--}}
                        {{--<th>Value</th>--}}
                    {{--</tr>--}}
                    {{--</thead>--}}
                    {{--<tbody>--}}
                    {{--@foreach($newLowPatterns as $pattern => $value)--}}
                        {{--<tr>--}}
                            {{--<td>{{$pattern}}</td>--}}
                            {{--<td>{{$value}}</td>--}}
                        {{--</tr>--}}
                    {{--@endforeach--}}
                    {{--</tbody>--}}

                {{--</table>--}}
            {{--</div>--}}
            <div class="col-4">
                <table class="table">
                    <thead>
                    <tr>
                        <th colspan="3">Number pattern - {{$total * 6}}</th>
                    </tr>
                    <tr>
                        <th>Pattern</th>
                        <th>Value</th>
                        <th>Repeat</th>
                        <th>Dayouts</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($newNumbersPatterns as $pattern => $value)
                        <tr @if($dayouts['numbers'][(int)$pattern] < $oldAveDayouts['numbers'][(int)$pattern])style="color: green" @else style="color:red;" @endif>
                            <td>{{$pattern}}</td>
                            <td>{{$numberPatterns[$pattern]}} ({{number_format($numberPatterns[$pattern]/$total*100, 2)}})</td>
                            <td>
                                {{!empty($repeatPatterns[1][$pattern]) ? $repeatPatterns[1][$pattern] : 0}} ({{!empty($repeatPatterns[1][$pattern]) ? number_format($repeatPatterns[1][$pattern]/$numberPatterns[$pattern]*100, 2) : 0}})
                                - {{!empty($repeatPatterns[2][$pattern]) ? $repeatPatterns[2][$pattern] : 0}} ({{!empty($repeatPatterns[2][$pattern]) ? number_format($repeatPatterns[2][$pattern]/$numberPatterns[$pattern]*100, 2) : 0}})
                                - {{!empty($repeatPatterns[3][$pattern]) ? $repeatPatterns[3][$pattern] : 0}} ({{!empty($repeatPatterns[3][$pattern]) ? number_format($repeatPatterns[3][$pattern]/$numberPatterns[$pattern]*100, 2) : 0}})
                            </td>
                            <td>
                                {{$dayouts['numbers'][(int)$pattern]}} ({{number_format($oldAveDayouts['numbers'][(int)$pattern], 2)}})
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="col-8">
                <table class="table">
                    <thead>
                    <tr>
                        <th colspan="4">Position pattern - {{$total}}</th>
                    </tr>
                    <tr>
                        <th>Pattern</th>
                        <th>Value</th>
                        <th>Expected</th>
                        <th>Diff</th>
                        <th>Dayouts</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($newPositionPatterns as $pattern => $value)
                        <tr @if(!empty($expectedPossibilities[$pattern]) && $expectedPossibilities[$pattern] < $value)style="color: green" @else style="color:red;" @endif>
                            <td>
                                <div class="pattern">
                                    {{$pattern}}
                                </div>
                                <div class="pattern-number" style="display: none; color: black">
                                    @foreach($positionNumbers[$pattern] as $number)
                                    <div>
                                        {{$number}}
                                    </div>
                                    @endforeach
                                </div>
                            </td>
                            <td>{{number_format($value*$total/100, 2)}} ({{$value}})</td>
                            <td>{{$expectedPossibilities[$pattern] ? number_format($expectedPossibilities[$pattern]*$total/100, 2) . ' (' . number_format($expectedPossibilities[$pattern], 3) .')' :''}}</td>
                            <td @if(!empty($expectedPossibilities[$pattern]) && $expectedPossibilities[$pattern] < $value)style="color: green" @else style="color:red;" @endif>{{!empty($expectedPossibilities[$pattern]) ? $value -  $expectedPossibilities[$pattern] : 'N/A'}}</td>
                            <td>
                                {{$dayouts['positions'][$pattern]}} ({{number_format($oldAveDayouts['positions'][$pattern], 2)}})
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
    <script type="text/javascript">
      jQuery('document').ready(() => {
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });
        $('#date_roll').datepicker();
        $('#date_roll').datepicker("option", "dateFormat", 'yy-mm-dd' );
        jQuery('.pattern').click(function(){
          jQuery(this).siblings('.pattern-number').toggle();
        });
          jQuery('#rollJackpot').click(function (event) {
            event.preventDefault();
            jQuery.get('', function (data) {
              let obj = JSON.parse(data);
              obj.forEach((value, index) => {
                $(`#number${index}`).prop('Counter',0).animate({
                  Counter: value
                }, {
                  duration: 3000,
                  easing: 'swing',
                  step: function (now) {
                    $(`#number${index}`).text(pad(Math.ceil(now), 2));
                  }
                });
                // $(`#number${index}`).html(value);
              })
            })
          })
        jQuery('#newJackpotBtn').click(function (event) {
          event.preventDefault();
        })
        $('#newJackpotBtn').click(function (event) {
          event.preventDefault();
          let date_roll = $('input[name="date_roll"]').val();
          let jackpot_numbers = $('input[name="jackpot_numbers"]').val();
          $.ajax({
            // url: '/mega45/',
            type: 'post',
            data: {date_roll, jackpot_numbers},
            success: () => {
              $('#success').show();
              setTimeout(() => {
                $('#success').hide()
                location.reload();
              }, 3000)
            }
          })
        })
      });
      function pad(num, size) {
        let s = num+"";
        while (s.length < size) s = "0" + s;
        return s;
      }
    </script>
</html>
