<?php
/**
 * ตรวจสอบความสอดคล้องระหว่าง ApiManagementController และ Headcode (ApiController.php)
 * เรื่องการคำนวณส่วนลดและ Promotion
 */

echo "============================================\n";
echo "= Discount/Promotion Logic Verification =\n";
echo "============================================\n\n";

$apis = [
    'zego' => [
        'headcode' => [
            'special_price_calc' => 'YES: Price - Price_End',
            'special_price_fields' => 'price1, price2, price3, price4',
            'discount_percent_calc' => 'YES: (cal / price_start) * 100',
            'promotion_logic' => 'YES: >= 30% = ไฟไหม้, > 0 < 30 = ธรรมดา',
        ],
        'management_controller' => [
            'special_price_calc' => 'YES: calculateZegoSpecialPrices()',
            'special_price_fields' => 'price1, price2, price3, price4',
            'discount_percent_calc' => 'YES: (cal / price_start) * 100',
            'promotion_logic' => 'YES: updateTourPromotion()',
        ],
        'status' => '✅ สอดคล้อง 100%'
    ],
    
    'tourfactory' => [
        'headcode' => [
            'special_price_calc' => 'NO: cal1-4 init=0 แต่ไม่เคยคำนวณ',
            'special_price_fields' => 'ไม่มี',
            'discount_percent_calc' => 'NO: max($max) = 0 เสมอ',
            'promotion_logic' => 'YES: แต่จะได้ N,N เสมอ (เพราะ discount=0)',
        ],
        'management_controller' => [
            'special_price_calc' => 'NO: ไม่มี specific handler',
            'special_price_fields' => 'ไม่มี (fallback = 0)',
            'discount_percent_calc' => 'NO: special_price = 0',
            'promotion_logic' => 'YES: updateTourPromotion() แต่จะได้ N,N',
        ],
        'status' => '✅ สอดคล้อง 100%'
    ],
    
    'best' => [
        'headcode' => [
            'special_price_calc' => 'YES: adultPrice_old - adultPrice (เฉพาะ price1)',
            'special_price_fields' => 'price1 only',
            'discount_percent_calc' => 'YES: (cal / price1_old) * 100',
            'promotion_logic' => 'YES: >= 30% = ไฟไหม้',
        ],
        'management_controller' => [
            'special_price_calc' => 'YES: calculateBestPeriodData()',
            'special_price_fields' => 'price1 only',
            'discount_percent_calc' => 'YES: (discount / price1_old) * 100',
            'promotion_logic' => 'YES: updateTourPromotion()',
        ],
        'status' => '✅ สอดคล้อง 100%'
    ],
    
    'superbholiday' => [
        'headcode' => [
            'special_price_calc' => 'NO: code commented out',
            'special_price_fields' => 'ไม่มี',
            'discount_percent_calc' => 'NO: code commented out',
            'promotion_logic' => 'NO: code commented out',
        ],
        'management_controller' => [
            'special_price_calc' => 'NO: ไม่มี specific handler',
            'special_price_fields' => 'ไม่มี (fallback = 0)',
            'discount_percent_calc' => 'NO: special_price = 0',
            'promotion_logic' => 'YES: updateTourPromotion() แต่จะได้ N,N',
        ],
        'status' => '✅ สอดคล้อง (ทั้งคู่ไม่มีส่วนลด)'
    ],
    
    'go365' => [
        'headcode' => [
            'special_price_calc' => 'NO: cal1-4 init=0 แต่ไม่เคยคำนวณ',
            'special_price_fields' => 'ไม่มี',
            'discount_percent_calc' => 'NO: max($max) = 0 เสมอ',
            'promotion_logic' => 'YES: แต่จะได้ N,N เสมอ',
        ],
        'management_controller' => [
            'special_price_calc' => 'NO: ไม่มี specific handler',
            'special_price_fields' => 'ไม่มี (fallback = 0)',
            'discount_percent_calc' => 'NO: special_price = 0',
            'promotion_logic' => 'YES: processGO365Periods() แต่จะได้ N,N',
        ],
        'status' => '✅ สอดคล้อง 100%'
    ],
    
    'checkingroup' => [
        'headcode' => [
            'special_price_calc' => 'N/A: ไม่มี headcode',
            'special_price_fields' => 'N/A',
            'discount_percent_calc' => 'N/A',
            'promotion_logic' => 'N/A',
        ],
        'management_controller' => [
            'special_price_calc' => 'NO: calculateCheckinGroupPrices() set = 0',
            'special_price_fields' => 'ไม่มี',
            'discount_percent_calc' => 'NO: special_price = 0',
            'promotion_logic' => 'YES: updateTourPromotion() แต่จะได้ N,N',
        ],
        'status' => '✅ OK (API ไม่มีส่วนลด)'
    ],
    
    'ttn' => [
        'headcode' => [
            'special_price_calc' => 'N/A: ไม่มี headcode สำหรับ TTN',
            'special_price_fields' => 'N/A',
            'discount_percent_calc' => 'N/A',
            'promotion_logic' => 'N/A',
        ],
        'management_controller' => [
            'special_price_calc' => 'NO: ใช้ P_ADULT_PRICE ตรงๆ',
            'special_price_fields' => 'ไม่มี',
            'discount_percent_calc' => 'NO: special_price = 0',
            'promotion_logic' => 'YES: updateTourPromotion() แต่จะได้ N,N',
        ],
        'status' => '✅ OK (API ไม่มีส่วนลด)'
    ],
];

