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
                                    <div class="col-span-12 lg:col-span-12">
                                        <label for="crud-form-1" class="form-label">รายละเอียด</label>
                                        <textarea  name="detail" class="tiny">{{$row->detail}}</textarea>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <label for="crud-form-1" class="form-label">คำอธิบาย</label>
                                        <textarea  name="text_center" class="tiny">{{$row->text_center}}</textarea>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <h6 class="mb-3">รูปภาพ(ซ้าย) <span class="text-danger">ขนาด 596*365 </span></h6>
                                        <img src="@if(@$row->img_left == null) {{url("noimage.jpg")}} @else {{asset($row->img_left)}} @endif"
                                            class="img-thumbnail" id="preview-left">
                                    </div>
                                    <div class="col-span-12 lg:col-span-12">
                                        <small class="help-block">* รองรับไฟล์ <strong class="text-danger">(jpg, jpeg,
                                                png)</strong> เท่านั้น</small>
                                        <div class="col-span-6 lg:col-span-6">
                                            <input type="file"
                                                class="custom-file-input block w-80 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                name="img_left" id="image-left">
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label">ข้อความ(ซ้าย)</label>
                                        <textarea name="text_left" class="tiny">{{$row->text_left}}</textarea>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <h6 class="mb-3">รูปภาพ(ขวา) <span class="text-danger">ขนาด 596*365 </span></h6>
                                        <img src="@if(@$row->img_right == null) {{url("noimage.jpg")}} @else {{asset($row->img_right)}} @endif"
                                            class="img-thumbnail" id="preview-right">
                                    </div>
                                    <div class="col-span-12 lg:col-span-12">
                                        <small class="help-block">* รองรับไฟล์ <strong class="text-danger">(jpg, jpeg,
                                                png)</strong> เท่านั้น</small>
                                        <div class="col-span-6 lg:col-span-6">
                                            <input type="file"
                                                class="custom-file-input block w-80 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                name="img_right" id="image-right">
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label">ข้อความ(ขวา)</label>
                                        <textarea name="text_right" class="tiny">{{$row->text_right}}</textarea>
                                    </div>
                                </div>
                                <div class="text-center mt-5">
                                    <button type="submit" class="btn btn-primary w-24">บันทึกข้อมูล</button>
                                    {{-- <a class="btn btn-outline-danger w-24 mr-1" href="{{ url("$segment/$folder") }}" >ยกเลิก</a> --}}
                                </div>
                            </div>
                            <!-- END: Form Layout -->
                        </div>
                    </div>
                </div>
            </form>
            <!-- END: Content -->
            <h2 class="intro-y text-lg font-medium mt-10">หัวข้อคำอธิบาย</h2>
            <br>
            <div class="intro-y box p-5 place-content-center">
                <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                    <div class="hidden md:block mx-auto text-slate-500"></div>
                </div>
                <br>
                <div class="overflow-x-auto"  style="background-color: #F5F5F5;padding:15px;border-radius:5px;">
                    <table id="datatable-list" class="table table-report"></table>
                </div>
                <!-- DATA TABLE -->
            </div>
            <h2 class="intro-y text-lg font-medium mt-10">ข้อมูลกรุ๊ปทัวร์</h2>
            <br>
            <div class="intro-y box p-5 place-content-center">
                <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                    <div class="hidden md:block mx-auto text-slate-500"></div>
                    <div class="float-right">
                        <a href="{{ url("$segment/$folder/add-group") }}" class="btn btn-primary shadow-md mr-2">เพิ่มข้อมูล</a>
                    </div>
                </div>
                <br>
                <div class="overflow-x-auto"  style="background-color: #F5F5F5;padding:15px;border-radius:5px;">
                    <table id="datatable" class="table table-report"></table>
                </div>
                <!-- DATA TABLE -->
            </div>
        </div>
        

        <!-- BEGIN: JS Assets-->
        @include("backend.layout.script")
        <script>
            var id = "{{$id}}";
            var fullUrl = "{{url('webpanel/group')}}";
            var oTable;
            var _token = '{{csrf_token()}}';
            $(function () {
                oTable = $('#datatable').DataTable({
                    searching: false,
                    ordering: false,
                    lengthChange: false,
                    pageLength: 10,
                    processing: true,
                    serverSide: true,
                    "language": {
                        "lengthMenu": "แสดง _MENU_ แถว",
                        "zeroRecords": "ไม่พบข้อมูล",
                        "info": "แสดงหน้า _PAGE_ จาก _PAGES_ หน้า",
                        "search": "ค้นหา",
                        "infoEmpty": "",
                        "infoFiltered": "",
                        "paginate": {
                            "first": "หน้าแรก",
                            "previous": "ย้อนกลับ",
                            "next": "ถัดไป",
                            "last": "หน้าสุดท้าย"
                        },
                        'processing': "กำลังโหลดข้อมูล",
                    },
                    iDisplayLength: 25,
                    ajax: {
                        url: fullUrl+"/"+id+"/datatable-group?_token="+_token,
                        data: function (d) {
                            d.Like = {};
                            $('.myLike').each(function () {
                                if ($.trim($(this).val()) && $.trim($(this).val()) != '0') {
                                    d.Like[$(this).attr('name')] = $.trim($(this).val());
                                }
                            });
                            oData = d;
                            
                        },
                       
                        method: 'POST'
                    },
                    columns: [
                        // {data: 'DT_RowIndex',    title :'#',    className: 'whitespace-nowrap w-10 text-center'}, // 0
                        {
                            data: 'img',
                            title: '<center>รูปภาพ</center>',
                            className: 'items-center justify-center'
                        }, //1
                        {
                            data: 'name',
                            title: '<center>กรุ๊ปทัวร์</center>',
                            className: 'whitespace-nowrap w-50 text-center'
                        }, //1
                        {
                            data: 'status',
                            title: 'สถานะ',
                            className: 'whitespace-nowrap w-25 text-center'
                        }, // 2
                        {
                            data: 'action',
                            title: '<center>จัดการ</center>',
                            className: 'whitespace-nowrap w-25 text-center',
                        }, // 4
                    ],
                    rowCallback: function (nRow, aData, dataIndex) {

                    }
                });
                $('.myWhere,.myLike,.myCustom,#onlyTrashed').on('change', function (e) {
                    // console.log($(this).val())
                    oTable.draw();
                });
            });

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
                        return fetch(fullUrl + '/destroy-group/' + id)
                            .then(response => response.json())
                            .then(data => location.reload())
                            .catch(error => {
                                Swal.showValidationMessage(`Request failed: ${error}`)
                            })
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
            $("#image-left").on('change', function () {
                var $this = $(this)
                const input = $this[0];
                const fileName = $this.val().split("\\").pop();
                $this.siblings(".custom-file-label").addClass("selected").html(fileName)
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $('#preview-left').attr('src', e.target.result).fadeIn('fast');
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            });
            $("#image-right").on('change', function () {
                var $this = $(this)
                const input = $this[0];
                const fileName = $this.val().split("\\").pop();
                $this.siblings(".custom-file-label").addClass("selected").html(fileName)
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $('#preview-right').attr('src', e.target.result).fadeIn('fast');
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            });
            $(function () {
                oTable = $('#datatable-list').DataTable({
                    searching: false,
                    ordering: false,
                    lengthChange: false,
                    pageLength: 10,
                    processing: true,
                    serverSide: true,
                    "language": {
                        "lengthMenu": "แสดง _MENU_ แถว",
                        "zeroRecords": "ไม่พบข้อมูล",
                        "info": "แสดงหน้า _PAGE_ จาก _PAGES_ หน้า",
                        "search": "ค้นหา",
                        "infoEmpty": "",
                        "infoFiltered": "",
                        "paginate": {
                            "first": "หน้าแรก",
                            "previous": "ย้อนกลับ",
                            "next": "ถัดไป",
                            "last": "หน้าสุดท้าย"
                        },
                        'processing': "กำลังโหลดข้อมูล",
                    },
                    iDisplayLength: 25,
                    ajax: {
                        url: fullUrl+"/"+id+"/datatable-list?_token="+_token,
                        data: function (d) {
                            d.Like = {};
                            $('.myLike').each(function () {
                                if ($.trim($(this).val()) && $.trim($(this).val()) != '0') {
                                    d.Like[$(this).attr('name')] = $.trim($(this).val());
                                }
                            });
                            oData = d;
                            
                        },
                       
                        method: 'POST'
                    },
                    columns: [
                        // {data: 'DT_RowIndex',    title :'#',    className: 'whitespace-nowrap w-10 text-center'}, // 0
                        {
                            data: 'img',
                            title: '<center>รูปภาพ</center>',
                            className: 'items-center justify-center'
                        }, //1
                        {
                            data: 'list',
                            title: '<center>หัวข้อ</center>',
                            className: 'whitespace-nowrap w-50 text-center'
                        }, //2
                        {
                            data: 'action',
                            title: '<center>จัดการ</center>',
                            className: 'whitespace-nowrap w-50 text-center',
                        }, // 4
                    ],
                    rowCallback: function (nRow, aData, dataIndex) {

                    }
                });
                $('.myWhere,.myLike,.myCustom,#onlyTrashed').on('change', function (e) {
                    // console.log($(this).val())
                    oTable.draw();
                });
            });
            function status(ids) {
                const $this = $(this),
                    id = ids;
                $.ajax({
                    type: 'get',
                    url: fullUrl + '/status/' + id,
                    success: function(res) {
                        if (res == false) {
                            $(this).prop('checked', false)
                        }
                    }
                });
            }
        </script>
        <!-- END: JS Assets-->
</body>

</html>
