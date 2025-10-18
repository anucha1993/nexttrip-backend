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
                                        <input type="text" id="name" name="name" value="{{@$row->name}}" class="form-control" required>
                                    </div>
                                </div>

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label"><span class="text-danger">*</span> กลุ่ม</label></b>
                                        <select name="group_id" id="group_id" class="form-control select2">
                                            <option value="" selected hidden>กรุณาเลือกข้อมูล</option>
                                            @foreach ($group as $g)
                                                <option value="{{$g->id}}" @if(@$row->group_id == $g->id) selected @endif>{{$g->group_name}}</option>
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
                                                <option value="{{$t->id}}" @if(@$row->type_id == $t->id) selected @endif>{{$t->type_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <input type="hidden" name="type_id" id="type_id" value="{{@$row->type_id}}">
                                </div>

                                {{-- @if(@$row->type_id == 1) --}}
                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3 c-type-1">
                                    <div class="col-span-12 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label"><span class="text-danger">*</span> ทวีป</label></b>
                                        <select name="landmass_id" id="landmass_id" class="form-control select2">
                                            <option value="" selected hidden>กรุณาเลือกข้อมูล</option>
                                            @foreach ($landmass as $land)
                                                <option value="{{$land->id}}" @if(@$row->landmass_id == $land->id) selected @endif>{{$land->landmass_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3 c-type-1">
                                    <div class="col-span-12 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label"><span class="text-danger">*</span> ประเทศ</label></b>
                                        <select name="country_id" id="country_id" class="form-control select2">
                                            <option value="" selected hidden>กรุณาเลือกข้อมูล</option>
                                            @foreach ($country as $c)
                                                <option value="{{$c->id}}" @if(@$row->country_id == $c->id) selected @endif>{{$c->country_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-span-12 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label"><span class="text-danger">*</span> เมือง</label></b>
                                        <select name="city_id" id="city_id" class="form-control select2">
                                            <option value="" selected hidden>กรุณาเลือกข้อมูล</option>
                                            @foreach ($city as $c)
                                                <option value="{{$c->id}}" @if(@$row->city_id == $c->id) selected @endif>{{$c->city_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                {{-- @endif --}}

                                {{-- @if(@$row->type_id == 2) --}}
                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3 c-type-2">
                                    <div class="col-span-12 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label"><span class="text-danger">*</span> จังหวัด</label></b>
                                        <select name="province_id" id="province_id" class="form-control select2">
                                            <option value="" selected hidden>กรุณาเลือกข้อมูล</option>
                                            @foreach ($province as $p)
                                                <option value="{{$p->id}}" @if(@$row->province_id == $p->id) selected @endif>{{$p->name_th}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-span-12 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label"><span class="text-danger">*</span> อำเภอ/เขต</label></b>
                                        <select name="district_id" id="district_id" class="form-control select2">
                                            <option value="" selected hidden>กรุณาเลือกข้อมูล</option>
                                            @foreach ($district as $d)
                                                <option value="{{$d->id}}" @if(@$row->district_id == $d->id) selected @endif>{{$d->name_th}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                {{-- @endif --}}
                                
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
                                                name="image" id="image" accept="image/png, image/jpeg, image/jpg">
                                        </div>
                                    </div>
                                </div>
                                {{-- image --}}

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label">จำนวนวัน</label></b>
                                        <input type="text" id="num_day" name="num_day" value="{{@$row->num_day}}" class="form-control">
                                    </div>
                                    <div class="col-span-12 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label">รหัส</label></b>
                                        <input type="text" id="code" name="code" value="{{@$row->code}}" class="form-control">
                                    </div>
                                </div>

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <div class="row">
                                            <b><label for="crud-form-1" class="form-label">ระดับดาว</label></b>
                                        </div>
                                        <div class="row">
                                            <div class="containerrating">

                                                <input id="star01" type="radio" name="rating" value="5" @if($row->rating == 5) checked @endif>
                                                <label for="star01">&#9733;</label>
    
                                                <input id="star02" type="radio" name="rating" value="4" @if($row->rating == 4) checked @endif>
                                                <label for="star02">&#9733;</label>
    
                                                <input id="star03" type="radio" name="rating" value="3" @if($row->rating == 3) checked @endif>
                                                <label for="star03">&#9733;</label>
    
                                                <input id="star04" type="radio" name="rating" value="2" @if($row->rating == 2) checked @endif>
                                                <label for="star04">&#9733;</label>
    
                                                <input id="star05" type="radio" name="rating" value="1" @if($row->rating == 1) checked @endif>
                                                <label for="star05">&#9733;</label>
    
                                            </div>
                                        </div>
                                        {{-- <input type="text" id="rating" name="rating" value="{{@$row->rating}}" class="form-control"> --}}
                                    </div>
                                    <div class="col-span-12 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label">เที่ยวบิน</label></b>
                                        <select name="airline_id" id="airline_id" class="form-control select2">
                                            <option value="" selected hidden>กรุณาเลือกข้อมูล</option>
                                            @foreach ($travel as $tra)
                                                <option value="{{$tra->id}}" @if(@$row->airline_id == $tra->id) selected @endif>{{$tra->travel_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label"><span class="text-danger">*</span> ราคา</label></b>
                                        <input type="text" id="price" name="price" value="{{@$row->price}}" class="form-control">
                                    </div>
                                    <div class="col-span-12 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label">ราคาพิเศษ</label></b>
                                        <input type="text" id="special_price" name="special_price" value="{{@$row->special_price}}" class="form-control">
                                    </div>
                                </div>

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <b><label for="crud-form-1" class="form-label"><span class="text-danger">*</span> รายละเอียด</label></b>
                                        <textarea type="text" id="description" name="description" class="form-control" rows="5">{{@$row->description}}</textarea>
                                    </div>
                                </div>

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-6 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label">เที่ยว</label></b>
                                        <textarea type="text" id="travel" name="travel" class="form-control" rows="5">{{@$row->travel}}</textarea>
                                    </div>
                                    <div class="col-span-6 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label">ช็อป</label></b>
                                        <textarea type="text" id="shop" name="shop" class="form-control" rows="5">{{@$row->shop}}</textarea>
                                    </div>
                                </div>

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-6 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label">กิน</label></b>
                                        <textarea type="text" id="eat" name="eat" class="form-control" rows="5">{{@$row->eat}}</textarea>
                                    </div>
                                    <div class="col-span-6 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label">พิเศษ</label></b>
                                        <textarea type="text" id="special" name="special" class="form-control" rows="5">{{@$row->special}}</textarea>
                                    </div>
                                </div>

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-6 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label">พัก</label></b>
                                        <textarea type="text" id="stay" name="stay" class="form-control" rows="5">{{@$row->stay}}</textarea>
                                    </div>
                                </div>

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <b><label for="crud-form-1" class="form-label">Video</label></b>
                                        <input type="text" id="video" name="video" value="{{@$row->video}}" class="form-control">
                                    </div>
                                </div>

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-6 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label">File PDF</label></b>
                                        @if($row->pdf_file)
                                        <div class="file mt-2 mb-2">
                                            <a href="{{asset($row->pdf_file)}}" target="_blank" class="file__icon file__icon--file" style="width: 20%;"></a>
                                        </div>
                                        @endif
                                        <small class="help-block">* รองรับไฟล์ <strong class="text-danger">(pdf)</strong> เท่านั้น</small>
                                        <div class="col-span-6 lg:col-span-6">
                                            <input type="file" class="custom-file-input block w-80 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                name="pdf_file" id="pdf_file" accept="application/pdf">
                                        </div>
                                    </div>

                                    <div class="col-span-6 lg:col-span-6">
                                        <b><label for="crud-form-1" class="form-label">File Word</label></b>
                                        @if($row->word_file)
                                        <div class="file mt-2 mb-2">
                                            <a href="{{asset($row->word_file)}}" target="_blank" class="file__icon file__icon--file" style="width: 20%;"></a>
                                        </div>
                                        @endif
                                        <small class="help-block">* รองรับไฟล์ <strong class="text-danger">(docx)</strong> เท่านั้น</small>
                                        <div class="col-span-6 lg:col-span-6">
                                            <input type="file" class="custom-file-input block w-80 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                name="word_file" id="word_file" accept=".doc, .docx">
                                        </div>
                                    </div>
                                </div>
                                {{-- หลิว 2 gallery --}}
                                <div class="text-left mt-5">
                                    <div class="col-span-6 lg:col-span-6">
                                    <h3 class="mb-3"><b>อัลบั้มรูปภาพ</b></h3>
                                    <div id="preview-gallery" class="grid grid-cols-12 gap-6 mt-5"></div><br>
                                        <input type="file"
                                            class="custom-file-input w-50 rounded-lg border border-gray-300 cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                            name="img[]" id="image-gallery" accept="image/png, image/jpeg, image/jpg" multiple onchange="readGallery()">
                                            <button type="button" class="btn btn-danger" onclick="removeImg()">Reset</button>
                                        <br>
                                        <small class="help-block">* รองรับไฟล์ <strong class="text-danger">(jpg, jpeg, png)</strong> เท่านั้น</small>
                                    </div>
                                </div>
                                <h2 class="intro-y text-lg font-medium mt-10">แก้ไขอัลบั้มรูป</h2>
                                <br>
                                <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                                    <div class="hidden md:block mx-auto text-slate-500"></div>
                                </div>
                                <div style="background-color: #F5F5F5;padding:15px;border-radius:5px;">
                                    <table id="datatable" class="table table-report"></table>
                                </div>
                                {{-- หลิว 2 gallery --}}
                                <br>

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <label for="crud-form-1" class="form-label">รายละเอียดทัวร์ <button class="btn btn-primary btn-block" type="button"style="margin: auto;" colspan="2" onclick="add_detail()"><i class="fa fa-plus-circle" ></i> เพิ่ม</button></label>
                                        {{-- @if(count(@$detail) > 0) --}}
                                            <table class="table table-striped table-bordered" >
                                                <thead class="table-light">
                                                    <tr role="row">
                                                        <th  class="vertid text-center sorting_asc" style="width: 30%">หัวข้อ</th>
                                                        <th  class="vertid text-center" style="width: 30%">รายละเอียด</th>
                                                        <th  class="vertid text-center" style="width: 3%">จัดการ</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="data_detail">
                                                    @php $d = 1; @endphp
                                                        @foreach($detail as $de)
                                                            <tr id="del_detail{{$d}}">
                                                                <td>
                                                                    <input type="text" class="form-control" name="detail[{{$d}}][title][]" value="{{@$de->title}}">
                                                                </td>
                                                                <td>
                                                                    <textarea class="form-control" name="detail[{{$d}}][detail][]" rows="3">{{@$de->detail}}</textarea>
                                                                </td>
                                                                <td>
                                                                    <button class="btn btn-danger del-btn btn-block" type="button" style="margin:auto;" onclick="del_detail({{$d}})">ลบ</button>
                                                                </td>
                                                            </tr>
                                                        @php $d++ @endphp
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        {{-- @endif --}}
                                    </div>
                                </div>
                                <br>

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <div>
                                            <label for="crud-form-1" class="form-label">ระยะเวลา <button class="btn btn-primary btn-block" type="button" style="margin: auto;" colspan="2" onclick="add_period(false)"><i class="fa fa-plus-circle" ></i> เพิ่ม</button></label>
                                        </div>
                                        <br>
                                        @if(count(@$period) > 0)
                                            <div id="data_period">
                                                @php $p = 1; @endphp
                                                @foreach($period as $pe)
                                                    <table class="table table-striped table-bordered" id="del_period{{$p}}">
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
                                                                    <input type="hidden" class="form-control" name="period[{{$p}}][period_id][]" value="{{@$pe->id}}">
                                                                    <input type="date" class="form-control" name="period[{{$p}}][start_date][]" value="{{@$pe->start_date}}">
                                                                </td>
                                                                <td>
                                                                    <input type="date" class="form-control" name="period[{{$p}}][end_date][]" value="{{@$pe->end_date}}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" onkeypress="return check_number(this)" class="form-control" name="period[{{$p}}][price1][]" value="{{@$pe->price1}}"><br>
                                                                    <center><label>ลดราคา</label></center>
                                                                    <input type="text" onkeypress="return check_number(this)" class="form-control" name="period[{{$p}}][special_price1][]" value="{{@$pe->special_price1}}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" onkeypress="return check_number(this)" class="form-control" name="period[{{$p}}][price2][]" value="{{@$pe->price2}}"><br>
                                                                    <center><label>ลดราคา</label></center>
                                                                    <input type="text" onkeypress="return check_number(this)" class="form-control" name="period[{{$p}}][special_price2][]" value="{{@$pe->special_price2}}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" onkeypress="return check_number(this)" class="form-control" name="period[{{$p}}][price3][]" value="{{@$pe->price3}}"><br>
                                                                    <center><label>ลดราคา</label></center>
                                                                    <input type="text" onkeypress="return check_number(this)" class="form-control" name="period[{{$p}}][special_price3][]" value="{{@$pe->special_price3}}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" onkeypress="return check_number(this)" class="form-control" name="period[{{$p}}][price4][]" value="{{@$pe->price4}}"><br>
                                                                    <center><label>ลดราคา</label></center>
                                                                    <input type="text" onkeypress="return check_number(this)" class="form-control" name="period[{{$p}}][special_price4][]" value="{{@$pe->special_price4}}">
                                                                </td>
                                                                <td>
                                                                    <select class="form-control" name="period[{{$p}}][status_period][]">
                                                                        <option value="">กรุณาเลือก</option>
                                                                        <option value="1" @if(@$pe->status_period == 1) selected @endif>จอง</option>
                                                                        <option value="2" @if(@$pe->status_period == 2) selected @endif>ไลน์</option>
                                                                        <option value="3" @if(@$pe->status_period == 3) selected @endif>เต็ม</option>
                                                                    </select>
                                                                </td>
                                                                <td class="text-center">
                                                                    <button class="btn btn-danger del-btn btn-block" type="button" style="margin:auto;" onclick="del_period({{$p}})">ลบ</button>
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
                                                                    <input type="text" onkeypress="return check_number(this)" class="form-control" name="period[{{$p}}][day][]" value="{{@$pe->day}}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" onkeypress="return check_number(this)" class="form-control" name="period[{{$p}}][night][]" value="{{@$pe->night}}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" onkeypress="return check_number(this)" class="form-control" name="period[{{$p}}][group][]" value="{{@$pe->group}}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" onkeypress="return check_number(this)" class="form-control" name="period[{{$p}}][count][]" value="{{@$pe->count}}">
                                                                </td>
                                                                <td colspan="2">
                                                                    <select class="form-control" name="period[{{$p}}][promotion_id][]">
                                                                        <option value="">กรุณาเลือก</option>
                                                                        @foreach($promotion as $pro)
                                                                            <option value="{{$pro->id}}" @if(@$pe->promotion_id == $pro->id) selected @endif>{{ $pro->promotion_name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <input type="date" class="form-control" name="period[{{$p}}][pro_start_date][]" value="{{@$pe->pro_start_date}}">
                                                                </td>
                                                                <td>
                                                                    <input type="date" class="form-control" name="period[{{$p}}][pro_end_date][]" value="{{@$pe->pro_end_date}}">
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                    <br>
                                                @php $p++ @endphp
                                                @endforeach
                                            </div>
                                        @endif
                                        <div class="mt-2">
                                            <input type="number" min="1" max="10" id="count_copy" class="form-control" style="width: 10%;">
                                            <button type="button" class="btn btn-warning btn-block ml-1" onclick="copy_period()">Copy</button>
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
        <?php 
            echo '<script>';
            if(count($period)){
                echo 'var last_period = '. json_encode($period[count($period) - 1]) .';';
            }else{
                echo 'var last_period = false;';
            }
            echo 'var promotion = '. json_encode($promotion) .';';
            echo '</script>';
        ?>
        <script>
            function readGallery() {
                const target = $('#preview-gallery');
                target.innerHTML = null;
                var total_file=document.getElementById("image-gallery").files.length;
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
            
            var id = "{{$id}}";
            var fullUrl = "{{url('webpanel/tour')}}";
            var oTable;
            var _token = '{{csrf_token()}}';
            $(function () {
                oTable = $('#datatable').DataTable({
                    searching: false,
                    ordering: false,
                    lengthChange: false,
                    pageLength: 10,
                    processing: true,
                    serverSide: true,
                    paging: false,
                    info: false,
                    iDisplayLength: 25,
                    ajax: {
                        url: fullUrl+"/"+id+"/datatable-gallery?_token="+_token,
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
                        { data: 'img', title: '<center>รูปภาพ</center>', className: 'items-center w-20 justify-center' },
                        { data: 'created_at', title: '<center>วันที่สร้าง</center>', className: 'items-center w-20 text-center' }, 
                        { data: 'action', title: '<center>จัดการ</center>', className: 'items-center w-20 text-center' },
                    ],
                    rowCallback: function (nRow, aData, dataIndex) {

                    }
                });
                $('.myWhere,.myLike,.myCustom,#onlyTrashed').on('change', function (e) {
                    oTable.draw();
                });
            });

            $(function () {
                oTable = $('#datatable-period').DataTable({
                    searching: false,
                    ordering: false,
                    lengthChange: false,
                    pageLength: 10,
                    processing: true,
                    serverSide: true,
                    paging: false,
                    info: false,
                    iDisplayLength: 25,
                    columnDefs: [{
                        orderable: false,
                        className: 'select-checkbox',
                        targets: 0,
                    }],
                    select: {
                        style: 'os', // 'single', 'multi', 'os', 'multi+shift'
                        selector: 'td:first-child',
                    },
                    ajax: {
                        url: fullUrl+"/"+id+"/datatable-period?_token="+_token,
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
                        {  data: '', defaultContent:'', checkboxes:{ 'selectRow':true },  className: 'whitespace-nowrap w-10 text-center'},
                        {  data: 'DT_RowIndex',     title :'<center>#</center>',     className: 'whitespace-nowrap w-10 text-center'},
                        {  data: 'period',      title: '<center>Period</center>',     className: 'items-center w-40 text-center'},
                        {  data: 'count',       title: '<center>จำนวนที่นั่ง</center>',     className: 'items-center w-20 text-center'},
                        {  data: 'price1',      title: '<center>พักคู่</center>',     className: 'items-center w-20 text-center'},
                        {  data: 'price2',      title: '<center>พักเดี่ยว(บวก)</center>',     className: 'items-center w-20 text-center'},
                        {  data: 'price3',      title: '<center>เด็กมีเตียง</center>',     className: 'items-center w-20 text-center'},
                        {  data: 'status',      title: '<center>สถานะ</center>',     className: 'items-center w-10 text-center'},
                        {  data: 'action',      title: '<center>จัดการ</center>',     className: 'items-center w-20 text-center'},
                    ],
                    rowCallback: function (nRow, aData, dataIndex) {

                    }
                });
                $('.myWhere,.myLike,.myCustom,#onlyTrashed').on('change', function (e) {
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
                        return fetch(fullUrl + '/destroy-gallery/' + id)
                            .then(response => response.json())
                            .then(data => location.reload())
                            .catch(error => {
                                Swal.showValidationMessage(`Request failed: ${error}`)
                            })
                    }
                });
            }
        </script>
        <script>
            function check_number(ele) {
                var vchar = String.fromCharCode(event.keyCode);
                if ((vchar<'0' || vchar>'9') && (vchar != '.')) return false;
                ele.onKeyPress=vchar;
            }

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

            var count_detail = {{@$d}};
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
            
            var count_period = {{@$p}};
            function add_period(t){  
                if(t && last_period){
                    var status_1 = last_period.status_period == '1' ? 'selected':'';
                    var status_2 = last_period.status_period == '2' ? 'selected':'';
                    var status_3 = last_period.status_period == '3' ? 'selected':'';

                    var op = '';
                    for(opt in promotion){
                        let select_op = promotion[opt].id == last_period.promotion_id ? 'selected':'';
                        op = op + '<option value="'+promotion[opt].id+'"'+select_op+'>'+promotion[opt].promotion_name+'</option>'
                    }

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
                    '<td><input type="date" class="form-control" name="period['+count_period+'][start_date][]" value='+last_period.start_date+'></td>'+
                    '<td><input type="date" class="form-control" name="period['+count_period+'][end_date][]" value='+last_period.end_date+'></td>'+
                    '<td><input type="text" onkeypress="return check_number(this)" class="form-control" name="period['+count_period+'][price1][]" value='+last_period.price1*1+'><br><center><label>ลดราคา</label></center><input type="text" onkeypress="return check_number(this)" class="form-control" name="period['+count_period+'][special_price1][]" value='+last_period.special_price1*1+'></td>'+
                    '<td><input type="text" onkeypress="return check_number(this)" class="form-control" name="period['+count_period+'][price2][]" value='+last_period.price2*1+'><br><center><label>ลดราคา</label></center><input type="text" onkeypress="return check_number(this)" class="form-control" name="period['+count_period+'][special_price2][]" value='+last_period.special_price2*1+'></td>'+
                    '<td><input type="text" onkeypress="return check_number(this)" class="form-control" name="period['+count_period+'][price3][]" value='+last_period.price3*1+'><br><center><label>ลดราคา</label></center><input type="text" onkeypress="return check_number(this)" class="form-control" name="period['+count_period+'][special_price3][]" value='+last_period.special_price3*1+'></td>'+
                    '<td><input type="text" onkeypress="return check_number(this)" class="form-control" name="period['+count_period+'][price4][]" value='+last_period.price4*1+'><br><center><label>ลดราคา</label></center><input type="text" onkeypress="return check_number(this)" class="form-control" name="period['+count_period+'][special_price4][]" value='+last_period.special_price4*1+'></td>'+
                    '<td><select class="form-control" name="period['+count_period+'][status_period][]"><option value="1"'+status_1+'>จอง</option><option value="2"'+status_2+'>ไลน์</option><option value="3"'+status_3+'>เต็ม</option></select></td>'+
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
                    '<td><input type="text" onkeypress="return check_number(this)" class="form-control" name="period['+count_period+'][day][]" value='+last_period.day*1+'></td>'+
                    '<td><input type="text" onkeypress="return check_number(this)" class="form-control" name="period['+count_period+'][night][]" value='+last_period.night*1+'></td>'+
                    '<td><input type="text" onkeypress="return check_number(this)" class="form-control" name="period['+count_period+'][group][]" value='+last_period.group*1+'></td>'+
                    '<td><input type="text" onkeypress="return check_number(this)" class="form-control" name="period['+count_period+'][count][]" value='+last_period.count*1+'></td>'+
                    '<td colspan="2"><select class="form-control" name="period['+count_period+'][promotion_id][]">'+
                    '<option value="">กรุณาเลือก</option>'+op+'</select></td>'+
                    '<td><input type="date" class="form-control" name="period['+count_period+'][pro_start_date][]" value='+last_period.pro_start_date+'></td>'+
                    '<td><input type="date" class="form-control" name="period['+count_period+'][pro_end_date][]" value='+last_period.pro_end_date+'></td>'+
                    '</tr>'+'</body>'+
                    '</table><br>';   
                }else{
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
                    '<td><select class="form-control" name="period['+count_period+'][status_period][]"><option value="1">จอง</option><option value="2">ไลน์</option><option value="3">เต็ม</option></select></td>'+
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
                }
                $('#data_period').append(add);
                count_period++;  
            }

            function del_period(d){
                var de = document.getElementById('del_period'+d);
                de.parentNode.removeChild(de);
            }

            async function copy_period() {
                var count = $('#count_copy').val();
                if(count < 0){
                    alert('จำนวนห้ามติดลบ');
                }
                if(count > 20){
                    alert('จำนวนห้ามมากกว่า 20 ต่อครั้ง');
                }
                if(count){
                    for(x=0; x<count; x++){
                        await add_period(true);
                    }
                }
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
                var type_id = $('#type_id').val();
                var landmass_id = $('#landmass_id').val();
                var country_id = $('#country_id').val();
                var city_id = $('#city_id').val();
                var province_id = $('#province_id').val();
                var district_id = $('#district_id').val();
                var name = $('#name').val();
                if (type_id == "" || name == "") {
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
