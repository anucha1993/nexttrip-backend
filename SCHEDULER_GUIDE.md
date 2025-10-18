# � NextTrip API Scheduler Guide

ระบบการกำหนดเวลาดึงข้อมูล API อัตโนมัติ สำหรับ NextTrip Backend

## 📋 ตารางเวลาที่ตั้งค่าเริ่มต้นแล้ว

### 🕘 รายวัน (Daily Schedule)
| API Provider | เวลา | Limit | คำอธิบาย |
|-------------|------|-------|-----------|
| GO365 API | 09:30 | 100 records | ซิงค์รายวัน GO365 |
| TTN Japan API | 10:00 | 50 records | อัปเดตรายวัน TTN Japan |
| Zego API | 11:00 | ไม่จำกัด | ซิงค์รายวัน Zego |
| Best Consortium API | 12:00 | ไม่จำกัด | อัปเดตรายวัน Best Consortium |
| Super Holiday API | 14:00 | ไม่จำกัด | ซิงค์รายวัน Super Holiday API |
| Tour Factory API | 14:00 | ไม่จำกัด | ซิงค์รายวัน Tour Factory API |

## 🎯 ภาพรวม

ระบบ API Scheduler ช่วยให้คุณสามารถกำหนดเวลาการดึงข้อมูลจาก API Provider ต่างๆ ได้อย่างอัตโนมัติ โดยไม่ต้องเข้ามากดปุ่ม Sync ด้วยตนเอง

## 🔧 การตั้งค่าตารางเวลา

### 1. เข้าสู่หน้าแก้ไข API Provider
- ไปยัง API Management
- คลิกแก้ไข Provider ที่ต้องการ
- มองหา **หมวด 7: การกำหนดเวลาดึงข้อมูลอัตโนมัติ**

### 2. เพิ่มตารางเวลาใหม่
คลิก **"เพิ่มตารางเวลา"** และกรอกข้อมูล:

#### 📝 ข้อมูลพื้นฐาน
- **ชื่อตารางเวลา**: เช่น "ซิงค์รายวัน", "อัปเดตเช้า"
- **ความถี่**: เลือกจากตัวเลือกต่างๆ

#### ⏰ ตัวเลือกความถี่

##### 1. **ทุกชั่วโมง (Hourly)**
- เลือกช่วงเวลา: 15, 30, 60, 120, 180, 360 นาที
- เหมาะสำหรับข้อมูลที่ต้องอัปเดตบ่อย

##### 2. **ทุกวัน (Daily)** 
- กำหนดเวลาที่ต้องการรัน เช่น 09:00
- เหมาะสำหรับการซิงค์ประจำวัน

##### 3. **ทุกสัปดาห์ (Weekly)**
- เลือกวันที่ต้องการรัน (อาทิตย์-เสาร์)
- กำหนดเวลาที่รัน
- สามารถเลือกหลายวันได้

##### 4. **ทุกเดือน (Monthly)**
- เลือกวันที่ของเดือน (1-31)
- กำหนดเวลาที่รัน

##### 5. **กำหนดเอง (Custom Cron)**
- ใช้ Cron Expression
- รูปแบบ: `minute hour day month day_of_week`
- ตัวอย่าง: `0 9 * * 1-5` = 09:00 ทุกวันจันทร์-ศุกร์

#### ⚙️ ตัวเลือกเพิ่มเติม
- **จำกัดจำนวนข้อมูล**: ระบุจำนวนสูงสุดที่จะซิงค์ต่อครั้ง (เว้นว่างถ้าไม่จำกัด)
- **สถานะ**: เปิด/ปิดใช้งานตารางเวลา

## 🚀 การรันอัตโนมัติด้วย Cron/Task Scheduler

### บน Linux/macOS (Cron)
```bash
# แก้ไข crontab
crontab -e

# เพิ่มบรรทัดนี้เพื่อตรวจสอบทุกนาที
* * * * * cd /path/to/your/project && php artisan api:sync-scheduled >> /var/log/api-scheduler.log 2>&1
```

### บน Windows (Task Scheduler)
1. เปิด Task Scheduler
2. สร้าง Basic Task ใหม่
3. ตั้งค่า Trigger เป็น "Daily" และรันทุกนาที
4. Action: Start a program
5. Program: `php.exe`
6. Arguments: `artisan api:sync-scheduled`
7. Start in: โฟลเดอร์ของโปรเจค

### ตัวอย่าง Batch File สำหรับ Windows
สร้างไฟล์ `run-scheduler.bat`:
```batch
@echo off
cd /d "c:\laragon\www\nexttrip-backend"
php artisan api:sync-scheduled
```

## 🛠️ คำสั่งจัดการผ่าน Artisan

### รันตารางเวลาที่ถึงเวลา
```bash
php artisan api:sync-scheduled
```

### รันตารางเวลาเฉพาะ
```bash
php artisan api:sync-scheduled --schedule-id=1
```

### ดูรายการคำสั่งที่เกี่ยวข้อง
```bash
php artisan list api
```

## 📊 การติดตามและตรวจสอบ

### 1. ในหน้า API Management
- ดูสถานะการรันล่าสุด
- เวลารันครั้งถัดไป
- ข้อผิดพลาดที่เกิดขึ้น (ถ้ามี)

### 2. ใน Log Files
```bash
# ดู log ของ Laravel
tail -f storage/logs/laravel.log

# ดู log เฉพาะ scheduler (ถ้าตั้งค่าแยก)
tail -f /var/log/api-scheduler.log
```

## ⚠️ ข้อควรระวังและคำแนะนำ

### 🔒 ความปลอดภัย
- ตั้งค่า sync_limit เพื่อไม่ให้ดึงข้อมูลมากเกินไป
- ตรวจสอบ API rate limits ของ Provider

### 📈 ประสิทธิภาพ
- หลีกเลี่ยงการตั้งเวลาใกล้เคียงกันมากเกินไป
- ใช้ sync_limit สำหรับ Provider ที่มีข้อมูลมาก

### 🐛 การแก้ปัญหา
- ตรวจสอบ Log ถ้ามีปัญหา
- ทดสอบรันด้วยคำสั่ง manual ก่อน
- ตรวจสอบ Permission ของไฟล์และฐานข้อมูล

## 📋 ตัวอย่างการตั้งค่า

### ตัวอย่างที่ 1: API ข้อมูลทัวร์รายวัน
- **ชื่อ**: "ซิงค์ทัวร์รายวัน"
- **ความถี่**: Daily
- **เวลา**: 06:00
- **Sync Limit**: 500

### ตัวอย่างที่ 2: API ราคาแบบเรียลไทม์
- **ชื่อ**: "อัปเดตราคา"
- **ความถี่**: Hourly  
- **ช่วงเวลา**: 30 นาที
- **Sync Limit**: 100

### ตัวอย่างที่ 3: API รายงานสัปดาห์
- **ชื่อ**: "รายงานสัปดาห์"
- **ความถี่**: Weekly
- **วัน**: จันทร์
- **เวลา**: 08:00
- **Sync Limit**: ไม่จำกัด

## 🆘 การสนับสนุนและช่วยเหลือ

หากพบปัญหาหรือต้องการความช่วยเหลือ:
1. ตรวจสอบ Log files ก่อน
2. ทดสอบรัน manual ด้วย `php artisan api:sync-scheduled --schedule-id=X`
3. ตรวจสอบการตั้งค่า cron/task scheduler

---
*สร้างโดย NextTrip API Management System* 🚀