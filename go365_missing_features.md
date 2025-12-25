# GO365 Missing Features (เทียบกับ headcode เดิม)

## ❌ 1. Price Group Calculation
**Headcode (lines 3745-3785):**
```php
// คำนวณ price_group จาก net_price (price - special_price)
$data4 = TourPeriodModel::where(['tour_id'=>$data->id, 'api_type'=>'go365'])->get();
$data5 = $data4->sortBy(function ($item) {
    return $item->price1 - $item->special_price1; // เลือก period ราคาต่ำสุด
})->first();

if($data5){
    $price = $data5->price1;
    $special_price = $data5->special_price1;
    $net_price = $price - $special_price;
    
    if($net_price <= 10000) $price_group = 1;
    else if($net_price <= 20000) $price_group = 2;
    else if($net_price <= 30000) $price_group = 3;
    else if($net_price <= 50000) $price_group = 4;
    else if($net_price <= 80000) $price_group = 5;
    else $price_group = 6;
    
    TourModel::update(['num_day' => $num_day, 'price' => $price, 'price_group' => $price_group]);
}
```

**New Code:** ❌ ไม่มี - ต้องเพิ่มใน processGO365Periods() หรือหลัง save periods

---

## ❌ 2. Promotion Logic (promotion1, promotion2)
**Headcode (lines 3789-3796):**
```php
$max = array(); // เก็บ max discount % จากทุก period
foreach($tour_period as $call2){
    $calmax = max($cal1, $cal2, $cal3, $cal4); // คำนวณ discount %
    array_push($max, $calmax);
}

$maxCheck = max($max);
if($maxCheck >= 30){
    TourModel::update(['promotion1'=>'Y', 'promotion2'=>'N']); // โปรไฟไหม้
}elseif($maxCheck > 0 && $maxCheck < 30){
    TourModel::update(['promotion1'=>'N', 'promotion2'=>'Y']); // โปรธรรมดา
}else{
    TourModel::update(['promotion1'=>'N', 'promotion2'=>'N']); // ไม่มีโปร
}
```

**Note:** ใน headcode ไม่เห็นโค้ดคำนวณ $cal1-$cal4 แต่ logic คือ:
- ถ้า discount >= 30% → promotion1 (ไฟไหม้)
- ถ้า discount < 30% → promotion2 (ธรรมดา)
- ถ้าไม่มี discount → ไม่โปรโมชั่น

**New Code:** ❌ ไม่มี - ต้องเพิ่มใน processGO365Periods()

---

## ❌ 3. Soft Delete Logic (ลบทัวร์/period ที่ไม่มีใน API)
**Headcode (lines 3808-3815):**
```php
// เก็บ IDs ที่ sync ได้
$tour = array(); // tour IDs ที่ sync
$tour_api_id = array(); // api_id ที่ sync
$period = array(); // period IDs ที่ sync
$period_api_id = array(); // period_api_id ที่ sync

// Soft delete ทัวร์ที่ไม่อยู่ในรายการ (หายจาก API)
TourModel::whereNotIn('id',$tour)->whereNotIn('api_id',$tour_api_id)
    ->where('api_type','go365')
    ->update(['deleted_at'=>date('Y-m-d H:i:s')]);

// Restore ทัวร์ที่อยู่ในรายการ (กลับมาใน API)
TourModel::whereIn('id',$tour)->whereIn('api_id',$tour_api_id)
    ->where('api_type','go365')
    ->update(['deleted_at'=>null]);

// เดียวกันสำหรับ periods
TourPeriodModel::whereNotIn('id',$period)->whereNotIn('period_api_id',$period_api_id)
    ->where('api_type','go365')
    ->update(['deleted_at'=>date('Y-m-d H:i:s')]);

TourPeriodModel::whereIn('id',$period)->whereIn('period_api_id',$period_api_id)
    ->where('api_type','go365')
    ->update(['deleted_at'=>null]);
```

**New Code:** ❌ ไม่มี - performMultiStepSync() ไม่มี cleanup logic

---

## ⚠️ 4. Country Mapping Difference
**Headcode:** ใช้ `country_code_2` (iso2) ค้นหา CountryModel
```php
$country = CountryModel::where('iso2', $countryData['country_code_2'])->first();
```

**New Code (lines 2659-2668):** ใช้ `country_id` โดยตรง
```php
if (is_array($apiValue)) {
    foreach ($apiValue as $countryObj) {
        if (isset($countryObj['country_id'])) {
            $countryIds[] = (string)$countryObj['country_id'];
        }
    }
}
```

**⚠️ ความเสี่ยง:** ถ้า API ส่ง `country_id` มาไม่ตรงกับ DB → ประเทศผิด!
**ควรใช้:** `country_code_2` เหมือน headcode เพื่อความแม่นยำ

---

## ✅ 5. ส่วนที่ถูกต้องแล้ว
- Response parsing: `{success, code, data, total_rows}`
- Multi-step: Main `/tours/search` → Detail `/tours/detail/{tour_id}`
- Image handling: `tour_cover_image` + resize
- PDF handling: `tour_file.file_pdf` + Last-Modified
- Airline: `tour_airline.airline_iata`
- Period fields: `period_date`, `period_back`, `period_rate_*`, `period_quota`, `period_available`
- Period status: `period_visible` (1,2→status 1, else→3)
- Price calculation: price2 = sgl - twn (if sgl >= twn)
- check_change fields: ✅

---

## Priority Fixes:

### 🔴 HIGH (ต้องแก้ทันที):
1. **Price Group Calculation** - หน้าบ้านใช้ filter by price group
2. **Soft Delete Logic** - ไม่ลบทัวร์ที่หายจาก API = ข้อมูลเก่าค้าง

### 🟡 MEDIUM:
3. **Promotion Logic** - ใช้แสดง badge โปรโมชั่น
4. **Country iso2 mapping** - ป้องกันประเทศผิด

### 🟢 LOW:
- Logging improvements
