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

            <form id="menuForm" method="post" action="" enctype="multipart/form-data" onsubmit="return check_add();">
                @csrf
                <div class="grid grid-cols-12 gap-6 mt-5">
                    <div class="col-span-12 lg:col-span-12 2xl:col-span-12 box p-3">
                        <div class="intro-y col-span-12 lg:col-span-6">
                            <!-- BEGIN: Form Layout -->
                            <div class="intro-y box p-5 place-content-center">

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <b><label for="crud-form-1" class="form-label"><span class="text-danger">*</span> ชื่อ</label></b>
                                        <input type="text" id="name" name="name" class="form-control" required>
                                    </div>
                                </div>

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label"><span class="text-danger">*</span> กลุ่ม</label></b>
                                        <select name="group_id" id="group_id" class="form-control select2">
                                            <option value="" selected hidden>กรุณาเลือกข้อมูล</option>
                                            @foreach ($group as $g)
                                                <option value="{{$g->id}}">{{$g->group_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <font style="color:red; font-size:18px"><b><p class="c-default-1">*** กรุณาเลือกประเภท ทัวร์ เพื่อใช้ในการกำหนดข้อมูลเพิ่มเติม.</p></b></font>
                                        <b><label for="crud-form-1" class="form-label"><span class="text-danger">*</span> ประเภท</label></b>
                                        <select name="type_id" id="type_id" class="form-control select2" required>
                                            <option value="" selected hidden>กรุณาเลือกข้อมูล</option>
                                            @foreach ($type as $t)
                                                <option value="{{$t->id}}">{{$t->type_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3 c-type-1">
                                    <div class="col-span-12 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label"><span class="text-danger">*</span> ทวีป</label></b>
                                        <select name="landmass_id" id="landmass_id" class="form-control select2">
                                            <option value="" selected hidden>กรุณาเลือกข้อมูล</option>
                                            @foreach ($landmass as $land)
                                                <option value="{{$land->id}}">{{$land->landmass_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3 c-type-1">
                                    <div class="col-span-12 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label"><span class="text-danger">*</span> ประเทศ</label></b>
                                        <select name="country_id" id="country_id" class="form-control select2">
                                            <option value="" selected hidden>กรุณาเลือกข้อมูล</option>
                                        </select>
                                    </div>
                                    <div class="col-span-12 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label"><span class="text-danger">*</span> เมือง</label></b>
                                        <select name="city_id" id="city_id" class="form-control select2">
                                            <option value="" selected hidden>กรุณาเลือกข้อมูล</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3 c-type-2">
                                    <div class="col-span-12 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label"><span class="text-danger">*</span> จังหวัด</label></b>
                                        <select name="province_id" id="province_id" class="form-control select2">
                                            <option value="" selected hidden>กรุณาเลือกข้อมูล</option>
                                            @foreach ($province as $p)
                                                <option value="{{$p->id}}">{{$p->name_th}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-span-12 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label"><span class="text-danger">*</span> อำเภอ/เขต</label></b>
                                        <select name="district_id" id="district_id" class="form-control select2">
                                            <option value="" selected hidden>กรุณาเลือกข้อมูล</option>
                                        </select>
                                    </div>
                                </div>
                                
                                {{-- image --}}
                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <b><h6 class="mb-3"><span class="text-danger">*</span> รูปภาพ</h6></b>
                                        <img src="@if(@$row->image == null) {{url("noimage.jpg")}} @else {{asset($row->image)}} @endif" class="img-thumbnail" id="preview">
                                    </div>
                                    <div class="col-span-12 lg:col-span-12">
                                        <small class="help-block">* รองรับไฟล์ <strong class="text-danger">(jpg, jpeg, png)</strong> เท่านั้น</small>
                                        <div class="col-span-6 lg:col-span-6">
                                            <input type="file" class="custom-file-input block w-80 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                name="image" id="image" accept="image/png, image/jpeg, image/jpg" required>
                                        </div>
                                    </div>
                                </div>
                                <br>
                                {{-- image --}}

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label">จำนวนวัน</label></b>
                                        <input type="text" id="num_day" name="num_day" class="form-control">
                                    </div>
                                    <div class="col-span-12 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label">รหัส</label></b>
                                        <input type="text" id="code" name="code" class="form-control">
                                    </div>
                                </div>

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <div class="row">
                                            <b><label for="crud-form-1" class="form-label">ระดับดาว</label></b>
                                        </div>
                                        <div class="row">
                                            <div class="containerrating">

                                                <input id="star01" type="radio" name="rating" value="5">
                                                <label for="star01">&#9733;</label>
    
                                                <input id="star02" type="radio" name="rating" value="4">
                                                <label for="star02">&#9733;</label>
    
                                                <input id="star03" type="radio" name="rating" value="3">
                                                <label for="star03">&#9733;</label>
    
                                                <input id="star04" type="radio" name="rating" value="2">
                                                <label for="star04">&#9733;</label>
    
                                                <input id="star05" type="radio" name="rating" value="1">
                                                <label for="star05">&#9733;</label>
    
                                            </div>
                                        </div>
                                        {{-- <input type="text" id="rating" name="rating" class="form-control"> --}}
                                    </div>
                                    <div class="col-span-12 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label">เที่ยวบิน</label></b>
                                        <select name="airline_id" id="airline_id" class="form-control select2">
                                            <option value="" selected hidden>กรุณาเลือกข้อมูล</option>
                                            @foreach ($travel as $tra)
                                                <option value="{{$tra->id}}">{{$tra->travel_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label"><span class="text-danger">*</span> ราคา</label></b>
                                        <input type="text" id="price" name="price" class="form-control">
                                    </div>
                                    <div class="col-span-12 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label">ราคาพิเศษ</label></b>
                                        <input type="text" id="special_price" name="special_price" class="form-control">
                                    </div>
                                </div>

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <b><label for="crud-form-1" class="form-label"><span class="text-danger">*</span> รายละเอียด</label></b>
                                        <textarea type="text" id="description" name="description" class="form-control" rows="5"></textarea>
                                    </div>
                                </div>

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-6 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label">เที่ยว</label></b>
                                        <textarea type="text" id="travel" name="travel" class="form-control" rows="5"></textarea>
                                    </div>
                                    <div class="col-span-6 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label">ช็อป</label></b>
                                        <textarea type="text" id="shop" name="shop" class="form-control" rows="5"></textarea>
                                    </div>
                                </div>

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-6 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label">กิน</label></b>
                                        <textarea type="text" id="eat" name="eat" class="form-control" rows="5"></textarea>
                                    </div>
                                    <div class="col-span-6 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label">พิเศษ</label></b>
                                        <textarea type="text" id="special" name="special" class="form-control" rows="5"></textarea>
                                    </div>
                                </div>

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-6 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label">พัก</label></b>
                                        <textarea type="text" id="stay" name="stay" class="form-control" rows="5"></textarea>
                                    </div>
                                </div>

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <b><label for="crud-form-1" class="form-label">Video</label></b>
                                        <input type="text" id="video" name="video" class="form-control">
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <b><label for="crud-form-1" class="form-label">Video</label></b>
                                        <input type="text" id="video" name="video" class="form-control">
                                    </div>
                                </div>

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-6 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label">File PDF</label></b>
                                        <small class="help-block">* รองรับไฟล์ <strong class="text-danger">(pdf)</strong> เท่านั้น</small>
                                        <div class="col-span-6 lg:col-span-6">
                                            <input type="file" class="custom-file-input block w-80 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                name="pdf_file" id="pdf_file" accept="application/pdf">
                                        </div>
                                    </div>

                                    <div class="col-span-6 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label">File Word</label></b>
                                        <small class="help-block">* รองรับไฟล์ <strong class="text-danger">(docx)</strong> เท่านั้น</small>
                                        <div class="col-span-6 lg:col-span-6">
                                            <input type="file" class="custom-file-input block w-80 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                name="word_file" id="word_file" accept=".doc, .docx">
                                        </div>
                                    </div>
                                </div>

                                {{-- หลิว 2 gallery --}}
                                <h3 class="mb-3"><b>อัลบั้มรูปภาพ</b></h3>
                                    <div id="preview-gallery" class="grid grid-cols-12 gap-6 mt-5"></div><br>
                                        <input type="file"
                                            class="custom-file-input w-50 rounded-lg border border-gray-300 cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                            name="img[]" id="image-gallery" accept="image/png, image/jpeg, image/jpg" multiple onchange="readGallery()">
                                            <button type="button" class="btn btn-danger" onclick="removeImg()">Reset</button>
                                        <br>
                                        <small class="help-block">* รองรับไฟล์ <strong class="text-danger">(jpg, jpeg, png)</strong> เท่านั้น</small>
                                    </div>
                                 {{-- หลิว 2 gallery --}}
                                <br>

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <label for="crud-form-1" class="form-label">รายละเอียดทัวร์ <button class="btn btn-primary btn-block" type="button"style="margin: auto;" colspan="2" onclick="add_detail()"><i class="fa fa-plus-circle" ></i> เพิ่ม</button></label>
                                        <table class="table table-striped table-bordered" >
                                            <thead class="table-light">
                                                <tr role="row">
                                                    <th  class="vertid text-center sorting_asc" style="width: 30%">หัวข้อ</th>
                                                    <th  class="vertid text-center" style="width: 30%">รายละเอียด</th>
                                                    <th  class="vertid text-center" style="width: 3%">จัดการ</th>
                                                </tr>
                                            </thead>
                                            <tbody id="data_detail">
                                                {{-- <tr id="del_detail0">
                                                    <td>
                                                        <input type="text" class="form-control" name="detail[0][title][]">
                                                    </td>
                                                    <td>
                                                        <textarea class="form-control" id="detail0" name="detail[0][detail][]" rows="3"></textarea>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-danger del-btn btn-block" type="button" style="margin:auto;" onclick="del_detail(0)">ลบ</button>
                                                    </td>
                                                </tr> --}}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <br>

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <div>
                                            <label for="crud-form-1" class="form-label">ระยะเวลา <button class="btn btn-primary btn-block" type="button"style="margin: auto;" colspan="2" onclick="add_period()"><i class="fa fa-plus-circle" ></i> เพิ่ม</button></label>
                                        </div>
                                        <div id="data_period">
                                            {{-- <table class="table table-striped table-bordered" id="del_period0">
                                                <thead class="table-light">
                                                    <tr role="row">
                                                        <th  class="vertid text-center">เริ่มต้น</th>
                                                        <th  class="vertid text-center">สิ้นสุด</th>
                                                        <th  class="vertid text-center">ผู้ใหญ่(พักคู่)</th>
                                                        <th  class="vertid text-center">ผู้ใหญ่(พักเดี่ยว)</th>
                                                        <th  class="vertid text-center">เด็ก(มีเตียง)</th>
                                                        <th  class="vertid text-center">เด็ก(ไม่มีเตียง)</th>
                                                        <th  class="vertid text-center">สถานะ</th>
                                                        <th  class="vertid text-center"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            <input type="date" class="form-control" name="period[0][start_date][]">
                                                        </td>
                                                        <td>
                                                            <input type="date" class="form-control" name="period[0][end_date][]">
                                                        </td>
                                                        <td>
                                                            <input type="text" onkeypress="return check_number(this)" class="form-control" name="period[0][price1][]"><br>
                                                            <center><label>ลดราคา</label></center>
                                                            <input type="text" onkeypress="return check_number(this)" class="form-control" name="period[0][special_price1][]">
                                                        </td>
                                                        <td>
                                                            <input type="text" onkeypress="return check_number(this)" class="form-control" name="period[0][price2][]"><br>
                                                            <center><label>ลดราคา</label></center>
                                                            <input type="text" onkeypress="return check_number(this)" class="form-control" name="period[0][special_price2][]">
                                                        </td>
                                                        <td>
                                                            <input type="text" onkeypress="return check_number(this)" class="form-control" name="period[0][price3][]"><br>
                                                            <center><label>ลดราคา</label></center>
                                                            <input type="text" onkeypress="return check_number(this)" class="form-control" name="period[0][special_price3][]">
                                                        </td>
                                                        <td>
                                                            <input type="text" onkeypress="return check_number(this)" class="form-control" name="period[0][price4][]"><br>
                                                            <center><label>ลดราคา</label></center>
                                                            <input type="text" onkeypress="return check_number(this)" class="form-control" name="period[0][special_price4][]">
                                                        </td>
                                                        <td>
                                                            <select class="form-control" name="period[0][status][]">
                                                                <option value="1">จอง</option>
                                                                <option value="2">ไลน์</option>
                                                                <option value="3">เต็ม</option>
                                                            </select>
                                                        </td>
                                                        <td class="text-center">
                                                            <button class="btn btn-danger del-btn btn-block" type="button" style="margin:auto;" onclick="del_period(0)">ลบ</button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                                <thead class="table-light">
                                                    <tr role="row">
                                                        <th  class="vertid text-center">Day</th>
                                                        <th  class="vertid text-center">Night</th>
                                                        <th  class="vertid text-center">Group Size</th>
                                                        <th  class="vertid text-center">จำนวน</th>
                                                        <th  class="vertid text-center" colspan="2">โปรโมชั่น</th>
                                                        <th  class="vertid text-center">โปรโมชั่นเริ่มต้น</th>
                                                        <th  class="vertid text-center">โปรโมชั่นสิ้นสุด</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            <input type="text" onkeypress="return check_number(this)" class="form-control" name="period[0][day][]">
                                                        </td>
                                                        <td>
                                                            <input type="text" onkeypress="return check_number(this)" class="form-control" name="period[0][night][]">
                                                        </td>
                                                        <td>
                                                            <input type="text" onkeypress="return check_number(this)" class="form-control" name="period[0][group][]"  >
                                                        </td>
                                                        <td>
                                                            <input type="text" onkeypress="return check_number(this)" class="form-control" name="period[0][count][]"  >
                                                        </td>
                                                        <td colspan="2">
                                                            <select class="form-control" name="period[0][promotion_id][]">
                                                                <option value="">กรุณาเลือก</option>
                                                                @foreach($promotion as $pro)
                                                                <option value="{{$pro->id}}">{{$pro->promotion_name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="date" class="form-control" name="period[0][pro_start_date][]">
                                                        </td>
                                                        <td>
                                                            <input type="date" class="form-control" name="period[0][pro_end_date][]">
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table> --}}
                                            <br>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-center mt-10">
                                    <button type="submit" class="btn btn-primary w-24">บันทึกข้อมูล</button>
                                    <a class="btn btn-outline-danger w-24 mr-1" href="{{ url("$segment/$folder") }}" >ยกเลิก</a>
                                </div>
                            </div>
                            <!-- END: Form Layout -->
                        </div>

                        <input type="hidden" value="{{url('call-country')}}" id="urlCountry">
                        <input type="hidden" value="{{url('call-city')}}" id="urlCity">
                        <input type="hidden" value="{{url('call-district')}}" id="urlDistrict">
                    </div>
                </div>
            </form>
        </div>

        <!-- BEGIN: JS Assets-->
        @include("backend.layout.script")
        
        <script>
            function readGallery() {
                const target = $('#preview-gallery');
                target.innerHTML = null;
                var total_file=document.getElementById("image-gallery").files.length;
                // target.find('.new-pre').remove();
                for(var i=0;i<total_file;i++)
                {
                    target.append("<div class='col-span-2 lg:col-span-2 preview-item'><div class='img-thumbnail'><div class='img-preview'><img class='img-fluid' src='"+URL.createObjectURL(event.target.files[i])+"'/></div><div class='caption' style='margin-top:5px;'><i class='fas fa-upload'></i></div></div></div>");
                }
            }
            function removeImg(){
                document.getElementById('preview-gallery').innerHTML = null;
                document.getElementById('image-gallery').value = null;
            }
            
            $( document ).ready(function() {
                p1_display_type_check();
            });

            // main type
            function p1_display_type_check(){

                v_id    = $("#type_id").val();
                v_name  = $("#type_id option:selected").html();

                if(v_id==''){
            
                    $( ".c-default-1" ).show();
                    $( ".c-type-1" ).hide();
                    $( ".c-type-2" ).hide();
                    $( ".c-type-3" ).hide();
                    $( ".c-type-4" ).hide();
                    // $( ".c-default-1" ).html('กรุณาเลือกประเภท Package เพื่อใช้ในการกำหนดข้อมูล');
                    return false;
                }

                if(v_id=='1'){
                    
                    $( ".c-type-1" ).show();   
                    $( ".c-default-1" ).hide();
                    $( ".c-type-2" ).hide();
                    $( ".c-type-3" ).hide();
                    $( ".c-type-4" ).hide();
                    return false;
                }

                if(v_id=='2'){
                    
                    $( ".c-type-2" ).show();   
                    $( ".c-default-1" ).hide();
                    $( ".c-type-1" ).hide();
                    $( ".c-type-3" ).hide();
                    $( ".c-type-4" ).hide();
                    return false;
                }

                if(v_id=='3'){
                    
                    $( ".c-type-3" ).show();   
                    $( ".c-default-1" ).hide();
                    $( ".c-type-1" ).hide();
                    $( ".c-type-2" ).hide();
                    $( ".c-type-4" ).hide();
                    return false;
                }

                if(v_id=='4'){
                    
                    $( ".c-type-4" ).show();   
                    $( ".c-default-1" ).hide();
                    $( ".c-type-1" ).hide();
                    $( ".c-type-2" ).hide();
                    $( ".c-type-3" ).hide();
                    return false;
                }
            } 
            $('#type_id').on('change', p1_display_type_check); 
            $('#landmass_id').on('change', f_call_country); 
            $('#country_id').on('change', f_call_city); 
            $('#province_id').on('change', f_call_district); 

            function f_call_country() {
                l_id    = $("#landmass_id").val();
                $.ajax({            
                    url: $('#urlCountry').val(),
                    data: {
                        p_id: l_id
                    },
                    type    : 'POST',
                    async   : false,
                    success: function(result) {
                        var myJSON = JSON.parse(result);
                        if (myJSON.r_status == 'y') {
                            v_detail = myJSON.r_detail;    
                            $("#country_id").html( v_detail );
                        }
                    }
                });
            }

            function f_call_city() {
                c_id    = $("#country_id").val();
                $.ajax({            
                    url: $('#urlCity').val(),
                    data: {
                        p_id: c_id
                    },
                    type    : 'POST',
                    async   : false,
                    success: function(result) {
                        var myJSON = JSON.parse(result);
                        if (myJSON.r_status == 'y') {
                            v_detail = myJSON.r_detail;    
                            $("#city_id").html( v_detail );
                        }
                    }
                });
            }

            function f_call_district() {
                p_id    = $("#province_id").val();
                $.ajax({            
                    url: $('#urlDistrict').val(),
                    data: {
                        p_id: p_id
                    },
                    type    : 'POST',
                    async   : false,
                    success: function(result) {
                        var myJSON = JSON.parse(result);
                        if (myJSON.r_status == 'y') {
                            v_detail = myJSON.r_detail;    
                            $("#district_id").html( v_detail );
                        }
                    }
                });
            }

            var count_detail = 1;
            function add_detail(){  
                add = '<tr id="del_detail'+count_detail+'" >'+
                    '<td><input type="text" class="form-control" name="detail['+count_detail+'][title][]"></td>'+
                    '<td><textarea class="form-control" name="detail['+count_detail+'][detail][]" rows="3"></textarea></td>'+
                    '<td><button class="btn btn-danger del-btn btn-block" type="button" style="margin:auto;" onclick="del_detail('+count_detail+')">ลบ</button>'+
                    '</td>'+
                    '</tr>';   
                $('#data_detail').append(add);
                count_detail++;  
            }
            function del_detail(d){
                var de = document.getElementById('del_detail'+d);
                de.parentNode.removeChild(de);
            }
            
            var count_period = 1;
            function add_period(){  
                add = '<table class="table table-striped table-bordered" id="del_period'+count_period+'">'+
                    '<thead class="table-light">'+'<tr role="row">'+
                    '<th  class="vertid text-center">เริ่มต้น</th>'+
                    '<th  class="vertid text-center">สิ้นสุด</th>'+
                    '<th  class="vertid text-center">ผู้ใหญ่(พักคู่)</th>'+
                    '<th  class="vertid text-center">ผู้ใหญ่(พักเดี่ยว)</th>'+
                    '<th  class="vertid text-center">เด็ก(มีเตียง)</th>'+
                    '<th  class="vertid text-center">เด็ก(ไม่มีเตียง)</th>'+
                    '<th  class="vertid text-center">สถานะ</th>'+
                    '<th  class="vertid text-center"></th>'+
                    '</tr>'+'</thead>'+

                    '<tbody>'+'<tr>'+
                    '<td><input type="date" class="form-control" name="period['+count_period+'][start_date][]"></td>'+
                    '<td><input type="date" class="form-control" name="period['+count_period+'][end_date][]"></td>'+
                    '<td><input type="text" onkeypress="return check_number(this)" class="form-control" name="period['+count_period+'][price1][]"><br><center><label>ลดราคา</label></center><input type="text" onkeypress="return check_number(this)" class="form-control" name="period['+count_period+'][special_price1][]"></td>'+
                    '<td><input type="text" onkeypress="return check_number(this)" class="form-control" name="period['+count_period+'][price2][]"><br><center><label>ลดราคา</label></center><input type="text" onkeypress="return check_number(this)" class="form-control" name="period['+count_period+'][special_price2][]"></td>'+
                    '<td><input type="text" onkeypress="return check_number(this)" class="form-control" name="period['+count_period+'][price3][]"><br><center><label>ลดราคา</label></center><input type="text" onkeypress="return check_number(this)" class="form-control" name="period['+count_period+'][special_price3][]"></td>'+
                    '<td><input type="text" onkeypress="return check_number(this)" class="form-control" name="period['+count_period+'][price4][]"><br><center><label>ลดราคา</label></center><input type="text" onkeypress="return check_number(this)" class="form-control" name="period['+count_period+'][special_price4][]"></td>'+
                    '<td><select class="form-control" name="period['+count_period+'][status][]"><option value="1">จอง</option><option value="2">ไลน์</option><option value="3">เต็ม</option></select></td>'+
                    '<td class="text-center"><button class="btn btn-danger del-btn btn-block" type="button" style="margin:auto;" onclick="del_period('+count_period+')">ลบ</button>'+
                    '</tr>'+'</body>'+

                    '<thead class="table-light">'+'<tr role="row">'+
                    '<th  class="vertid text-center">Day</th>'+
                    '<th  class="vertid text-center">Night</th>'+
                    '<th  class="vertid text-center">Group Size</th>'+
                    '<th  class="vertid text-center">จำนวน</th>'+
                    '<th  class="vertid text-center" colspan="2">โปรโมชั่น</th>'+
                    '<th  class="vertid text-center">โปรโมชั่นเริ่มต้น</th>'+
                    '<th  class="vertid text-center">โปรโมชั่นสิ้นสุด</th>'+
                    '</tr>'+'</thead>'+
                    '<tbody>'+'<tr>'+
                    '<td><input type="text" onkeypress="return check_number(this)" class="form-control" name="period['+count_period+'][day][]"></td>'+
                    '<td><input type="text" onkeypress="return check_number(this)" class="form-control" name="period['+count_period+'][night][]"></td>'+
                    '<td><input type="text" onkeypress="return check_number(this)" class="form-control" name="period['+count_period+'][group][]"></td>'+
                    '<td><input type="text" onkeypress="return check_number(this)" class="form-control" name="period['+count_period+'][count][]"></td>'+
                    '<td colspan="2"><select class="form-control" name="period['+count_period+'][promotion_id][]">'+
                    '<option value="">กรุณาเลือก</option>'+
                    @foreach($promotion as $pro)
                    '<option value="{{$pro->id}}">{{$pro->promotion_name}}</option>'+
                    @endforeach
                    '</select></td>'+
                    '<td><input type="date" class="form-control" name="period['+count_period+'][pro_start_date][]"></td>'+
                    '<td><input type="date" class="form-control" name="period['+count_period+'][pro_end_date][]"></td>'+
                    '</tr>'+'</body>'+
                    '</table><br>';   
                $('#data_period').append(add);
                count_period++;  
            }
            function del_period(d){
                var de = document.getElementById('del_period'+d);
                de.parentNode.removeChild(de);
            }

            function check_number(ele) {
                var vchar = String.fromCharCode(event.keyCode);
                if ((vchar<'0' || vchar>'9') && (vchar != '.')) return false;
                ele.onKeyPress=vchar;
            }

            $("#image").on('change', function () {
                var $this = $(this)
                const input = $this[0];
                const fileName = $this.val().split("\\").pop();
                $this.siblings(".custom-file-label").addClass("selected").html(fileName)
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $('#preview').attr('src', e.target.result).fadeIn('fast');
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            });
            
            function check_add() {
                var image = $('#image').val();
                var type_id = $('#type_id').val();
                var landmass_id = $('#landmass_id').val();
                var country_id = $('#country_id').val();
                var city_id = $('#city_id').val();
                var province_id = $('#province_id').val();
                var district_id = $('#district_id').val();
                var name = $('#name').val();
                if (image == "" || type_id == "" || name == "") {
                    toastr.error('กรุณากรอกข้อมูลให้ครบถ้วนก่อนบันทึกรายการ');
                    return false;
                }else if(type_id == 1){
                    if (landmass_id == "" || country_id == "" || city_id == "") {
                        toastr.error('กรุณากรอกข้อมูลให้ครบถ้วนก่อนบันทึกรายการ');
                        return false;
                    }
                }else if(type_id == 2){
                    if (province_id == "" || district_id == "") {
                        toastr.error('กรุณากรอกข้อมูลให้ครบถ้วนก่อนบันทึกรายการ');
                        return false;
                    }
                }
            }
        </script>
        <!-- END: JS Assets-->
</body>

</html>
