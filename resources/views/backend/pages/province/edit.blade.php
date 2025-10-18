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
                    <div class="col-span-12 lg:col-span-12 2xl:col-span-8">
                        <div class="intro-y col-span-12 lg:col-span-12">
                            <div class="intro-y box p-5 place-content-center">
                            <!-- BEGIN: Form Layout -->

                            {{-- image --}}
                            <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                <div class="col-span-12 lg:col-span-6">
                                    <h6 class="mb-3"><span class="text-danger">*</span>รูปภาพแบนเนอร์<span class="text-danger"> ขนาด 1920*400</span></h6>
                                    <img src="@if(@$row->banner == null) {{url("noimage.jpg")}} @else {{asset($row->banner)}} @endif" class="img-thumbnail" id="preview">
                                </div>
                                <div class="col-span-12 lg:col-span-12">
                                    <small class="help-block">* รองรับไฟล์ <strong class="text-danger">(jpg, jpeg, png, webp)</strong> เท่านั้น</small>
                                    <div class="col-span-12 lg:col-span-12">
                                        <input type="file" class="custom-file-input block w-80 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                            name="banner" id="banner" accept="image/png, image/jpeg, image/jpg, image/webp">
                                    </div>
                                </div>
                                <div class="col-span-12 lg:col-span-6">
                                    <label for="crud-form-1" class="form-label">คำอธิบาย <span class="text-danger">(รูปภาพแบนเนอร์)</span></label>
                                    <textarea  name="banner_detail"  class="tiny">{{ $row->banner_detail }}</textarea>
                                </div>
                            </div>
                            {{-- image --}}

                            
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <label for="crud-form-1" class="form-label">ชื่อภาษาไทย</label>
                                        <input type="text" id="name_th" name="name_th" class="form-control" value="{{$row->name_th}}" required>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <label for="crud-form-1" class="form-label">ชื่อภาษาอังกฤษ</label>
                                        <input type="text" id="name_en" name="name_en" class="form-control" value="{{$row->name_en}}" required>
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
                var name_th = $('#name_th').val();
                var name_en = $('#name_en').val();
                if (name_th == "" || name_en == "") {
                    toastr.error('กรุณากรอกข้อมูลให้ครบถ้วนก่อนบันทึกรายการ');
                    return false;
                }
            }
        </script>
        <!-- END: JS Assets-->
</body>

</html>