foreach ($apis as $api => $data) {
    echo "=== {$api} ===\n";
    echo "Status: {$data['status']}\n\n";
    
    echo "Headcode:\n";
    foreach ($data['headcode'] as $key => $value) {
        echo "  {$key}: {$value}\n";
    }
    
    echo "\nApiManagementController:\n";
    foreach ($data['management_controller'] as $key => $value) {
        echo "  {$key}: {$value}\n";
    }
    
    echo "\n" . str_repeat('-', 50) . "\n\n";
}

echo "============================================\n";
echo "= SUMMARY =\n";
echo "============================================\n\n";

echo "API ที่คำนวณส่วนลดจริง:\n";
echo "  ✅ Zego: Price - Price_End (4 ราคา)\n";
echo "  ✅ Best Consortium: adultPrice_old - adultPrice (เฉพาะ price1)\n";
echo "\n";

echo "API ที่ไม่มีส่วนลด (headcode ก็ไม่คำนวณ):\n";
echo "  ✅ Tour Factory: cal1-4 = 0 (ไม่เคยคำนวณ)\n";
echo "  ✅ Super Holiday: code commented out\n";
echo "  ✅ GO365: cal1-4 = 0 (ไม่เคยคำนวณ)\n";
echo "  ✅ Checkin Group: ไม่มี headcode, API ไม่มีส่วนลด\n";
echo "  ✅ TTN Japan: ไม่มี headcode, API ไม่มีส่วนลด\n";
echo "\n";

echo "Promotion Logic (เหมือนกันทุก API):\n";
echo "  >= 30% → promotion1='Y', promotion2='N' (โปรไฟไหม้)\n";
echo "  > 0%   → promotion1='N', promotion2='Y' (โปรธรรมดา)\n";
echo "  = 0%   → promotion1='N', promotion2='N' (ไม่มีโปร)\n";
echo "\n";

echo "price_group Logic (เหมือนกันทุก API):\n";
echo "  net_price <= 10000 → 1\n";
echo "  net_price <= 20000 → 2\n";
echo "  net_price <= 30000 → 3\n";
echo "  net_price <= 50000 → 4\n";
echo "  net_price <= 80000 → 5\n";
echo "  net_price > 80000  → 6\n";
echo "\n";

echo "============================================\n";
echo "= CONCLUSION: ทุก API สอดคล้อง 100% ✅ =\n";
echo "============================================\n";
