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

            <!-- BEGIN: Content -->
            <h2 class="intro-y text-lg font-medium mt-10">ข้อมูลใบจอง</h2>

            <form id="menuForm" method="post" action="" enctype="multipart/form-data" >
            @csrf
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
                                        }elseif($row->status == 'Pay'){
                                            $style = 'color: #20B2AA;';
                                            // $style = 'color: goldenrod;';
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
                                        <p>วันที่จอง : {{ date('d/m/Y H:i', strtotime('+543 Years', strtotime($row->created_at))) }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label">Sale</label>
                                        <select name="sale_id" id="sale_id" class="form-control select2" required>
                                            <option value="">กรุณาเลือก</option>
                                            @foreach (@$sales as $sale)
                                            <option value="{{@$sale->id}}" @if($row->sale_id == $sale->id) selected @endif>{{@$sale->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <br>
                                <hr>
                                <br>
                                @php
                                    $month = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
                                    $tours = App\Models\Backend\TourModel::where(['status'=>'on'])->whereNull('deleted_at')->get();
                                    $periods = App\Models\Backend\TourPeriodModel::where(['tour_id'=>@$row->tour_id,'status_display'=>'on'])->whereNull('deleted_at')->get();
                                @endphp
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label">ทัวร์</label>
                                        <select class="form-control select2" name="tour_id" id="tour_id" required>
                                            <option value="">กรุณาเลือก</option>
                                            @foreach (@$tours as $t)
                                            <option value="{{$t->id}}" @if(@$row->tour_id == $t->id) selected @endif>{{@$t->code}} {{$t->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <input type="hidden" value="{{url('webpanel/call-period')}}" id="urlPeriodsDetail">
                                    <input type="hidden" value="{{url('webpanel/booking-form/load-price')}}" id="urlLoadPrice">
                                    <div class="col-span-12 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label">วันที่เดินทาง</label>
                                        <select class="form-control" name="period_id" id="period_id" required>
                                            <option value="">กรุณาเลือก</option>
                                            @foreach (@$periods as $p)
                                            <option value="{{$p->id}}" @if(@$row->period_id == $p->id) selected @endif>{{date('d',strtotime($p->start_date))}} {{$month[date('n',strtotime($p->start_date))]}} {{  date('y', strtotime('+543 Years', strtotime($p->start_date))) }} - {{date('d',strtotime($p->end_date))}} {{$month[date('n',strtotime($p->end_date))]}} {{  date('y', strtotime('+543 Years', strtotime($p->end_date))) }} </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                {{-- <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <label for="crud-form-1" class="form-label">ชื่อทัวร์</label>
                                        <input type="text"  class="form-control" value="{{$tour->name}}" readonly>
                                    </div>
                                </div> --}}
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-3">
                                        <label for="crud-form-1" class="form-label">ชื่อ</label>
                                        <input type="text" name="name"  class="form-control" value="{{$row->name}}">
                                    </div>
                                    <div class="col-span-12 lg:col-span-3">
                                        <label for="crud-form-1" class="form-label">นามสกุล</label>
                                        <input type="text" name="surname" class="form-control"  value="{{$row->surname}}">
                                    </div>
                                    <div class="col-span-12 lg:col-span-3">
                                        <label for="crud-form-1" class="form-label">อีเมล</label>
                                        <input type="text" name="email" class="form-control" value="{{$row->email}}">
                                    </div>
                                    <div class="col-span-12 lg:col-span-3">
                                        <label for="crud-form-1" class="form-label">เบอร์โทร</label>
                                        <input type="text" name="phone" class="form-control"  value="{{$row->phone}}">
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <label for="crud-form-1" class="form-label">ความต้องการพิเศษ</label>
                                        <textarea  name="detail" class="form-control" rows="7">{{$row->detail}}</textarea>
                                    </div>
                                </div>
                                <br>
                                <hr>
                                <br>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-4">
                                        <label for="crud-form-1" class="form-label">จำนวนผู้ใหญ่พักคู่</label>
                                        <input type="text" class="form-control input-number" id="qty_1" name="num_twin" onchange="cal_price(1)" onkeypress="return check_number(this)" value="{{$row->num_twin}}">
                                    </div>
                                    <div class="col-span-12 lg:col-span-4">
                                        <label for="crud-form-1" class="form-label">ราคาต่อคนผู้ใหญ่พักคู่</label>
                                        <input type="text" class="form-control" id="price1" name="price1" onchange="cal_price(1)" value="{{$row->price1}}">
                                    </div>
                                    <div class="col-span-12 lg:col-span-4">
                                        <label for="crud-form-1" class="form-label">ราคารวมผู้ใหญ่พักคู่</label>
                                        <input type="text" class="form-control cal_price" id="sum_price1" name="sum_price1" value="{{$row->sum_price1}}" readonly>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-4">
                                        <label for="crud-form-1" class="form-label">จำนวนผู้ใหญ่พักเดี่ยว</label>
                                        <input type="text" class="form-control input-number" id="qty_2" name="num_single" onchange="cal_price(2)" onkeypress="return check_number(this)" value="{{$row->num_single}}">
                                    </div>
                                    <div class="col-span-12 lg:col-span-4">
                                        <label for="crud-form-1" class="form-label">ราคาต่อคนผู้ใหญ่พักเดี่ยว</label>
                                        <input type="text" class="form-control" id="price2" name="price2" onchange="cal_price(2)" value="{{$row->price2}}">
                                    </div>
                                    <div class="col-span-12 lg:col-span-4">
                                        <label for="crud-form-1" class="form-label">ราคารวมผู้ใหญ่พักเดี่ยว</label>
                                        <input type="text" class="form-control cal_price" id="sum_price2" name="sum_price2" value="{{$row->sum_price2}}" readonly>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-4">
                                        <label for="crud-form-1" class="form-label">จำนวนเด็กมีเตียง</label>
                                        <input type="text" class="form-control input-number" id="qty_3" name="num_child" onchange="cal_price(3)" onkeypress="return check_number(this)" value="{{$row->num_child}}">
                                    </div>
                                    <div class="col-span-12 lg:col-span-4">
                                        <label for="crud-form-1" class="form-label">ราคาต่อคนเด็กมีเตียง</label>
                                        <input type="text" class="form-control" id="price3" name="price3" onchange="cal_price(3)" value="{{$row->price3}}">
                                    </div>
                                    <div class="col-span-12 lg:col-span-4">
                                        <label for="crud-form-1" class="form-label">ราคารวมเด็กมีเตียง</label>
                                        <input type="text" class="form-control cal_price" id="sum_price3" name="sum_price3" value="{{$row->sum_price3}}" readonly>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-4">
                                        <label for="crud-form-1" class="form-label">จำนวนเด็กไม่มีเตียง</label>
                                        <input type="text" class="form-control input-number" id="qty_4" name="num_childnb" onchange="cal_price(4)" onkeypress="return check_number(this)" value="{{$row->num_childnb}}">
                                    </div>
                                    <div class="col-span-12 lg:col-span-4">
                                        <label for="crud-form-1" class="form-label">ราคาต่อคนเด็กไม่มีเตียง</label>
                                        <input type="text" class="form-control" id="price4" name="price4" onchange="cal_price(4)" value="{{$row->price4}}">
                                    </div>
                                    <div class="col-span-12 lg:col-span-4">
                                        <label for="crud-form-1" class="form-label">ราคารวมเด็กไม่มีเตียง</label>
                                        <input type="text" class="form-control cal_price" id="sum_price4" name="sum_price4" value="{{$row->sum_price4}}" readonly>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-4">
                                    </div>
                                    <div class="col-span-12 lg:col-span-4">
                                    </div>
                                    <div class="col-span-12 lg:col-span-4">
                                        <label for="crud-form-1" class="form-label">ราคารวม</label>
                                        <input type="text" class="form-control" id="total_price" name="total_price" value="{{$row->total_price}}" readonly>
                                    </div>
                                </div>
                                <input type="hidden" id="total_qty" name="total_qty" value="{{$row->total_qty}}">
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-4">
                                        <label for="crud-form-1" class="form-label"><span class="text-danger">สถานะการจอง</span></label>
                                        <select name="status" id="status" class="form-control" >
                                            <option value="Booked" @if($row->status == 'Booked') selected @endif>Booked</option>
                                            <option value="Waiting" @if($row->status == 'Waiting') selected @endif>Wait List</option>
                                            <option value="Pay" @if($row->status == 'Pay') selected @endif>Wait Pay</option>
                                            <option value="Success" @if($row->status == 'Success') selected @endif>Success</option>
                                            <option value="Cancel" @if($row->status == 'Cancel') selected @endif>Cancel</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <label for="crud-form-1" class="form-label">Remark</label>
                                        <textarea  name="remark" class="form-control">{{$row->remark}}</textarea>
                                    </div>
                                </div>
                                <div class="text-center mt-5">
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

        $('#tour_id').change(function () {
            var tour_id = $(this).val();
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

            $.ajax({
                type: "POST",
                url: $('#urlPeriodsDetail').val(),
                data: {
                    _token:"{{ csrf_token() }}",
                    tour_id:tour_id,
                },
                success: function(res){
                    var myJSON = JSON.parse(res);
                    if (myJSON.r_status == 'y') {
                        v_detail = myJSON.r_detail;
                        $('#period_id').html(v_detail);
                    }
                }
            });
        });
        
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
                    url: $('#urlLoadPrice').val(),
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
