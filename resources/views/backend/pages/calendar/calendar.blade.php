<!DOCTYPE html>
<html lang="en" class="light">
<!-- BEGIN: Head -->

<head>

    <!-- BEGIN: CSS Assets-->
    @include("backend.layout.css")
    <!-- END: CSS Assets-->
</head>
<!-- END: Head -->
<body class="py-5">
    <div class="row">
        <?php $month = ['','มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];  
                $calendar = array();
                    foreach ($date as $dd) {
                        $start = strtotime($dd->start_date);
                        $end = strtotime($dd->end_date);
                        while ($start <= $end) {
                            if(date('n',$start) == $n){
                                $calendar[date('j',$start)] = $dd->holiday;
                            }
                        $start = $start+86400;   
                    }
                }
         ?>
         <div class="flex">
            <button class="btn btn-outline-secondary mr-1 mb-2" style="background-color: #FF6C00;" onclick="remonth('r')"><span style="color: aliceblue"><b> <  </b></span></button> 
            <div class="font-medium text-base mx-auto" style="font-size: 18px;color:#FF6C00;"><b>{{$month[$n]}} {{$year}}</b></div>
            <button class="btn btn-outline-secondary mr-1 mb-2" style="background-color: #FF6C00;" onclick="remonth('a')"><span style="color: aliceblue"><b> >  </b></span></button>
        </div>
    </div>
    <br>
    <table class="table table-bordered">
        <thead style="height:60px" >
            <th class=' text-center ' width="14.285%" style="black;font-size: 14px"><b>
                จันทร์</b></th>
            <th class=' text-center ' width="14.285%" style="black;font-size: 14px"><b>
                อังคาร</b></th>
            <th class=' text-center ' width="14.285%" style="black;font-size: 14px"><b>
                พุธ</b></th>
            <th class=' text-center ' width="14.285%" style="black;font-size: 14px"><b>
                พฤหัสบดี</b></th>
            <th class=' text-center ' width="14.285%" style="black;font-size: 14px"><b>
                ศุกร์</b></th>
            <th class=' text-center ' width="14.285%" style="black;font-size: 14px"><b>
                เสาร์</b></th>
            <th class=' text-center ' width="14.285%" style="black;font-size: 14px"><b>
                อาทิตย์</b></th>
        </thead>
        <tbody>

            @for($i=1;$i<=$day;$i++) @if($i==1) <tr>
                @for($x=1;$x<$round;$x++) <td>
                    </td>
                    @endfor
                    @endif
                    <td valign="top" style="height: 80px" id="act_calen{{$i}}" onclick="act_calendar({{$year}},{{date('m',$now)}},{{$i}})">
                        @if($i == date('j') && date('n') == $n && date('Y') == $year-543) <div class="text-right" style="font-size: 14px;color:red;"><b>{{$i}}</b></div><br> @else <div class="text-right" style="font-size: 14px"> {{$i}} <br> </div>@endif 
                        @if(isset($calendar[$i]))<div class="py-0.5 bg-pending/20 dark:bg-pending/30 rounded relative text-center">{{$calendar[$i]}} </div> @endif
                    </td>
                    @if($round==7)
                    </tr>
                    @if($i != $day)
                    <tr>
                        @endif
                        <?php $round = 0; ?>
                        @endif

                        @if($i == $day)
                        <?php $a = 7 - $round; ?>
                        @if($a!=7)
                        @for($z=1;$z<=$a;$z++) <td>
                            </td>
                            @endfor
                            @endif
                            @endif
                            <?php $round++; ?>
                    @endfor
        </tbody>
    </table>
    <br>
    <input type="hidden" id="save_date" >
    <input type="hidden" id="save_month" value="{{$now}}">
    <input type="hidden" id="save_year" value="{{$year}}">

    @include("backend.layout.script")
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>

</html>