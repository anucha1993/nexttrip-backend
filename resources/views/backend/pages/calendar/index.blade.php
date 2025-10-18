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
    <!-- BEGIN: Mobile Menu -->
    @include("backend.layout.mobile-menu")
    <!-- END: Mobile Menu -->
    <div class="flex">
        <!-- BEGIN: Side Menu -->
        @include("backend.layout.side-menu")
        <!-- END: Side Menu -->


        <!-- BEGIN: Content -->
        <div class="content">
            <!-- BEGIN: Top Bar -->
            @include("backend.layout.topbar")
            <!-- END: Top Bar -->

            <h2 class="intro-y text-lg font-medium mt-10">ปฏิทินวันหยุด</h2>
            <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                <div class="hidden md:block mx-auto text-slate-500"></div>
               
            </div>
            <br>
            <div class="intro-y box p-5 place-content-center">
                <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                    <div class="hidden md:block mx-auto text-slate-500"></div>
                </div>
                <div class="overflow-x-auto" style="background-color:white;padding:15px;border-radius:5px;">
                    <div class="month" id="show_calendar">
                        <div class="row">
                            <?php $monthTH = ['','มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];   
                                  $month = ['','01','02','03','04','05','06','07','08','09','10','11','12'];   
                                  $date = \App\Models\Backend\CalendarModel::whereMonth('start_date',$month[$nm])->whereYear('start_date',$year-543)->get();
                                  $calendar = array();
                                  foreach ($date as $dd) {
                                    $start = strtotime($dd->start_date);
                                    $end = strtotime($dd->end_date);
                                    while ($start <= $end) {
                                        if(date('n',$start) == $nm){
                                            $calendar[date('j',$start)] = $dd->holiday;
                                        }
                                        $start = $start+86400;   
                                    }
                                  }
                            ?>
                            <div class="flex">
                                <button class="btn btn-outline-secondary mr-1 mb-2" style="background-color: #FF6C00;" onclick="remonth('r')"><span style="color: aliceblue"><b> <  </b></span></button> 
                                <div class="font-medium text-base mx-auto" style="font-size: 18px;color:#FF6C00;"><b>{{$monthTH[$nm]}} {{$year}}</b></div>
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
                                                @if($i == date('j') && date('n') == $nm && date('Y') == $year-543) <div class="text-right" style="font-size: 14px;color:red;"><b>{{$i}}</b></div><br> @else <div class="text-right" style="font-size: 14px"> {{$i}} <br> </div>@endif 
                                                @if(isset($calendar[$i]))<div class="py-0.5 bg-pending/20 dark:bg-pending/30 rounded  text-center"> {{$calendar[$i]}} </div> @endif
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
                        <input type="hidden" id="save_year"  value="{{$year}}">
                    </div>
                </div>
            </div>
            <br>
            <h2 class="intro-y text-lg font-medium mt-10">ข้อมูลวันหยุด</h2>
            <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                <form method="post" action="{{url('webpanel/calendar/edit-status')}}" enctype="multipart/form-data">
                    @csrf
                        <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                            <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0" >
                                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">ระยะเวลาเฟดรูปภาพ <span class="text-danger">(หน่วยเป็นวินาที)</span></label>
                                    <input type="number" value="{{$row->time_calendar}}" min="1" max="9999" name="time" class="form-control w-56 box  sm:w-20">
                                </div>
                                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">สถานะการสไลด์รูปภาพ</label>
                                    <select name="slide" class="form-select w-full sm:w-20">
                                        <option value="on" @if($row->status_calendar == 'on') selected @endif>เปิด</option>
                                        <option value="off" @if($row->status_calendar == 'off') selected @endif>ปิด</option>
                                    </select>
                                </div>
                            </div>
                            <div class="w-full sm:w-auto mt-3 mr-2 sm:mt-0 sm:ml-auto md:ml-0">
                                <button type="submit" class="btn btn-success">บันทึกข้อมูล</button>
                            </div>
                        </div>
                    </form>
                <div class="hidden md:block mx-auto text-slate-500"></div>
                <div class="float-right">
                    @if(@$menu_control->add == "on")
                    <a href="{{ url("$segment/$folder/add") }}" class="btn btn-primary shadow-md mr-2">เพิ่มข้อมูล</a>
                    @endif
                </div>
            </div>
            <br>
            <div class="intro-y box p-5 place-content-center">
                <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                    <div class="hidden md:block mx-auto text-slate-500"></div>
                </div>
                <div class="overflow-x-auto" style="background-color: #F5F5F5;padding:15px;border-radius:5px;">
                    <table id="datatable" class="table table-report"></table>
                </div>
            </div>
                
        <!-- BEGIN: JS Assets-->
        @include("backend.layout.script")
        <script>
            function remonth(g) {
                var month = document.getElementById('save_month').value;
                var year = document.getElementById('save_year').value;
                $.ajax({
                    type: 'GET',
                    url: '{{url("webpanel/calendar/gen-calendar")}}',
                    data: {
                        _token: '{{csrf_token()}}',
                        type:g,
                        month:month,
                        year:year,
                    },
                    success: function (data) {
                        document.getElementById('show_calendar').innerHTML = data;
                    }
                });
            }
            var fullUrl = window.location.origin + window.location.pathname;
            var oTable;

            $(function () {
                oTable = $('#datatable').DataTable({
                    searching: false,
                    ordering: false,
                    lengthChange: false,
                    pageLength: 10,
                    processing: true,
                    serverSide: true,
                    "language": {
                        "lengthMenu": "แสดง _MENU_ แถว",
                        "zeroRecords": "ไม่พบข้อมูล",
                        "info": "แสดงหน้า _PAGE_ จาก _PAGES_ หน้า",
                        "search": "ค้นหา",
                        "infoEmpty": "",
                        "infoFiltered": "",
                        "paginate": {
                            "first": "หน้าแรก",
                            "previous": "ย้อนกลับ",
                            "next": "ถัดไป",
                            "last": "หน้าสุดท้าย"
                        },
                        'processing': "กำลังโหลดข้อมูล",
                    },
                    iDisplayLength: 25,
                    ajax: {
                        url: fullUrl + "/datatable",
                        data: function (d) {
                            d.Like = {};
                            $('.myLike').each(function () {
                                if ($.trim($(this).val()) && $.trim($(this).val()) != '0') {
                                    d.Like[$(this).attr('name')] = $.trim($(this).val());
                                }
                            });
                            oData = d;
                        },
                        method: 'POST'
                    },
                    columns: [
                        { data: 'img', title: '<center>รูปปก</center>', className: 'items-center justify-center' },
                        { data: 'holiday', title: '<center>วันหยุด</center>', className: 'whitespace-nowrap w-40 text-center' },
                        { data: 'date', title: '<center>วันที่</center>', className: 'whitespace-nowrap w-30 text-center' },
                        { data: 'status', title: '<center>สถานะ</center>', className: 'items-center w-10 text-center' },
                        { data: 'action', title: '<center>จัดการ</center>', className: 'whitespace-nowrap w-20 text-center', },
                    ],
                    rowCallback: function (nRow, aData, dataIndex) {

                    }
                });
                $('.myWhere,.myLike,.myCustom,#onlyTrashed').on('change', function (e) {
                    // console.log($(this).val())
                    oTable.draw();
                });
            });
            function deleteItem(ids) {
                const id = [ids];
                if (id.length > 0) {
                    destroy(id)
                }
            }
            function destroy(id) {
                Swal.fire({
                    title: "ลบข้อมูล",
                    text: "คุณต้องการลบข้อมูลใช่หรือไม่?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return fetch(fullUrl + '/destroy/' + id)
                            .then(response => response.json())
                            .then(data => location.reload())
                            .catch(error => {
                                Swal.showValidationMessage(`Request failed: ${error}`)
                            })
                    }
                });
            }

            function status(ids) {
                const $this = $(this),
                    id = ids;
                $.ajax({
                    type: 'get',
                    url: fullUrl + '/status/' + id,
                    success: function(res) {
                        if (res == false) {
                            $(this).prop('checked', false)
                        }
                    }
                });
            }
           
        </script>
        <!-- END: JS Assets-->
</body>

</html>
