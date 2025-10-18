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

        .form-check-input{
            border-color: #2C2727 !important;
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

                                <div id="accordion-color" data-accordion="collapse" data-active-classes="bg-blue-100 dark:bg-gray-800 text-blue-600 dark:text-white">

                                    {{-- ข้อมูลโปรแกรมทัวร์ --}}
                                    <h2 id="accordion-color-heading-1">
                                        <button type="button" id="button_tour" class="flex items-center justify-between w-full p-5 font-medium text-left text-gray-500 border border-b-0 border-gray-200 rounded-t-xl focus:ring-4 focus:ring-blue-200 dark:focus:ring-blue-800 dark:border-gray-700 dark:text-gray-400 hover:bg-blue-100 dark:hover:bg-gray-800" 
                                        data-accordion-target="#accordion-color-body-1" aria-expanded="false" aria-controls="accordion-color-body-1">
                                          <span>ข้อมูลโปรแกรมทัวร์</span>
                                          <svg data-accordion-icon class="w-3 h-3 rotate-180 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5 5 1 1 5"/>
                                          </svg>
                                        </button>
                                    </h2>
                                    <div id="accordion-color-body-1" class="hidden" aria-labelledby="accordion-color-heading-1">
                                        <div class="p-5 border border-b-0 border-gray-200 dark:border-gray-700 dark:bg-gray-900">
                                            <div class="container">

                                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                                    <div class="col-span-12 lg:col-span-12">
                                                        <b><label for="crud-form-1" class="form-label"><span class="text-danger">*</span> ชื่อ</label></b>
                                                        <input type="text" id="name" name="name" class="form-control" required>
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                                    {{-- <div class="col-span-6 lg:col-span-6">
                                                        <b><label for="crud-form-1" class="form-label">รหัสทัวร์ (Generate)</label></b>
                                                        <input type="text" id="code" name="code" class="form-control">
                                                    </div> --}}
                                                    <div class="col-span-12 lg:col-span-6">
                                                        <b><label for="crud-form-1" class="form-label">รหัสทัวร์ (Manual)</label></b> <input class="form-check-input ml-2" type="checkbox" name="code1_check" value="1"> เลือกเมื่อต้องการใช้รหัสทัวร์
                                                        <input type="text" id="code1" name="code1" class="form-control">
                                                    </div>
                                                </div>
                
                                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                                    <div class="col-span-12 lg:col-span-6">
                                                        <b><label for="crud-form-1" class="form-label">ประเภททัวร์</label></b>
                                                        <select name="type_id" id="type_id" class="form-control tom-select">
                                                            <option value="" selected hidden>กรุณาเลือกข้อมูล</option>
                                                            @foreach ($type as $t)
                                                                <option value="{{$t->id}}">{{$t->type_name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-span-12 lg:col-span-6">
                                                        <b><label for="crud-form-1" class="form-label">เที่ยวบิน</label></b>
                                                        <select name="airline_id" id="airline_id" class="form-control tom-select">
                                                            <option value="" selected hidden>กรุณาเลือกข้อมูล</option>
                                                            @foreach ($travel as $tra)
                                                                <option value="{{$tra->id}}">({{$tra->code}}) {{$tra->travel_name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                                    <div class="col-span-12 lg:col-span-6">
                                                        <b><label for="crud-form-1" class="form-label"><span class="text-danger">*</span> กลุ่ม</label></b>
                                                        <select name="group_id" id="group_id" class="form-control tom-select" required>
                                                            <option value="" selected hidden>กรุณาเลือกข้อมูล</option>
                                                            @foreach ($group as $g)
                                                                <option value="{{$g->id}}">{{$g->group_name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-span-12 lg:col-span-6 wholesale" hidden>
                                                        <b><label for="crud-form-1" class="form-label">Supplier</label></b>
                                                        <select name="wholesale_id" id="wholesale_id" class="form-control tom-select">
                                                            @foreach ($wholesale as $w)
                                                                <option value="{{$w->id}}">({{ $w->code }}) {{$w->wholesale_name_en}} ({{$w->wholesale_name_th}})</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                                    <div class="col-span-12 lg:col-span-6">
                                                        <b><label for="crud-form-1" class="form-label"><span class="text-danger">*</span> ประเทศ/เมือง</label></b>
                                                        <select data-placeholder="กรุณาเลือกข้อมูล" id="category" class="tom-select w-full" multiple name="category[]" required>
                                                            @foreach($country as $co)
                                                                <option value="CO.{{$co->id}}">{{$co->country_name_th}}</option>
                                                            @endforeach
                                                            @foreach($city as $ci)
                                                                <option value="CI.{{$ci->id}}">{{$ci->city_name_th}}</option>
                                                            @endforeach
                                                            @foreach($province as $p)
                                                                <option value="P.{{$p->id}}">{{$p->name_th}}</option>
                                                            @endforeach
                                                            @foreach($district as $d)
                                                                <option value="D.{{$d->id}}">{{$d->name_th}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-span-12 lg:col-span-6">
                                                        <b><label for="crud-form-1" class="form-label">Tag</label></b>
                                                        {{-- <select data-placeholder="กรุณาเลือกข้อมูล" class="tom-select w-full" multiple name="tag_id[]" >
                                                            @foreach($tag as $t)
                                                                <option value="{{$t->id}}">{{$t->tag}}</option>
                                                            @endforeach
                                                        </select> --}}
                                                        <input type="text" list="browsers"  id="browser" onchange="TagValue()" class="form-control">
                                                        <datalist id="browsers" >
                                                            @foreach($tag as $t)
                                                            <option >{{$t->tag}}</option>
                                                            @endforeach
                                                        </datalist>
                                                        <br><br>
                                                        <div id="hashtag"></div>
                                                    </div>
                                                </div>

                                                {{-- image --}}
                                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                                    <div class="col-span-12 lg:col-span-6">
                                                        <div class="col-span-12 lg:col-span-6">
                                                            <b><h6 class="mb-3"><span class="text-danger">*</span> รูปภาพ <span class="text-danger"> ขนาด 600*600</span></h6></b>
                                                            <img src="@if(@$row->image == null) {{url("noimage.jpg")}} @else {{asset($row->image)}} @endif" class="img-thumbnail" id="preview">
                                                        </div>
                                                        <div class="col-span-12 lg:col-span-12">
                                                            <small class="help-block">* รองรับไฟล์ <strong class="text-danger">(jpg, jpeg, png, webp)</strong> เท่านั้น</small>
                                                            <div class="col-span-6 lg:col-span-6">
                                                                <input type="file" class="custom-file-input block w-80 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                                    name="image" id="image" accept="image/png, image/jpeg, image/jpg,  image/webp" required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-span-12 lg:col-span-6">
                                                        <div class="col-span-12 lg:col-span-6">
                                                            <b><h6 class="mb-3">รูปภาพปกวิดีโอ <span class="text-danger"> ขนาด 394*230</span></h6></b>
                                                            <img src="@if(@$row->video_cover == null) {{url("noimage.jpg")}} @else {{asset($row->video_cover)}} @endif" class="img-thumbnail" id="preview_video">
                                                        </div>
                                                        <div class="col-span-12 lg:col-span-12">
                                                            <small class="help-block">* รองรับไฟล์ <strong class="text-danger">(jpg, jpeg, png, webp)</strong> เท่านั้น</small>
                                                            <div class="col-span-6 lg:col-span-6">
                                                                <input type="file" class="custom-file-input block w-80 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                                    name="video_cover" id="video_cover" accept="image/png, image/jpeg, image/jpg,  image/webp">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <br>
                                                {{-- image --}}

                                                <h3 class="mb-3"><b>อัลบั้มรูปภาพ</b> <span class="text-danger"> ขนาด 600*600</span></h3>
                                                <div id="preview-gallery" class="grid grid-cols-12 gap-6 mt-5"></div><br>
                                                <input type="file"
                                                    class="custom-file-input w-50 rounded-lg border border-gray-300 cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                    name="img[]" id="image-gallery" accept="image/png, image/jpeg, image/jpg" multiple onchange="readGallery()">
                                                    <button type="button" class="btn btn-danger" onclick="removeImg()">Reset</button>
                                                <br>
                                                <small class="help-block">* รองรับไฟล์ <strong class="text-danger">(jpg, jpeg, png)</strong> เท่านั้น</small>
                                                <br>
                
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
                                                        <b><label for="crud-form-1" class="form-label">Video</label></b>
                                                        <input type="text" id="video" name="video" class="form-control">
                                                    </div>
                                                </div>
                
                                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                                    <div class="col-span-12 lg:col-span-12">
                                                        <b><label for="crud-form-1" class="form-label">ไฮไลท์</label></b>
                                                        <textarea type="text" id="description" name="description" class="form-control" rows="2"></textarea>
                                                    </div>
                                                </div>
                
                                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                                    <div class="col-span-12 lg:col-span-6">
                                                        <b><label for="crud-form-1" class="form-label">เที่ยว</label></b>
                                                        <textarea type="text" id="travel" name="travel" class="form-control" rows="2"></textarea>
                                                    </div>
                                                    <div class="col-span-12 lg:col-span-6">
                                                        <b><label for="crud-form-1" class="form-label">ช็อป</label></b>
                                                        <textarea type="text" id="shop" name="shop" class="form-control" rows="2"></textarea>
                                                    </div>
                                                </div>
                
                                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                                    <div class="col-span-12 lg:col-span-6">
                                                        <b><label for="crud-form-1" class="form-label">กิน</label></b>
                                                        <textarea type="text" id="eat" name="eat" class="form-control" rows="2"></textarea>
                                                    </div>
                                                    <div class="col-span-12 lg:col-span-6">
                                                        <b><label for="crud-form-1" class="form-label">พิเศษ</label></b>
                                                        <textarea type="text" id="special" name="special" class="form-control" rows="2"></textarea>
                                                    </div>
                                                </div>
                
                                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                                    <div class="col-span-12 lg:col-span-6">
                                                        <b><label for="crud-form-1" class="form-label">พัก</label></b>
                                                        <textarea type="text" id="stay" name="stay" class="form-control" rows="2"></textarea>
                                                    </div>
                                                </div>

                                                {{-- เอกสารโปรแกรมทัวร์ --}}
                                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                                    <div class="col-span-12 lg:col-span-6">
                                                        <b><label for="crud-form-1" class="form-label">File PDF</label></b>
                                                        <small class="help-block">* รองรับไฟล์ <strong class="text-danger">(pdf)</strong> เท่านั้น</small>
                                                        <div class="col-span-6 lg:col-span-6">
                                                            <input type="file" class="custom-file-input block w-80 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                                name="pdf_file" id="pdf_file" accept="application/pdf">
                                                        </div>
                                                    </div>
                
                                                    <div class="col-span-12 lg:col-span-6">
                                                        <b><label for="crud-form-1" class="form-label">File Word</label></b>
                                                        <small class="help-block">* รองรับไฟล์ <strong class="text-danger">(docx)</strong> เท่านั้น</small>
                                                        <div class="col-span-6 lg:col-span-6">
                                                            <input type="file" class="custom-file-input block w-80 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                                name="word_file" id="word_file" accept=".doc, .docx">
                                                        </div>
                                                    </div>
                                                </div>
                                                {{-- เอกสารโปรแกรมทัวร์ --}}
                
                                                <div class="border-gray-200 dark:border-gray-700">
                                                    <div class="container col-span-12">
                                                        <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                                            <div class="col-span-12 lg:col-span-12">
                                                                <div class="col-span-12 lg:col-span-12">
                                                                    <label for="crud-form-1" class="form-label">รายละเอียดทัวร์ &nbsp;&nbsp;&nbsp;<button class="btn btn-primary btn-block" type="button"style="margin: auto;" onclick="add_detail()"> เพิ่ม &nbsp;<i class="fa fa-plus-circle" aria-hidden="true"></i></button></label>
                                                                </div>
                                                                <div class="overflow-x-auto">
                                                                    <div id="data_detail">
                                                                        {{-- <div id="del_detail0">
                                                                            <div class="text-right">
                                                                                <button class="btn btn-danger del-btn btn-block" type="button" style="margin:auto;" onclick="del_detail(0)">ลบ &nbsp;<i class="fa fa-arrow-down" aria-hidden="true"></i></button>
                                                                            </div>
                                                                            <table class="table table-striped table-bordered mt-2" style="width: 100%;">
                                                                                <thead class="table-light">
                                                                                    <tr role="row">
                                                                                        <th  class="vertid text-center" style="width: 5%;">#</th>
                                                                                        <th  class="vertid text-center" style="width: 20%;"></th>
                                                                                        <th  class="vertid text-center" style="width: 25%;"></th>
                                                                                        <th  class="vertid text-center" style="width: 20%;"></th>
                                                                                        <th  class="vertid text-center" style="width: 20%;"></th>
                                                                                        <th  class="vertid text-center" style="width: 10%;"></th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    <tr>
                                                                                        <td><center>0</center></td>
                                                                                        <td colspan="5">
                                                                                            <center><label>หัวข้อหลัก</label></center>
                                                                                            <input type="text" class="form-control" name="detail[][header][]">
                                                                                        </td>
                                                                                    </tr>
                                                                                </tbody>
                                                                                <thead class="table-light">
                                                                                    <tr role="row">
                                                                                        <th  class="vertid text-center"></th>
                                                                                        <th  class="vertid text-center"></th>
                                                                                        <th  class="vertid text-center"></th>
                                                                                        <th  class="vertid text-center"></th>
                                                                                        <th  class="vertid text-center"></th>
                                                                                        <th  class="vertid text-center"></th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody id="data_detail_sub0">
                                                                                    <tr id="del_detail_sub0">
                                                                                        <td></td>
                                                                                        <td>
                                                                                            <input type="file" id="detail_image0" class="form-control" name="detail[0][sub][0][image]" accept="image/png, image/jpeg, image/jpg" style="width: auto;">
                                                                                        </td>
                                                                                        <td>
                                                                                            <center><label>เวลา</label></center>
                                                                                            <input type="text" id="" class="form-control" name="detail[0][sub][0][time]">
                                                                                            <center><label>หัวข้อย่อย</label></center>
                                                                                            <input type="text" id="" class="form-control" name="detail[0][sub][0][title]">
                                                                                        </td>
                                                                                        <td colspan="2">
                                                                                            <center><label>รายละเอียด</label></center>
                                                                                            <textarea class="form-control" id="detail0" name="detail[0][sub][0][detail]" rows="3"></textarea>
                                                                                        </td>
                                                                                        <td>
                                                                                            <center>
                                                                                                <button class="btn btn-danger del-btn btn-block" type="button" style="margin:auto;" onclick="del_detail_sub(0)">ลบ</button>
                                                                                            </center>
                                                                                        </td>
                                                                                    </tr>
                                                                                </tbody>
                                                                            </table>
                                                                            <br>
                                                                            <div class="text-left">
                                                                                <button class="btn btn-primary btn-block" type="button"style="margin: auto;" onclick="add_detail_sub(0)"> เพิ่ม &nbsp;<i class="fa fa-plus-circle" aria-hidden="true"></i></button>
                                                                            </div>
                                                                        </div> --}}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                        </div>
                                    </div>
                                    {{-- ข้อมูลโปรแกรมทัวร์ --}}

                                    {{-- ข้อมูล Period --}}
                                    <h2 id="accordion-color-heading-2">
                                        <button type="button" id="button_period" class="flex items-center justify-between w-full p-5 font-medium text-left text-gray-500 border border-b-0 border-gray-200 focus:ring-4 focus:ring-blue-200 dark:focus:ring-blue-800 dark:border-gray-700 dark:text-gray-400 hover:bg-blue-100 dark:hover:bg-gray-800" 
                                        data-accordion-target="#accordion-color-body-2" aria-expanded="false" aria-controls="accordion-color-body-2">
                                          <span>ข้อมูล Period</span>
                                          <svg data-accordion-icon class="w-3 h-3 rotate-180 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5 5 1 1 5"/>
                                          </svg>
                                        </button>
                                    </h2>
                                    <div id="accordion-color-body-2" class="hidden" aria-labelledby="accordion-color-heading-2">
                                        <div class="border-gray-200 dark:border-gray-700">
                                            <div class="container">
                                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                                    <div class="col-span-12 lg:col-span-12">
                                                        <div>
                                                            <label for="crud-form-1" class="form-label">ระยะเวลา &nbsp;&nbsp;&nbsp;<button class="btn btn-primary btn-block" type="button" style="margin: auto;" onclick="add_period(false)"> เพิ่ม &nbsp;<i class="fa fa-plus-circle" aria-hidden="true"></i></button></label>
                                                        </div>
                                                        <div class="mt-3"> <label>การคิดราคา</label>
                                                            <div class="flex flex-col sm:flex-row mt-2">
                                                                <div class="form-check mr-2"> <input id="promotion_check" class="form-check-input" type="checkbox" name="promotion_check" value="1"> <label class="form-check-label">Promotion เหมิอนกันทุก Period</label> </div>
                                                                <div class="form-check mr-2 mt-2 sm:mt-0"> <input id="start_date_check" class="form-check-input" type="checkbox" name="start_date_check" value="1"> <label class="form-check-label">วันเริ่ม Promotion เหมิอนกันทุก Period</label> </div>
                                                                <div class="form-check mr-2 mt-2 sm:mt-0"> <input id="end_date_check" class="form-check-input" type="checkbox" name="end_date_check" value="1"> <label class="form-check-label">วันสิ้นสุด Promotion เหมิอนกันทุก Period</label> </div>
                                                                <div class="form-check mr-2 mt-2 sm:mt-0"> <input id="copy_price4" class="form-check-input" type="checkbox" name="copy_price4" value="1" checked> <label class="form-check-label">ราคาเด็กไม่มีเตียง เหมือนราคาพัก(2-3)</label> </div>
                                                                <div class="form-check mr-2 mt-2 sm:mt-0"> <input id="copy_price3" class="form-check-input" type="checkbox" name="copy_price3" value="1"> <label class="form-check-label">ราคาเด็กมีเตียง เหมือนราคาพัก(2-3)</label> </div>
                                                            </div>
                                                            <div class="flex flex-col sm:flex-row mt-2">
                                                                <div class="form-check mr-2"> <input id="price1_check" class="form-check-input" type="checkbox" name="price1_check" value="1"> <label class="form-check-label">ราคาพัก(2-3) เหมิอนกันทุก Period</label> </div>
                                                                <div class="form-check mr-2 mt-2 sm:mt-0"> <input id="price2_check" class="form-check-input" type="checkbox" name="price2_check" value="1"> <label class="form-check-label">ราคาพักเดี่ยว เหมิอนกันทุก Period</label> </div>
                                                                <div class="form-check mr-2 mt-2 sm:mt-0"> <input id="price4_check" class="form-check-input" type="checkbox" name="price4_check" value="1"> <label class="form-check-label">ราคาเด็กไม่มีเตียง เหมิอนกันทุก Period</label> </div>
                                                                <div class="form-check mr-2 mt-2 sm:mt-0"> <input id="price3_check" class="form-check-input" type="checkbox" name="price3_check" value="1"> <label class="form-check-label">ราคาเด็กมีเตียง เหมิอนกันทุก Period</label> </div>
                                                                <div class="form-check mr-2 mt-2 sm:mt-0"> <input id="group_check" class="form-check-input" type="checkbox" name="group_check" value="1"> <label class="form-check-label">GroupSize เหมิอนกันทุก Period</label> </div>
                                                                <div class="form-check mr-2 mt-2 sm:mt-0"> <input id="count_check" class="form-check-input" type="checkbox" name="count_check" value="1"> <label class="form-check-label">จำนวน เหมิอนกันทุก Period</label> </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="flex flex-col sm:flex-row mt-2">
                                                                <div class="col-span-12 lg:col-span-3 mt-2 ml-2 price1_all" hidden>
                                                                    <label for="crud-form-1" class="form-label">ราคา(พัก2-3)</label>
                                                                    <input type="text" id="price1_all" class="form-control" onkeypress="return check_number(this)" name="price1_all" value="">
                                                                    <label for="crud-form-1" class="form-label">ลดราคา(พัก2-3)</label>
                                                                    <input type="text" id="special_price1_all" class="form-control" onkeypress="return check_number(this)" name="special_price1_all" value="">
                                                                </div>
                                                                <div class="col-span-12 lg:col-span-3 mt-2 ml-2 price2_all" hidden>
                                                                    <label for="crud-form-1" class="form-label">ราคา(พักเดี่ยว)</label>
                                                                    <input type="text" id="price2_all" class="form-control" onkeypress="return check_number(this)" name="price2_all" value="">
                                                                    <label for="crud-form-1" class="form-label">ลดราคา(พักเดี่ยว)</label>
                                                                    <input type="text" id="special_price2_all" class="form-control" onkeypress="return check_number(this)" name="special_price2_all" value="">
                                                                </div>
                                                                <div class="col-span-12 lg:col-span-3 mt-2 ml-2 price4_all" hidden>
                                                                    <label for="crud-form-1" class="form-label">ราคา(เด็กไม่มีเตียง)</label>
                                                                    <input type="text" id="price4_all" class="form-control" onkeypress="return check_number(this)" name="price4_all" value="">
                                                                    <label for="crud-form-1" class="form-label">ลดราคา(เด็กไม่มีเตียง)</label>
                                                                    <input type="text" id="special_price4_all" class="form-control" onkeypress="return check_number(this)" name="special_price4_all" value="">
                                                                </div>
                                                                <div class="col-span-12 lg:col-span-3 mt-2 ml-2 price3_all" hidden>
                                                                    <label for="crud-form-1" class="form-label">ราคา(เด็กมีเตียง)</label>
                                                                    <input type="text" id="price3_all" class="form-control" onkeypress="return check_number(this)" name="price3_all" value="">
                                                                    <label for="crud-form-1" class="form-label">ลดราคา(เด็กมีเตียง)</label>
                                                                    <input type="text" id="special_price3_all" class="form-control" onkeypress="return check_number(this)" name="special_price3_all" value="">
                                                                </div>
                                                                <div class="col-span-12 lg:col-span-3 mt-2 ml-2 group_all" hidden>
                                                                    <label for="crud-form-1" class="form-label">Group Size</label>
                                                                    <input type="text" id="group_all" class="form-control" onkeypress="return check_number(this)" name="group_all" value="">
                                                                </div>
                                                                <div class="col-span-12 lg:col-span-3 mt-2 ml-2 count_all" hidden>
                                                                    <label for="crud-form-1" class="form-label">จำนวน</label>
                                                                    <input type="text" id="count_all" class="form-control" onkeypress="return check_number(this)" name="count_all" value="">
                                                                </div>
                                                            </div>
                                                            <div class="flex flex-col sm:flex-row mt-2">
                                                                <div class="col-span-12 lg:col-span-6 mt-2 ml-2 promotion_all" hidden>
                                                                    <label for="crud-form-1" class="form-label">โปรโมชั่น</label>
                                                                    <select id="promotion_all" class="form-control" name="promotion_all">
                                                                        <option value="">กรุณาเลือก</option>
                                                                        @foreach($promotion as $pro)
                                                                            <option value="{{$pro->id}}" @if(@$pe->promotion_id == $pro->id) selected @endif>{{ $pro->promotion_name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="col-span-12 lg:col-span-6 mt-2 ml-2 pro_start_date_all" hidden>
                                                                    <label for="crud-form-1" class="form-label">วันที่เริ่มโปรโมชั่น</label>
                                                                    <input type="date" id="pro_start_date_all" class="form-control" name="pro_start_date_all" value="">
                                                                </div>
                                                                <div class="col-span-12 lg:col-span-6 mt-2 ml-2 pro_end_date_all" hidden>
                                                                    <label for="crud-form-1" class="form-label">วันที่สิ้นสุดโปรโมชั่น</label>
                                                                    <input type="date" id="pro_end_date_all" class="form-control" name="pro_end_date_all" value="">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="overflow-x-auto">
                                                            <div id="data_period">
                                                                {{-- <div id="del_period0">
                                                                    <div class="text-right">
                                                                        <button class="btn btn-danger del-btn btn-block" type="button" style="margin:auto;" onclick="del_period({{$p}})">ลบ &nbsp;<i class="fa fa-arrow-down" aria-hidden="true"></i></button>
                                                                    </div>
                                                                    <table class="table table-striped table-bordered mt-2" style="width: 100%;">
                                                                        <thead class="table-light">
                                                                            <tr role="row">
                                                                                <th  class="vertid text-center">#</th>
                                                                                <th  class="vertid text-center">เริ่มต้น</th>
                                                                                <th  class="vertid text-center">สิ้นสุด</th>
                                                                                <th  class="vertid text-center">ผู้ใหญ่(พัก2-3)</th>
                                                                                <th  class="vertid text-center">พักเดี่ยว</th>
                                                                                <th  class="vertid text-center" colspan="2">เด็ก(มีเตียง)</th>
                                                                                <th  class="vertid text-center" colspan="2">เด็ก(ไม่มีเตียง)</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <tr>
                                                                                <td>0</td>
                                                                                <td>
                                                                                    <input type="date" id="start_date0" onchange="return cal_date(0)" class="form-control start_date" name="period[0][start_date][]">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="date" id="end_date0" onchange="return cal_date(0)" class="form-control end_date" name="period[0][end_date][]">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" id="price10" onkeypress="return check_number(this)" class="form-control price1" name="period[0][price1][]"><br>
                                                                                    <center><label class="special_price1">ลดราคา</label></center>
                                                                                    <input type="text" id="special_price10" onkeypress="return check_number(this)" class="form-control special_price1" name="period[0][special_price1][]">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" id="price20" onkeypress="return check_number(this)" class="form-control price2" name="period[0][price2][]"><br>
                                                                                    <center><label class="special_price2">ลดราคา</label></center>
                                                                                    <input type="text" id="special_price20" onkeypress="return check_number(this)" class="form-control special_price2" name="period[0][special_price2][]">
                                                                                </td>
                                                                                <td colspan="2">
                                                                                    <input type="text" id="price30" onkeypress="return check_number(this)" class="form-control price3" name="period[0][price3][]"><br>
                                                                                    <center><label class="special_price3">ลดราคา</label></center>
                                                                                    <input type="text" id="special_price30" onkeypress="return check_number(this)" class="form-control special_price3" name="period[0][special_price3][]">
                                                                                </td>
                                                                                <td colspan="2">
                                                                                    <input type="text" id="price40" onkeypress="return check_number(this)" class="form-control price4" name="period[0][price4][]"><br>
                                                                                    <center><label class="special_price4">ลดราคา</label></center>
                                                                                    <input type="text" id="special_price40" onkeypress="return check_number(this)" class="form-control special_price4" name="period[0][special_price4][]">
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                        <thead class="table-light">
                                                                            <tr role="row">
                                                                                <th></th>
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
                                                                                <td></td>
                                                                                <td colspan="2">
                                                                                    <select id="promotion_id0" class="form-control promotion_id" name="period[0][promotion_id][]">
                                                                                        <option value="">กรุณาเลือก</option>
                                                                                        @foreach($promotion as $pro)
                                                                                        <option value="{{$pro->id}}">{{$pro->promotion_name}}</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </td>
                                                                                <td>
                                                                                    <input type="date" id="pro_start_date0" class="form-control pro_start_date" name="period[0][pro_start_date][]">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="date" id="pro_end_date0" class="form-control pro_end_date" name="period[0][pro_end_date][]">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" id="day0" onkeypress="return check_number(this)" class="form-control day" name="period[0][day][]">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" id="night0" onkeypress="return check_number(this)" class="form-control night" name="period[0][night][]">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" id="group0" onkeypress="return check_number(this)" class="form-control group" name="period[0][group][]"  >
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" id="count0" onkeypress="return check_number(this)" class="form-control count" name="period[0][count][]"  >
                                                                                </td>
                                                                                <input type="hidden" class="form-control" name="period[0][period_id][]" value="">
                                                                                <input type="hidden" class="form-control" name="period[0][status_display][]" value="draft">
                                                                                <input type="hidden" class="form-control" name="period[0][status_period][]" value="1">
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                    <br>
                                                                </div> --}}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- ข้อมูล Period --}}

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
            echo 'var data = '.$data_tag.';';
            echo 'var arr = new Array();';
            echo '</script>';
        ?>
        @include("backend.layout.tag")
        
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

            var count_detail = 0;
            var count_detail_sub = 0;
            function add_detail(){  
                add = '<div id="del_detail'+count_detail+'">'+
                    '<div class="text-right">'+
                    '<button class="btn btn-danger del-btn btn-block" type="button" style="margin:auto;" onclick="del_detail('+count_detail+')">ลบ &nbsp;<i class="fa fa-arrow-down" aria-hidden="true"></i></button>'+
                    '</div>'+
                    '<table class="table table-striped table-bordered mt-2" style="width: 100%;">'+
                    '<thead class="table-light">'+
                    '<tr role="row">'+
                    '<th  class="vertid text-center" style="width: 5%;">#</th>'+
                    '<th  class="vertid text-center" style="width: 20%;"></th>'+
                    '<th  class="vertid text-center" style="width: 25%;"></th>'+
                    '<th  class="vertid text-center" style="width: 20%;"></th>'+
                    '<th  class="vertid text-center" style="width: 20%;"></th>'+
                    '<th  class="vertid text-center" style="width: 10%;"></th>'+
                    '</tr></thead>'+
                    '<tbody><tr>'+
                    '<td><center>'+(count_detail+1)+'</center></td>'+
                    '<td colspan="5">'+
                    '<center><label>หัวข้อหลัก</label></center>'+
                    '<input type="text" class="form-control" name="detail[][header][]">'+
                    '</td>'+
                    '</tr></tbody>'+
                    '<thead class="table-light">'+
                    '<tr role="row">'+
                    '<th  class="vertid text-center"></th>'+
                    '<th  class="vertid text-center"></th>'+
                    '<th  class="vertid text-center"></th>'+
                    '<th  class="vertid text-center"></th>'+
                    '<th  class="vertid text-center"></th>'+
                    '<th  class="vertid text-center"></th>'+
                    '</tr></thead>'+
                    '<tbody id="data_detail_sub'+count_detail+'">'+
                    '</tbody></table><br>'+
                    '<div class="text-left">'+
                    '<button class="btn btn-primary btn-block" type="button"style="margin: auto;" onclick="add_detail_sub('+count_detail+')"> เพิ่ม &nbsp;<i class="fa fa-arrow-up" aria-hidden="true"></i></button>'+
                    '</div></div>';
                $('#data_detail').append(add);
                count_detail++;
            }

            function add_detail_sub(d){  
                add_sub = '<tr id="del_detail_sub'+count_detail_sub+'">'+
                    '<td></td>'+
                    '<td><input type="file" id="detail_image'+count_detail_sub+'" class="form-control" name="detail['+d+'][sub]['+count_detail_sub+'][image]" accept="image/png, image/jpeg, image/jpg" style="width: auto;"></td>'+
                    '<td><center><label>เวลา</label></center><input type="text" id="" class="form-control" name="detail['+d+'][sub]['+count_detail_sub+'][time]"><center><label>หัวข้อย่อย</label></center><input type="text" id="" class="form-control" name="detail['+d+'][sub]['+count_detail_sub+'][title]"></td>'+
                    '<td colspan="2"><center><label>รายละเอียด</label></center><textarea class="form-control" name="detail['+d+'][sub]['+count_detail_sub+'][detail]" rows="3"></textarea></td>'+
                    '<td><center><button class="btn btn-danger del-btn btn-block" type="button" style="margin:auto;" onclick="del_detail_sub('+count_detail_sub+')">ลบ</button></center></td>'+
                    '</tr>';
                $('#data_detail_sub'+d).append(add_sub);
                count_detail_sub++;  
            }

            function del_detail(d){
                var de = document.getElementById('del_detail'+d);
                de.parentNode.removeChild(de);
            }

            function del_detail_sub(d_s){
                var de_s = document.getElementById('del_detail_sub'+d_s);
                de_s.parentNode.removeChild(de_s);
            }

            $('#promotion_check').click(function() {
                if($(this).is(":checked")) {
                    $(".promotion_all").attr("hidden",false);
                    $(".promotion_id").attr("hidden",true);
                } else {
                    $(".promotion_all").attr("hidden",true);
                    $(".promotion_id").attr("hidden",false);
                }
            });

            $('#start_date_check').click(function() {
                if($(this).is(":checked")) {
                    $(".pro_start_date_all").attr("hidden",false);
                    $(".pro_start_date").attr("hidden",true);
                } else {
                    $(".pro_start_date_all").attr("hidden",true);
                    $(".pro_start_date").attr("hidden",false);
                }
            });

            $('#end_date_check').click(function() {
                if($(this).is(":checked")) {
                    $(".pro_end_date_all").attr("hidden",false);
                    $(".pro_end_date").attr("hidden",true);
                } else {
                    $(".pro_end_date_all").attr("hidden",true);
                    $(".pro_end_date").attr("hidden",false);
                }
            });

            $('#copy_price3').click(function() {
                if($(this).is(":checked")) {
                    $(".price3").attr("hidden",true);
                    $(".special_price3").attr("hidden",true);
                    $("#price3_check").prop("checked",false);
                    $(".price3_all").attr("hidden",true);
                } else {
                    $(".price3").attr("hidden",false);
                    $(".special_price3").attr("hidden",false);
                }
            });

            $('#copy_price4').click(function() {
                if($(this).is(":checked")) {
                    $(".price4").attr("hidden",true);
                    $(".special_price4").attr("hidden",true);
                    $("#price4_check").prop("checked",false);
                    $(".price4_all").attr("hidden",true);
                } else {
                    $(".price4").attr("hidden",false);
                    $(".special_price4").attr("hidden",false);
                }
            });

            $('#price1_check').click(function() {
                if($(this).is(":checked")) {
                    $(".price1_all").attr("hidden",false);
                    $(".price1").attr("hidden",true);
                    $(".special_price1").attr("hidden",true);
                } else {
                    $(".price1_all").attr("hidden",true);
                    $(".price1").attr("hidden",false);
                    $(".special_price1").attr("hidden",false);
                }
            });

            $('#price2_check').click(function() {
                if($(this).is(":checked")) {
                    $(".price2_all").attr("hidden",false);
                    $(".price2").attr("hidden",true);
                    $(".special_price2").attr("hidden",true);
                } else {
                    $(".price2_all").attr("hidden",true);
                    $(".price2").attr("hidden",false);
                    $(".special_price2").attr("hidden",false);
                }
            });

            $('#price3_check').click(function() {
                if($(this).is(":checked")) {
                    $(".price3_all").attr("hidden",false);
                    $(".price3").attr("hidden",true);
                    $(".special_price3").attr("hidden",true);
                    $("#copy_price3").prop("checked",false);
                } else {
                    $(".price3_all").attr("hidden",true);
                    $(".price3").attr("hidden",false);
                    $(".special_price3").attr("hidden",false);
                }
            });

            $('#price4_check').click(function() {
                if($(this).is(":checked")) {
                    $(".price4_all").attr("hidden",false);
                    $(".price4").attr("hidden",true);
                    $(".special_price4").attr("hidden",true);
                    $("#copy_price4").prop("checked",false);
                } else {
                    $(".price4_all").attr("hidden",true);
                    $(".price4").attr("hidden",false);
                    $(".special_price4").attr("hidden",false);
                }
            });
            
            $('#group_check').click(function() {
                if($(this).is(":checked")) {
                    $(".group_all").attr("hidden",false);
                    $(".group").attr("hidden",true);
                } else {
                    $(".group_all").attr("hidden",true);
                    $(".group").attr("hidden",false);
                }
            });

            $('#count_check').click(function() {
                if($(this).is(":checked")) {
                    $(".count_all").attr("hidden",false);
                    $(".count").attr("hidden",true);
                } else {
                    $(".count_all").attr("hidden",true);
                    $(".count").attr("hidden",false);
                }
            });
    
            var count_period = 1;
            function add_period(){
                var promotion_check = ($('#promotion_check').is(":checked")) ? 'hidden' :'';
                var start_date_check = ($('#start_date_check').is(":checked")) ? 'hidden' :'';
                var end_date_check = ($('#end_date_check').is(":checked")) ? 'hidden' :'';
                var copy_price4 = ($('#copy_price4').is(":checked")) ? 'hidden' :'';
                var copy_price3 = ($('#copy_price3').is(":checked")) ? 'hidden' :'';
                var price1_check = ($('#price1_check').is(":checked")) ? 'hidden' :'';
                var price2_check = ($('#price2_check').is(":checked")) ? 'hidden' :'';
                var price3_check = ($('#price3_check').is(":checked")) ? 'hidden' :'';
                var price4_check = ($('#price4_check').is(":checked")) ? 'hidden' :'';
                var group_check = ($('#group_check').is(":checked")) ? 'hidden' :'';
                var count_check = ($('#count_check').is(":checked")) ? 'hidden' :'';
                
                add = '<div id="del_period'+count_period+'"><div class="text-right">'+
                    '<button class="btn btn-danger del-btn btn-block" type="button" style="margin:auto;" onclick="del_period('+count_period+')">ลบ &nbsp;<i class="fa fa-arrow-down" aria-hidden="true"></i></button></div>'+
                    '<table class="table table-striped table-bordered mt-2" style="width: 100%;">'+
                    '<thead class="table-light">'+'<tr role="row">'+
                    '<th  class="vertid text-center">#</th>'+
                    '<th  class="vertid text-center">เริ่มต้น</th>'+
                    '<th  class="vertid text-center">สิ้นสุด</th>'+
                    '<th  class="vertid text-center">ผู้ใหญ่(พัก2-3)</th>'+
                    '<th  class="vertid text-center">พักเดี่ยว</th>'+
                    '<th  class="vertid text-center" colspan="2">เด็ก(มีเตียง)</th>'+
                    '<th  class="vertid text-center" colspan="2">เด็ก(ไม่มีเตียง)</th>'+
                    '</tr>'+'</thead>'+

                    '<tbody>'+'<tr>'+
                    '<td>'+count_period+'</td>'+
                    '<td><input type="date" id="start_date'+count_period+'" onchange="return cal_date('+count_period+')" class="form-control start_date" name="period['+count_period+'][start_date][]"></td>'+
                    '<td><input type="date" id="end_date'+count_period+'" onchange="return cal_date('+count_period+')" class="form-control end_date" name="period['+count_period+'][end_date][]"></td>'+
                    '<td><input type="text" id="price1'+count_period+'" onkeypress="return check_number(this)" class="form-control price1" name="period['+count_period+'][price1][]"'+price1_check+'><br><center><label class="special_price1"'+price1_check+'>ลดราคา</label></center><input type="text" id="special_price1'+count_period+'" onkeypress="return check_number(this)" class="form-control special_price1" name="period['+count_period+'][special_price1][]"'+price1_check+'></td>'+
                    '<td><input type="text" id="price2'+count_period+'" onkeypress="return check_number(this)" class="form-control price2" name="period['+count_period+'][price2][]"'+price2_check+'><br><center><label class="special_price2"'+price2_check+'>ลดราคา</label></center><input type="text" id="special_price2'+count_period+'" onkeypress="return check_number(this)" class="form-control special_price2" name="period['+count_period+'][special_price2][]"'+price2_check+'></td>'+
                    '<td colspan="2"><input type="text" id="price3'+count_period+'" onkeypress="return check_number(this)" class="form-control price3" name="period['+count_period+'][price3][]"'+copy_price3+' '+price3_check+'><br><center><label class="special_price3"'+copy_price3+' '+price3_check+'>ลดราคา</label></center><input type="text" id="special_price3'+count_period+'" onkeypress="return check_number(this)" class="form-control special_price3" name="period['+count_period+'][special_price3][]"'+copy_price3+' '+price3_check+'></td>'+
                    '<td colspan="2"><input type="text" id="price4'+count_period+'" onkeypress="return check_number(this)" class="form-control price4" name="period['+count_period+'][price4][]"'+copy_price4+' '+price4_check+'><br><center><label class="special_price4"'+copy_price4+' '+price4_check+'>ลดราคา</label></center><input type="text" id="special_price4'+count_period+'" onkeypress="return check_number(this)" class="form-control special_price4" name="period['+count_period+'][special_price4][]"'+copy_price4+' '+price4_check+'></td>'+
                    '</tr>'+'</body>'+

                    '<thead class="table-light">'+'<tr role="row">'+
                    '<th></th>'+
                    '<th  class="vertid text-center" colspan="2">โปรโมชั่น</th>'+
                    '<th  class="vertid text-center">โปรโมชั่นเริ่มต้น</th>'+
                    '<th  class="vertid text-center">โปรโมชั่นสิ้นสุด</th>'+
                    '<th  class="vertid text-center">Day</th>'+
                    '<th  class="vertid text-center">Night</th>'+
                    '<th  class="vertid text-center">Group Size</th>'+
                    '<th  class="vertid text-center">จำนวน</th>'+
                    '</tr>'+'</thead>'+
                    '<tbody>'+'<tr>'+
                    '<td></td>'+
                    '<td colspan="2"><select id="promotion_id'+count_period+'" class="form-control promotion_id" name="period['+count_period+'][promotion_id][]"'+promotion_check+'>'+
                    '<option value="">กรุณาเลือก</option>'+
                    @foreach($promotion as $pro)
                    '<option value="{{$pro->id}}">{{$pro->promotion_name}}</option>'+
                    @endforeach
                    '</select></td>'+
                    '<td><input type="date" id="pro_start_date'+count_period+'" class="form-control pro_start_date" name="period['+count_period+'][pro_start_date][]"'+start_date_check+'></td>'+
                    '<td><input type="date" id="pro_end_date'+count_period+'" class="form-control pro_end_date" name="period['+count_period+'][pro_end_date][]"'+end_date_check+'></td>'+
                    '<td><input type="text" id="day'+count_period+'" onkeypress="return check_number(this)" class="form-control day" name="period['+count_period+'][day][]"></td>'+
                    '<td><input type="text" id="night'+count_period+'" onkeypress="return check_number(this)" class="form-control night" name="period['+count_period+'][night][]"></td>'+
                    '<td><input type="text" id="group'+count_period+'" onkeypress="return check_number(this)" class="form-control group" name="period['+count_period+'][group][]"'+group_check+'></td>'+
                    '<td><input type="text" id="count'+count_period+'" onkeypress="return check_number(this)" class="form-control count" name="period['+count_period+'][count][]"'+count_check+'></td>'+
                    '<input type="hidden" class="form-control" name="period['+count_period+'][period_id][]" value="">'+
                    '<input type="hidden" class="form-control" name="period['+count_period+'][status_display][]" value="draft">'+
                    '<input type="hidden" class="form-control" name="period['+count_period+'][status_period][]" value="1">'+
                    '</tr>'+'</body>'+
                    '</table><br></div>';   
                $('#data_period').append(add);
                count_period++;  
            }

            $("#group_id").change(function(){
                var group_id = $(this).val();
                if(group_id == 3){
                    $(".wholesale").attr("hidden",false);
                }else{
                    $(".wholesale").attr("hidden",true);
                }
            });

            function del_period(d){
                var de = document.getElementById('del_period'+d);
                de.parentNode.removeChild(de);
            }

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

            $("#image").on('change', function () {
                var $this = $(this)
                console.log($this);
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

            $("#video_cover").on('change', function () {
                var $this = $(this)
                const input = $this[0];
                const fileName = $this.val().split("\\").pop();
                $this.siblings(".custom-file-label").addClass("selected").html(fileName)
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $('#preview_video').attr('src', e.target.result).fadeIn('fast');
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            });
            
            function check_add() {
                var image = $('#image').val();
                var group_id = $('#group_id').val();
                var category = $('#category').val();
                var name = $('#name').val();
                if (image == "" || group_id == "" || category == "" || name == "") {
                    toastr.error('กรุณากรอกข้อมูลให้ครบถ้วนก่อนบันทึกรายการ');
                    return false;
                }
            }
        </script>
        <!-- END: JS Assets-->
</body>

</html>
