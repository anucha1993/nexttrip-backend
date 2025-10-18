<!doctype html>
<html lang="en">

<head>

    @include("backend.layout.css")
</head>

<body data-sidebar="dark">

    <!-- Script Zone -->
    @include("backend.layout.script")
    <script>
        const url = '{{@$url}}';
        $(function() {
            Swal.fire({
                title: "{{@$title}}",
                text: "{{@$text}}",
                icon: "{{@$icon}}",
                allowOutsideClick: false,
            }).then((result) => {
                if (url == '') {
                    window.location = window.location.href;
                } else {
                    window.location = url
                }
            });
        })
    </script>

</body>

</html>