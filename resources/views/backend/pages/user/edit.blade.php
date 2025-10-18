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


            
            <div class="content">
                <!-- BEGIN: Top Bar -->
                @include("backend.layout.topbar")
                <!-- END: Top Bar -->


                <!-- BEGIN: Content -->
                <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
                    <h2 class="text-lg font-medium mr-auto">
                        ฟอร์มข้อมูล
                    </h2>
                </div>

                <form id="menuForm" method="post" action="" onsubmit="return check_add();">
                @csrf
                    <div class="grid grid-cols-12 gap-6 mt-5">
                        <div class="intro-y col-span-12 lg:col-span-6">
                            <!-- BEGIN: Form Layout -->
                            <div class="intro-y box p-5">
                                
                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-6 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label">ระดับ</label>
                                        <select class="form-select" name="role" id="role">
                                            <option value="">กรุณาเลือก</option>
                                            @if(@$roles)
                                            @foreach(@$roles as $role)
                                            <option value="{{$role->id}}" @if($row->role == $role->id) selected @endif>{{$role->name}}</option>
                                            @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-span-6 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label">สถานะ</label>
                                        <select class="form-control" name="status_check" id="status_check">
                                            <option value="" hidden>กรุณาเลือก</option>
                                            <option value="pending" @if($row->status == "pending") selected @endif>รอดำเนินการ</option>
                                            <option value="inactive" @if($row->status == "inactive") selected @endif>ปิดการใช้งาน</option>
                                            <option value="active" @if($row->status == "active") selected @endif>ใช้งาน</option>
                                            <option value="banned" @if($row->status == "banned") selected @endif>ระงับการใช้งาน</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-6 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label">ชื่อ</label>
                                        <input class="form-control" id="name" type="text" name="name" value="{{$row->name}}" placeholder="ชื่อ-นามสกุล" autocomplete="off">
                                    </div>
                                    <div class="col-span-6 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label">อีเมล</label>
                                        <input class="form-control" id="username" type="email" name="username" value="{{$row->email}}" placeholder="อีเมล" autocomplete="off">
                                    </div>
									<div class="col-span-6 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label">เบอร์ติดต่อ</label>
                                        <input class="form-control" id="phone" type="text" name="phone" placeholder="+66" autocomplete="off" value="{{$row->phone}}">
                                    </div>
                                </div>

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <input type="checkbox" id="resetpassword" name="resetpassword"> เปลี่ยนรหัสผ่าน
                                    </div>
                                </div>

                            
                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-6 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label">รหัสผ่าน</label>
                                        <div class="input-group col-mb-6">
                                            <input type="password" id="password" class="form-control" name="password" placeholder="รหัสผ่าน" autocomplete="off" disabled>
                                            <div class="input-group-append">
                                                <div class="input-group-text">
                                                    <span class="card-link show_pass"><i class="far fa-eye" data-id="password"></i></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-span-6 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label">ยืนยันรหัสผ่าน</label>
                                        <div class="input-group col-mb-6">
                                            <input type="password" id="confirm_password" class="form-control" name="confirm_password" placeholder="ยืนยันรหัสผ่าน" autocomplete="off" disabled>
                                            <div class="input-group-append">
                                                <div class="input-group-text">
                                                    <span class="card-link show_pass_confirm"><i class="far fa-eye" data-id="confirm_password"></i></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            
                                <div class="text-right mt-5">
                                    <a class="btn btn-outline-secondary w-24 mr-1" href="{{url("$segment/$folder")}}">ยกเลิก</a>
                                    <button type="submit" class="btn btn-primary w-24">บันทึกข้อมูล</button>
                                </div>
                            </div>
                            <!-- END: Form Layout -->
                        </div>
                    </div>
                </form>


                

            </div>
            
        </div>
       
        <!-- BEGIN: JS Assets-->
        @include("backend.layout.script")

        <script>
      function check_add() {
            var role = $('#role').val();
            var status_check = $('#status_check').val();
            var name = $('#name').val();
            var username = $('#username').val();
            var password = $('#password').val();
            var confirm_password = $('#confirm_password').val();
            var resetpassword = $('#resetpassword').val();

            if (role == "") {
                toastr.error('กรุณาเลือกระดับของผู้ใช้งานนี้');
                return false;
            }
            if (status_check == "") {
                toastr.error('กรุณาเลือกสถานะการใช้งาน');
                return false;
            }
            if (name == "" || username == "") {
                toastr.error('กรุณากรอกข้อมูลให้ครบถ้วนก่อนบันทึกรายการ');
                return false;
            }
            if (password != confirm_password) {
                toastr.error('กรุณากรอกรหัสผ่านให้เหมือนกัน');
                return false;
            }
        }
        //== Script Ajax Regular ==
        $('#resetpassword').change(function() {
            if ($(this).prop("checked") == true) {
                $('#password').attr('disabled', false);
                $('#confirm_password').attr('disabled', false);
            } else if ($(this).prop("checked") == false) {
                $('#password').attr('disabled', true);
                $('#confirm_password').attr('disabled', true);
                $('#password').val(null);
                $('#confirm_password').val(null);
            }
        });

        $('.show_pass').click(function() {
            var password = $('#password').attr('type');
            if (password == "password") {
                $('#password').attr('type', 'text');
            } else {
                $('#password').attr('type', 'password');
            }
        });


        $('.show_pass_confirm').click(function() {
            var confirm_password = $('#confirm_password').attr('type');
            if (confirm_password == "password") {
                $('#confirm_password').attr('type', 'text');
            } else {
                $('#confirm_password').attr('type', 'password');
            }
        });
      
        </script>
        <!-- END: JS Assets-->
    </body>
</html>