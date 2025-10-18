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
                        <div class="intro-y col-span-12 lg:col-span-12">
                            <!-- BEGIN: Form Layout -->
                            <div class="intro-y box p-5 place-content-center">

                                <div id="accordion-color" data-accordion="collapse" data-active-classes="bg-blue-100 dark:bg-gray-800 text-blue-600 dark:text-white">

                                    {{-- ข้อมูลโปรแกรมทัวร์ --}}
                                    <h2 id="accordion-color-heading-1">
                                        <button type="button" id="button_tour" class="flex items-center justify-between w-full p-5 font-medium text-left text-gray-500 border border-b-0 border-gray-200 rounded-t-xl focus:ring-4 focus:ring-blue-200 dark:focus:ring-blue-800 dark:border-gray-700 dark:text-gray-400 hover:bg-blue-100 dark:hover:bg-gray-800" 
                                        data-accordion-target="#accordion-color-body-1" aria-expanded="true" aria-controls="accordion-color-body-1">
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
                                                        <b><label for="crud-form-1" class="form-label"><span class="text-danger">*</span> ชื่อ</label></b> @if(@$row->data_type == 2)<input class="form-check-input ml-2" type="checkbox" name="name_check_change" value="1" @if(@$row->name_check_change == 1) checked @endif> เลือกเมื่อไม่ต้องการอัพเดทชื่อจาก Api @endif
                                                        <input type="text" id="name" name="name" value="{{@$row->name}}" class="form-control" required>
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                                    <div class="col-span-12 lg:col-span-6">
                                                        <b><label for="crud-form-1" class="form-label">รหัสทัวร์ (Generate)</label></b>
                                                        <input type="text" id="code" name="code" value="{{@$row->code}}" class="form-control" readonly>
                                                    </div>
                                                    <div class="col-span-12 lg:col-span-6">
                                                        <b><label for="crud-form-1" class="form-label">รหัสทัวร์ (Manual)</label></b> <input class="form-check-input ml-2" type="checkbox" name="code1_check" value="1" @if(@$row->code1_check == 1) checked @endif> เลือกเมื่อต้องการใช้รหัสทัวร์
                                                        <input type="text" id="code1" name="code1" value="{{@$row->code1}}" class="form-control">
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                                    <div class="col-span-12 lg:col-span-6">
                                                        <b><label for="crud-form-1" class="form-label">ประเภททัวร์</label></b>
                                                        <select name="type_id" id="type_id" class="form-control tom-select">
                                                            <option value="" selected hidden>กรุณาเลือกข้อมูล</option>
                                                            @foreach ($type as $t)
                                                                <option value="{{$t->id}}" @if(@$row->type_id == $t->id) selected @endif>{{$t->type_name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-span-12 lg:col-span-6">
                                                        <b><label for="crud-form-1" class="form-label">เที่ยวบิน</label></b> @if(@$row->data_type == 2)<input class="form-check-input ml-2" type="checkbox" name="airline_check_change" value="1" @if(@$row->airline_check_change == 1) checked @endif> เลือกเมื่อไม่ต้องการอัพเดทเที่ยวบินจาก Api @endif
                                                        <select name="airline_id" id="airline_id" class="form-control tom-select">
                                                            <option value="" selected hidden>กรุณาเลือกข้อมูล</option>
                                                            @foreach ($travel as $tra)
                                                                <option value="{{$tra->id}}" @if(@$row->airline_id == $tra->id) selected @endif>({{$tra->code}}) {{$tra->travel_name}}</option>
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
                                                                <option value="{{$g->id}}" @if(@$row->group_id == $g->id) selected @endif>{{$g->group_name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-span-12 lg:col-span-6 wholesale" @if(@$row->group_id != 3) hidden @endif>
                                                        <b><label for="crud-form-1" class="form-label">Supplier</label></b>
                                                        <select name="wholesale_id" id="wholesale_id" class="form-control tom-select">
                                                            @foreach ($wholesale as $w)
                                                                <option value="{{$w->id}}" @if(@$row->wholesale_id == $w->id) selected @endif>({{ $w->code }}) {{$w->wholesale_name_en}} ({{$w->wholesale_name_th}})</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                @php
                                                    $country_select = json_decode($row->country_id,true); 
                                                    $city_select = json_decode($row->city_id,true); 
                                                    $province_select = json_decode($row->province_id,true); 
                                                    $district_select = json_decode($row->district_id,true); 
                                                    $tag_select = json_decode($row->tag_id,true);
                                                @endphp
                                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                                    <div class="col-span-12 lg:col-span-6">
                                                        <b><label for="crud-form-1" class="form-label"><span class="text-danger">*</span> ประเทศ/เมือง</label></b> @if(@$row->data_type == 2)<input class="form-check-input ml-2" type="checkbox" name="country_check_change" value="1" @if(@$row->country_check_change == 1) checked @endif> เลือกเมื่อไม่ต้องการอัพเดทประเทศจาก Api @endif
                                                        <select data-placeholder="กรุณาเลือกข้อมูล" id="category" class="tom-select w-full" multiple name="category[]">
                                                            @foreach($country as $co)
                                                                <option value="CO.{{$co->id}}" @if(in_array($co->id,$country_select)) selected @endif>{{$co->country_name_th}}</option>
                                                            @endforeach
                                                            @foreach($city as $ci)
                                                                <option value="CI.{{$ci->id}}" @if(in_array($ci->id,$city_select)) selected @endif>{{$ci->city_name_th}}</option>
                                                            @endforeach
                                                            @foreach($province as $p)
                                                                <option value="P.{{$p->id}}" @if(in_array($p->id,$province_select)) selected @endif>{{$p->name_th}}</option>
                                                            @endforeach
                                                            @foreach($district as $d)
                                                                <option value="D.{{$d->id}}" @if(in_array($d->id,$district_select)) selected @endif>{{$d->name_th}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-span-12 lg:col-span-6">
                                                        <b><label for="crud-form-1" class="form-label">Tag</label></b>
                                                        {{-- <select data-placeholder="กรุณาเลือกข้อมูล" class="tom-select w-full" multiple name="tag_id[]" >
                                                            @foreach($tag as $t)
                                                                <option value="{{$t->id}}" @if(in_array($t->id,$tag_select)) selected @endif>{{$t->tag}}</option>
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
                                                                    name="image" id="image" accept="image/png, image/jpeg, image/jpg, image/webp">
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
                                                {{-- image --}}

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
                                                    </div>
                                                    <div class="col-span-12 lg:col-span-6">
                                                        <b><label for="crud-form-1" class="form-label">Video</label></b>
                                                        <input type="text" id="video" name="video" value="{{@$row->video}}" class="form-control">
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                                    <div class="col-span-12 lg:col-span-6">
                                                        <b><label for="crud-form-1" class="form-label">ราคา</label></b>
                                                        <input type="text" id="price" name="price" value="{{@$row->price}}" class="form-control" readonly>
                                                    </div>
                                                    <div class="col-span-12 lg:col-span-6">
                                                        <b><label for="crud-form-1" class="form-label">ส่วนลด</label></b>
                                                        <input type="text" id="special_price" name="special_price" value="{{@$row->special_price}}" class="form-control" readonly>
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                                    <div class="col-span-12 lg:col-span-12">
                                                        <b><label for="crud-form-1" class="form-label">ไฮไลท์</label></b> @if(@$row->data_type == 2)<input class="form-check-input ml-2" type="checkbox" name="description_check_change" value="1" @if(@$row->description_check_change == 1) checked @endif> เลือกเมื่อไม่ต้องการอัพเดทไฮไลท์จาก Api @endif
                                                        <textarea type="text" id="description" name="description" class="form-control" rows="2">{{@$row->description}}</textarea>
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                                    <div class="col-span-12 lg:col-span-6">
                                                        <b><label for="crud-form-1" class="form-label">เที่ยว</label></b>
                                                        <textarea type="text" id="travel" name="travel" class="form-control" rows="2">{{@$row->travel}}</textarea>
                                                    </div>
                                                    <div class="col-span-12 lg:col-span-6">
                                                        <b><label for="crud-form-1" class="form-label">ช็อป</label></b>
                                                        <textarea type="text" id="shop" name="shop" class="form-control" rows="2">{{@$row->shop}}</textarea>
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                                    <div class="col-span-12 lg:col-span-6">
                                                        <b><label for="crud-form-1" class="form-label">กิน</label></b>
                                                        <textarea type="text" id="eat" name="eat" class="form-control" rows="2">{{@$row->eat}}</textarea>
                                                    </div>
                                                    <div class="col-span-12 lg:col-span-6">
                                                        <b><label for="crud-form-1" class="form-label">พิเศษ</label></b>
                                                        <textarea type="text" id="special" name="special" class="form-control" rows="2">{{@$row->special}}</textarea>
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                                    <div class="col-span-12 lg:col-span-6">
                                                        <b><label for="crud-form-1" class="form-label">พัก</label></b>
                                                        <textarea type="text" id="stay" name="stay" class="form-control" rows="2">{{@$row->stay}}</textarea>
                                                    </div>
                                                </div>

                                                <div class="text-left mt-5">
                                                    <div class="col-span-12 lg:col-span-6">
                                                    <h3 class="mb-3"><b>อัลบั้มรูปภาพ</b> <span class="text-danger"> ขนาด 600*600</span></h3>
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
                                                <div class="overflow-x-auto" style="background-color: #F5F5F5;padding:15px;border-radius:5px;">
                                                    <table id="datatable-gallery" class="table table-report">
                                                        <thead>
                                                            <tr>
                                                                <th><center>#</center></th>
                                                                <th><center>รูปภาพ</center></th>
                                                                <th><center>จัดการ</center></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @if(count($gallery) > 0)
                                                                @foreach($gallery as $i => $gal)
                                                                    <tr>
                                                                        <td><center>{{$i+1}}</center></td>
                                                                        <td><center><img src="{{$gal->img}}" style="width: 30%;"></center></td>
                                                                        <td>
                                                                            <center>
                                                                                <a href="{{$segment}}/{{$folder}}/edit-gallery/{{$gal->id}}" class="mr-3 mb-2 btn btn-sm btn-info" title="Edit"><i class="fa fa-edit w-4 h-4 mr-1"></i> แก้ไข </a>                                                 
                                                                                <a href="javascript:" class="mr-3 mb-2 btn btn-sm btn-danger" onclick="deleteItem({{$gal->id}})" title="Delete"><i class="far fa-trash-alt w-4 h-4 mr-1"></i> ลบ</a>
                                                                            </center>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @else
                                                                <tr class="text-center">
                                                                    <td colspan="4">--ไม่มีข้อมูล--</td>
                                                                </tr>
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <br>

                                                <div class="intro-y grid grid-cols-12 gap-4 sm:gap-6 mt-5">
                                                    <div class="intro-y col-span-12 lg:col-span-3">
                                                        <b><label for="crud-form-1" class="form-label">File PDF</label></b>
                                                        <div class="file box rounded-md px-5 pt-8 pb-5 px-3 sm:px-5 relative zoom-in">
                                                            @if($row->pdf_file)
                                                                <a href="{{asset($row->pdf_file)}}" target="_blank" class="w-3/5 file__icon file__icon--file mx-auto"></a>
                                                                <br>
                                                                <div class="absolute top-0 right-0 mr-2 mt-3 dropdown ml-auto">
                                                                    <i onclick="pdf_delete('{{$row->id}}');" data-lucide="x" class="w-7 h-7 text-slate-700"></i>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <small class="help-block">* รองรับไฟล์ <strong class="text-danger">(pdf)</strong> เท่านั้น</small>
                                                        <input type="file" class="custom-file-input block w-60 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" style="width: 100%;"
                                                            name="pdf_file" id="pdf_file" accept="application/pdf">
                                                    </div>
                                                    <div class="intro-y col-span-12 lg:col-span-3">
                                                        <b><label for="crud-form-1" class="form-label">File Word</label></b>
                                                        <div class="file box rounded-md px-5 pt-8 pb-5 px-3 sm:px-5 relative zoom-in">
                                                            @if($row->word_file)
                                                                <a href="{{asset($row->word_file)}}" class="w-3/5 file__icon file__icon--file mx-auto"></a>
                                                                <br>
                                                                <div class="absolute top-0 right-0 mr-2 mt-3 dropdown ml-auto">
                                                                    <i onclick="word_delete('{{$row->id}}');" data-lucide="x" class="w-7 h-7 text-slate-700"></i>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <small class="help-block">* รองรับไฟล์ <strong class="text-danger">(docx)</strong> เท่านั้น</small>
                                                        <input type="file" class="custom-file-input block w-60 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" style="width: 100%;"
                                                            name="word_file" id="word_file" accept=".doc, .docx">
                                                    </div>
                                                </div>
                
                                                <div class="border-gray-200 dark:border-gray-700">
                                                    <div class="container col-span-12">
                                                        <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                                            <div class="col-span-12 lg:col-span-12">
                                                                <div class="col-span-12 lg:col-span-12">
                                                                    <label for="crud-form-1" class="form-label">รายละเอียดทัวร์ &nbsp;&nbsp;&nbsp;<button class="btn btn-primary btn-block" type="button"style="margin: auto;" onclick="add_detail()"> เพิ่ม &nbsp;<i class="fa fa-plus-circle" aria-hidden="true"></i></button></label>
                                                                </div>
                                                                {{-- @if(count(@$detail) > 0) --}}
                                                                <div class="overflow-x-auto">
                                                                    <div id="data_detail">
                                                                        @php 
                                                                            $d = 0; 
                                                                            $ds = 0; 
                                                                            $data_tour = json_decode($row->tour_detail,true);
                                                                        @endphp
                                                                        @foreach ($data_tour as $dat)
                                                                        <div id="del_detail{{$d}}">
                                                                            <div class="text-right">
                                                                                <button class="btn btn-danger del-btn btn-block" type="button" style="margin:auto;" onclick="del_detail({{$d}})">ลบ &nbsp;<i class="fa fa-arrow-down" aria-hidden="true"></i></button>
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
                                                                                        <td><center>{{$d+1}}</center></td>
                                                                                        <td colspan="5">
                                                                                            <center><label>หัวข้อหลัก</label></center>
                                                                                            @foreach ($dat['header'] as $header)
                                                                                                <input type="text" class="form-control" name="detail[][header][]" value="{{ $header }}">
                                                                                            @endforeach
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
                                                                                <tbody id="data_detail_sub{{$d}}">
                                                                                    @if(isset($dat['sub']))
                                                                                        @foreach ($dat['sub'] as $subItem)
                                                                                        <tr id="del_detail_sub{{$ds}}">
                                                                                            <td></td>
                                                                                            <td>
                                                                                                <center>
                                                                                                    @if(isset($subItem['image']))
                                                                                                    <img src="{{asset($subItem['image'])}}" alt="" style="width: 50%;"><br>
                                                                                                    @endif
                                                                                                    <input type="file" id="detail_image0" class="form-control" name="detail[{{$d}}][sub][{{$ds}}][image]" accept="image/png, image/jpeg, image/jpg" style="width: auto;">
                                                                                                </center>
                                                                                            </td>
                                                                                            <td>
                                                                                                <center><label>เวลา</label></center>
                                                                                                <input type="text" id="" class="form-control" name="detail[{{$d}}][sub][{{$ds}}][time]" value="{{ $subItem['time'] }}">
                                                                                                <center><label>หัวข้อย่อย</label></center>
                                                                                                <input type="text" id="" class="form-control" name="detail[{{$d}}][sub][{{$ds}}][title]" value="{{ $subItem['title'] }}">
                                                                                            </td>
                                                                                            <td colspan="2">
                                                                                                <center><label>รายละเอียด</label></center>
                                                                                                <textarea class="form-control" id="detail0" name="detail[{{$d}}][sub][{{$ds}}][detail]" rows="3">{{ $subItem['detail'] }}</textarea>
                                                                                            </td>
                                                                                            <td>
                                                                                                <center>
                                                                                                    <button class="btn btn-danger del-btn btn-block" type="button" style="margin:auto;" onclick="del_detail_sub({{$ds}})">ลบ</button>
                                                                                                </center>
                                                                                            </td>
                                                                                        </tr>
                                                                                        @php
                                                                                            $ds++;
                                                                                        @endphp
                                                                                        @endforeach
                                                                                    @endif
                                                                                </tbody>
                                                                            </table>
                                                                            <br>
                                                                            <div class="text-left">
                                                                                <button class="btn btn-primary btn-block" type="button"style="margin: auto;" onclick="add_detail_sub({{$d}})"> เพิ่ม &nbsp;<i class="fa fa-plus-circle" aria-hidden="true"></i></button>
                                                                            </div>
                                                                        </div>
                                                                        @php
                                                                            $d++;
                                                                        @endphp
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                                {{-- @endif --}}
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
                                                        @if($row->data_type == 1)
                                                        <div class="col-span-12 lg:col-span-12">
                                                            <label for="crud-form-1" class="form-label">ระยะเวลา &nbsp;&nbsp;&nbsp;<button class="btn btn-primary btn-block" type="button" style="margin: auto;" onclick="add_period(false)"> เพิ่ม &nbsp;<i class="fa fa-plus-circle" aria-hidden="true"></i></button></label>
                                                        </div>
                                                        @endif
                                                        <div class="mt-3"> <label>การคิดราคา</label>
                                                            <div class="flex flex-col sm:flex-row mt-2">
                                                                <div class="form-check mr-2"> <input id="promotion_check" class="form-check-input" type="checkbox" name="promotion_check" value="1"> <label class="form-check-label">Promotion เหมิอนกันทุก Period</label> </div>
                                                                <div class="form-check mr-2 mt-2 sm:mt-0"> <input id="start_date_check" class="form-check-input" type="checkbox" name="start_date_check" value="1"> <label class="form-check-label">วันเริ่ม Promotion เหมิอนกันทุก Period</label> </div>
                                                                <div class="form-check mr-2 mt-2 sm:mt-0"> <input id="end_date_check" class="form-check-input" type="checkbox" name="end_date_check" value="1"> <label class="form-check-label">วันสิ้นสุด Promotion เหมิอนกันทุก Period</label> </div>
                                                                <div class="form-check mr-2 mt-2 sm:mt-0"> <input id="copy_price4" class="form-check-input" type="checkbox" name="copy_price4" value="1"> <label class="form-check-label">ราคาเด็กไม่มีเตียง เหมือนราคาพัก(2-3)</label> </div>
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
                                                        {{-- @if(count(@$period) > 0) --}}
                                                        <div class="overflow-x-auto">
                                                            <div id="data_period">
                                                                @php $p = 1; @endphp
                                                                @foreach($period as $pe)
                                                                <div id="del_period{{$p}}">
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
                                                                                <td>
                                                                                    {{$p}}
                                                                                </td>
                                                                                <td>
                                                                                    <input type="date" id="start_date{{$p}}" onchange="return cal_date({{$p}})" class="form-control start_date" name="period[{{$p}}][start_date][]" value="{{@$pe->start_date}}">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="date" id="end_date{{$p}}" onchange="return cal_date({{$p}})" class="form-control end_date" name="period[{{$p}}][end_date][]" value="{{@$pe->end_date}}">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" id="price1{{$p}}" onkeypress="return check_number(this)" class="form-control price1" name="period[{{$p}}][price1][]" value="{{@$pe->price1}}"><br>
                                                                                    <center><label class="special_price1">ลดราคา</label></center>
                                                                                    <input type="text" id="special_price1{{$p}}" onkeypress="return check_number(this)" class="form-control special_price1" name="period[{{$p}}][special_price1][]" value="{{@$pe->special_price1}}">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" id="price2{{$p}}" onkeypress="return check_number(this)" class="form-control price2" name="period[{{$p}}][price2][]" value="{{@$pe->price2}}"><br>
                                                                                    <center><label class="special_price2">ลดราคา</label></center>
                                                                                    <input type="text" id="special_price2{{$p}}" onkeypress="return check_number(this)" class="form-control special_price2" name="period[{{$p}}][special_price2][]" value="{{@$pe->special_price2}}">
                                                                                </td>
                                                                                <td colspan="2">
                                                                                    <input type="text" id="price3{{$p}}" onkeypress="return check_number(this)" class="form-control price3" name="period[{{$p}}][price3][]" value="{{@$pe->price3}}"><br>
                                                                                    <center><label class="special_price3">ลดราคา</label></center>
                                                                                    <input type="text" id="special_price3{{$p}}" onkeypress="return check_number(this)" class="form-control special_price3" name="period[{{$p}}][special_price3][]" value="{{@$pe->special_price3}}">
                                                                                </td>
                                                                                <td colspan="2">
                                                                                    <input type="text" id="price4{{$p}}" onkeypress="return check_number(this)" class="form-control price4" name="period[{{$p}}][price4][]" value="{{@$pe->price4}}"><br>
                                                                                    <center><label class="special_price4">ลดราคา</label></center>
                                                                                    <input type="text" id="special_price4{{$p}}" onkeypress="return check_number(this)" class="form-control special_price4" name="period[{{$p}}][special_price4][]" value="{{@$pe->special_price4}}">
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
                                                                                    <select id="promotion_id{{$p}}" class="form-control promotion_id" name="period[{{$p}}][promotion_id][]">
                                                                                        <option value="">กรุณาเลือก</option>
                                                                                        @foreach($promotion as $pro)
                                                                                            <option value="{{$pro->id}}" @if(@$pe->promotion_id == $pro->id) selected @endif>{{ $pro->promotion_name }}</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </td>
                                                                                <td>
                                                                                    <input type="date" id="pro_start_date{{$p}}" class="form-control pro_start_date" name="period[{{$p}}][pro_start_date][]" value="{{@$pe->pro_start_date}}">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="date" id="pro_end_date{{$p}}" class="form-control pro_end_date" name="period[{{$p}}][pro_end_date][]" value="{{@$pe->pro_end_date}}">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" id="day{{$p}}" onkeypress="return check_number(this)" class="form-control day" name="period[{{$p}}][day][]" value="{{@$pe->day}}">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" id="night{{$p}}" onkeypress="return check_number(this)" class="form-control night" name="period[{{$p}}][night][]" value="{{@$pe->night}}">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" id="group{{$p}}" onkeypress="return check_number(this)" class="form-control group" name="period[{{$p}}][group][]" value="{{@$pe->group}}">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" id="count{{$p}}" onkeypress="return check_number(this)" class="form-control count" name="period[{{$p}}][count][]" value="{{@$pe->count}}">
                                                                                </td>
                                                                                <input type="hidden" class="form-control" name="period[{{$p}}][period_id][]" value="{{@$pe->id}}">
                                                                                <input type="hidden" class="form-control" name="period[{{$p}}][status_display][]" value="{{@$pe->status_display}}">
                                                                                <input type="hidden" class="form-control" name="period[{{$p}}][status_period][]" value="{{@$pe->status_period}}">
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                    <br>
                                                                </div>
                                                                @php $p++ @endphp
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                        {{-- @endif --}}
                                                        @if($row->data_type == 1)
                                                        <div class="mt-2">
                                                            <input type="number" min="1" max="10" id="count_copy" class="form-control" style="width: 10%;">
                                                            <button type="button" class="btn btn-warning btn-block ml-1" onclick="copy_period()">Copy</button>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- ข้อมูล Period --}}

                                    <br>
                                    <div class="intro-y box mt-5 datatable-period" style="background-color: #F5F5F5;padding:15px;border-radius:5px;">
                                        <div class="flex flex-col sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                                            <div class="form-check form-switch w-full sm:w-auto sm:ml-0 mt-3 sm:mt-0">
                                                <b><label>เปลี่ยนการแสดงสินค้า</label></b>
                                                <select name="display" id="display" class="form-control ml-1" style="width: 180px">
                                                    <option value="" selected hidden>เลือกการแสดงสินค้า</option>
                                                    <option value="draft">Draft</option>
                                                    <option value="on">On</option>
                                                    <option value="off">Off</option>
                                                </select>
                                                <button type="button" class="btn btn-success btn-block ml-1" onclick="change_status_display()">ยืนยัน</button>
                                            </div>
                                            <div class="form-check form-switch w-full sm:w-auto sm:ml-2 mt-3 sm:mt-0">
                                                <b><label>เปลี่ยนสถานะวางขาย</label></b>
                                                <select name="status_sale" id="status_sale" class="form-control ml-1" style="width: 180px">
                                                    <option value="" selected hidden>เลือกสถานะวางขาย</option>
                                                    @foreach($period_status as $ps)
                                                        <option value="{{$ps->id}}">{{$ps->status_name}}</option>
                                                    @endforeach
                                                </select>
                                                <button type="button" class="btn btn-success btn-block ml-1" onclick="change_status_sale()">ยืนยัน</button>
                                            </div>
                                            {{-- <div class="form-check form-switch w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                                <button type="button" class="btn btn-danger btn-block ml-1" onclick="delete_period()">ลบ Period</button>
                                            </div> --}}
                                        </div>
                                    </div>
                                    <br>
                                    <div class="overflow-x-auto datatable-period" style="background-color: #F5F5F5;padding:15px;border-radius:5px;">
                                        <table id="datatable-period" class="table table-report">
                                            <thead>
                                                <tr>
                                                    <th><input type="checkbox" name="checkall" class="select-checkall" id="checkall" value=""></th>
                                                    <th><center>#</center></th>
                                                    <th><center>Period</center></th>
                                                    <th><center>จำนวนที่นั่ง</center></th>
                                                    <th><center>พัก(2-3)</center></th>
                                                    <th><center>พักเดี่ยว(บวก)</center></th>
                                                    <th><center>เด็กมีเตียง</center></th>
                                                    <th><center>สถานะแสดง</center></th>
                                                    <th><center>สถานะวางขาย</center></th>
                                                    <th><center>จัดการ</center></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if(count($period) > 0)
                                                    @foreach($period as $i => $pe)
                                                        @php
                                                            $period_status = \App\Models\Backend\TourPeriodStatusModel::find($pe->status_period);
                                                        @endphp
                                                        <tr>
                                                            <td><input type="checkbox" id="checkbox_period_{{@$pe->id}}" name="checkbox_period" value="{{@$pe->id}}" onclick="period_check('checkbox_period')"></td>
                                                            <td><center>{{$i+1}}</center></td>
                                                            <td><center>{{ date('d/m/Y',strtotime($pe->start_date)) }} - {{ date('d/m/Y',strtotime($pe->end_date))}}</center></td>
                                                            <td><center>{{ $pe->count}}</center></td>
                                                            <td><center>{{ $pe->price1}}</center></td>
                                                            <td><center>{{ $pe->price2}}</center></td>
                                                            <td><center>{{ $pe->price3}}</center></td>
                                                            <td>
                                                                <center>
                                                                    @if($pe->status_display == "draft")
                                                                        แบบร่าง
                                                                    @else
                                                                        <div class="form-switch w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                                                            <input id="status_change_{{$pe->id}}" data-id="{{$pe->id}}" onclick="status({{$pe->id}});" class="show-code form-check-input mr-0 ml-3" type="checkbox" @if($pe->status_display == "on") checked @endif>
                                                                        </div>
                                                                    @endif
                                                                </center>
                                                            </td>
                                                            <td>
                                                                <center>
                                                                    @if($period_status)
                                                                        <p>{{$period_status->status_name}}</p>
                                                                    @else
                                                                        <p style="color:red;">ไม่มีสถานะ</p>
                                                                    @endif
                                                                </center>
                                                            </td>
                                                            <td><center>
                                                                <a href="{{$segment}}/{{$folder}}/edit-period/{{$pe->id}}" class="mr-3 mb-2 btn btn-sm btn-info" title="Edit"><i class="fa fa-edit w-4 h-4 mr-1"></i> แก้ไข </a>                                                 
                                                                <a href="javascript:" class="mr-3 mb-2 btn btn-sm btn-danger" onclick="deleteItemPeriod({{$pe->id}})" title="Delete"><i class="far fa-trash-alt w-4 h-4 mr-1"></i> ลบ</a>
                                                            </center></td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr class="text-center">
                                                        <td colspan="10">--ไม่มีข้อมูล--</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                            <input type="hidden" id="data_period_id" name="data_period_id" value="">
                                        </table>
                                    </div>

                                </div>

                                <div class="text-center mt-10">
                                    <button type="submit" class="btn btn-primary w-24">บันทึกข้อมูล</button>
                                    <a class="btn btn-outline-danger w-24 mr-1" href="{{ url("$segment/$folder") }}" >ยกเลิก</a>
                                </div>
                            </div>
                            <!-- END: Form Layout -->
                        </div>

                        {{-- <input type="hidden" value="{{url('call-country')}}" id="urlCountry">
                        <input type="hidden" value="{{url('call-city')}}" id="urlCity">
                        <input type="hidden" value="{{url('call-district')}}" id="urlDistrict"> --}}
                    </div>
                </div>
            </form>
        </div>

        <!-- BEGIN: JS Assets-->
        @include("backend.layout.script")

        @include("backend.layout.tag") 
        <script>
            <?php echo "var data = $data_tag;"; 
                  echo "var arr = $t_select;"; 
            ?>
            SaveTag();
        </script>
        <?php 
            echo '<script>';
            if(count($period)){
                echo 'var last_period = '. json_encode($period[count($period) - 1]) .';';
            }else{
                echo 'var last_period = false;';
            }
            echo 'var period_status = '. json_encode($period_status) .';';
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
            
            var tour_id = "{{$id}}";
            var fullUrl = "{{url('webpanel/tour')}}";

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

            function pdf_delete(id){
                Swal.fire({
                    title: "ลบข้อมูล",
                    text: "คุณต้องการลบข้อมูลใช่หรือไม่?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return fetch(fullUrl + '/destroy-pdf/' + id)
                            .then(response => response.json())
                            .then(data => location.reload())
                            .catch(error => {
                                Swal.showValidationMessage(`Request failed: ${error}`)
                            })
                    }
                });
            }

            function word_delete(id){
                Swal.fire({
                    title: "ลบข้อมูล",
                    text: "คุณต้องการลบข้อมูลใช่หรือไม่?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return fetch(fullUrl + '/destroy-word/' + id)
                            .then(response => response.json())
                            .then(data => location.reload())
                            .catch(error => {
                                Swal.showValidationMessage(`Request failed: ${error}`)
                            })
                    }
                });
            }

            $("#group_id").change(function(){
                var group_id = $(this).val();
                if(group_id == 3){
                    $(".wholesale").attr("hidden",false);
                }else{
                    $(".wholesale").attr("hidden",true);
                }
            });

            $("#checkall").click(function(){
                if($(this).is(':checked')){
                    $('input[name="checkbox_period"]').prop('checked', true);

                    var checkboxes = document.querySelectorAll('input[name="checkbox_period"]:checked'), values = [];
                    Array.prototype.forEach.call(checkboxes, function(el) {
                        values.push(el.value);
                    });
                    $('#data_period_id').val(values);
                }else{
                    $('input[name="checkbox_period"]').prop('checked', false); 
                    $('#data_period_id').val("");
                }
            }); 

            function period_check(checkboxName) {
                var checkboxes = document.querySelectorAll('input[name="' + checkboxName + '"]:checked'), values = [];
                Array.prototype.forEach.call(checkboxes, function(el) {
                    values.push(el.value);
                });
                $('#data_period_id').val(values);
            }

            function change_status_display() {
                var data_period_id = $('#data_period_id').val();
                var display = $('#display').val();

                if(data_period_id != "" && display != "")
                {
                    Swal.fire({
                        title: 'ตรวจสอบข้อมูลให้ถูกต้อง ?',
                        text: "คุณต้องการเปลี่ยนสถานะใช่หรือไม่?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        showLoaderOnConfirm: true,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                type: 'get',
                                url: fullUrl + '/change-status-display',
                                dataType: 'json',
                                data: {
                                    tour_id: tour_id,
                                    data_period_id: data_period_id,
                                    display: display,
                                },
                                success: function(data) {
                                    if(data.result == "success"){
                                        Swal.fire({
                                            title: 'สำเร็จ',
                                            text: "เปลี่ยนสถานะการแสดงสินค้าเรียบร้อยแล้ว",
                                            icon: 'success',
                                            showCancelButton: false,
                                            confirmButtonColor: '#3085d6',
                                            showLoaderOnConfirm: true,
                                            confirmButtonText: 'ปิด',
                                        }).then((result) => {
                                            location.reload();
                                        });
                                    }else{
                                        Swal.fire({
                                            title: 'เกิดข้อผิดพลาด!!',
                                            text: "กรุณาทำรายการใหม่อีกครั้ง",
                                            icon: 'error',
                                            showCancelButton: false,
                                            confirmButtonColor: "#3085d6",
                                            showLoaderOnConfirm: true,
                                            confirmButtonText: 'ปิด',
                                        });
                                    }

                                }
                            });
                        }
                    })
                }else if(data_period_id == ""){
                    Swal.fire({
                        title: 'ตรวจสอบข้อมูลให้ถูกต้อง ?',
                        text: "กรุณาเลือกรายการที่ต้องการจะเปลี่ยนสถานะ",
                        icon: 'error',
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        showLoaderOnConfirm: true,
                    }).then((result) => {
                        return false;
                    })
                }else if(display == ""){
                    Swal.fire({
                        title: 'ตรวจสอบข้อมูลให้ถูกต้อง ?',
                        text: "กรุณาเลือกสถานะเปลี่ยนการแสดงสินค้า",
                        icon: 'error',
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        showLoaderOnConfirm: true,
                    }).then((result) => {
                        return false;
                    })
                }
            }

            function change_status_sale() {
                var data_period_id = $('#data_period_id').val();
                var status_sale = $('#status_sale').val();

                if(data_period_id != "" && status_sale != "")
                {
                    Swal.fire({
                        title: 'ตรวจสอบข้อมูลให้ถูกต้อง ?',
                        text: "คุณต้องการเปลี่ยนสถานะใช่หรือไม่?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        showLoaderOnConfirm: true,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                type: 'get',
                                url: fullUrl + '/change-status-period',
                                dataType: 'json',
                                data: {
                                    tour_id: tour_id,
                                    data_period_id: data_period_id,
                                    status: status_sale,
                                },
                                success: function(data) {
                                    if(data.result == "success"){
                                        Swal.fire({
                                            title: 'สำเร็จ',
                                            text: "เปลี่ยนสถานะวางขายเรียบร้อยแล้ว",
                                            icon: 'success',
                                            showCancelButton: false,
                                            confirmButtonColor: '#3085d6',
                                            showLoaderOnConfirm: true,
                                            confirmButtonText: 'ปิด',
                                        }).then((result) => {
                                            location.reload();
                                        });
                                    }else{
                                        Swal.fire({
                                            title: 'เกิดข้อผิดพลาด!!',
                                            text: "กรุณาทำรายการใหม่อีกครั้ง",
                                            icon: 'error',
                                            showCancelButton: false,
                                            confirmButtonColor: "#3085d6",
                                            showLoaderOnConfirm: true,
                                            confirmButtonText: 'ปิด',
                                        });
                                    }

                                }
                            });
                        }
                    })
                }else if(data_period_id == ""){
                    Swal.fire({
                        title: 'ตรวจสอบข้อมูลให้ถูกต้อง ?',
                        text: "กรุณาเลือกรายการที่ต้องการจะเปลี่ยนสถานะ",
                        icon: 'error',
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        showLoaderOnConfirm: true,
                    }).then((result) => {
                        return false;
                    })
                }else if(status_sale == ""){
                    Swal.fire({
                        title: 'ตรวจสอบข้อมูลให้ถูกต้อง ?',
                        text: "กรุณาเลือกสถานะเปลี่ยนสถานะวางขาย",
                        icon: 'error',
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        showLoaderOnConfirm: true,
                    }).then((result) => {
                        return false;
                    })
                }
            }

            var count_detail = {{@$d}};
            var count_detail_sub = {{@$ds}};
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
            
            var count_period = {{@$p}};
            function add_period(t){ 
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

                if(t && last_period){
                    // var ps = '';
                    // for(pst in period_status){
                    //     let select_ps = period_status[pst].id == last_period.status_period ? 'selected':'';
                    //     ps = ps + '<option value="'+period_status[pst].id+'"'+select_ps+'>'+period_status[pst].status_name+'</option>'
                    // }

                    var op = '';
                    for(opt in promotion){
                        let select_op = promotion[opt].id == last_period.promotion_id ? 'selected':'';
                        op = op + '<option value="'+promotion[opt].id+'"'+select_op+'>'+promotion[opt].promotion_name+'</option>'
                    }

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
                    '<td><input type="date" id="start_date'+count_period+'" onchange="return cal_date('+count_period+')" class="form-control start_date" name="period['+count_period+'][start_date][]" value='+last_period.start_date+'></td>'+
                    '<td><input type="date" id="end_date'+count_period+'" onchange="return cal_date('+count_period+')" class="form-control count_date" name="period['+count_period+'][end_date][]" value='+last_period.end_date+'></td>'+
                    '<td><input type="text" id="price1'+count_period+'" onkeypress="return check_number(this)" class="form-control price1" name="period['+count_period+'][price1][]" value='+last_period.price1*1+' '+price1_check+'><br><center><label class="special_price1" '+price1_check+'>ลดราคา</label></center><input type="text" id="special_price1'+count_period+'" onkeypress="return check_number(this)" class="form-control special_price1" name="period['+count_period+'][special_price1][]" value='+last_period.special_price1*1+' '+price1_check+'></td>'+
                    '<td><input type="text" id="price2'+count_period+'" onkeypress="return check_number(this)" class="form-control price2" name="period['+count_period+'][price2][]" value='+last_period.price2*1+' '+price2_check+'><br><center><label class="special_price2" '+price2_check+'>ลดราคา</label></center><input type="text" id="special_price2'+count_period+'" onkeypress="return check_number(this)" class="form-control special_price2" name="period['+count_period+'][special_price2][]" value='+last_period.special_price2*1+' '+price2_check+'></td>'+
                    '<td colspan="2"><input type="text" id="price3'+count_period+'" onkeypress="return check_number(this)" class="form-control price3" name="period['+count_period+'][price3][]" value='+last_period.price3*1+' '+copy_price3+' '+price3_check+'><br><center><label class="special_price3" '+copy_price3+' '+price3_check+'>ลดราคา</label></center><input type="text" id="special_price3'+count_period+'" onkeypress="return check_number(this)" class="form-control special_price3" name="period['+count_period+'][special_price3][]" value='+last_period.special_price3*1+' '+copy_price3+' '+price3_check+'></td>'+
                    '<td colspan="2"><input type="text" id="price4'+count_period+'" onkeypress="return check_number(this)" class="form-control price4" name="period['+count_period+'][price4][]" value='+last_period.price4*1+' '+copy_price4+' '+price4_check+'><br><center><label class="special_price4" '+copy_price4+' '+price4_check+'>ลดราคา</label></center><input type="text" id="special_price4'+count_period+'" onkeypress="return check_number(this)" class="form-control special_price4" name="period['+count_period+'][special_price4][]" value='+last_period.special_price4*1+' '+copy_price4+' '+price4_check+'></td>'+
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
                    '<td colspan="2"><select id="promotion_id'+count_period+'" class="form-control promotion_id" name="period['+count_period+'][promotion_id][]" '+promotion_check+'>'+
                    '<option value="">กรุณาเลือก</option>'+op+'</select></td>'+
                    '<td><input type="date" id="pro_start_date'+count_period+'" class="form-control pro_start_date" name="period['+count_period+'][pro_start_date][]" value='+last_period.pro_start_date+' '+start_date_check+'></td>'+
                    '<td><input type="date" id="pro_end_date'+count_period+'" class="form-control pro_end_date" name="period['+count_period+'][pro_end_date][]" value='+last_period.pro_end_date+' '+end_date_check+'></td>'+
                    '<td><input type="text" id="day'+count_period+'" onkeypress="return check_number(this)" class="form-control day" name="period['+count_period+'][day][]" value='+last_period.day*1+'></td>'+
                    '<td><input type="text" id="night'+count_period+'" onkeypress="return check_number(this)" class="form-control night" name="period['+count_period+'][night][]" value='+last_period.night*1+'></td>'+
                    '<td><input type="text" id="group'+count_period+'" onkeypress="return check_number(this)" class="form-control group" name="period['+count_period+'][group][]" value='+last_period.group*1+' '+group_check+'></td>'+
                    '<td><input type="text" id="count'+count_period+'" onkeypress="return check_number(this)" class="form-control count" name="period['+count_period+'][count][]" value='+last_period.count*1+' '+count_check+'></td>'+
                    '<input type="hidden" class="form-control" name="period['+count_period+'][period_id][]" value="">'+
                    '<input type="hidden" class="form-control" name="period['+count_period+'][status_display][]" value='+last_period.status_display+'>'+
                    '<input type="hidden" class="form-control" name="period['+count_period+'][status_period][]" value='+last_period.status_period+'>'+
                    '</tr>'+'</body>'+
                    '</table><br></div>';   
                }else{
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
                    '<td><input type="text" id="price1'+count_period+'" onkeypress="return check_number(this)" class="form-control price1" name="period['+count_period+'][price1][]" '+price1_check+'><br><center><label class="special_price1" '+price1_check+'>ลดราคา</label></center><input type="text" id="special_price1'+count_period+'" onkeypress="return check_number(this)" class="form-control special_price1" name="period['+count_period+'][special_price1][]" '+price1_check+'></td>'+
                    '<td><input type="text" id="price2'+count_period+'" onkeypress="return check_number(this)" class="form-control price2" name="period['+count_period+'][price2][]" '+price2_check+'><br><center><label class="special_price2" '+price2_check+'>ลดราคา</label></center><input type="text" id="special_price2'+count_period+'" onkeypress="return check_number(this)" class="form-control special_price2" name="period['+count_period+'][special_price2][]" '+price2_check+'></td>'+
                    '<td colspan="2"><input type="text" id="price3'+count_period+'" onkeypress="return check_number(this)" class="form-control price3" name="period['+count_period+'][price3][]" '+copy_price3+' '+price3_check+'><br><center><label class="special_price3" '+copy_price3+' '+price3_check+'>ลดราคา</label></center><input type="text" id="special_price3'+count_period+'" onkeypress="return check_number(this)" class="form-control special_price3" name="period['+count_period+'][special_price3][]" '+copy_price3+' '+price3_check+'></td>'+
                    '<td colspan="2"><input type="text" id="price4'+count_period+'" onkeypress="return check_number(this)" class="form-control price4" name="period['+count_period+'][price4][]" '+copy_price4+' '+price4_check+'><br><center><label class="special_price4" '+copy_price4+' '+price4_check+'>ลดราคา</label></center><input type="text" id="special_price4'+count_period+'" onkeypress="return check_number(this)" class="form-control special_price4" name="period['+count_period+'][special_price4][]" '+copy_price4+' '+price4_check+'></td>'+
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
                    '<td colspan="2"><select id="promotion_id'+count_period+'" class="form-control promotion_id" name="period['+count_period+'][promotion_id][]" '+promotion_check+'>'+
                    '<option value="">กรุณาเลือก</option>'+
                    @foreach($promotion as $pro)
                    '<option value="{{$pro->id}}">{{$pro->promotion_name}}</option>'+
                    @endforeach
                    '</select></td>'+
                    '<td><input type="date" id="pro_start_date'+count_period+'" class="form-control pro_start_date" name="period['+count_period+'][pro_start_date][]"></td>'+
                    '<td><input type="date" id="pro_end_date'+count_period+'" class="form-control pro_end_date" name="period['+count_period+'][pro_end_date][]"></td>'+
                    '<td><input type="text" id="day'+count_period+'" onkeypress="return check_number(this)" class="form-control day" name="period['+count_period+'][day][]"></td>'+
                    '<td><input type="text" id="night'+count_period+'" onkeypress="return check_number(this)" class="form-control night" name="period['+count_period+'][night][]"></td>'+
                    '<td><input type="text" id="group'+count_period+'" onkeypress="return check_number(this)" class="form-control group" name="period['+count_period+'][group][]" '+group_check+'></td>'+
                    '<td><input type="text" id="count'+count_period+'" onkeypress="return check_number(this)" class="form-control count" name="period['+count_period+'][count][]" '+count_check+'></td>'+
                    '<input type="hidden" class="form-control" name="period['+count_period+'][period_id][]" value="">'+
                    '<input type="hidden" class="form-control" name="period['+count_period+'][status_display][]" value="draft">'+
                    '<input type="hidden" class="form-control" name="period['+count_period+'][status_period][]" value="1">'+
                    '</tr>'+'</body>'+
                    '</table><br></div>';   
                }
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

            function cal_date(d) {
                var start = $("#start_date"+d).val(); // วันที่เริ่มต้น
                var end = $("#end_date"+d).val(); // วันที่สิ้นสุด
                var c_day = $("#day"+d).val(); // จำนวนวัน

                if(c_day){ // คำนวณวันสิ้นสุดอัตโนมัติ ถ้ามีค่าช่อง Day
                    if (start) {
                        var startDate = new Date(start);
                        var endDate = new Date(startDate.getTime() + (c_day - 1) * 24 * 60 * 60 * 1000); // คำนวณวันที่สิ้นสุดจากวันที่เริ่มและจำนวนวัน

                        // กำหนดค่าในช่องวันที่สิ้นสุด
                        $("#end_date"+d).val(endDate.toISOString().slice(0, 10));
                    } else {
                        $("#end_date"+d).val("");
                    }
                }else{
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
            }

            $("#button_period").on('click', function () {
                var button = $('#button_period').attr('aria-expanded');
                if(button == "true"){
                    $(".datatable-period").attr("hidden",false);
                }else{
                    $(".datatable-period").attr("hidden",true);
                }
            });

            function deleteItemPeriod(ids) {
                const id = [ids];
                if (id.length > 0) {
                    destroyPeriod(id)
                }
            }

            function destroyPeriod(id) {
                Swal.fire({
                    title: "ลบข้อมูล",
                    text: "คุณต้องการลบข้อมูลใช่หรือไม่?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return fetch(fullUrl + '/destroy-period/' + id)
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
                    url: fullUrl + '/status-edit/' + id,
                    success: function(res) {
                        if (res == false) {
                            $(this).prop('checked', false)
                        }
                    }
                });
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
                var group_id = $('#group_id').val();
                var category = $('#category').val();
                var name = $('#name').val();
                if (group_id == "" || category == "" || name == "") {
                    toastr.error('กรุณากรอกข้อมูลให้ครบถ้วนก่อนบันทึกรายการ');
                    return false;
                }
            }
            
        </script>
        <!-- END: JS Assets-->
</body>

</html>
