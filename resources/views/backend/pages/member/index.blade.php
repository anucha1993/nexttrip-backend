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

            <h2 class="intro-y text-lg font-medium mt-10">{{ @$namepage }}</h2>
            <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                <div class="w-full sm:w-auto mt-3 mr-2 sm:mt-0 sm:ml-auto md:ml-0">
                    <div class="w-56 relative text-slate-500">
                        <input type="text" class="form-control w-56 box pr-10 myLike" name="search_name" placeholder="ค้นหาชื่อ-นามสกุล">
                        <i class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" data-lucide="search"></i>
                    </div>
                </div>
                <div class="w-full sm:w-auto mt-3 mr-2 sm:mt-0 sm:ml-auto md:ml-0">
                    <div class="w-56 relative text-slate-500">
                        <input type="text" class="form-control w-56 box pr-10 myLike" name="search_phone" placeholder="ค้นหาเบอร์โทรศัพท์">
                        <i class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" data-lucide="search"></i>
                    </div>
                </div>
                <div class="hidden md:block mx-auto text-slate-500"></div>
                <div class="float-right">
                    @if(@$menu_control->add == "on")
                    <a href="{{ url("$segment/$folder/add") }}" class="btn btn-primary shadow-md mr-2">เพิ่มข้อมูล</a>
                    @endif
                </div>
            </div>
            <br>
            <div class="intro-y box p-5 place-content-center">
                <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                    <div class="hidden md:block mx-auto text-slate-500"></div>
                </div>
                <div class="overflow-x-auto" style="background-color: #F5F5F5;padding:15px;border-radius:5px;">
                    <table id="datatable" class="table table-report"></table>
                </div>
            </div>
                
        <!-- BEGIN: JS Assets-->
        @include("backend.layout.script")
        <script>
            var fullUrl = window.location.origin + window.location.pathname;
            var oTable;

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
                        url: fullUrl + "/datatable",
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
                        {  data: 'DT_RowIndex',     title :'<center>#</center>',     className: 'whitespace-nowrap w-5 text-center'},
                        {  data: 'type',            title: '<center>ประเภท</center>',     className: 'items-center w-10 text-center'},
                        {  data: 'name',            title: '<center>ชื่อ-นามสกุล</center>',     className: 'items-center w-15 text-center'},
                        {  data: 'email',            title: '<center>อีเมล</center>',     className: 'items-center w-15 text-center'},
                        {  data: 'phone',           title: '<center>เบอร์โทรศัพท์</center>',     className: 'items-center w-10 text-center'},
                        {  data: 'updated_at',      title: '<center>วันที่อัพเดท</center>',     className: 'items-center w-10 text-center'},
                        {  data: 'action',          title: '<center>จัดการ</center>',     className: 'items-center w-15 text-center'},
                    ],
                    rowCallback: function (nRow, aData, dataIndex) {

                    }
                });
                $('.myWhere,.myLike,.myCustom,#onlyTrashed').on('change', function (e) {
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
                    title: 'ลบข้อมูล',
                    text: "คุณต้องการลบข้อมูลใช่หรือไม่?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'ตกลง',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "POST",
                            url: '{{ url("/webpanel/member/delete") }}',
                            data: {
                                id:id,
                                _token:"{{ csrf_token() }}"
                            },
                            success: function(data){
                                    if(data){
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'ข้อมูลถูกลบแล้ว',
                                            showConfirmButton: true,
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                window.location.reload();
                                            }
                                        })
                                    }else{
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'เกิดข้อผิดพลาด',
                                            showConfirmButton: false,
                                            timer: 3000
                                        })
                                    }
                            }
                        });
                    }
                })
            }

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
