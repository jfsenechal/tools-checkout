<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Checkout;
use App\Models\Tool;
use App\Models\User;
use App\Models\Worker;
use App\Services\QRCodeService;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

final class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Creating workers...');
        $workers = Worker::factory()->count(5)->create()->all();

        $this->command->info('Creating tools...');
        $tools = $this->createTools();

        $this->command->info('Generating QR codes...');
        $this->generateQRCodes($tools);
        $this->generateQRCodes($workers);

        $this->command->info('Creating sample checkouts...');
        $this->createCheckouts($tools, $workers);

        $this->command->info('Database seeded successfully!');

        User::factory()->create([
            'name' => config('app.default_user.name'),
            'username' => config('app.default_user.name'),
            'email' => config('app.default_user.email'),
            'password' => bcrypt(config('app.default_user.password')),
        ]);
    }

    private function createTools(): array
    {
        $tools = [
            // Outils électriques
            [
                'name' => 'Perceuse sans fil DeWalt 20V',
                'category' => 'Outils électriques',
                'description' => 'Perceuse/visseuse sans fil lithium-ion 20V MAX',
                'status' => 'available',
                'manufacturer' => 'DeWalt',
                'model' => 'DCD771C2',
            ],
            [
                'name' => 'Scie circulaire Milwaukee',
                'category' => 'Outils électriques',
                'description' => 'Scie circulaire 7-1/4" avec frein électrique',
                'status' => 'available',
                'manufacturer' => 'Milwaukee',
                'model' => '6390-21',
            ],
            [
                'name' => 'Meuleuse d\'angle Makita',
                'category' => 'Outils électriques',
                'description' => 'Meuleuse d\'angle 4-1/2" avec interrupteur à palette',
                'status' => 'available',
                'manufacturer' => 'Makita',
                'model' => '9557PBX1',
            ],

            // Outils à main
            [
                'name' => 'Marteau Stanley',
                'category' => 'Outils à main',
                'description' => 'Marteau arrache-clou manche fibre de verre 16 oz',
                'status' => 'available',
                'manufacturer' => 'Stanley',
                'model' => '51-163',
            ],
            [
                'name' => 'Jeu de clés Craftsman',
                'category' => 'Outils à main',
                'description' => 'Jeu de clés mixtes SAE et métriques',
                'status' => 'available',
                'manufacturer' => 'Craftsman',
                'model' => 'CMMT12024',
            ],

            // Outils de mesure
            [
                'name' => 'Niveau laser Bosch',
                'category' => 'Outils de mesure',
                'description' => 'Laser à lignes croisées auto-nivelant avec support de montage',
                'status' => 'available',
                'manufacturer' => 'Bosch',
                'model' => 'GLL 30',
            ],
            [
                'name' => 'Ruban à mesurer Stanley',
                'category' => 'Outils de mesure',
                'description' => 'Ruban à mesurer PowerLock 7,5 m',
                'status' => 'available',
                'manufacturer' => 'Stanley',
                'model' => '33-525',
            ],

            // Équipement de sécurité
            [
                'name' => 'Harnais de sécurité',
                'category' => 'Équipement de sécurité',
                'description' => 'Harnais de sécurité intégral avec anneau en D',
                'status' => 'available',
                'manufacturer' => '3M',
                'model' => 'Protecta',
            ],

            // Échelles et échafaudages
            [
                'name' => 'Échelle coulissante Werner',
                'category' => 'Échelles et échafaudages',
                'description' => 'Échelle coulissante aluminium 7,3 m, capacité 100 kg',
                'status' => 'available',
                'manufacturer' => 'Werner',
                'model' => 'D1224-2',
            ],
            [
                'name' => 'Escabeau Little Giant',
                'category' => 'Échelles et échafaudages',
                'description' => 'Escabeau aluminium 1,8 m',
                'status' => 'available',
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

    private function generateQRCodes(array $records): void
    {
        $qrService = app(QRCodeService::class);

        foreach ($records as $record) {
            $filename = match (true) {
                $record instanceof Tool => $qrService->generateForTool($record),
                $record instanceof Worker => $qrService->generateForWorker($record),
            };
            $record->update(['qr_code' => $filename]);
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
            'checkout_notes' => 'Besoin pour le projet d\'entrepôt',
        ]);

        $tools[0]->markAsCheckedOut();

        Checkout::create([
            'tool_id' => $tools[2]->id, // Grinder
            'worker_id' => $workers[1]->id, // Maria
            'checked_out_at' => Carbon::now()->subDays(1),
            'expected_return_at' => Carbon::now()->addDays(3),
            'condition_out' => 'excellent',
            'checkout_notes' => 'Travaux de découpe de métal',
        ]);

        $tools[2]->markAsCheckedOut();

        // Create an overdue checkout
        Checkout::create([
            'tool_id' => $tools[5]->id, // Laser Level
            'worker_id' => $workers[2]->id, // David
            'checked_out_at' => Carbon::now()->subDays(10),
            'expected_return_at' => Carbon::now()->subDays(3),
            'condition_out' => 'good',
            'checkout_notes' => 'Projet de nivellement de sol',
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
            'checkout_notes' => 'Découpe de panneaux de contreplaqué',
            'return_notes' => 'Retourné en bon état',
        ]);

        Checkout::create([
            'tool_id' => $tools[4]->id, // Wrench Set
            'worker_id' => $workers[4]->id, // Michael
            'checked_out_at' => Carbon::now()->subDays(20),
            'expected_return_at' => Carbon::now()->subDays(15),
            'returned_at' => Carbon::now()->subDays(14),
            'condition_out' => 'excellent',
            'condition_in' => 'good',
            'checkout_notes' => 'Travaux de plomberie',
            'return_notes' => 'Usure mineure, toujours fonctionnel',
        ]);
    }
}
