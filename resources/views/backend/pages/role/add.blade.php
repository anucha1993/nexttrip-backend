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
                                    <div class="col-span-12 lg:col-span-12">
                                        <label for="crud-form-1" class="form-label">ชื่อบทบาท</label>
                                        <input type="text" id="name" name="name" class="form-control w-full" placeholder="ชื่อบทบาท">
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
                <!-- END: Content -->


            </div>
            
        </div>
       
        <!-- BEGIN: JS Assets-->
        @include("backend.layout.script")

        <script>
        function check_add() {
            var name = $('#name').val();
            if (name == "") {
                toastr.error('กรุณากรอกชื่อ');
                return false;
            }
        }
        </script>
        <!-- END: JS Assets-->
    </body>
</html>