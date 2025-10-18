<!DOCTYPE html>
<html lang="en" class="light">
<!-- BEGIN: Head -->
<head>
    <!-- BEGIN: CSS Assets-->
    @include("backend.layout.css")
    <!-- END: CSS Assets-->
    
    <style>
        .field-mapping-row { border: 1px solid #e5e7eb; border-radius: 8px; padding: 15px; margin-bottom: 10px; }
        .add-mapping-btn { background: #f3f4f6; border: 2px dashed #d1d5db; }
        .condition-rule-row { border-left: 4px solid #3b82f6; }
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

<body class="py-5">
    @include("backend.layout.mobile-menu")
    <div class="flex">
        @include("backend.layout.side-menu")
        
        <div class="content">
            @include("backend.layout.topbar")
            
            <div class="intro-y flex items-center mt-8">
                <h2 class="text-lg font-medium mr-auto">เพิ่ม API Provider</h2>
                <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
                    <a href="{{ route('api-management.index') }}" class="btn btn-outline-secondary shadow-md mr-2">กลับ</a>
                </div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger show mb-2 mt-4" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('api-management.store') }}" method="POST" class="mt-5">
                @csrf
                <div class="grid grid-cols-12 gap-6">
                    <!-- Basic Information -->
                    <div class="col-span-12 lg:col-span-8">
                        <div class="intro-y box">
                            <div class="flex items-center p-5 border-b border-slate-200/60">
                                <h2 class="font-medium text-base mr-auto">ข้อมูลพื้นฐาน</h2>
                            </div>
                            <div class="p-5">
                                <div class="grid grid-cols-12 gap-4 gap-y-3">
                                    <div class="col-span-12 sm:col-span-6">
                                        <label for="name" class="form-label">ชื่อ API Provider *</label>
                                        <input id="name" name="name" type="text" class="form-control" placeholder="เช่น Zego API" value="{{ old('name') }}" required>
                                    </div>
                                    <div class="col-span-12 sm:col-span-6">
                                        <label for="code" class="form-label">รหัส API *</label>
                                        <input id="code" name="code" type="text" class="form-control" placeholder="เช่น zego" value="{{ old('code') }}" required>
                                        <div class="form-help">รหัสนี้จะใช้ในการระบุ API (ต้องไม่ซ้ำ)</div>
                                    </div>
                                    <div class="col-span-12">
                                        <label for="url" class="form-label">API URL (หลัก) *</label>
                                        <input id="url" name="url" type="url" class="form-control" placeholder="https://api.example.com/tours" value="{{ old('url') }}" required>
                                        <div class="form-help">URL หลักสำหรับดึงข้อมูล tours</div>
                                    </div>
                                    
                                    <!-- Multi-step API Configuration -->
                                    <div class="col-span-12">
                                        <div class="form-check mt-4">
                                            <input id="requires_multi_step" name="requires_multi_step" class="form-check-input" type="checkbox" value="1" onchange="toggleMultiStepFields()">
                                            <label class="form-check-label" for="requires_multi_step">
                                                API แบบหลายขั้นตอน (Multi-step API)
                                            </label>
                                            <div class="form-help">เลือกถ้า API ต้องเรียกหลาย endpoint เพื่อดึงข้อมูล periods</div>
                                        </div>
                                    </div>
                                    
                                    <div id="multi-step-fields" class="col-span-12" style="display: none;">
                                        <div class="grid grid-cols-12 gap-4 p-4 bg-slate-50 rounded-lg">
                                            <div class="col-span-12 sm:col-span-6">
                                                <label for="tour_detail_endpoint" class="form-label">Tour Detail Endpoint</label>
                                                <input id="tour_detail_endpoint" name="tour_detail_endpoint" type="text" class="form-control" placeholder="/api/tours/{id}" value="{{ old('tour_detail_endpoint') }}">
                                                <div class="form-help">Endpoint สำหรับดึงรายละเอียด tour (ใช้ {id} สำหรับ parameter)</div>
                                            </div>
                                            <div class="col-span-12 sm:col-span-6">
                                                <label for="period_endpoint" class="form-label">Period Endpoint</label>
                                                <input id="period_endpoint" name="period_endpoint" type="text" class="form-control" placeholder="/api/tours/{id}/periods" value="{{ old('period_endpoint') }}">
                                                <div class="form-help">Endpoint สำหรับดึงข้อมูล periods (ใช้ {id} สำหรับ parameter)</div>
                                            </div>
                                            <div class="col-span-12 sm:col-span-6">
                                                <label for="tour_detail_id_field" class="form-label">Tour ID Field</label>
                                                <input id="tour_detail_id_field" name="url_parameters[tour_detail_id_field]" type="text" class="form-control" placeholder="P_ID" value="{{ old('url_parameters.tour_detail_id_field', 'id') }}">
                                                <div class="form-help">ชื่อฟิลด์ที่เก็บ ID ของ tour</div>
                                            </div>
                                            <div class="col-span-12 sm:col-span-6">
                                                <label for="period_id_field" class="form-label">Period ID Field</label>
                                                <input id="period_id_field" name="url_parameters[period_id_field]" type="text" class="form-control" placeholder="P_ID" value="{{ old('url_parameters.period_id_field', 'id') }}">
                                                <div class="form-help">ชื่อฟิลด์ที่ใช้ในการเรียก period endpoint</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-span-12">
                                        <label for="description" class="form-label">คำอธิบาย</label>
                                        <textarea id="description" name="description" class="form-control" rows="3" placeholder="คำอธิบายเกี่ยวกับ API นี้">{{ old('description') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- API Headers -->
                        <div class="intro-y box mt-5">
                            <div class="flex items-center p-5 border-b border-slate-200/60">
                                <h2 class="font-medium text-base mr-auto">API Headers</h2>
                                <button type="button" onclick="addHeader()" class="btn btn-outline-primary btn-sm">เพิ่ม Header</button>
                            </div>
                            <div class="p-5">
                                <div id="headers-container">
                                    <div class="grid grid-cols-12 gap-4 mb-3 header-row">
                                        <div class="col-span-5">
                                            <input type="text" name="headers[0][key]" class="form-control" placeholder="Header Name" value="Content-Type">
                                        </div>
                                        <div class="col-span-6">
                                            <input type="text" name="headers[0][value]" class="form-control" placeholder="Header Value" value="application/json">
                                        </div>
                                        <div class="col-span-1">
                                            <button type="button" onclick="removeHeader(this)" class="btn btn-outline-danger btn-sm w-full">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Field Mappings -->
                        <div class="intro-y box mt-5">
                            <div class="flex items-center p-5 border-b border-slate-200/60">
                                <h2 class="font-medium text-base mr-auto">Field Mappings</h2>
                                <button type="button" onclick="addFieldMapping()" class="btn btn-outline-primary btn-sm">เพิ่ม Mapping</button>
                            </div>
                            <div class="p-5">
                                <div id="field-mappings-container">
                                    <!-- Default mappings will be added by JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="col-span-12 lg:col-span-4">
                        <div class="intro-y box">
                            <div class="flex items-center p-5 border-b border-slate-200/60">
                                <h2 class="font-medium text-base mr-auto">การตั้งค่า</h2>
                            </div>
                            <div class="p-5">
                                <div class="mb-3">
                                    <label for="status" class="form-label">สถานะ</label>
                                    <select id="status" name="status" class="form-select">
                                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="intro-y box mt-5">
                            <div class="p-5">
                                <button type="submit" class="btn btn-primary w-full">บันทึก API Provider</button>
                                <a href="{{ route('api-management.index') }}" class="btn btn-outline-secondary w-full mt-3">ยกเลิก</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript for Create API Provider -->
    
    <script>
        let headerIndex = 1;
        let mappingIndex = 0;

        function addHeader() {
            const container = document.getElementById('headers-container');
            const newRow = document.createElement('div');
            newRow.className = 'grid grid-cols-12 gap-4 mb-3 header-row';
            newRow.innerHTML = `
                <div class="col-span-5">
                    <input type="text" name="headers[${headerIndex}][key]" class="form-control" placeholder="Header Name">
                </div>
                <div class="col-span-6">
                    <input type="text" name="headers[${headerIndex}][value]" class="form-control" placeholder="Header Value">
                </div>
                <div class="col-span-1">
                    <button type="button" onclick="removeHeader(this)" class="btn btn-outline-danger btn-sm w-full">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
            `;
            container.appendChild(newRow);
            headerIndex++;
            
            // Re-initialize Lucide icons
            lucide.createIcons();
        }

        function removeHeader(button) {
            const row = button.closest('.header-row');
            if (document.querySelectorAll('.header-row').length > 1) {
                row.remove();
            }
        }

        function addFieldMapping() {
            const container = document.getElementById('field-mappings-container');
            const newMapping = document.createElement('div');
            newMapping.className = 'field-mapping-row';
            newMapping.innerHTML = `
                <div class="grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12 sm:col-span-3">
                        <label class="form-label">Field Type</label>
                        <select name="field_mappings[${mappingIndex}][field_type]" class="form-select">
                            <option value="tour">Tour</option>
                            <option value="period">Period</option>
                        </select>
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <label class="form-label">Local Field</label>
                        <input type="text" name="field_mappings[${mappingIndex}][local_field]" class="form-control" placeholder="เช่น name">
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <label class="form-label">API Field</label>
                        <input type="text" name="field_mappings[${mappingIndex}][api_field]" class="form-control" placeholder="เช่น ProductName">
                    </div>
                    <div class="col-span-12 sm:col-span-1">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" onclick="removeFieldMapping(this)" class="btn btn-outline-danger btn-sm w-full">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <label class="form-label">Data Type</label>
                        <select name="field_mappings[${mappingIndex}][data_type]" class="form-select">
                            <option value="string">String</option>
                            <option value="integer">Integer</option>
                            <option value="decimal">Decimal</option>
                            <option value="boolean">Boolean</option>
                            <option value="json">JSON</option>
                            <option value="date">Date</option>
                        </select>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">Transformation Rules (JSON)</label>
                        <textarea name="field_mappings[${mappingIndex}][transformation_rules]" class="form-control" rows="2" placeholder='[{"type": "string_replace", "search": "old", "replace": "new"}]'></textarea>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check mt-6">
                            <input id="required_${mappingIndex}" name="field_mappings[${mappingIndex}][is_required]" class="form-check-input" type="checkbox" value="1">
                            <label class="form-check-label" for="required_${mappingIndex}">Required</label>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(newMapping);
            mappingIndex++;
            
            // Re-initialize Lucide icons
            lucide.createIcons();
        }

        function removeFieldMapping(button) {
            const mapping = button.closest('.field-mapping-row');
            mapping.remove();
        }

        // Initialize default field mappings
        document.addEventListener('DOMContentLoaded', function() {
            // Add common tour field mappings
            const defaultTourMappings = [
                {field_type: 'tour', local_field: 'api_id', api_field: 'ProductID', data_type: 'string', required: true},
                {field_type: 'tour', local_field: 'name', api_field: 'ProductName', data_type: 'string', required: true},
                {field_type: 'tour', local_field: 'description', api_field: 'Highlight', data_type: 'string', required: false},
                {field_type: 'tour', local_field: 'image', api_field: 'URLImage', data_type: 'string', required: false},
                {field_type: 'tour', local_field: 'pdf_file', api_field: 'FilePDF', data_type: 'string', required: false}
            ];

            defaultTourMappings.forEach(() => {
                addFieldMapping();
            });

            // Fill default values
            const mappingRows = document.querySelectorAll('.field-mapping-row');
            defaultTourMappings.forEach((mapping, index) => {
                if (mappingRows[index]) {
                    const row = mappingRows[index];
                    row.querySelector('select[name$="[field_type]"]').value = mapping.field_type;
                    row.querySelector('input[name$="[local_field]"]').value = mapping.local_field;
                    row.querySelector('input[name$="[api_field]"]').value = mapping.api_field;
                    row.querySelector('select[name$="[data_type]"]').value = mapping.data_type;
                    if (mapping.required) {
                        row.querySelector('input[name$="[is_required]"]').checked = true;
                    }
                }
            });
        });
        
        // Toggle multi-step API fields
        function toggleMultiStepFields() {
            const checkbox = document.getElementById('requires_multi_step');
            const fields = document.getElementById('multi-step-fields');
            
            if (checkbox.checked) {
                fields.style.display = 'block';
            } else {
                fields.style.display = 'none';
                // Clear values when disabled
                fields.querySelectorAll('input').forEach(input => {
                    if (input.type !== 'text') return;
                    input.value = '';
                });
            }
        }
    </script>

        </div>
        <!-- END: Content -->
    </div>
    
    <!-- BEGIN: JS Assets-->
    @include("backend.layout.script")
    <!-- END: JS Assets-->
</body>
</html>