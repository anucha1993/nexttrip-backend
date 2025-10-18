<!DOCTYPE html>
<html lang="en" class="light">
<!-- BEGIN: Head -->

<head>

    <!-- BEGIN: CSS Assets-->
    @include("backend.layout.css")
    <!-- END: CSS Assets-->

    <style>
        .containerrating input[type="radio"] {
            display: none;

            &:checked + label,
            &:checked ~ label {
                color: #d8a75b;
            }
        }

        .containerrating label {
            color: #959595;
            font-size: 30px;
            margin: 0 3px;

            &:hover,
            &:hover ~ label {
                color: #d8a75b;
            }
        }
        .containerrating {
            margin: 0 auto;
            direction: rtl;
            float: left;
        }
    </style>
    
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

            <!-- BEGIN: Content -->
            <h2 class="intro-y text-lg font-medium mt-10">ฟอร์มข้อมูล</h2>

            <form id="menuForm" method="post" action="" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-12 gap-6 mt-5">
                    <div class="col-span-12 lg:col-span-12 2xl:col-span-12 box p-3">
                        <div class="intro-y col-span-12 lg:col-span-6">
                            <!-- BEGIN: Form Layout -->
                            <div class="intro-y box p-5 place-content-center">
                                <div class="flex flex-col sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                                    <div class="form-check form-switch w-full sm:w-auto sm:ml-0 mt-3 sm:mt-0">
                                        <b><label>เปลี่ยนการแสดงสินค้า</label></b>
                                        <select name="status_display" id="status_display" class="form-control ml-1" style="width: 120px">
                                            <option value="" selected hidden>เลือกการแสดงสินค้า</option>
                                            <option value="draft" @if($row->status_display == "draft") selected @endif>Draft</option>
                                            <option value="on" @if($row->status_display == "on") selected @endif>On</option>
                                            <option value="off" @if($row->status_display == "off") selected @endif>Off</option>
                                        </select>
                                    </div>
                                    <div class="form-check form-switch w-full sm:w-auto sm:ml-2 mt-3 sm:mt-0">
                                        <b><label>เปลี่ยนสถานะวางขาย</label></b>
                                        <select name="status_period" id="status_period" class="form-control ml-1" style="width: 120px">
                                            <option value="" selected hidden>เลือกสถานะวางขาย</option>
                                            @foreach($period_status as $ps)
                                                <option value="{{$ps->id}}" @if($row->status_period == $ps->id) selected @endif>{{$ps->status_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- ข้อมูล Period --}}
                                <div class="overflow-x-auto">
                                    <table class="table table-striped table-bordered" style="width: 100%;" id="del_period0">
                                        <thead class="table-light">
                                            <tr>
                                                <th  class="vertid text-center">เริ่มต้น</th>
                                                <th  class="vertid text-center">สิ้นสุด</th>
                                                <th  class="vertid text-center">ผู้ใหญ่(พักคู่)</th>
                                                <th  class="vertid text-center">พักเดี่ยว</th>
                                                <th  class="vertid text-center" colspan="2">เด็ก(มีเตียง)</th>
                                                <th  class="vertid text-center" colspan="2">เด็ก(ไม่มีเตียง)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <input type="date" id="start_date0" onchange="return cal_date(0)" class="form-control start_date" name="start_date" value="{{@$row->start_date}}">
                                                </td>
                                                <td>
                                                    <input type="date" id="end_date0" onchange="return cal_date(0)" class="form-control end_date" name="end_date" value="{{@$row->end_date}}">
                                                </td>
                                                <td>
                                                    <input type="text" id="price10" onkeypress="return check_number(this)" class="form-control price1" name="price1" value="{{@$row->price1}}"><br>
                                                    <center><label>ลดราคา</label></center>
                                                    <input type="text" id="special_price10" onkeypress="return check_number(this)" class="form-control special_price1" name="special_price1" value="{{@$row->special_price1}}">
                                                </td>
                                                <td>
                                                    <input type="text" id="price20" onkeypress="return check_number(this)" class="form-control price2" name="price2" value="{{@$row->price2}}"><br>
                                                    <center><label>ลดราคา</label></center>
                                                    <input type="text" id="special_price20" onkeypress="return check_number(this)" class="form-control special_price2" name="special_price2" value="{{@$row->special_price2}}">
                                                </td>
                                                <td colspan="2">
                                                    <input type="text" id="price30" onkeypress="return check_number(this)" class="form-control price3" name="price3" value="{{@$row->price3}}"><br>
                                                    <center><label>ลดราคา</label></center>
                                                    <input type="text" id="special_price30" onkeypress="return check_number(this)" class="form-control special_price3" name="special_price3" value="{{@$row->special_price3}}">
                                                </td>
                                                <td colspan="2">
                                                    <input type="text" id="price40" onkeypress="return check_number(this)" class="form-control price4" name="price4" value="{{@$row->price4}}"><br>
                                                    <center><label>ลดราคา</label></center>
                                                    <input type="text" id="special_price40" onkeypress="return check_number(this)" class="form-control special_price4" name="special_price4" value="{{@$row->special_price4}}">
                                                </td>
                                            </tr>
                                        </tbody>
                                        <thead class="table-light">
                                            <tr>
                                                <th  class="vertid text-center" colspan="2">โปรโมชั่น</th>
                                                <th  class="vertid text-center">โปรโมชั่นเริ่มต้น</th>
                                                <th  class="vertid text-center">โปรโมชั่นสิ้นสุด</th>
                                                <th  class="vertid text-center">Day</th>
                                                <th  class="vertid text-center">Night</th>
                                                <th  class="vertid text-center">Group Size</th>
                                                <th  class="vertid text-center">จำนวน</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="2">
                                                    <select id="promotion_id0" class="form-control promotion_id" name="promotion_id">
                                                        <option value="">กรุณาเลือก</option>
                                                        @foreach($promotion as $pro)
                                                        <option value="{{$pro->id}}" @if($row->promotion_id == $pro->id) selected @endif>{{$pro->promotion_name}}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="date" id="pro_start_date0" class="form-control pro_start_date" name="pro_start_date" value="{{@$row->pro_start_date}}">
                                                </td>
                                                <td>
                                                    <input type="date" id="pro_end_date0" class="form-control pro_end_date" name="pro_end_date" value="{{@$row->pro_end_date}}">
                                                </td>
                                                <td>
                                                    <input type="text" id="day0" onkeypress="return check_number(this)" class="form-control day" name="day" value="{{@$row->day}}">
                                                </td>
                                                <td>
                                                    <input type="text" id="night0" onkeypress="return check_number(this)" class="form-control night" name="night" value="{{@$row->night}}">
                                                </td>
                                                <td>
                                                    <input type="text" id="group0" onkeypress="return check_number(this)" class="form-control group" name="group" value="{{@$row->group}}">
                                                </td>
                                                <td>
                                                    <input type="text" id="count0" onkeypress="return check_number(this)" class="form-control count" name="count" value="{{@$row->count}}">
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <br>
                                </div>
                                {{-- ข้อมูล Period --}}

                                

                                <div class="text-center mt-10">
                                    <button type="submit" class="btn btn-primary w-24">บันทึกข้อมูล</button>
                                    <a class="btn btn-outline-danger w-24 mr-1" href="{{ url("$segment/$folder") }}" >ยกเลิก</a>
                                </div>
                            </div>
                            <!-- END: Form Layout -->
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- BEGIN: JS Assets-->
        @include("backend.layout.script")
        
        <script>
            function check_number(ele) {
                var vchar = String.fromCharCode(event.keyCode);
                if ((vchar<'0' || vchar>'9') && (vchar != '.')) return false;
                ele.onKeyPress=vchar;
            }

            function cal_date(d) {
                var start = $("#start_date"+d).val();
                var end = $("#end_date"+d).val();

                if(start && end){
                    var startDate = new Date(start);
                    var endDate = new Date(end);

                    // คำนวณจำนวนวันระหว่างวันเริ่มต้นและวันสิ้นสุด
                    var timeDiff = endDate.getTime() - startDate.getTime();
                    var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));

                    var day = daysDiff + 1;
                    var night = daysDiff;

                    if(daysDiff >= 0){
                        $("#day"+d).val(day);
                        $("#night"+d).val(night);
                    }else{
                        $("#day"+d).val("");
                        $("#night"+d).val("");
                    }

                }else{
                    $("#day"+d).val("");
                    $("#night"+d).val("");
                }
            }
        </script>
        <!-- END: JS Assets-->
</body>

</html>
