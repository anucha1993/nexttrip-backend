<!DOCTYPE html>
<html lang="en" class="light">
<!-- BEGIN: Head -->

<head>

    <!-- BEGIN: CSS Assets-->
    @include("backend.layout.css")
    <!-- END: CSS Assets-->

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
        .select2-container--default{
            width: 100% !important;
        }
    </style>
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

            
            <div class="text-sm font-medium text-center text-gray-500 border-b border-gray-200 dark:text-gray-400 dark:border-gray-700 mb-5">
                <ul class="flex flex-wrap -mb-px">
                    <li class="mr-2">
                        <a href="javascript:void(0);" onclick="getTabValue('all')" class="inline-block p-4 text-blue-600 border-b-2 border-blue-600 rounded-t-lg active dark:text-blue-500 dark:border-blue-500 mylike"><b>ทัวร์ทั้งหมด</b></a>
                    </li>
                    {{-- <li class="mr-2">
                        <a href="javascript:void(0);" onclick="getTabValue('on')" class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 mylike"><b>ทัวร์ที่จะออกเดินทาง</b></a>
                    </li> --}}
                    <li class="mr-2">
                        <a href="javascript:void(0);" onclick="getTabValue('off')" class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 mylike"><b>ทัวร์ออกเดินทางแล้ว</b></a>
                    </li>
                    <li class="mr-2">
                        <a href="javascript:void(0);" onclick="getTabValue('not')" class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 mylike"><b>ทัวร์ที่ไม่ต้องจัดการ</b></a>
                    </li>
                </ul>
            </div>

            
            <div class="intro-y col-span-12 flex flex-wrap items-center mt-2">
                <div class="w-full sm:w-auto mt-2 mr-2 sm:mt-0 sm:ml-auto md:ml-0 mb-2 sm-mb-1">
                    <div class="w-100 relative">
                        <input type="text" class="form-control box pr-10 myLike" name="search_title" placeholder="รหัสทัวร์,ชื่อโปรแกรมทัวร์">
                        <i class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" data-lucide="search"></i>
                    </div>
                </div>
                <div class="w-full sm:w-auto mt-2 mr-2 sm:mt-0 sm:ml-auto md:ml-0 mb-2 sm-mb-1">
                    <div class="w-100 relative">
                        <select name="search_status" class="form-control box pr-10 myLike">
                            <option value="">สถานะทั้งหมด</option>
                            <option value="on">On</option>
                            <option value="off">Off</option>
                        </select>
                    </div>
                </div>
                <div class="w-full sm:w-auto mt-2 mr-2 sm:mt-0 sm:ml-auto md:ml-0 mb-2 sm-mb-1">
                    <div class="w-100 relative">
                        <select name="search_wholesale" class="form-control box pr-10 select2 myLike">
                            <option value="">Wholesale ทั้งหมด</option>
                            @foreach($tour->groupBy('wholesale_id') as $wholesaleGroup)
                                @php
                                    // $wholesale = App\Models\Backend\WholesaleModel::where('id',$wholesaleGroup[0]->wholesale_id)->whereNull('deleted_at')->first();
                                    $wholesale = App\Models\Backend\WholesaleModel::find($wholesaleGroup[0]->wholesale_id);
                                @endphp
                                <option value="{{ @$wholesale->id }}">{{ @$wholesale->wholesale_name_en }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="w-full sm:w-auto mt-2 mr-2 sm:mt-0 sm:ml-auto md:ml-0 mb-2 sm-mb-1">
                    <div class="w-100 relative">
                        <select name="search_country" class="form-control box pr-10 select2 myLike">
                            <option value="">ประเทศทั้งหมด</option>
                            @foreach($country as $co)
                                <option value="{{@$co->id}}">{{ @$co->country_name_th}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                {{-- <div class="w-full sm:w-auto mt-2 mr-2 sm:mt-0 sm:ml-auto md:ml-0 mb-2 sm-mb-1">
                    <div class="w-100 relative">
                        <select name="search_city" class="form-control box pr-10 select2 myLike">
                            <option value="">จังหวัดทั้งหมด</option>
                            @foreach($city as $ci)
                                <option value="CI.{{@$ci->id}}">{{ @$ci->city_name_th}}</option>
                            @endforeach
                            @foreach($province as $pro)
                                <option value="PRO.{{@$pro->id}}">{{ @$pro->name_th}}</option>
                            @endforeach
                        </select>
                    </div>
                </div> --}}
                <div class="w-full sm:w-auto mt-2 mr-2 sm:mt-0 sm:ml-auto md:ml-0 mb-2 sm-mb-1">
                    <div class="w-100 relative">
                        <select name="search_tag_promotion" class="form-control box pr-10 select2 myLike">
                            <option value="">โปรโมชั่นแท็กทั้งหมด</option>
                            @foreach($promotion as $pro)
                                <option value="{{@$pro->id}}">{{ @$pro->promotion_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="intro-y col-span-12 flex flex-wrap items-center mt-2">
                <div class="w-full sm:w-auto mt-2 mr-2 sm:mt-0 sm:ml-auto md:ml-0 mb-2 sm-mb-1">
                    <div class="w-100 relative">
                        <select name="search_type" class="form-control box pr-10 select2 myLike">
                            <option value="">ประเภททั้งหมด</option>
                            @foreach($type as $t)
                                <option value="{{@$t->id}}">{{ @$t->type_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="w-full sm:w-auto mt-2 mr-2 sm:mt-0 sm:ml-auto md:ml-0 mb-2 sm-mb-1">
                    <div class="w-100 relative">
                        <select name="search_promotion" class="form-control box pr-10 select2 myLike">
                            <option value="">โปรโมชั่นทั้งหมด</option>
                            <option value="1">โปรไฟไหม้</option>
                            <option value="2">โปรโมชั่นทัวร์</option>
                        </select>
                    </div>
                </div>
                <div class="hidden md:block mx-auto text-slate-500"></div>
                <div class="float-right">
                    @if(@$menu_control->add == "on")
                    <a href="{{ url("$segment/$folder/edit-pdf") }}" class="btn btn-success shadow-md mr-2">หัว-ท้าย PDF</a>
                    <a href="{{ url("$segment/$folder/add") }}" class="btn btn-primary shadow-md mr-2">เพิ่มข้อมูล</a>
                    @endif
                </div>
            </div>
            <br>
            <div class="intro-y box p-5 place-content-center">
                    <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                        <button id="delete-selected" class="btn btn-danger mr-2 mb-2">ลบที่เลือก</button>
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
            var s_tab;
            var oTable;

            window.onload = function() {
                getTabValue('all');
            };

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
                        pageLength: 20,
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
                                if(s_tab){
                                    d.Like['search_tab_name'] = s_tab;
                                } 
                                oData = d;
                            },
                            method: 'POST'
                        },
                        columns: [
                            { data: null, title: '<input type="checkbox" id="select-all">', orderable: false, className: 'text-center', render: function (data, type, row) {
                                return '<input type="checkbox" class="row-checkbox" value="' + row.id + '">';
                            }},
                            {  data: 'DT_RowIndex',        title :'#',     className: 'whitespace-nowrap w-10 text-center'},
                            {  data: 'image',       title: '<center>รหัสทัวร์</center>',     className: 'items-center w-60 text-center'},
                            {  data: 'name',        title: '<center>ชื่อ</center>',     className: 'items-center w-40 text-center'},
                            {  data: 'country',     title: '<center>ประเทศ</center>',     className: 'items-center w-20 text-center'},
                            {  data: 'period',      title: '<center>Period</center>',     className: 'items-center w-60 text-center'},
                            {  data: 'price',       title: '<center>ราคา</center>',     className: 'items-center w-20 text-center'},
                            {  data: 'status',      title: '<center>สถานะ</center>',     className: 'items-center w-10 text-center'},
                            {  data: 'tab_status',  title: '<center>สถานะจัดการ</center>',     className: 'items-center w-10 text-center'},
                            {  data: 'updated_at',  title: '<center>วันที่อัพเดท</center>',     className: 'items-center w-20 text-center'},
                            {  data: 'action',      title: '<center>จัดการ</center>',     className: 'items-center w-20 text-center'},
                        ],
                        rowCallback: function (nRow, aData, dataIndex) {
                        }
                    });
                    $('.myWhere,.myLike,.myCustom,#onlyTrashed').on('change', function (e) {
                        oTable.draw();
                    });

                    // เลือก/ไม่เลือกทั้งหมด
                    $(document).on('click', '#select-all', function(){
                        $('.row-checkbox').prop('checked', this.checked);
                    });
                    // ถ้า uncheck รายแถว ให้ uncheck select-all ด้วย
                    $(document).on('click', '.row-checkbox', function(){
                        if(!this.checked){
                            $('#select-all').prop('checked', false);
                        }
                    });
                    // ปุ่มลบที่เลือก
                    $('#delete-selected').on('click', function() {
                        var ids = [];
                        $('.row-checkbox:checked').each(function() {
                            ids.push($(this).val());
                        });
                        if(ids.length > 0){
                            destroy(ids);
                        }else{
                            Swal.fire('กรุณาเลือกข้อมูลที่ต้องการลบ');
                        }
                    });
            });

            function getTabValue(tab) {
                s_tab = tab;
                oTable.draw();
            }
            
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
                            url: '{{ url("/webpanel/tour/delete") }}',
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

            function tab_status(ids) {
                const $this = $(this),
                    id = ids;
                $.ajax({
                    type: 'get',
                    url: fullUrl + '/tab_status/' + id,
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
