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
                    <div class="col-span-12 lg:col-span-6 2xl:col-span-12">
                        <div class="intro-y col-span-12 lg:col-span-6">
                            <!-- BEGIN: Form Layout -->
                            <div class="intro-y box p-5 place-content-center">
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label"><span class="text-danger">*</span>ประเทศ</label>
                                        <select name="country_id" id="country_id" class="form-control select2" required>
                                            <option value="" selected hidden>กรุณาเลือกข้อมูล</option>
                                            @foreach ($country as $c)
                                                <option value="{{$c->id}}">{{$c->country_name_th}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label"><span class="text-danger">*</span>ชื่อภาษาไทย</label>
                                        <input type="text" id="city_name_th" name="city_name_th" class="form-control" required>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label"><span class="text-danger">*</span>ชื่อภาษาอังกฤษ</label>
                                        <input type="text" id="city_name_en" name="city_name_en" class="form-control" required>
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
            function check_add() {
                var country_id = $('#country_id').val();
                var city_name_th = $('#city_name_th').val();
                var city_name_en = $('#city_name_en').val();
                if (country_id == "" || city_name_th == "" || city_name_en == "") {
                    toastr.error('กรุณากรอกข้อมูลให้ครบถ้วนก่อนบันทึกรายการ');
                    return false;
                }
            }
        </script>
        <!-- END: JS Assets-->
</body>

</html>
