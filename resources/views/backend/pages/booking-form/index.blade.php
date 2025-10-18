<!DOCTYPE html>
<html lang="en" class="light">
<!-- BEGIN: Head -->

<head>

    <!-- BEGIN: CSS Assets-->
    @include("backend.layout.css")
    
    <style>
        /* select2 */
        .select2-selection__rendered {
            line-height: 33px !important;
        }
        /*ความยาว*/
        .select2-container .select2-selection--single {
            height: 37px !important;
            margin-top: 0.5px !important;
        }
        /*ลูกศร*/
        .select2-selection__arrow {
            height: 35px !important;
        }
        /*สีกรอบ*/
        .select2-container--default .select2-selection--single {
            /* border: 1px solid #2C2727 !important; */
            border: 1px solid rgb(226, 232, 240) !important;
        }
    </style>
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

        <h2 class="intro-y text-lg font-medium mt-10">ข้อมูลการจองทัวร์</h2>

        <div class="text-sm font-medium text-center text-gray-500 border-b border-gray-200 dark:text-gray-400 dark:border-gray-700 mb-5">
            <ul class="flex flex-wrap -mb-px">
                <li class="mr-2">
                    <a href="javascript:void(0);" onclick="getTapValue(1)" class="inline-block p-4 text-blue-600 border-b-2 border-blue-600 rounded-t-lg active dark:text-blue-500 dark:border-blue-500 mylike"><b>ใบจองใหม่</b></a>
                </li>
                <li class="mr-2">
                    <a href="javascript:void(0);" onclick="getTapValue(2)" class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 mylike"><b>ใบจองที่กดรับ/ใบจองเก่า</b></a>
                </li>
            </ul>
        </div>
        
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
            <div class="w-full sm:w-auto mt-3 mr-2 sm:mt-0 sm:ml-auto md:ml-0">
                <div class="w-56 relative">
                    <input type="text" class="form-control box pr-10 myLike mt-5" name="search_name" placeholder="ค้นหารหัส,ชื่อ,นามสกุล">
                </div>
            </div>
            <div class="w-full sm:w-auto mt-3 mr-2 sm:mt-0 sm:ml-auto md:ml-0">
                <div class="w-56 relative">
                    <input type="radio" name="search_type_date" onchange="getTypeDate(1)"> วันที่เดินทาง <input type="radio" class="ml-5" name="search_type_date" onchange="getTypeDate(2)"> วันที่จอง
                    <input type="date" class="form-control box pr-0.5 myLike mt-1" name="search_start_date" placeholder="วันที่เริ่ม">
                </div>
            </div>
            <div class="w-full sm:w-auto mt-3 mr-2 sm:mt-0 sm:ml-auto md:ml-0">
                <div class="w-56 relative">
                    <input type="date" class="form-control box pr-0.5 myLike mt-5" name="search_end_date" placeholder="วันที่สิ้นสุด">
                </div>
            </div>
            <div class="w-full sm:w-auto mt-3 mr-2 sm:mt-0 sm:ml-auto md:ml-0">
                <div class="w-56 relative">
                    <select name="search_status" class="form-control box pr-10 myLike mt-5">
                        <option value="">สถานะทั้งหมด</option>
                        <option value="Booked">Booked</option>
                        <option value="Waiting">Waiting</option>
                        <option value="Success">Success</option>
                        <option value="Cancel">Cancel</option>
                    </select>
                </div>
            </div>
        </div>

        @php
            $user = App\Models\Backend\User::find(Auth::Guard()->id());
            $sales = App\Models\Backend\User::where(['role'=>2,'status'=>'active'])->get();
        @endphp
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
            @if($user->role == 1)
            <div class="w-full sm:w-auto mt-3 mr-2 sm:mt-0 sm:ml-auto md:ml-0">
                <div class="w-56 relative">
                    <select name="search_sale" class="form-control box pr-10 select2 myLike mt-5">
                        {{-- @php
                            $user = App\Models\Backend\User::find(Auth::Guard()->id());
                            if($user->role == 1){
                                $sales = App\Models\Backend\User::where(['role'=>2,'status'=>'active'])->get();
                            }else{
                                $sales = App\Models\Backend\User::find(Auth::Guard()->id());
                            }
                        @endphp --}}
                        <option value="">Sale ทั้งหมด</option>
                        @foreach($sales as $sale)
                            <option value="{{@$sale->id}}">{{$sale->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endif
            <div class="hidden md:block mx-auto text-slate-500"></div>
            <div class="float-right">
                @if(@$menu_control->add == "on")
                <a href="{{ url("$segment/$folder/edit-detail/1") }}" class="btn btn-success shadow-md mr-2">เงื่อนไขการจองทัวร์</a>
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
            var s_tap = 1;
            var s_type;
            var oTable;

            const tabs = document.querySelectorAll('.mylike');
    
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    tabs.forEach(t => {
                        t.classList.remove('text-blue-600', 'border-blue-600', 'active', 'dark:text-blue-500', 'dark:border-blue-500');
                        t.classList.add('border-transparent', 'hover:text-gray-600', 'hover:border-gray-300', 'dark:hover:text-gray-300');
                    });
                    this.classList.remove('border-transparent', 'hover:text-gray-600', 'hover:border-gray-300', 'dark:hover:text-gray-300');
                    this.classList.add('text-blue-600', 'border-blue-600', 'active', 'dark:text-blue-500', 'dark:border-blue-500');
                });
            });

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
                            if(s_tap){
                                d.Like['search_tap_name'] = s_tap;
                            }
                            if(s_type){
                                d.Like['search_type_date'] = s_type;
                            }
                            oData = d;
                        },
                        method: 'POST'
                    },
                    columns: [
                        {  data: 'DT_RowIndex',        title :'#',     className: 'whitespace-nowrap w-10 text-center'},
                        {  data: 'ref',        title: '<center>Ref.Booking</center>',     className: 'items-center w-10 text-center'},
                        {  data: 'name',        title: '<center>ชื่อ-นามสกุล</center>',     className: 'items-center w-40 text-center'},
                        {  data: 'code',        title: '<center>รหัสทัวร์</center>',     className: 'items-center w-10 text-center'},
                        {  data: 'tour',        title: '<center>โปรแกรมทัวร์</center>',     className: 'items-center w-50 text-center'},
                        {  data: 'period',      title: '<center>วันที่เดินทาง</center>',     className: 'items-center w-20 text-center'},
                        {  data: 'total_qty',   title: '<center>Pax</center>',     className: 'items-center w-10 text-center'},
                        {  data: 'status',      title: '<center>สถานะ</center>',     className: 'items-center w-10 text-center'},
                        {  data: 'sale',        title: '<center>เซลล์</center>',     className: 'items-center w-40 text-center'},
                        {  data: 'created_at',  title: '<center>วันที่จอง</center>',     className: 'items-center w-10 text-center'},
                        {  data: 'action',      title: '<center>จัดการ</center>',     className: 'items-center w-25 text-center'},
                    ],
                    rowCallback: function (nRow, aData, dataIndex) {

                    }
                });
                $('.myWhere,.myLike,.myCustom,#onlyTrashed').on('change', function (e) {
                    oTable.draw();
                });
            });

            function getTapValue(tap) {           
                s_tap = tap;
                oTable.draw();
            }

            function getTypeDate(type) {    
                s_type = type;
                oTable.draw();
            }

            function Confirm(id) {
                Swal.fire({
                    title: "ยืนยันที่นั่ง",
                    text: "คุณต้องการยืนยันที่นั่งใช่หรือไม่?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return fetch(fullUrl + '/confirm/' + id)
                            .then(response => response.json())
                            .then(data => location.reload())
                            .catch(error => {
                                Swal.showValidationMessage(`Request failed: ${error}`)
                            })
                    }
                });
            }

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
                        return fetch(fullUrl + '/destroy/' + id)
                            .then(response => response.json())
                            .then(data => location.reload())
                            .catch(error => {
                                Swal.showValidationMessage(`Request failed: ${error}`)
                            })
                    }
                });
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
