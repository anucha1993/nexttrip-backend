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
                                    <?php  @$tag_select = \App\Models\Backend\PromotionTagModel::find($row->tag_id); ?>
                                    <div class="col-span-12 lg:col-span-2">
                                        <label for="crud-form-1" class="form-label">แท็กโปรโมชั่น</span></label>
                                        <select name="tag_id" id="tag_id" class="form-control" >
                                            @if($row->tag_id == null)<option value="" selected hidden>กรุณาเลือกข้อมูล</option> @endif
                                            @foreach ($tag as $t)
                                            <option value="{{$t->id}}" @if(@$tag_select->id == $t->id) selected @endif >{{$t->tag}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-span-12 lg:col-span-10">
                                        <label for="crud-form-1" class="form-label"><span class="text-danger">*</span>โปรโมชั่น</label>
                                        <input type="text" id="promotion_name" name="promotion_name" class="form-control" value="{{$row->promotion_name}}">
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    {{-- <div class="col-span-12 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label"><span class="text-danger">*</span>วันที่เริ่มต้น</label>
                                        <input type="date" id="start_date" name="start_date" class="form-control" value="{{$row->start_date}}">
                                    </div>
                                    <div class="col-span-12 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label"><span class="text-danger">*</span>วันที่สิ้นสุด</label>
                                        <input type="date" id="end_date" name="end_date" class="form-control" value="{{$row->end_date}}">
                                    </div> --}}
                                    {{-- <div class="col-span-12 lg:col-span-2">
                                        <label for="crud-form-1" class="form-label">จำนวนส่วนลด<span class="text-danger">(ทัวร์ไฟไหม้)</span></label>
                                        <input type="number" id="discount" name="discount" class="form-control" value="{{$row->discount}}">
                                    </div>
                                    <div class="col-span-12 lg:col-span-2">
                                        <label for="crud-form-1" class="form-label">ประเภทส่วนลด<span class="text-danger">(ทัวร์ไฟไหม้)</span></label>
                                        <select name="type_duscount" id="type_duscount" class="form-control" >
                                            <option value="" selected hidden>กรุณาเลือกข้อมูล</option>
                                            <option value="1" @if($row->type_duscount == 1) selected @endif >เปอร์เซ็นต์</option>
                                            <option value="2" @if($row->type_duscount == 2) selected @endif >บาท</option>
                                            <option value="" @if($row->type_duscount == '') selected @endif >ไม่มีส่วนลด</option>
                                        </select>
                                    </div> --}}
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <label for="crud-form-1" class="form-label">รายละเอียด</label>
                                        <textarea  name="detail" class="tiny">{{$row->detail}}</textarea>
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

        </script>
        <!-- END: JS Assets-->
</body>

</html>
