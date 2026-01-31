<?php

namespace Database\Seeders;

use App\Models\Checkout;
use App\Models\Tool;
use App\Models\User;
use App\Models\Worker;
use App\Services\QRCodeService;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Creating workers...');
        $workers = $this->createWorkers();

        $this->command->info('Creating tools...');
        $tools = $this->createTools();

        $this->command->info('Generating QR codes...');
        $this->generateQRCodes($tools);

        $this->command->info('Creating sample checkouts...');
        $this->createCheckouts($tools, $workers);

        $this->command->info('Database seeded successfully!');

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'jf@marche.be',
        ]);
    }

    private function createWorkers(): array
    {
        $workers = [
            [
                'name' => 'John Smith',
                'badge_number' => 'EMP001',
                'email' => 'john.smith@example.com',
                'phone' => '555-0101',
                'department' => 'Construction',
                'position' => 'Foreman',
                'status' => 'active',
            ],
            [
                'name' => 'Maria Garcia',
                'badge_number' => 'EMP002',
                'email' => 'maria.garcia@example.com',
                'phone' => '555-0102',
                'department' => 'Maintenance',
                'position' => 'Technician',
                'status' => 'active',
            ],
            [
                'name' => 'David Chen',
                'badge_number' => 'EMP003',
                'email' => 'david.chen@example.com',
                'phone' => '555-0103',
                'department' => 'Construction',
                'position' => 'Carpenter',
                'status' => 'active',
            ],
            [
                'name' => 'Sarah Johnson',
                'badge_number' => 'EMP004',
                'email' => 'sarah.johnson@example.com',
                'phone' => '555-0104',
                'department' => 'Electrical',
                'position' => 'Electrician',
                'status' => 'active',
            ],
            [
                'name' => 'Michael Brown',
                'badge_number' => 'EMP005',
                'email' => 'michael.brown@example.com',
                'phone' => '555-0105',
                'department' => 'Plumbing',
                'position' => 'Plumber',
                'status' => 'active',
            ],
        ];

        $created = [];
        foreach ($workers as $worker) {
            $created[] = Worker::create($worker);
        }

        return $created;
    }

    private function createTools(): array
    {
        $tools = [
            // Power Tools
            [
                'name' => 'DeWalt 20V Cordless Drill',
                'code' => 'DRILL-001',
                'category' => 'Power Tools',
                'description' => '20V MAX lithium-ion cordless drill/driver',
                'status' => 'available',
                'location' => 'Tool Room A - Shelf 1',
                'purchase_price' => 199.99,
                'purchase_date' => Carbon::now()->subMonths(6),
                'manufacturer' => 'DeWalt',
                'model' => 'DCD771C2',
            ],
            [
                'name' => 'Milwaukee Circular Saw',
                'code' => 'SAW-001',
                'category' => 'Power Tools',
                'description' => '7-1/4" circular saw with electric brake',
                'status' => 'available',
                'location' => 'Tool Room A - Shelf 1',
                'purchase_price' => 149.99,
                'purchase_date' => Carbon::now()->subMonths(8),
                'manufacturer' => 'Milwaukee',
                'model' => '6390-21',
            ],
            [
                'name' => 'Makita Angle Grinder',
                'code' => 'GRINDER-001',
                'category' => 'Power Tools',
                'description' => '4-1/2" angle grinder with paddle switch',
                'status' => 'available',
                'location' => 'Tool Room A - Shelf 2',
                'purchase_price' => 89.99,
                'purchase_date' => Carbon::now()->subMonths(3),
                'manufacturer' => 'Makita',
                'model' => '9557PBX1',
            ],

            // Hand Tools
            [
                'name' => 'Stanley Hammer',
                'code' => 'HAMMER-001',
                'category' => 'Hand Tools',
                'description' => '16 oz fiberglass handle claw hammer',
                'status' => 'available',
                'location' => 'Tool Room B - Drawer 1',
                'purchase_price' => 29.99,
                'purchase_date' => Carbon::now()->subYear(),
                'manufacturer' => 'Stanley',
                'model' => '51-163',
            ],
            [
                'name' => 'Craftsman Wrench Set',
                'code' => 'WRENCH-001',
                'category' => 'Hand Tools',
                'description' => 'SAE and metric combination wrench set',
                'status' => 'available',
                'location' => 'Tool Room B - Drawer 2',
                'purchase_price' => 79.99,
                'purchase_date' => Carbon::now()->subMonths(9),
                'manufacturer' => 'Craftsman',
                'model' => 'CMMT12024',
            ],

            // Measuring Tools
            [
                'name' => 'Bosch Laser Level',
                'code' => 'LEVEL-001',
                'category' => 'Measuring Tools',
                'description' => 'Self-leveling cross-line laser with mounting bracket',
                'status' => 'available',
                'location' => 'Tool Room A - Shelf 3',
                'purchase_price' => 129.99,
                'purchase_date' => Carbon::now()->subMonths(4),
                'manufacturer' => 'Bosch',
                'model' => 'GLL 30',
            ],
            [
                'name' => 'Stanley Tape Measure',
                'code' => 'TAPE-001',
                'category' => 'Measuring Tools',
                'description' => '25ft PowerLock tape measure',
                'status' => 'available',
                'location' => 'Tool Room B - Drawer 1',
                'purchase_price' => 19.99,
                'purchase_date' => Carbon::now()->subMonths(7),
                'manufacturer' => 'Stanley',
                'model' => '33-525',
            ],

            // Safety Equipment
            [
                'name' => 'Safety Harness',
                'code' => 'HARNESS-001',
                'category' => 'Safety Equipment',
                'description' => 'Full body safety harness with D-ring',
                'status' => 'available',
                'location' => 'Safety Equipment Room',
                'purchase_price' => 159.99,
                'purchase_date' => Carbon::now()->subMonths(2),
                'manufacturer' => '3M',
                'model' => 'Protecta',
            ],

            // Ladders
            [
                'name' => 'Werner Extension Ladder',
                'code' => 'LADDER-001',
                'category' => 'Ladders & Scaffolding',
                'description' => '24ft aluminum extension ladder, 225lb capacity',
                'status' => 'available',
                'location' => 'Outdoor Storage',
                'purchase_price' => 249.99,
                'purchase_date' => Carbon::now()->subMonths(10),
                'manufacturer' => 'Werner',
                'model' => 'D1224-2',
            ],
            [
                'name' => 'Little Giant Step Ladder',
                'code' => 'LADDER-002',
                'category' => 'Ladders & Scaffolding',
                'description' => '6ft aluminum step ladder',
                'status' => 'available',
                'location' => 'Tool Room A',
                'purchase_price' => 129.99,
                'purchase_date' => Carbon::now()->subMonths(5),
                'manufacturer' => 'Little Giant',
                'model' => 'King Kombo',
            ],
        ];

        $created = [];
        foreach ($tools as $tool) {
            $created[] = Tool::create($tool);
        }

        return $created;
    }

    private function generateQRCodes(array $tools): void
    {
        $qrService = app(QRCodeService::class);

        foreach ($tools as $tool) {
            $filename = $qrService->generateForTool($tool);
            $tool->update(['qr_code' => $filename]);
        }
    }

    private function createCheckouts(array $tools, array $workers): void
    {
        // Create a few active checkouts
        Checkout::create([
            'tool_id' => $tools[0]->id, // Drill
            'worker_id' => $workers[0]->id, // John
            'checked_out_at' => Carbon::now()->subDays(2),
            'expected_return_at' => Carbon::now()->addDays(5),
            'condition_out' => 'good',
            'checkout_notes' => 'Need for warehouse project',
        ]);

        $tools[0]->markAsCheckedOut();

        Checkout::create([
            'tool_id' => $tools[2]->id, // Grinder
            'worker_id' => $workers[1]->id, // Maria
            'checked_out_at' => Carbon::now()->subDays(1),
            'expected_return_at' => Carbon::now()->addDays(3),
            'condition_out' => 'excellent',
            'checkout_notes' => 'Metal cutting work',
        ]);

        $tools[2]->markAsCheckedOut();

        // Create an overdue checkout
        Checkout::create([
            'tool_id' => $tools[5]->id, // Laser Level
            'worker_id' => $workers[2]->id, // David
            'checked_out_at' => Carbon::now()->subDays(10),
            'expected_return_at' => Carbon::now()->subDays(3),
            'condition_out' => 'good',
            'checkout_notes' => 'Floor leveling project',
            'is_overdue' => true,
        ]);

        $tools[5]->markAsCheckedOut();

        // Create some returned checkouts (history)
        Checkout::create([
            'tool_id' => $tools[1]->id, // Circular Saw
            'worker_id' => $workers[3]->id, // Sarah
            'checked_out_at' => Carbon::now()->subDays(15),
            'expected_return_at' => Carbon::now()->subDays(8),
            'returned_at' => Carbon::now()->subDays(7),
            'condition_out' => 'good',
            'condition_in' => 'good',
            'checkout_notes' => 'Cutting plywood sheets',
            'return_notes' => 'Returned in good condition',
        ]);

        Checkout::create([
            'tool_id' => $tools[4]->id, // Wrench Set
            'worker_id' => $workers[4]->id, // Michael
            'checked_out_at' => Carbon::now()->subDays(20),
            'expected_return_at' => Carbon::now()->subDays(15),
            'returned_at' => Carbon::now()->subDays(14),
            'condition_out' => 'excellent',
            'condition_in' => 'good',
            'checkout_notes' => 'Pipe fitting work',
            'return_notes' => 'Minor wear, still functional',
        ]);
    }
}
