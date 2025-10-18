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
                    {{@$name_page}}
                </h2>
                <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
                    <a href="{{ url("$segment/$folder/add") }}"><button type="button" class="btn btn-primary shadow-md mr-2">Add Items</button></a>
                </div>
            </div>

             <!-- DATA TABLE -->
             <div hidden id="loading-div" class="">
                <div class="col-span-6 sm:col-span-3 xl:col-span-2 flex flex-col justify-end items-center">
                    <i data-loading-icon="ball-triangle" class="w-8 h-8"></i> 
                </div>
            </div>

            <div id="content-div" class="intro-y col-span-12 lg:col-span-6 mt-3">
                <div class="intro-y box">
                    <div class="p-5" id="head-options-table">
                        <div class="overflow-x-auto">
                            <div class="hidden md:block mx-auto text-slate-500"><b>Showing {{$items->currentPage()}} to {{$items->total()}} of {{$items->total()}} entries</b></div>
                            <table class="table">
                                <thead class="" style="background-color: #e8ecef;">
                                    <tr>
                                        <th style="width:5%;" class="whitespace-nowrap text-center">#</th>
                                        <th style="width:75%;" class="whitespace-nowrap">Name Eng</th>
                                        <th style="width:20%;" class="whitespace-nowrap text-center">ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $index => $item)
                                    <tr>
                                        <td class="text-center">{{ $index+1; }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td>
                                            <div class="flex justify-center items-center">
                                                <a class="flex items-center mr-3" href="{{ url("$segment/$folder/edit/$item->id") }}"> <i data-lucide="edit" class="w-4 h-4 mr-1"></i> </a>
                                                <a class="flex items-center text-danger" href="javascript:;" onclick="destroy('{{$item->id}}')" data-tw-toggle="modal" data-tw-target="#delete-confirmation-modal"> <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i>  </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            
                        </div>

                    </div>
                </div>

                <div class="table-footer mt-2">
                    <div class="row">
                        <div class="col-sm-5">
                            <p style=" ">
                                
                            </p>
                        </div>
                        <div class="col-sm-7" >
                            {!! $items->appends(request()->all())->links('backend.layout.pagination') !!}
                        </div>
                    </div>
                </div>
            </div>
            <!-- END: Content -->
            
        </div>

    </div>


    <div class="modal fade" id="showpagemodal" data-bs-backdrop="static" tabindex="-1"
    aria-labelledby="showpagemodalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="show_modal"></div>
    </div>
</div>

<div class="modal fade" id="showpagemodal1" data-bs-backdrop="static" tabindex="-1"
    aria-labelledby="showpagemodal1Label" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="show_modal1"></div>
    </div>
</div>

<div class="modal fade" id="secondmodal" data-bs-backdrop="static" aria-hidden="true" aria-labelledby="..."
    tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="show_secondmodal"></div>
    </div>
</div>
    <!-- End Modal -->
    
    <!-- BEGIN: JS Assets-->
    
    @include('backend.layout.script')
    <script>
        var fullUrl = window.location.origin + window.location.pathname;
        function status(ids) {
            const $this = $(this),
                id = ids;
            $.ajax({
                type: 'post',
                url: fullUrl + '/status/' + id,
                success: function(res) {
                    if (res == false) {
                        $(this).prop('checked', false)
                    }
                }
            });
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
    </script>
    <!-- END: JS Assets-->
</body>

</html>

