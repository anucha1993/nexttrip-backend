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

            <form id="menuForm" method="post" action="" enctype="multipart/form-data" >
                @csrf
                <div class="grid grid-cols-12 gap-6 mt-5">
                    <div class="col-span-12 lg:col-span-6 2xl:col-span-12">
                        <div class="intro-y col-span-12 lg:col-span-6">
                            <!-- BEGIN: Form Layout -->
                            <div class="intro-y box p-5 place-content-center">
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-8">
                                        <label for="crud-form-1" class="form-label">แผนกขายหน้าร้าน</label>
                                        <input type="text" name="phone_front" class="form-control" value="{{$row->phone_front}}">
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-8">
                                        <label for="crud-form-1" class="form-label">แผนกกรุ๊ปเหมา</label>
                                        <input type="text" name="phone_group" class="form-control" value="{{$row->phone_group}}">
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-8">
                                        <label for="crud-form-1" class="form-label">ร้องเรียนปัญหา</label>
                                        <input type="text" name="phone_problem" class="form-control" value="{{$row->phone_problem}}">
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-8">
                                        <label for="crud-form-1" class="form-label">ที่อยู่</label>
                                        <textarea name="address" class="tiny">{{$row->address}}</textarea>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-4">
                                        <label for="crud-form-1" class="form-label">วัน-เวลาทำการ</label>
                                        <input type="text" name="time" class="form-control" value="{{$row->time}}">
                                    </div>
                                    <div class="col-span-12 lg:col-span-3">
                                        <label for="crud-form-1" class="form-label">เวลาให้บริการ <span class="text-danger">(แสดงใน popup)</span></label>
                                        <input type="text" name="service_time" class="form-control" value="{{$row->service_time}}">
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-8">
                                        <label for="crud-form-1" class="form-label">ติดต่อสำนักงาน</label>
                                        <input type="text" name="office" class="form-control" value="{{$row->office}}">
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-8">
                                        <label for="crud-form-1" class="form-label">สายด่วน</label>
                                        <input type="text" name="hotline" class="form-control" value="{{$row->hotline}}">
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-8">
                                        <label for="crud-form-1" class="form-label">แฟกซ์</label>
                                        <input type="text" name="fax" class="form-control" value="{{$row->fax}}">
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-8">
                                        <label for="crud-form-1" class="form-label">เมล</label>
                                        <input type="text" name="mail" class="form-control" value="{{$row->mail}}">
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-4">
                                        <label for="crud-form-1" class="form-label">ไอดีไลน์</label>
                                        <input type="text" name="line_id" class="form-control" value="{{$row->line_id}}">
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-4">
                                        <h6 class="mb-3"><span class="text-danger">*</span>คิวอาร์โค้ดไลน์</h6>
                                        <img src="@if(@$row->qr_code == null) {{url("noimage.jpg")}} @else {{asset($row->qr_code)}} @endif"
                                            class="img-thumbnail" id="preview">
                                    </div>
                                    <div class="col-span-12 lg:col-span-12">
                                        <small class="help-block">* รองรับไฟล์ <strong class="text-danger">(jpg, jpeg,
                                                png)</strong> เท่านั้น</small>
                                        <div class="col-span-6 lg:col-span-6">
                                            <input type="file"
                                                class="custom-file-input block w-80 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                name="image" id="image">
                                        </div>
                                    </div>
                                </div>
                                <br>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <label for="crud-form-1" class="form-label">เฟซบุ๊ก</label>
                                        <input type="text"  name="link_fb" class="form-control" value="{{$row->link_fb}}">
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <label for="crud-form-1" class="form-label">อินสตาแกรม</label>
                                        <input type="text"  name="link_ig" class="form-control" value="{{$row->link_ig}}">
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <label for="crud-form-1" class="form-label">ยูทูบ</label>
                                        <input type="text"  name="link_yt" class="form-control" value="{{$row->link_yt}}">
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <label for="crud-form-1" class="form-label">ติ๊กต็อก</label>
                                        <input type="text"  name="link_tiktok" class="form-control" value="{{$row->link_tiktok}}">
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <label for="crud-form-1" class="form-label">กูเกิลแมพ</label>
                                        <textarea name="google_map" rows="5" class="form-control">{{$row->google_map}}</textarea>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-4">
                                        <h6 class="mb-3">รูปแผนที่</h6>
                                        <img src="@if(@$row->map == null) {{url("noimage.jpg")}} @else {{asset($row->map)}} @endif"
                                            class="img-thumbnail" id="preview-map">
                                    </div>
                                    <div class="col-span-12 lg:col-span-12">
                                        <small class="help-block">* รองรับไฟล์ <strong class="text-danger">(jpg, jpeg,
                                                png)</strong> เท่านั้น</small>
                                        <div class="col-span-6 lg:col-span-6">
                                            <input type="file"
                                                class="custom-file-input block w-80 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                name="map" id="image-map">
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center mt-5">
                                    <button type="submit" class="btn btn-primary w-24">บันทึกข้อมูล</button>
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
        <!-- END: JS Assets-->
        <script>
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
            $("#image-map").on('change', function () {
                var $this = $(this)
                const input = $this[0];
                const fileName = $this.val().split("\\").pop();
                $this.siblings(".custom-file-label").addClass("selected").html(fileName)
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $('#preview-map').attr('src', e.target.result).fadeIn('fast');
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            });
        </script>
</body>

</html>
