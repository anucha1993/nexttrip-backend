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
                                    <div class="col-span-12 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label">ชื่อ</label>
                                        <input type="text"  class="form-control" value="{{$row->name}}" readonly>
                                    </div>
                                    <div class="col-span-12 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label">นามสกุล</label>
                                        <input type="text"  class="form-control" value="{{$row->surname}}" readonly>
                                    </div>
                                </div>
                                <br>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label">บริษัท</label>
                                        <input type="text"  class="form-control" value="{{$row->company}}" readonly>
                                    </div>
                                    <div class="col-span-12 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label">อีเมล</label>
                                        <input type="text"  class="form-control" value="{{$row->mail}}" readonly>
                                    </div>
                                </div>
                                <br>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label">เบอร์โทรศัพท์</label>
                                        <input type="text"  class="form-control" value="{{$row->phone}}" readonly>
                                    </div>
                                    <div class="col-span-12 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label">รู้จักจาก</label>
                                        <input type="text" class="form-control" value="{{$topic->topic}}" readonly>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <label for="crud-form-1" class="form-label">รายละเอียดเนื้อหา</label>
                                        <textarea  cols="20" rows="10"  class="form-control" readonly>{{$row->detail}}</textarea>
                                    </div>
                                </div>
                                <div class="text-center mt-5">
                                    <a class="btn btn-primary w-24" href="{{ url("$segment/$folder") }}" >ย้อนกลับ</a>
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
</body>

</html>
