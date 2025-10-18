<script src="{{ asset('backend/dist/js/app.js') }}"></script>
<script src="{{ asset('backend/libs/jquery/jquery.min.js') }}"  type="text/javascript"></script>
<script src="{{ asset('backend/libs/toastr/toastr.js') }}"></script>
<script src="{{ asset('backend/libs/tabulator/js/tabulator.js') }}"></script>
<script src="{{ asset('backend/libs/fontawesome/fontawesome.min.js') }}"></script>
<script src="{{ asset('backend/flowbite/dist/flowbite.js')}}"></script>
<script src="{{asset('backend/tinymce/tinymce.min.js')}}"></script>

<!-- Sweet Alerts js -->
<script src="{{ asset('backend/libs/sweetalert2/sweetalert2.min.js')}}"></script>

<!-- Sweet alert init js-->
<script src="{{ asset('backend/libs/sweet-alerts/sweet-alerts.init.js')}}"></script>

<script src="{{ asset('backend/libs/select2/js/select2.min.js') }}"></script>

<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.12.1/datatables.min.js"></script>
<script src="https://cdn.ckeditor.com/4.16.0/full/ckeditor.js"></script>
<script src="https://unpkg.com/flowbite@1.4.7/dist/flowbite.js"></script>

<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).ready(function() {
        $('.select2').select2({});
    });

    $(document).on("keydown", "form", function(event) { 
        return event.key != "Enter";
    });

    if ($('textarea.tiny').length > 0) {
        tinymce.init({
            selector: 'textarea.tiny',
            menubar: false,
            force_br_newlines: true,
            force_p_newlines: false,
            forced_root_block: '',
            width: '100%',
            height:300,
            plugins: [
                "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
                "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                "save table contextmenu directionality emoticons template paste textcolor colorpicker layer textpattern moxiemanager"
            ],
            toolbar: 'insertfile undo redo | table | styleselect fontsizeselect | bold italic forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | print nonbreaking hr emoticons code',

        });
    }
    
</script>