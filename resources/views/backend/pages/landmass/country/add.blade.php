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
            <h2 class="intro-y text-lg font-medium mt-10">ฟอร์มข้อมูล</h2>

            <form id="menuForm" method="post" action="" enctype="multipart/form-data" onsubmit="return check_add();">
                @csrf
                <div class="grid grid-cols-12 gap-6 mt-5">
                    <div class="col-span-12 lg:col-span-12 2xl:col-span-12">
                        <div class="intro-y col-span-12 lg:col-span-12">
                            <!-- BEGIN: Form Layout -->
                            <div class="intro-y box p-5 place-content-center">
                                
                                {{-- image --}}
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <h6 class="mb-3"><span class="text-danger">*</span>รูปภาพแบนเนอร์<span class="text-danger"> ขนาด 1920*536</span></h6>
                                        <img src="@if(@$row->banner == null) {{url("noimage.jpg")}} @else {{asset($row->banner)}} @endif" class="img-thumbnail" id="preview">
                                    </div>
                                    <div class="col-span-12 lg:col-span-12">
                                        <small class="help-block">* รองรับไฟล์ <strong class="text-danger">(jpg, jpeg, png, webp)</strong> เท่านั้น</small>
                                        <div class="col-span-12 lg:col-span-12">
                                            <input type="file" class="custom-file-input block w-80 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                name="banner" id="banner" accept="image/png, image/jpeg, image/jpg, image/webp">
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <h6 class="mb-3"><span class="text-danger">*</span>รูปภาพธง <span class="text-danger"> ขนาด 27*20</span></h6>
                                        <img src="@if(@$row->img_icon == null) {{url("noimage.jpg")}} @else {{asset($row->img_icon)}} @endif" class="img-thumbnail" id="preview_icon" style="width:27px; height:20px;">
                                    </div>
                                    <div class="col-span-12 lg:col-span-12">
                                        <small class="help-block">* รองรับไฟล์ <strong class="text-danger">(jpg, jpeg, png,svg)</strong> เท่านั้น</small>
                                        <div class="col-span-6 lg:col-span-6">
                                            <input type="file" class="custom-file-input block w-80 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                name="img_icon" id="img_icon" accept="image/png, image/jpeg, image/jpg, image/svg" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <h6 class="mb-3"><span class="text-danger">*</span>รูปภาพปก (ประเทศยอดนิยม) <span class="text-danger"> ขนาด 397*561</span></h6>
                                        <img src="@if(@$row->img_banner == null) {{url("noimage.jpg")}} @else {{asset($row->img_banner)}} @endif" class="img-thumbnail" id="preview_banner" style="width:397px; height:561px;">
                                    </div>
                                    <div class="col-span-12 lg:col-span-12">
                                        <small class="help-block">* รองรับไฟล์ <strong class="text-danger">(jpg, jpeg, png)</strong> เท่านั้น</small>
                                        <div class="col-span-6 lg:col-span-6">
                                            <input type="file" class="custom-file-input block w-80 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                name="img_banner" id="img_banner" accept="image/png, image/jpeg, image/jpg" required>
                                        </div>
                                    </div>
                                </div>
                                {{-- image --}}

                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label"><span class="text-danger">*</span>ทวีป</label>
                                        <select name="landmass_id" id="landmass_id" class="form-control select2" required>
                                            <option value="" selected hidden>กรุณาเลือกข้อมูล</option>
                                            @foreach ($landmass as $land)
                                                <option value="{{$land->id}}">{{$land->landmass_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label"><span class="text-danger">*</span>ชื่อภาษาไทย</label>
                                        <input type="text" id="country_name_th" name="country_name_th" class="form-control" required>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label"><span class="text-danger">*</span>ชื่อภาษาอังกฤษ</label>
                                        <input type="text" id="country_name_en" name="country_name_en" class="form-control" required>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label">รหัสประเทศ (ISO2)</label>
                                        <input type="text" id="iso2" name="iso2" class="form-control">
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label">รหัสประเทศ (ISO3)</label>
                                        <input type="text" id="iso3" name="iso3" class="form-control">
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label">คำอธิบาย(หน้าหลัก)</label>
                                        <textarea  name="description"  class="tiny"></textarea>
                                        {{-- <input type="text"  name="description" class="form-control" > --}}
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

        <!-- BEGIN: JS Assets-->
        @include("backend.layout.script")
        
        <script>
            $("#img_icon").on('change', function () {
                var $this = $(this)
                const input = $this[0];
                const fileName = $this.val().split("\\").pop();
                $this.siblings(".custom-file-label").addClass("selected").html(fileName)
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $('#preview_icon').attr('src', e.target.result).fadeIn('fast');
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            });

            $("#img_banner").on('change', function () {
                var $this = $(this)
                const input = $this[0];
                const fileName = $this.val().split("\\").pop();
                $this.siblings(".custom-file-label").addClass("selected").html(fileName)
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $('#preview_banner').attr('src', e.target.result).fadeIn('fast');
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            });

            $("#banner").on('change', function () {
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
                var img = $('#img').val();
                var landmass_id = $('#landmass_id').val();
                var country_name_th = $('#country_name_th').val();
                var country_name_en = $('#country_name_en').val();
                if (img == "" || landmass_id == "" || country_name_th == "" || country_name_en == "") {
                    toastr.error('กรุณากรอกข้อมูลให้ครบถ้วนก่อนบันทึกรายการ');
                    return false;
                }
            }
        </script>
        <!-- END: JS Assets-->
</body>

</html>
