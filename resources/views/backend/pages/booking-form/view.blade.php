<!DOCTYPE html>
<html lang="en" class="light">
<!-- BEGIN: Head -->
<head>
    <!-- BEGIN: CSS Assets-->
    @include("backend.layout.css")

    <style>
        .table-report:not(.table-report--bordered):not(.table-report--tabulator) td{
            /* box-shadow: none !important; */
        }
    </style>
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

            <!-- BEGIN: Content -->
            <h2 class="intro-y text-lg font-medium mt-10">ข้อมูลใบจอง</h2>
                <div class="grid grid-cols-12 gap-6 mt-5">
                    <div class="col-span-12 lg:col-span-6 2xl:col-span-12">
                        <div class="intro-y col-span-12 lg:col-span-6">
                            <!-- BEGIN: Form Layout -->
                            <div class="intro-y box p-5 place-content-center">
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    @php
                                        if($row->status == 'Booked'){
                                            $style = 'color: blue;';
                                            // $style = 'color: rgb(31, 161, 133);';
                                        }elseif($row->status == 'Waiting'){
                                            $style = 'color: #f39d12;';
                                            // $style = 'color: #f39d12;';
                                        // }elseif($row->status == 'Pay'){
                                        //     $style = 'color: #20B2AA;';
                                        //     // $style = 'color: goldenrod;';
                                        }elseif($row->status == 'Success'){
                                            $style = 'color: green;';
                                        }elseif($row->status == 'Cancel'){
                                            $style = 'color: red;';
                                        }else{
                                            $style = '';
                                        }
                                    @endphp
                                    <div class="col-span-12 lg:col-span-6">
                                        <p>Ref.Booking : {{$row->code}} ( Status  : <span style="{{$style}}"> {{$row->status}}</span>)</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        @php
                                            $user = App\Models\Backend\User::find(@$row->sale_id);
                                        @endphp
                                        <p>Reservation By : {{ @$user->name }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <p>วันที่จอง : {{ date('d/m/Y H:i', strtotime('+543 Years', strtotime($row->created_at))) }}</p>
                                    </div>
                                </div>
                                <br>
                                <hr>
                                <br>
                                @php
                                    $month = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
                                    $airline = App\Models\Backend\TravelTypeModel::find(@$tour->airline_id);
                                    $wholesale = App\Models\Backend\WholesaleModel::find(@$tour->wholesale_id);
                                    $period = App\Models\Backend\TourPeriodModel::find(@$row->period_id);
                                @endphp
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <p style="font-size: 19px;"><b>รายละเอียดการจอง</b></p>
                                    </div>
                                </div>
                                <div class="intro-y box p-5 place-content-center">
                                    <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                                        <div class="hidden md:block mx-auto text-slate-500"></div>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table id="datatable" class="table table-report" style="width: 100% border: 1px solid black;">
                                            <thead>
                                                <tr>
                                                    <th style="width: 20%"></th>
                                                    <th style="width: 8%"></th>
                                                    <th style="width: 52%"></th>
                                                    <th style="width: 20%"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td rowspan="10">
                                                        <p><b>ข้อมูลติดต่อกลับลูกค้า</b></p><br>
                                                        <label for="crud-form-1" class="form-label">ชื่อ-นามสกุล</label>
                                                        <p>{{$row->name .' '. $row->surname}}</p><br>
                                                        <label for="crud-form-1" class="form-label">อีเมล</label>
                                                        <p>{{$row->email}}</p><br>
                                                        <label for="crud-form-1" class="form-label">เบอร์โทร</label>
                                                        <p>{{$row->phone}}</p>
                                                    </td>
                                                    <td colspan="3">
                                                        @if(@$row->num_twin)
                                                            ผู้ใหญ่(พัก2-3ท่าน) : {{@$row->num_twin}} คน , 
                                                        @endif
                                                        @if(@$row->num_single)
                                                            พักเดี่ยว : {{@$row->num_single}} คน , 
                                                        @endif
                                                        @if(@$row->num_child)
                                                            เด็กมีเตียง : {{@$row->num_child}} คน , 
                                                        @endif
                                                        @if(@$row->num_childnb)
                                                            เด็กไม่มีเตียง : {{@$row->num_childnb}} คน
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td rowspan="10" class="text-right" style="vertical-align: top;">
                                                        <img src="{{asset(@$airline->image)}}" alt="">
                                                    </td>
                                                    <td rowspan="10">
                                                        <p><b>{{$tour->name}}</b></p>
                                                        <p><b>{{$tour->code}}</b> @if(@$wholesale) โดย : {{@$wholesale->wholesale_name_th}} @endif</p><br>
                                                        <p>วันที่เดินทาง : {{date('d',strtotime($period->start_date))}} {{$month[date('n',strtotime($period->start_date))]}} {{  date('y', strtotime('+543 Years', strtotime($period->start_date))) }} - {{date('d',strtotime($period->end_date))}} {{$month[date('n',strtotime($period->end_date))]}} {{  date('y', strtotime('+543 Years', strtotime($period->end_date))) }}</p><br>
                                                        <p>ผู้ใหญ่(พัก2-3ท่าน) :<label style="margin-left: 50px;">{{@$row->num_twin}}</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ราคา&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{number_format(@$row->sum_price1,0)}}</p>
                                                        <p>ผู้ใหญ่(พักเดี่ยว) :<label style="margin-left: 67px;">{{@$row->num_single}}</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ราคา&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{number_format(@$row->sum_price2,0)}}</p>
                                                        <p>เด็กมีเตียง :<label style="margin-left: 101px;">{{@$row->num_child}}</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ราคา&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{number_format(@$row->sum_price3,0)}}</p>
                                                        <p>เด็กไม่มีเตียง :<label style="margin-left: 85px;">{{@$row->num_childnb}}</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ราคา&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{number_format(@$row->sum_price4,0)}}</p>
                                                        <p style="margin-left: 198px;">รวม&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{number_format(@$row->total_price,0)}}</p>
                                                    </td>
                                                    <td rowspan="10" class="text-right" style="vertical-align: top;">
                                                        @if(@$tour->word_file)
                                                            <a href="{{@$tour->word_file}}" target="_blank"><i class="fa fa-file-word-o" style="font-size:20px; color:blue; !important"></i></a>&nbsp;&nbsp;&nbsp;
                                                        @endif
                                                        @if(@$tour->pdf_file)
                                                            <a href="{{@$tour->pdf_file}}" target="_blank"><i class="fa fa-file-pdf-o" style="font-size:20px; color:red; !important"></i></a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                        <div class="col-span-12 lg:col-span-12">
                                            <label for="crud-form-1" class="form-label">ความต้องการพิเศษ (ข้อมูลจากหน้าบ้าน)</label>
                                            <textarea  name="detail" class="form-control" rows="7" readonly>{{$row->detail}}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <br>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <label for="crud-form-1" class="form-label">Remark</label>
                                        <textarea  name="remark" class="form-control" readonly>{{$row->remark}}</textarea>
                                    </div>
                                </div>
                                <div class="text-center mt-5">
                                    {{-- <button type="submit" class="btn btn-primary w-24">บันทึกข้อมูล</button> --}}
                                    <a class="btn btn-outline-danger w-24 mr-1" href="{{ url("$segment/$folder") }}" >ย้อนกลับ</a>
                                </div>
                            </div>
                            <!-- END: Form Layout -->
                        </div>
                    </div>
                </div>
        </div>
    </div>
        
    <!-- BEGIN: JS Assets-->
    @include("backend.layout.script")
    
    <script>
        var fullUrl = window.location.origin + window.location.pathname;

        function check_number(ele) {
            var vchar = String.fromCharCode(event.keyCode);
            if ((vchar<'0' || vchar>'9') && (vchar != '.')) return false;
            ele.onKeyPress=vchar;
        }

        function updateTotal() {
            var total = 0;
            $('.input-number').each(function () {
                total += parseInt($(this).val()) || 0;
            });
            $('#total_qty').val(total);
        }
        
        $('#period_id').change(function () {
            var id = $(this).val();
            if(id != ''){
                // เคลียข้อมูลเก่าก่อน
                $('#qty_1').val(0);
                $('#qty_2').val(0);
                $('#qty_3').val(0);
                $('#qty_4').val(0);

                $("#price1").val(0);
                $("#sum_price1").val(0);
                $("#price2").val(0);
                $("#sum_price2").val(0);
                $("#price3").val(0);
                $("#sum_price3").val(0);
                $("#price4").val(0);
                $("#sum_price4").val(0);

                $('#total_qty').val(0);
                $('#total_price').val(0);

                loadPrice(id);
            }else{
                loadPrice(0);
            }

        });

        function loadPrice(id) {
            if(id != 0){
                $.ajax({
                    type: "POST",
                    url: fullUrl + "/load-price",
                    data: {
                        _token:"{{ csrf_token() }}",
                        id:id,
                    },
                    success: function(res){
                        if(res.status == 200){
                            $("#price1").val(res.price1);
                            $("#price2").val(res.price2);
                            $("#price3").val(res.price3);
                            $("#price4").val(res.price4);
                        }else{
                            $("#price1").val(0);
                            $("#price2").val(0);
                            $("#price3").val(0);
                            $("#price4").val(0);
                        }
                    }
                });
            }
            
        }

        function cal_price(id) {
            var price = $('#price'+id).val();
            var qty = $('#qty_'+id).val(); // จำนวน
            var sum_price = price*qty;

            // $('#sum_price_text'+id).text(Number.parseFloat(sum_price).toLocaleString());
            $('#sum_price'+id).val(sum_price);
            
            sum_net= 0;
            $('.cal_price').each(function () {
                sum_net += parseInt($(this).val()); // ราคารวม 4 แบบ
            });
            
            $('#total_price').val(Number.parseFloat(sum_net).toLocaleString());

            updateTotal();
        }
    </script>
    <!-- END: JS Assets-->
</body>

</html>
