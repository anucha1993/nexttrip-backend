<!DOCTYPE html>
<html>
<head>
    <title>DataTables Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .error { color: red; }
        .success { color: green; }
        button { margin: 5px; padding: 10px 15px; }
    </style>
</head>
<body>
    <h1>🧪 DataTables Test Page</h1>
    
    <div class="test-section">
        <h2>URL Information</h2>
        <p><strong>Current URL:</strong> <span id="current-url"></span></p>
        <p><strong>DataTable URL:</strong> <span id="datatable-url"></span></p>
        <p><strong>Test URL:</strong> <span id="test-url"></span></p>
    </div>

    <div class="test-section">
        <h2>URL Tests</h2>
        <button onclick="testOriginalUrl()">Test Original URL</button>
        <button onclick="testSimpleUrl()">Test Simple URL</button>
        <div id="url-test-results"></div>
    </div>

    <div class="test-section">
        <h2>DataTables Tests</h2>
        <button onclick="loadSimpleDataTable()">Load Simple DataTable</button>
        <button onclick="loadOriginalDataTable()">Load Original DataTable</button>
        <button onclick="destroyDataTable()">Destroy DataTable</button>
        <div id="datatable-results"></div>
    </div>

    <div class="test-section">
        <h2>DataTable</h2>
        <table id="test-datatable" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>

    <script>
        // Get base URL
        const baseUrl = window.location.origin + '/webpanel/tour';
        
        // Update URL display
        $('#current-url').text(window.location.href);
        $('#datatable-url').text(baseUrl + '/datatable');
        $('#test-url').text(baseUrl + '/datatable-test');

        let dataTable = null;

        function testOriginalUrl() {
            $('#url-test-results').html('<p>🧪 Testing original URL...</p>');
            
            $.ajax({
                url: baseUrl + '/datatable',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    draw: 1,
                    start: 0,
                    length: 10
                },
                success: function(response) {
                    $('#url-test-results').append('<p class="success">✅ Original URL works!</p>');
                    console.log('Original response:', response);
                },
                error: function(xhr) {
                    $('#url-test-results').append(`<p class="error">❌ Original URL failed: ${xhr.status} ${xhr.statusText}</p>`);
                    $('#url-test-results').append(`<p class="error">Response: ${xhr.responseText.substring(0, 500)}...</p>`);
                    console.error('Original error:', xhr);
                }
            });
        }

        function testSimpleUrl() {
            $('#url-test-results').append('<p>🧪 Testing simple URL...</p>');
            
            $.ajax({
                url: baseUrl + '/datatable-test',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    draw: 1,
                    start: 0,
                    length: 10
                },
                success: function(response) {
                    $('#url-test-results').append('<p class="success">✅ Simple URL works!</p>');
                    console.log('Simple response:', response);
                },
                error: function(xhr) {
                    $('#url-test-results').append(`<p class="error">❌ Simple URL failed: ${xhr.status} ${xhr.statusText}</p>`);
                    $('#url-test-results').append(`<p class="error">Response: ${xhr.responseText.substring(0, 500)}...</p>`);
                    console.error('Simple error:', xhr);
                }
            });
        }

        function destroyDataTable() {
            if (dataTable) {
                dataTable.destroy();
                dataTable = null;
                $('#datatable-results').append('<p>🗑️ DataTable destroyed</p>');
            }
        }

        function loadSimpleDataTable() {
            $('#datatable-results').append('<p>🚀 Loading Simple DataTable...</p>');
            
            destroyDataTable();
            
            dataTable = $('#test-datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: baseUrl + '/datatable-test',
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    error: function(xhr, error, code) {
                        $('#datatable-results').append(`<p class="error">❌ DataTable Error: ${xhr.status} ${error}</p>`);
                        console.error('DataTable error:', xhr.responseText);
                    }
                },
                columns: [
                    {data: 'id'},
                    {data: 'name'},
                    {data: 'code'},
                    {data: 'status'},
                    {data: 'action', orderable: false}
                ]
            });
            
            $('#datatable-results').append('<p class="success">✅ Simple DataTable initialized</p>');
        }

        function loadOriginalDataTable() {
            $('#datatable-results').append('<p>🚀 Loading Original DataTable...</p>');
            
            destroyDataTable();
            
            dataTable = $('#test-datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: baseUrl + '/datatable',
                    type: 'POST',
                    data: function(d) {
                        d._token = $('meta[name="csrf-token"]').attr('content');
                        d.Like = {};
                        return d;
                    },
                    error: function(xhr, error, code) {
                        $('#datatable-results').append(`<p class="error">❌ DataTable Error: ${xhr.status} ${error}</p>`);
                        console.error('DataTable error:', xhr.responseText);
                    }
                },
                columns: [
                    {data: 'id'},
                    {data: 'name'},
                    {data: 'code'},
                    {data: 'status'},
                    {data: 'action', orderable: false}
                ]
            });
            
            $('#datatable-results').append('<p class="success">✅ Original DataTable initialized</p>');
        }
    </script>
</body>
</html>