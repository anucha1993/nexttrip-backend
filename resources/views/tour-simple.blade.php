<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Tour Management - Simple Version</title>
    
    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .error { color: red; background: #ffe6e6; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .success { color: green; background: #e6ffe6; padding: 10px; border-radius: 5px; margin: 10px 0; }
        #datatable { margin-top: 20px; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container">
        <h1 class="text-3xl font-bold mb-6">🚀 Tour Management (Simple Version)</h1>
        
        <!-- Debug Info -->
        <div id="debug-info" class="mb-4 p-4 bg-blue-50 rounded">
            <h2 class="text-lg font-bold">Debug Information:</h2>
            <div id="url-info"></div>
            <div id="status-info"></div>
        </div>
        
        <!-- Control Buttons -->
        <div class="mb-4">
            <button id="test-simple" class="bg-green-500 text-white px-4 py-2 rounded mr-2">Test Simple DataTable</button>
            <button id="test-original" class="bg-blue-500 text-white px-4 py-2 rounded mr-2">Test Original DataTable</button>
            <button id="destroy-table" class="bg-red-500 text-white px-4 py-2 rounded">Destroy Table</button>
        </div>
        
        <!-- DataTable -->
        <table id="datatable" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Updated</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        console.log('🚀 Simple Tour Management - Starting...');
        
        // Global variables
        var fullUrl = window.location.origin + '/webpanel/tour';
        var oTable = null;
        
        // Display URL info
        $('#url-info').html(`
            <strong>Base URL:</strong> ${fullUrl}<br>
            <strong>Original Endpoint:</strong> ${fullUrl}/datatable<br>
            <strong>Simple Endpoint:</strong> ${fullUrl}/datatable-test
        `);
        
        function updateStatus(message, isError = false) {
            $('#status-info').html(`<div class="${isError ? 'error' : 'success'}">${message}</div>`);
            console.log(isError ? '❌' : '✅', message);
        }
        
        function destroyTable() {
            if (oTable) {
                oTable.destroy();
                oTable = null;
                updateStatus('DataTable destroyed');
            }
        }
        
        function testSimpleDataTable() {
            updateStatus('Creating Simple DataTable...');
            destroyTable();
            
            try {
                oTable = $('#datatable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: fullUrl + '/datatable-test',
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        error: function(xhr, error, code) {
                            updateStatus(`AJAX Error: ${xhr.status} - ${error}<br>Response: ${xhr.responseText.substring(0, 200)}...`, true);
                        }
                    },
                    columns: [
                        {data: 'id', title: 'ID'},
                        {data: 'code', title: 'Code'},
                        {data: 'name', title: 'Name'},
                        {data: 'status', title: 'Status'},
                        {data: 'updated_at', title: 'Updated'},
                        {data: 'action', title: 'Action', orderable: false}
                    ]
                });
                
                updateStatus('✅ Simple DataTable created successfully!');
                
            } catch(error) {
                updateStatus(`JavaScript Error: ${error.message}`, true);
            }
        }
        
        function testOriginalDataTable() {
            updateStatus('Creating Original DataTable...');
            destroyTable();
            
            try {
                oTable = $('#datatable').DataTable({
                    searching: false,
                    ordering: false,
                    lengthChange: false,
                    pageLength: 20,
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: fullUrl + '/datatable',
                        type: 'POST',
                        data: function (d) {
                            d._token = $('meta[name="csrf-token"]').attr('content');
                            d.Like = {};
                            return d;
                        },
                        error: function(xhr, error, code) {
                            updateStatus(`AJAX Error: ${xhr.status} - ${error}<br>Response: ${xhr.responseText.substring(0, 200)}...`, true);
                        }
                    },
                    columns: [
                        {data: 'DT_RowIndex', title: '#'},
                        {data: 'image', title: 'รหัสทัวร์'},
                        {data: 'name', title: 'ชื่อ'},
                        {data: 'country', title: 'ประเทศ'},
                        {data: 'period', title: 'Period'},
                        {data: 'price', title: 'ราคา'},
                        {data: 'status', title: 'สถานะ'},
                        {data: 'tab_status', title: 'สถานะจัดการ'},
                        {data: 'updated_at', title: 'วันที่อัพเดท'},
                        {data: 'action', title: 'จัดการ', orderable: false}
                    ]
                });
                
                updateStatus('✅ Original DataTable created successfully!');
                
            } catch(error) {
                updateStatus(`JavaScript Error: ${error.message}`, true);
            }
        }
        
        // Event handlers
        $('#test-simple').click(testSimpleDataTable);
        $('#test-original').click(testOriginalDataTable);
        $('#destroy-table').click(destroyTable);
        
        // Initialize on page load
        $(document).ready(function() {
            updateStatus('Page loaded - Ready for testing!');
            console.log('✅ Simple Tour Management - Ready!');
        });
        
        console.log('✅ All JavaScript loaded successfully');
    </script>
</body>
</html>