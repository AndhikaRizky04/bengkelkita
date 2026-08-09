<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use App\Models\VehicleBrand;
use App\Models\VehicleModel;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\ServiceCategory;
use App\Models\Service;
use App\Models\WashPackage;
use App\Models\SparepartCategory;
use App\Models\Sparepart;
use App\Models\OilProduct;
use App\Models\InspectionCategory;
use App\Models\InspectionTemplate;
use App\Models\Queue;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Models\ServiceOrderSparepart;
use App\Models\ServiceOrderOil;
use App\Models\Inspection;
use App\Models\InspectionItem;
use App\Models\ServiceRecommendation;
use App\Models\WashOrder;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        $adminRole = Role::create(['name' => 'admin', 'display_name' => 'Administrator / Kasir', 'description' => 'Akses penuh ke seluruh sistem']);
        $mechanicRole = Role::create(['name' => 'mekanik', 'display_name' => 'Mekanik', 'description' => 'Akses ke antrean dan pengerjaan servis']);
        $kasirRole = Role::create(['name' => 'kasir', 'display_name' => 'Kasir', 'description' => 'Akses ke kasir dan pembayaran']);
        $cuciRole = Role::create(['name' => 'cuci', 'display_name' => 'Petugas Cuci', 'description' => 'Akses ke antrean dan status cuci motor']);

        // 2. Demo Users
        $admin = User::create([
            'name' => 'Budi Santoso (Admin)',
            'email' => 'admin@bengkelkita.test',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        $mechanic1 = User::create([
            'name' => 'Andi Pratama (Mekanik)',
            'email' => 'mekanik@bengkelkita.test',
            'password' => Hash::make('password'),
            'role_id' => $mechanicRole->id,
            'phone' => '081234567891',
            'is_active' => true,
        ]);

        $mechanic2 = User::create([
            'name' => 'Rizky Maulana (Mekanik Senior)',
            'email' => 'mekanik2@bengkelkita.test',
            'password' => Hash::make('password'),
            'role_id' => $mechanicRole->id,
            'phone' => '081234567892',
            'is_active' => true,
        ]);

        $kasir = User::create([
            'name' => 'Citra Dewi (Kasir)',
            'email' => 'kasir@bengkelkita.test',
            'password' => Hash::make('password'),
            'role_id' => $kasirRole->id,
            'phone' => '081234567893',
            'is_active' => true,
        ]);

        $washer = User::create([
            'name' => 'Fajar Hidayat (Petugas Cuci)',
            'email' => 'cuci@bengkelkita.test',
            'password' => Hash::make('password'),
            'role_id' => $cuciRole->id,
            'phone' => '081234567894',
            'is_active' => true,
        ]);

        // 3. Settings
        Setting::create(['key' => 'shop_name', 'value' => 'BengkelKita Motor & Cuci', 'group' => 'general', 'type' => 'string']);
        Setting::create(['key' => 'shop_address', 'value' => 'Jl. Merdeka No. 45, Semarang, Jawa Tengah', 'group' => 'general', 'type' => 'string']);
        Setting::create(['key' => 'shop_phone', 'value' => '0812-3456-7890', 'group' => 'general', 'type' => 'string']);
        Setting::create(['key' => 'shop_hours', 'value' => 'Senin - Sabtu: 08:00 - 17:00 WIB', 'group' => 'general', 'type' => 'string']);
        Setting::create(['key' => 'next_service_km_interval', 'value' => '2500', 'group' => 'service', 'type' => 'number']);

        // 4. Vehicle Brands & Models
        $brandsData = [
            'Honda' => [
                'BeAT' => 'matic', 'BeAT Street' => 'matic', 'Scoopy' => 'matic', 'Vario 125' => 'matic',
                'Vario 160' => 'matic', 'PCX 160' => 'matic', 'ADV 160' => 'matic', 'Genio' => 'matic',
                'Stylo 160' => 'matic', 'Supra X 125' => 'manual', 'Revo' => 'manual', 'CB150R' => 'kopling',
                'CBR150R' => 'kopling', 'CRF150L' => 'kopling'
            ],
            'Yamaha' => [
                'Mio M3' => 'matic', 'Gear 125' => 'matic', 'Fazzio' => 'matic', 'Grand Filano' => 'matic',
                'NMAX' => 'matic', 'Aerox' => 'matic', 'Lexi' => 'matic', 'XMAX' => 'matic',
                'Jupiter Z' => 'manual', 'Vega Force' => 'manual', 'MX King' => 'kopling', 'R15' => 'kopling',
                'MT-15' => 'kopling'
            ],
            'Suzuki' => [
                'Nex II' => 'matic', 'Address' => 'matic', 'Burgman Street' => 'matic', 'Satria F150' => 'kopling',
                'GSX-R150' => 'kopling', 'GSX-S150' => 'kopling'
            ],
            'Kawasaki' => [
                'KLX 150' => 'kopling', 'Ninja 250' => 'kopling', 'Z250' => 'kopling', 'W175' => 'kopling', 'D-Tracker' => 'kopling'
            ]
        ];

        $modelsMap = [];
        foreach ($brandsData as $brandName => $models) {
            $brand = VehicleBrand::create(['name' => $brandName, 'is_active' => true]);
            foreach ($models as $modelName => $type) {
                $model = VehicleModel::create([
                    'vehicle_brand_id' => $brand->id,
                    'name' => $modelName,
                    'type' => $type,
                    'is_active' => true
                ]);
                $modelsMap[$brandName . ' ' . $modelName] = $model;
            }
        }

        // 5. Customers & Vehicles (20 Customers, 30 Vehicles)
        $indonesianNames = [
            ['Andhika Rizky', '081234567001', 'Jl. Pemuda No. 12', 'H 1234 ABC', 'Honda', 'Vario 160', 2022, 12450],
            ['Bagus Setiawan', '081234567002', 'Jl. Gajah Mada No. 45', 'H 5678 XYZ', 'Yamaha', 'NMAX', 2021, 18200],
            ['Dini Lestari', '081234567003', 'Jl. Pandanaran No. 8', 'H 9012 DEF', 'Honda', 'BeAT', 2023, 6500],
            ['Eko Prasetyo', '081234567004', 'Jl. Ahmad Yani No. 88', 'H 3456 GHI', 'Honda', 'PCX 160', 2022, 15300],
            ['Fajar Nugroho', '081234567005', 'Jl. Pahlawan No. 33', 'H 7890 JKL', 'Yamaha', 'Aerox', 2020, 24100],
            ['Gita Gutawa', '081234567006', 'Jl. Diponegoro No. 19', 'H 2345 MNO', 'Honda', 'Scoopy', 2021, 11200],
            ['Heri Susanto', '081234567007', 'Jl. Imam Bonjol No. 56', 'H 6789 PQR', 'Kawasaki', 'KLX 150', 2019, 32000],
            ['Irfan Bachdim', '081234567008', 'Jl. Sultan Agung No. 101', 'H 1122 STU', 'Yamaha', 'R15', 2022, 9800],
            ['Joko Widodo', '081234567009', 'Jl. Mataram No. 77', 'H 3344 VWX', 'Honda', 'Supra X 125', 2018, 45000],
            ['Kurnia Meiga', '081234567010', 'Jl. Siliwangi No. 24', 'H 5566 YZA', 'Suzuki', 'Satria F150', 2020, 21500],
            ['Larasati Putri', '081234567011', 'Jl. Thamrin No. 9', 'H 7788 BCD', 'Yamaha', 'Fazzio', 2023, 4200],
            ['Muhammad Arifin', '081234567012', 'Jl. Majapahit No. 142', 'H 9900 EFG', 'Honda', 'Vario 125', 2019, 28400],
            ['Nanda Pratama', '081234567013', 'Jl. Wolter Monginsidi No. 67', 'H 1230 HIJ', 'Honda', 'ADV 160', 2023, 8100],
            ['Oki Setiana', '081234567014', 'Jl. Fatmawati No. 15', 'H 4560 KLM', 'Yamaha', 'Mio M3', 2018, 38900],
            ['Putri Marino', '081234567015', 'Jl. Sudirman No. 89', 'H 7890 NOP', 'Honda', 'Genio', 2021, 14200],
            ['Rian D\'Masiv', '081234567016', 'Jl. Veteran No. 3', 'H 2341 QRS', 'Kawasaki', 'W175', 2020, 16700],
            ['Siti Badriah', '081234567017', 'Jl. MT Haryono No. 110', 'H 5672 TUV', 'Yamaha', 'Grand Filano', 2023, 5300],
            ['Taufik Hidayat', '081234567018', 'Jl. Dr Cipto No. 52', 'H 8903 WXY', 'Honda', 'CB150R', 2019, 31000],
            ['Utami Dewi', '081234567019', 'Jl. Supriyadi No. 94', 'H 1472 ZAB', 'Suzuki', 'Nex II', 2021, 19400],
            ['Vino G Bastian', '081234567020', 'Jl. Soekarno Hatta No. 200', 'H 3694 CDE', 'Honda', 'Stylo 160', 2024, 2100],
        ];

        $customersList = [];
        $vehiclesList = [];

        foreach ($indonesianNames as $cData) {
            $customer = Customer::create([
                'name' => $cData[0],
                'phone' => $cData[1],
                'address' => $cData[2],
            ]);
            $customersList[] = $customer;

            $modelKey = $cData[4] . ' ' . $cData[5];
            $model = $modelsMap[$modelKey] ?? null;

            $vehicle = Vehicle::create([
                'customer_id' => $customer->id,
                'vehicle_brand_id' => $model?->vehicle_brand_id ?? 1,
                'vehicle_model_id' => $model?->id ?? 1,
                'license_plate' => $cData[3],
                'year' => $cData[6],
                'color' => 'Hitam',
                'last_kilometer' => $cData[7],
            ]);
            $vehiclesList[] = $vehicle;
        }

        // Add 10 additional vehicles for existing customers
        for ($i = 0; $i < 10; $i++) {
            $customer = $customersList[$i];
            $extraVehiclesData = [
                ['H 8811 AAA', 'Honda', 'BeAT Street', 2022, 11000],
                ['H 8822 BBB', 'Yamaha', 'Gear 125', 2021, 16500],
                ['H 8833 CCC', 'Honda', 'CBR150R', 2020, 23000],
                ['H 8844 DDD', 'Yamaha', 'XMAX', 2022, 14000],
                ['H 8855 EEE', 'Kawasaki', 'Ninja 250', 2019, 29000],
                ['H 8866 FFF', 'Suzuki', 'Address', 2020, 18000],
                ['H 8877 GGG', 'Honda', 'Revo', 2017, 52000],
                ['H 8888 HHH', 'Yamaha', 'Lexi', 2021, 17200],
                ['H 8899 III', 'Honda', 'CRF150L', 2022, 9500],
                ['H 8800 JJJ', 'Suzuki', 'Burgman Street', 2023, 4800],
            ];

            $evData = $extraVehiclesData[$i];
            $modelKey = $evData[1] . ' ' . $evData[2];
            $model = $modelsMap[$modelKey] ?? null;

            $vehicle = Vehicle::create([
                'customer_id' => $customer->id,
                'vehicle_brand_id' => $model?->vehicle_brand_id ?? 1,
                'vehicle_model_id' => $model?->id ?? 1,
                'license_plate' => $evData[0],
                'year' => $evData[3],
                'color' => 'Merah',
                'last_kilometer' => $evData[4],
            ]);
            $vehiclesList[] = $vehicle;
        }

        // 6. Service Categories & Services
        $catServisRutin = ServiceCategory::create(['name' => 'Servis Rutin', 'description' => 'Perawatan rutin berkala', 'sort_order' => 1]);
        $catMesin = ServiceCategory::create(['name' => 'Perbaikan Mesin', 'description' => 'Servis dan perbaikan komponen mesin', 'sort_order' => 2]);
        $catCVT = ServiceCategory::create(['name' => 'Servis CVT', 'description' => 'Pembersihan dan servis transmisi matic', 'sort_order' => 3]);
        $catKelistrikan = ServiceCategory::create(['name' => 'Kelistrikan & Rem', 'description' => 'Perbaikan sistem listrik dan pengereman', 'sort_order' => 4]);

        $servisRingan = Service::create(['service_category_id' => $catServisRutin->id, 'name' => 'Servis Ringan / Tune Up', 'description' => 'Pembersihan karbu/injector, cek kelistrikan, stel rantai, cek rem', 'estimated_duration_minutes' => 45, 'price' => 50000]);
        $servisBerkala = Service::create(['service_category_id' => $catServisRutin->id, 'name' => 'Servis Berkala Lengkap', 'description' => 'Tune up lengkap + pembersihan throttle body + reset ECU', 'estimated_duration_minutes' => 60, 'price' => 75000]);
        $servisCVT = Service::create(['service_category_id' => $catCVT->id, 'name' => 'Servis CVT Matic', 'description' => 'Pembersihan mangkok ganda, roller, v-belt, pemberian grease khusus CVT', 'estimated_duration_minutes' => 45, 'price' => 45000]);
        $gantiOliJasa = Service::create(['service_category_id' => $catServisRutin->id, 'name' => 'Jasa Ganti Oli', 'description' => 'Jasa penggantian oli mesin / gardan', 'estimated_duration_minutes' => 15, 'price' => 15000]);
        $gantiKampasRem = Service::create(['service_category_id' => $catKelistrikan->id, 'name' => 'Ganti Kampas Rem (Depan/Belakang)', 'description' => 'Jasa penggantian dan penyetelan kampas rem', 'estimated_duration_minutes' => 30, 'price' => 25000]);
        $gantiBan = Service::create(['service_category_id' => $catKelistrikan->id, 'name' => 'Ganti Ban (Tubeless/Biasa)', 'description' => 'Jasa bongkar pasang ban luar/dalam', 'estimated_duration_minutes' => 30, 'price' => 20000]);
        $servisKelistrikan = Service::create(['service_category_id' => $catKelistrikan->id, 'name' => 'Servis Kelistrikan & Lampu', 'description' => 'Pemeriksaan kabel, fiting lampu, saklar, aki', 'estimated_duration_minutes' => 45, 'price' => 40000]);
        $overhaulMesin = Service::create(['service_category_id' => $catMesin->id, 'name' => 'Bongkar Mesin / Overhaul', 'description' => 'Turun mesin, penggantian seher, skir klep, paking', 'estimated_duration_minutes' => 240, 'price' => 350000]);

        // 7. Wash Packages
        $washReguler = WashPackage::create(['name' => 'Cuci Reguler', 'description' => 'Cuci bodi luar + semir ban', 'price' => 15000, 'estimated_duration_minutes' => 20, 'sort_order' => 1]);
        $washPremium = WashPackage::create(['name' => 'Cuci Premium', 'description' => 'Cuci bodi + pembersihan kolong mesin + semir ban + wax pengilap', 'price' => 25000, 'estimated_duration_minutes' => 35, 'sort_order' => 2]);
        $washWax = WashPackage::create(['name' => 'Cuci + Wax Protection', 'description' => 'Cuci detil + perlindungan paint protection wax 3M', 'price' => 35000, 'estimated_duration_minutes' => 45, 'sort_order' => 3]);
        $washDetailing = WashPackage::create(['name' => 'Detailing Motor Matik/Bebek', 'description' => 'Pembersihan kerak mesin, polishing bodi, dressing plastik kasar', 'price' => 75000, 'estimated_duration_minutes' => 90, 'sort_order' => 4]);

        // 8. Sparepart Categories & Spareparts (30+ items)
        $catRem = SparepartCategory::create(['name' => 'Kampas Rem & Pengereman']);
        $catMesinParts = SparepartCategory::create(['name' => 'Komponen Mesin & Busi']);
        $catFilter = SparepartCategory::create(['name' => 'Filter & Udara']);
        $catCVTParts = SparepartCategory::create(['name' => 'V-Belt & Roller CVT']);
        $catBan = SparepartCategory::create(['name' => 'Ban & Roda']);
        $catAki = SparepartCategory::create(['name' => 'Aki & Kelistrikan']);
        $catCairan = SparepartCategory::create(['name' => 'Cairan & Coolant']);

        $sparepartsData = [
            ['KMP-001', 'Kampas Rem Depan Honda Vario/BeAT', 'AHM', $catRem->id, 35000, 50000, 15, 5, 'pcs'],
            ['KMP-002', 'Kampas Rem Belakang BeAT/Vario', 'AHM', $catRem->id, 40000, 55000, 12, 5, 'pcs'],
            ['KMP-003', 'Kampas Rem Depan NMAX/Aerox', 'Yamalube', $catRem->id, 45000, 65000, 10, 4, 'pcs'],
            ['KMP-004', 'Kampas Rem Belakang NMAX', 'Yamalube', $catRem->id, 50000, 70000, 8, 3, 'pcs'],
            ['BSI-001', 'Busi CPR9EA-9 (Vario 125/160, NMAX)', 'NGK', $catMesinParts->id, 18000, 25000, 25, 10, 'pcs'],
            ['BSI-002', 'Busi CR6HSA (Mio, BeAT Karbu)', 'NGK', $catMesinParts->id, 15000, 22000, 20, 8, 'pcs'],
            ['BSI-003', 'Busi Iridium CPR9EAIX-9', 'NGK', $catMesinParts->id, 75000, 100000, 6, 2, 'pcs'],
            ['FLT-001', 'Filter Udara Vario 125/150', 'AHM', $catFilter->id, 38000, 52000, 14, 5, 'pcs'],
            ['FLT-002', 'Filter Udara BeAT FI / Scoopy FI', 'AHM', $catFilter->id, 32000, 45000, 18, 5, 'pcs'],
            ['FLT-003', 'Filter Udara NMAX Old / New', 'Yamalube', $catFilter->id, 42000, 58000, 11, 4, 'pcs'],
            ['VBT-001', 'V-Belt Set Roller Vario 125', 'AHM', $catCVTParts->id, 110000, 150000, 8, 3, 'set'],
            ['VBT-002', 'V-Belt Set Roller BeAT FI', 'AHM', $catCVTParts->id, 95000, 130000, 10, 3, 'set'],
            ['VBT-003', 'V-Belt Set Roller NMAX', 'Yamalube', $catCVTParts->id, 125000, 170000, 6, 2, 'set'],
            ['RLR-001', 'Roller Standard Vario 125 (18gr)', 'AHM', $catCVTParts->id, 30000, 45000, 12, 4, 'set'],
            ['RLR-002', 'Roller Standard NMAX (13gr)', 'Yamalube', $catCVTParts->id, 35000, 50000, 9, 3, 'set'],
            ['KPG-001', 'Kampas Ganda CVT Vario 125/150', 'AHM', $catCVTParts->id, 90000, 130000, 5, 2, 'pcs'],
            ['AKI-001', 'Aki Dry GTZ5S / YTZ5S (BeAT/Mio/Vario)', 'GS Astra', $catAki->id, 185000, 230000, 8, 3, 'pcs'],
            ['AKI-002', 'Aki Dry GTZ7V (NMAX / Aerox / PCX)', 'GS Astra', $catAki->id, 240000, 290000, 5, 2, 'pcs'],
            ['BAN-001', 'Ban Tubeless 80/90-14 Depan', 'IRC NR73', $catBan->id, 140000, 180000, 6, 2, 'pcs'],
            ['BAN-002', 'Ban Tubeless 90/90-14 Belakang', 'IRC NR73', $catBan->id, 165000, 210000, 6, 2, 'pcs'],
            ['BAN-003', 'Ban Tubeless 110/70-13 Depan NMAX', 'Maxxis Victra', $catBan->id, 210000, 260000, 4, 2, 'pcs'],
            ['BAN-004', 'Ban Tubeless 130/70-13 Belakang NMAX', 'Maxxis Victra', $catBan->id, 260000, 320000, 4, 2, 'pcs'],
            ['CRN-001', 'Minyak Rem DOT 4 (300ml)', 'Prestone', $catCairan->id, 22000, 30000, 15, 5, 'botol'],
            ['CRN-002', 'Air Radiator / Coolant (1L)', 'Pertamina Coolant', $catCairan->id, 20000, 28000, 20, 5, 'botol'],
            ['LMP-001', 'Bohlam Utama H4 12V 35/35W', 'Osram', $catAki->id, 25000, 35000, 15, 5, 'pcs'],
            ['LMP-002', 'Lampu LED Utama H4 T10', 'Autovision', $catAki->id, 65000, 90000, 8, 3, 'pcs'],
            ['RNT-001', 'Rantai Roda Set (Supra X 125 / Revo)', 'Fukuyama', $catBan->id, 90000, 125000, 4, 2, 'set'],
            ['OFI-001', 'Filter Oli Mesin Jupiter MX / Vixion', 'Yamaha', $catFilter->id, 20000, 30000, 12, 4, 'pcs'],
            ['SPG-001', 'Spion Standard Vario Honda', 'AHM', $catBan->id, 35000, 50000, 8, 2, 'pair'],
            ['GRE-001', 'Grease CVT High Temp (10gr)', 'AHM', $catCVTParts->id, 8000, 12000, 30, 10, 'pcs'],
            // Add a low stock item to test alert
            ['KMP-999', 'Kampas Rem Depan KLX 150', 'Kawasaki Genuine', $catRem->id, 65000, 90000, 1, 3, 'pcs'],
        ];

        foreach ($sparepartsData as $sp) {
            Sparepart::create([
                'code' => $sp[0], 'name' => $sp[1], 'brand' => $sp[2],
                'sparepart_category_id' => $sp[3], 'purchase_price' => $sp[4],
                'selling_price' => $sp[5], 'stock' => $sp[6], 'minimum_stock' => $sp[7],
                'unit' => $sp[8], 'is_active' => true
            ]);
        }

        // 9. Oil Products (12 items)
        $oilsData = [
            ['Pertamina', 'Enduro Matic 10W-30 (0.8L)', '10W-30', 'matic', 45000, 25, 'botol', 5],
            ['Pertamina', 'Enduro 4T 20W-50 (1L)', '20W-50', 'manual', 42000, 20, 'botol', 5],
            ['Pertamina', 'Enduro Racing 10W-40 (1L)', '10W-40', 'universal', 55000, 15, 'botol', 4],
            ['Shell', 'Shell Advance AX7 Matic 10W-40 (0.8L)', '10W-40', 'matic', 52000, 18, 'botol', 5],
            ['Shell', 'Shell Advance AX7 10W-40 (1L)', '10W-40', 'universal', 58000, 16, 'botol', 4],
            ['Motul', 'Motul Expert LE Matic 10W-30 (0.8L)', '10W-30', 'matic', 70000, 12, 'botol', 3],
            ['Motul', 'Motul 5100 4T 10W-40 (1L)', '10W-40', 'universal', 120000, 8, 'botol', 2],
            ['AHM Oil', 'AHM Oil MPX 2 Matic (0.8L)', '10W-30', 'matic', 46000, 30, 'botol', 8],
            ['AHM Oil', 'AHM Oil SPX 2 Full Synthetic (0.8L)', '10W-30', 'matic', 62000, 15, 'botol', 4],
            ['Yamalube', 'Yamalube Super Matic 10W-40 (1L)', '10W-40', 'matic', 65000, 20, 'botol', 5],
            ['Yamalube', 'Yamalube Silver 20W-50 (0.8L)', '20W-50', 'manual', 40000, 15, 'botol', 4],
            ['Federal', 'Federal Matic 10W-30 (0.8L)', '10W-30', 'matic', 44000, 22, 'botol', 5],
        ];

        foreach ($oilsData as $oil) {
            OilProduct::create([
                'brand' => $oil[0], 'name' => $oil[1], 'viscosity' => $oil[2],
                'type' => $oil[3], 'price' => $oil[4], 'stock' => $oil[5],
                'unit' => $oil[6], 'minimum_stock' => $oil[7], 'is_active' => true
            ]);
        }

        // 10. Inspection Categories & Templates
        $catInspMesin = InspectionCategory::create(['name' => 'Mesin & Performa', 'sort_order' => 1]);
        $catInspRem = InspectionCategory::create(['name' => 'Sistem Rem & Roda', 'sort_order' => 2]);
        $catInspBan = InspectionCategory::create(['name' => 'Kondisi Ban', 'sort_order' => 3]);
        $catInspListrik = InspectionCategory::create(['name' => 'Kelistrikan & Lampu', 'sort_order' => 4]);
        $catInspCVT = InspectionCategory::create(['name' => 'CVT & Transmisi (Matic)', 'sort_order' => 5]);

        $templates = [
            $catInspMesin->id => ['Kondisi Oli Mesin', 'Suara Mesin', 'Kebocoran Oli/Coolant', 'Respons Gas / Idle', 'Filter Udara'],
            $catInspRem->id => ['Rem Depan (Diskon/Kampas)', 'Rem Belakang', 'Minyak Rem / Kabel Rem'],
            $catInspBan->id => ['Tekanan Ban Depan', 'Tekanan Ban Belakang', 'Ketebalan Ban (Ulir)'],
            $catInspListrik->id => ['Lampu Utama & Sen', 'Lampu Rem & Plat', 'Klakson', 'Kondisi Starter & Aki'],
            $catInspCVT->id => ['V-Belt CVT', 'Roller CVT', 'Kampas Ganda CVT', 'Mangkok & Rumah Roller'],
        ];

        foreach ($templates as $catId => $items) {
            foreach ($items as $idx => $name) {
                InspectionTemplate::create([
                    'inspection_category_id' => $catId,
                    'name' => $name,
                    'sort_order' => $idx + 1
                ]);
            }
        }

        // 11. DEMO DATA FOR ALL 10 SCENARIOS & ACTIVE DASHBOARD
        $today = now()->toDateString();

        // QUEUE 1: A-001 (Skenario 1 - 10 completed cycle) - Finished & Paid
        $q1 = Queue::create([
            'queue_number' => 'A-001',
            'queue_date' => $today,
            'customer_id' => $customersList[0]->id, // Andhika Rizky
            'vehicle_id' => $vehiclesList[0]->id,  // Vario 160
            'service_type' => 'servis_cuci',
            'status' => 'selesai',
            'complaint' => 'Motor terasa bergetar saat kecepatan 40-60 km/jam dan suara mesin agak kasar.',
            'registered_at' => now()->subHours(5),
            'called_at' => now()->subHours(4, 50),
            'completed_at' => now()->subMinutes(30),
        ]);

        $wo1 = ServiceOrder::create([
            'order_number' => 'WO-1001',
            'queue_id' => $q1->id,
            'customer_id' => $q1->customer_id,
            'vehicle_id' => $q1->vehicle_id,
            'mechanic_id' => $mechanic1->id,
            'status' => 'completed',
            'complaint' => $q1->complaint,
            'diagnosis' => 'V-Belt dan Roller CVT sudah haus, kampas rem belakang tipis',
            'mechanic_notes' => 'Servis CVT selesai, oli diganti, kampas rem baru terpasang.',
            'kilometer_in' => 12450,
            'started_at' => now()->subHours(4, 45),
            'completed_at' => now()->subHours(2),
            'total_service_cost' => 75000,
            'total_sparepart_cost' => 55000,
            'total_oil_cost' => 45000,
            'total_wash_cost' => 25000,
            'discount' => 0,
            'grand_total' => 200000,
        ]);

        ServiceOrderItem::create(['service_order_id' => $wo1->id, 'service_id' => $servisBerkala->id, 'price' => 75000, 'quantity' => 1, 'subtotal' => 75000]);
        ServiceOrderSparepart::create(['service_order_id' => $wo1->id, 'sparepart_id' => 2, 'quantity' => 1, 'price' => 55000, 'subtotal' => 55000]); // Kampas rem belakang
        ServiceOrderOil::create(['service_order_id' => $wo1->id, 'oil_product_id' => 1, 'quantity' => 1, 'price' => 45000, 'subtotal' => 45000]); // Enduro Matic

        // Wash order for WO1
        $wash1 = WashOrder::create([
            'queue_id' => $q1->id,
            'service_order_id' => $wo1->id,
            'customer_id' => $q1->customer_id,
            'vehicle_id' => $q1->vehicle_id,
            'wash_package_id' => $washPremium->id,
            'washer_id' => $washer->id,
            'status' => 'selesai',
            'price' => 25000,
            'started_at' => now()->subHours(1, 45),
            'completed_at' => now()->subHour(),
        ]);

        // Invoice 1 & Payment
        $inv1 = Invoice::create([
            'invoice_number' => 'INV-' . date('Ymd') . '-001',
            'service_order_id' => $wo1->id,
            'wash_order_id' => $wash1->id,
            'customer_id' => $q1->customer_id,
            'total_amount' => 200000,
            'discount' => 0,
            'tax' => 0,
            'grand_total' => 200000,
            'status' => 'paid',
            'created_by' => $admin->id,
        ]);

        Payment::create([
            'invoice_id' => $inv1->id,
            'payment_method' => 'qris',
            'amount' => 200000,
            'payment_date' => now()->subMinutes(40),
            'reference_number' => 'QRIS-9928172',
            'status' => 'completed',
            'received_by' => $kasir->id,
        ]);

        // QUEUE 2: A-002 - Siap Diambil (Ready for pickup)
        $q2 = Queue::create([
            'queue_number' => 'A-002',
            'queue_date' => $today,
            'customer_id' => $customersList[1]->id, // Bagus Setiawan
            'vehicle_id' => $vehiclesList[1]->id,  // NMAX
            'service_type' => 'servis',
            'status' => 'siap_diambil',
            'complaint' => 'Ganti oli mesin & oli gardan, sekalian rem depan agak dalam',
            'registered_at' => now()->subHours(4),
            'called_at' => now()->subHours(3, 40),
        ]);

        $wo2 = ServiceOrder::create([
            'order_number' => 'WO-1002',
            'queue_id' => $q2->id,
            'customer_id' => $q2->customer_id,
            'vehicle_id' => $q2->vehicle_id,
            'mechanic_id' => $mechanic2->id,
            'status' => 'ready',
            'complaint' => $q2->complaint,
            'diagnosis' => 'Oli sudah hitam, kampas rem depan habis',
            'mechanic_notes' => 'Oli diganti Shell AX7, kampas rem baru.',
            'kilometer_in' => 18200,
            'started_at' => now()->subHours(3, 30),
            'completed_at' => now()->subMinutes(45),
            'total_service_cost' => 50000,
            'total_sparepart_cost' => 65000,
            'total_oil_cost' => 58000,
            'total_wash_cost' => 0,
            'discount' => 0,
            'grand_total' => 173000,
        ]);

        ServiceOrderItem::create(['service_order_id' => $wo2->id, 'service_id' => $servisRingan->id, 'price' => 50000, 'quantity' => 1, 'subtotal' => 50000]);
        ServiceOrderSparepart::create(['service_order_id' => $wo2->id, 'sparepart_id' => 3, 'quantity' => 1, 'price' => 65000, 'subtotal' => 65000]); // Kampas rem NMAX
        ServiceOrderOil::create(['service_order_id' => $wo2->id, 'oil_product_id' => 5, 'quantity' => 1, 'price' => 58000, 'subtotal' => 58000]); // Shell AX7

        $inv2 = Invoice::create([
            'invoice_number' => 'INV-' . date('Ymd') . '-002',
            'service_order_id' => $wo2->id,
            'customer_id' => $q2->customer_id,
            'total_amount' => 173000,
            'discount' => 0,
            'tax' => 0,
            'grand_total' => 173000,
            'status' => 'paid',
            'created_by' => $kasir->id,
        ]);

        Payment::create([
            'invoice_id' => $inv2->id,
            'payment_method' => 'tunai',
            'amount' => 173000,
            'payment_date' => now()->subMinutes(30),
            'status' => 'completed',
            'received_by' => $kasir->id,
        ]);

        // QUEUE 3: A-003 - Sedang Dikerjakan (In Progress)
        $q3 = Queue::create([
            'queue_number' => 'A-003',
            'queue_date' => $today,
            'customer_id' => $customersList[2]->id, // Dini Lestari
            'vehicle_id' => $vehiclesList[2]->id,  // BeAT
            'service_type' => 'servis',
            'status' => 'pengerjaan',
            'complaint' => 'Servis CVT karena geredek saat tarikan awal',
            'registered_at' => now()->subHours(2),
            'called_at' => now()->subHours(1, 45),
        ]);

        $wo3 = ServiceOrder::create([
            'order_number' => 'WO-1003',
            'queue_id' => $q3->id,
            'customer_id' => $q3->customer_id,
            'vehicle_id' => $q3->vehicle_id,
            'mechanic_id' => $mechanic1->id,
            'status' => 'in_progress',
            'complaint' => $q3->complaint,
            'diagnosis' => 'Mangkok CVT kotor berdebu, roller agak gepeng',
            'kilometer_in' => 6500,
            'started_at' => now()->subHours(1, 30),
            'total_service_cost' => 45000,
            'total_sparepart_cost' => 45000,
            'total_oil_cost' => 46000,
            'grand_total' => 136000,
        ]);

        ServiceOrderItem::create(['service_order_id' => $wo3->id, 'service_id' => $servisCVT->id, 'price' => 45000, 'quantity' => 1, 'subtotal' => 45000]);
        ServiceOrderOil::create(['service_order_id' => $wo3->id, 'oil_product_id' => 8, 'quantity' => 1, 'price' => 46000, 'subtotal' => 46000]); // AHM MPX 2

        // QUEUE 4: A-004 - Pemeriksaan (Inspection phase with recommendation)
        $q4 = Queue::create([
            'queue_number' => 'A-004',
            'queue_date' => $today,
            'customer_id' => $customersList[3]->id, // Eko Prasetyo
            'vehicle_id' => $vehiclesList[3]->id,  // PCX 160
            'service_type' => 'servis_cuci',
            'status' => 'pemeriksaan',
            'complaint' => 'Tarikan mesin terasa berat dan ada bunyi decit di rem depan',
            'registered_at' => now()->subHour(),
            'called_at' => now()->subMinutes(30),
        ]);

        $wo4 = ServiceOrder::create([
            'order_number' => 'WO-1004',
            'queue_id' => $q4->id,
            'customer_id' => $q4->customer_id,
            'vehicle_id' => $q4->vehicle_id,
            'mechanic_id' => $mechanic2->id,
            'status' => 'inspection',
            'complaint' => $q4->complaint,
            'kilometer_in' => 15300,
            'started_at' => now()->subMinutes(25),
        ]);

        // Inspection record for WO4
        $insp4 = Inspection::create([
            'service_order_id' => $wo4->id,
            'mechanic_id' => $mechanic2->id,
            'inspected_at' => now()->subMinutes(20),
            'notes' => 'Kondisi umum lumayan, kampas rem depan tinggal 15%',
        ]);

        InspectionItem::create(['inspection_id' => $insp4->id, 'name' => 'Kondisi Oli Mesin', 'category' => 'Mesin & Performa', 'condition' => 'perlu_diperiksa', 'notes' => 'Oli sudah menghitam']);
        $inspItemRem = InspectionItem::create(['inspection_id' => $insp4->id, 'name' => 'Rem Depan (Diskon/Kampas)', 'category' => 'Sistem Rem & Roda', 'condition' => 'perlu_diganti', 'notes' => 'Kampas rem tipis']);
        InspectionItem::create(['inspection_id' => $insp4->id, 'name' => 'V-Belt CVT', 'category' => 'CVT & Transmisi (Matic)', 'condition' => 'baik']);

        ServiceRecommendation::create([
            'service_order_id' => $wo4->id,
            'inspection_item_id' => $inspItemRem->id,
            'description' => 'Ganti Kampas Rem Depan Original AHM',
            'sparepart_id' => 1,
            'sparepart_price' => 50000,
            'service_price' => 25000,
            'total_price' => 75000,
            'status' => 'pending',
        ]);

        // QUEUE 5 & 6: Menunggu (Waiting in queue)
        Queue::create([
            'queue_number' => 'A-005',
            'queue_date' => $today,
            'customer_id' => $customersList[4]->id, // Fajar Nugroho
            'vehicle_id' => $vehiclesList[4]->id,  // Aerox
            'service_type' => 'servis',
            'status' => 'menunggu',
            'complaint' => 'Ganti oli mesin dan filter udara',
            'registered_at' => now()->subMinutes(45),
        ]);

        Queue::create([
            'queue_number' => 'A-006',
            'queue_date' => $today,
            'customer_id' => $customersList[5]->id, // Gita Gutawa
            'vehicle_id' => $vehiclesList[5]->id,  // Scoopy
            'service_type' => 'cuci',
            'status' => 'menunggu',
            'complaint' => 'Cuci Premium',
            'registered_at' => now()->subMinutes(20),
        ]);

        // Separate Wash Orders for Wash Dashboard
        WashOrder::create([
            'customer_id' => $customersList[6]->id,
            'vehicle_id' => $vehiclesList[6]->id,
            'wash_package_id' => $washReguler->id,
            'washer_id' => $washer->id,
            'status' => 'sedang_dicuci',
            'price' => 15000,
            'started_at' => now()->subMinutes(15),
        ]);

        WashOrder::create([
            'customer_id' => $customersList[7]->id,
            'vehicle_id' => $vehiclesList[7]->id,
            'wash_package_id' => $washPremium->id,
            'washer_id' => $washer->id,
            'status' => 'finishing',
            'price' => 25000,
            'started_at' => now()->subMinutes(25),
        ]);
    }
}
