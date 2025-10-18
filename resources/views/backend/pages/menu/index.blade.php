<!DOCTYPE html>
<html lang="en" class="light">
<!-- BEGIN: Head -->

<head>
    <!-- BEGIN: CSS Assets-->
    @include('backend.layout.css')
    <!-- END: CSS Assets-->
</head>
<!-- END: Head -->

<body class="py-5">
   <!-- BEGIN: Mobile Menu -->
   @include('backend.layout.mobile-menu')
   <!-- END: Mobile Menu -->
   <div class="flex">
       <!-- BEGIN: Side Menu -->
       @include('backend.layout.side-menu')
       <!-- END: Side Menu -->



       <div class="content">
           <!-- BEGIN: Top Bar -->
           @include('backend.layout.topbar')
           <!-- END: Top Bar -->
            <!-- BEGIN: Content -->
            <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
                <h2 class="text-lg font-medium mr-auto">
                    Menu
                </h2>
                <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
                    <a href="{{ url("$segment/$folder/add") }}"><button type="button"
                            class="btn btn-primary shadow-md mr-2">Add New Menu</button></a>
                </div>
            </div>

            <div class="intro-y box p-5 mt-5">
                <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                    <div class="sm:flex items-center sm:mr-4">
                        <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">ค้นหา</label>
                        <select id="search_choice" name="search_choice" class="myLike form-select w-full sm:w-32 2xl:w-full mt-2 sm:mt-0 sm:w-auto">
                            <option value="" selected>ทั้งหมด</option>
                            <option value="name">ชื่อ</option>
                            <option value="sort">ลำดับ</option>
                        </select>
                    </div>
                    <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                        <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">ลักษณะ</label>
                        <select id="search_type" name="search_type" class="myLike form-select w-full mt-2 sm:mt-0 sm:w-auto">
                            <option value="like" selected>like</option>
                            <option value="=">=</option>
                            <option value="<">&lt;</option>
                            <option value="<=">&lt;=</option>
                            <option value=">">></option>
                            <option value=">=">>=</option>
                            <option value="!=">!=</option>
                        </select>
                    </div>
                    <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                        <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">ค้นหา</label>
                        <input id="search_keyword" name="search_keyword" type="text" class="myLike form-control sm:w-40 2xl:w-full mt-2 sm:mt-0" placeholder="ค้นหาข้อมูล">
                    </div>
                    <div class="mt-2 xl:mt-0">
                        <button id="search_button" name="search_button" onclick="search_datatable()" type="button" class="btn btn-primary w-full sm:w-16">Search</button>
                        <button id="search_reset" name="search_reset" onclick="reset_datatable()" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1">Reset</button>
                    </div>
                </div>
                <div class="overflow-x-auto scrollbar-hidden">
                    <div id="datatable_01" class="mt-5 table-report table-report--tabulator"></div>
                </div>
            </div>
            <!-- END: Content -->
            
        </div>

    </div>

    <!-- Modal -->
    {{-- <div class="modal fade" id="show_modal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">

            </div>
        </div>
    </div> --}}

    <div id="show_modal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-body p-10 text-center"> </div>
            </div>
        </div>
    </div>
    <!-- End Modal -->
    
    <!-- BEGIN: JS Assets-->
    
    @include('backend.layout.script')
    <script>
        var fullUrl = window.location.origin + window.location.pathname;

        getdatatable();
        
        function getdatatable(){
            new Tabulator("#datatable_01", {
                height:"100%",
                layout:"fitColumns",
                ajaxURL: fullUrl + "/datatable",
                ajaxConfig:"GET", //ajax HTTP request type
                ajaxFiltering:true,
                ajaxParams : {
                    search_choice: $('#search_choice').val(),
                    search_type: $('#search_type').val(),
                    search_keyword: $('#search_keyword').val(),
                },
                
                progressiveLoad:"scroll",
                paginationSize:20,
                placeholder:"<br/><center><b>ไม่พบข้อมูล</b></center>",
                columns:[
                    {field: 'DT_RowIndex',    title :'#',  width:'5%', vertAlign:"middle"}, // 0
                    {field: 'action_name',   title :'<center>ชื่อเมนู</center>', vertAlign:"middle" , formatter:"html",  width:'40%'}, // 1
                    {field: 'change_sort',    title :'<center>ลำดับ</center>', vertAlign:"middle", formatter:"html",    width:'10%'}, // 2
                    {field: 'created',    title :'วันที่สร้าง', formatter:"html", vertAlign:"middle", width:'15%'}, // 3
                    {field: 'status',    title :'สถานะ', formatter:"html", vertAlign:"middle",    width:'10%'}, // 4
                    {field: 'action',    title :'จัดการ', formatter:"html", vertAlign:"middle",    width:'20%'}, // 5
                ],
            
            });
        }
        function search_datatable()
        {
            getdatatable();
        }
        function reset_datatable()
        {
            $('#search_choice').val(null),
            $('#search_type').val("like"),
            $('#search_keyword').val(null),
            getdatatable();
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
        

        function badge_status(ids) {
            const $this = $(this),
                id = ids;
            $.ajax({
                type: 'get',
                url: fullUrl + '/sub_status/' + id,
                success: function(res) {
                    if (res.value == true) {
                        $('#sub_status_' + id).html(res.text);
                    }
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
                    return fetch(fullUrl + '/destroy?id=' + id)
                        .then(response => response.json())
                        .then(data => location.reload())
                        .catch(error => {
                            Swal.showValidationMessage(`Request failed: ${error}`)
                        })
                }
            });
        }

        // sub
        function deleteItem_sub(ids) {
            const id = [ids];
            if (id.length > 0) {
                destroy_sub(id)
            }
        }

        function destroy_sub(id) {
            Swal.fire({
                title: "ลบข้อมูล",
                text: "คุณต้องการลบข้อมูลใช่หรือไม่?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch(fullUrl + '/destroy_sub?id=' + id)
                        .then(response => response.json())
                        .then(data => location.reload())
                        .catch(error => {
                            Swal.showValidationMessage(`Request failed: ${error}`)
                        })
                }
            });
        }

        function dragsort(id, from, to) {
            $.ajax({
                url: fullUrl + '/dragsort',
                type: 'post',
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id,
                    from: from,
                    to: to,
                    _token: $('input[name="_token"]').val()
                },
                dataType: 'json',
                success: function(data) {}
            })
        }

        function show_submenu(id) {
            $.ajax({
                type: 'GET',
                url: fullUrl + '/showsubmenu',
                data: {
                    _token: "{{ csrf_token() }}",
                    id:id,
                },
                dataType: 'html',
                success: function(data) {
                    // Add response in Modal body
                    $('.modal-content').html(data);

                    // $('#show_modal').modal('show');
                }
            });
        }
        function changesort(id)
        {
            var sort = $('#sort_'+id).val();
            $.ajax({
                type: "post",
                url: fullUrl+"/changesort",
                data:{
                    _token: "{{ csrf_token() }}",
                    sort:sort,
                    id:id
                },
                success:function(data)
                {
                    location.reload();
                }
            });
        }
        function changesort_sub(id)
        {
            var sort = $('#sort_'+id).val();
            $.ajax({
                type: "post",
                url: fullUrl+"/changesort_sub",
                data:{
                    _token: "{{ csrf_token() }}",
                    sort:sort,
                    id:id
                },
                success:function(data)
                {
                    location.reload();
                }
            });
        }

    </script>
    <!-- END: JS Assets-->
</body>

</html>

