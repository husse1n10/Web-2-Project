<?php

namespace Database\Seeders;

use App\Models\{
    Appointment,
    Feedback,
    Message,
    Municipality,
    Office,
    RequestDocument,
    RequestStatusLog,
    Service,
    ServiceCategory,
    ServiceRequest,
    User
};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake();
        $faker->seed(20260324);

        $defaultWorkingHours = [
            'mon' => '08:00-16:00',
            'tue' => '08:00-16:00',
            'wed' => '08:00-16:00',
            'thu' => '08:00-16:00',
            'fri' => '08:00-14:00',
            'sat' => 'closed',
            'sun' => 'closed',
        ];

        // Admin account
        User::updateOrCreate(
            ['email' => 'admin@eservices.gov'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // 6 Lebanese municipalities (>= 5 required)
        $municipalityData = [
            ['name' => 'Beirut', 'region' => 'Beirut Governorate'],
            ['name' => 'Baabda', 'region' => 'Mount Lebanon'],
            ['name' => 'Tripoli', 'region' => 'North Governorate'],
            ['name' => 'Saida', 'region' => 'South Governorate'],
            ['name' => 'Zahle', 'region' => 'Bekaa Governorate'],
            ['name' => 'Jounieh', 'region' => 'Keserwan District'],
        ];

        $municipalities = collect($municipalityData)->mapWithKeys(function (array $row) {
            $municipality = Municipality::updateOrCreate(
                ['name' => $row['name']],
                [
                    'region' => $row['region'],
                    'country' => 'Lebanon',
                    'is_active' => true,
                ]
            );

            return [$row['name'] => $municipality];
        });

        // 10 offices (>= 10 required)
        $officeData = [
            [
                'municipality' => 'Beirut',
                'name' => 'Beirut Central Municipal Office',
                'address' => 'Riad El Solh Square, Beirut',
                'latitude' => 33.8938,
                'longitude' => 35.5018,
                'phone' => '+961 1 203 200',
                'email' => 'office@beirut.gov.lb',
                'manager_name' => 'Beirut Office Manager',
                'manager_email' => 'manager@beirut.gov.lb',
            ],
            [
                'municipality' => 'Beirut',
                'name' => 'Ras Beirut Services Office',
                'address' => 'Hamra Main Road, Beirut',
                'latitude' => 33.9002,
                'longitude' => 35.4820,
                'phone' => '+961 1 203 201',
                'email' => 'rasbeirut.office@beirut.gov.lb',
                'manager_name' => 'Ras Beirut Office Manager',
                'manager_email' => 'manager.rasbeirut@beirut.gov.lb',
            ],
            [
                'municipality' => 'Baabda',
                'name' => 'Baabda Municipal Office',
                'address' => 'Old Saida Road, Baabda',
                'latitude' => 33.8338,
                'longitude' => 35.5449,
                'phone' => '+961 5 920 200',
                'email' => 'office@baabda.gov.lb',
                'manager_name' => 'Baabda Office Manager',
                'manager_email' => 'manager@baabda.gov.lb',
            ],
            [
                'municipality' => 'Baabda',
                'name' => 'Hazmieh Citizen Services Office',
                'address' => 'Hazmieh Main Street, Baabda',
                'latitude' => 33.8556,
                'longitude' => 35.5587,
                'phone' => '+961 5 920 201',
                'email' => 'hazmieh.office@baabda.gov.lb',
                'manager_name' => 'Hazmieh Office Manager',
                'manager_email' => 'manager.hazmieh@baabda.gov.lb',
            ],
            [
                'municipality' => 'Tripoli',
                'name' => 'Tripoli Municipal Office',
                'address' => 'Al Tal District, Tripoli',
                'latitude' => 34.4367,
                'longitude' => 35.8497,
                'phone' => '+961 6 420 200',
                'email' => 'office@tripoli.gov.lb',
                'manager_name' => 'Tripoli Office Manager',
                'manager_email' => 'manager@tripoli.gov.lb',
            ],
            [
                'municipality' => 'Tripoli',
                'name' => 'Mina Public Services Office',
                'address' => 'Port Avenue, El Mina, Tripoli',
                'latitude' => 34.4488,
                'longitude' => 35.8173,
                'phone' => '+961 6 420 201',
                'email' => 'mina.office@tripoli.gov.lb',
                'manager_name' => 'El Mina Office Manager',
                'manager_email' => 'manager.mina@tripoli.gov.lb',
            ],
            [
                'municipality' => 'Saida',
                'name' => 'Saida Municipal Office',
                'address' => 'Riad El Solh Street, Saida',
                'latitude' => 33.5606,
                'longitude' => 35.3758,
                'phone' => '+961 7 720 200',
                'email' => 'office@saida.gov.lb',
                'manager_name' => 'Saida Office Manager',
                'manager_email' => 'manager@saida.gov.lb',
            ],
            [
                'municipality' => 'Saida',
                'name' => 'Haret Saida Services Office',
                'address' => 'Municipal Road, Haret Saida',
                'latitude' => 33.5549,
                'longitude' => 35.3881,
                'phone' => '+961 7 720 201',
                'email' => 'haretsaida.office@saida.gov.lb',
                'manager_name' => 'Haret Saida Office Manager',
                'manager_email' => 'manager.haretsaida@saida.gov.lb',
            ],
            [
                'municipality' => 'Zahle',
                'name' => 'Zahle Municipal Office',
                'address' => 'Maarakesh Road, Zahle',
                'latitude' => 33.8467,
                'longitude' => 35.9020,
                'phone' => '+961 8 820 200',
                'email' => 'office@zahle.gov.lb',
                'manager_name' => 'Zahle Office Manager',
                'manager_email' => 'manager@zahle.gov.lb',
            ],
            [
                'municipality' => 'Jounieh',
                'name' => 'Jounieh Municipal Office',
                'address' => 'Fouad Chehab Avenue, Jounieh',
                'latitude' => 33.9808,
                'longitude' => 35.6178,
                'phone' => '+961 9 920 200',
                'email' => 'office@jounieh.gov.lb',
                'manager_name' => 'Jounieh Office Manager',
                'manager_email' => 'manager@jounieh.gov.lb',
            ],
        ];

        $offices = collect();
        $officeManagersByOfficeId = [];

        foreach ($officeData as $row) {
            $municipality = $municipalities[$row['municipality']];

            $office = Office::updateOrCreate(
                ['email' => $row['email']],
                [
                    'municipality_id' => $municipality->id,
                    'name' => $row['name'],
                    'address' => $row['address'],
                    'latitude' => $row['latitude'],
                    'longitude' => $row['longitude'],
                    'phone' => $row['phone'],
                    'working_hours' => $defaultWorkingHours,
                    'is_active' => true,
                ]
            );

            $officeUser = User::updateOrCreate(
                ['email' => $row['manager_email']],
                [
                    'name' => $row['manager_name'],
                    'password' => Hash::make('password'),
                    'role' => 'office_user',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            $officeUser->offices()->syncWithoutDetaching([$office->id => ['role' => 'manager']]);

            $offices->push($office);
            $officeManagersByOfficeId[$office->id] = $officeUser->id;
        }

        // 8 services per office -> 80 services total (>= 20 required)
        $serviceTemplates = [
            [
                'name' => 'Civil Registry',
                'icon' => 'bi-person-vcard',
                'description' => 'Civil status documents and extracts.',
                'services' => [
                    [
                        'name' => 'Birth Certificate Issuance',
                        'price' => 15.00,
                        'duration' => 3,
                        'docs' => ['Hospital birth record', 'Parent ID copies'],
                    ],
                    [
                        'name' => 'Family Civil Extract',
                        'price' => 18.00,
                        'duration' => 4,
                        'docs' => ['Family booklet copy', 'Applicant ID copy'],
                    ],
                ],
            ],
            [
                'name' => 'Licensing & Permits',
                'icon' => 'bi-card-checklist',
                'description' => 'Commercial permits and municipal licenses.',
                'services' => [
                    [
                        'name' => 'Business License Renewal',
                        'price' => 85.00,
                        'duration' => 7,
                        'docs' => ['Previous license', 'Commercial register', 'Lease agreement'],
                    ],
                    [
                        'name' => 'Occupancy Permit Request',
                        'price' => 120.00,
                        'duration' => 9,
                        'docs' => ['Property title deed', 'Engineering plan', 'ID copy'],
                    ],
                ],
            ],
            [
                'name' => 'Property & Urban Planning',
                'icon' => 'bi-house',
                'description' => 'Property and construction related services.',
                'services' => [
                    [
                        'name' => 'Property Ownership Statement',
                        'price' => 35.00,
                        'duration' => 5,
                        'docs' => ['Property number', 'National ID copy'],
                    ],
                    [
                        'name' => 'Building Permit Follow-up',
                        'price' => 165.00,
                        'duration' => 12,
                        'docs' => ['Permit file number', 'Architect approval', 'Tax clearance'],
                    ],
                ],
            ],
            [
                'name' => 'Public Services',
                'icon' => 'bi-tools',
                'description' => 'Service complaints and maintenance requests.',
                'services' => [
                    [
                        'name' => 'Waste Collection Request',
                        'price' => 12.00,
                        'duration' => 2,
                        'docs' => ['Address proof', 'National ID copy'],
                    ],
                    [
                        'name' => 'Street Lighting Maintenance Request',
                        'price' => 10.00,
                        'duration' => 3,
                        'docs' => ['Location details', 'Issue description'],
                    ],
                ],
            ],
        ];

        foreach ($offices as $office) {
            foreach ($serviceTemplates as $template) {
                $category = ServiceCategory::updateOrCreate(
                    ['office_id' => $office->id, 'name' => $template['name']],
                    [
                        'icon' => $template['icon'],
                        'description' => $template['description'],
                    ]
                );

                foreach ($template['services'] as $svc) {
                    Service::updateOrCreate(
                        ['office_id' => $office->id, 'name' => $svc['name']],
                        [
                            'category_id' => $category->id,
                            'description' => "{$svc['name']} for {$office->name}.",
                            'price' => $svc['price'],
                            'currency' => 'USD',
                            'estimated_duration_days' => $svc['duration'],
                            'required_documents' => $svc['docs'],
                            'is_active' => true,
                        ]
                    );
                }
            }
        }

        // 50 citizens (>= 50 required). Keep citizen1..5 for existing test credentials.
        $firstNames = ['Ali', 'Nour', 'Rami', 'Maya', 'Zeinab', 'Karim', 'Mira', 'Jad', 'Yara', 'Omar', 'Lina', 'Fadi'];
        $lastNames = ['Khoury', 'Haddad', 'Nasser', 'Younes', 'Farah', 'Aoun', 'Mansour', 'Hobeika', 'Mikhael', 'Issa'];

        for ($i = 1; $i <= 50; $i++) {
            $name = $i <= 5
                ? "Test Citizen {$i}"
                : $faker->randomElement($firstNames) . ' ' . $faker->randomElement($lastNames);

            $idDocumentPath = "seed/id_documents/citizen_{$i}.pdf";
            Storage::disk('private')->put($idDocumentPath, "%PDF-1.4\n% Seed National ID placeholder for citizen {$i}\n");

            User::updateOrCreate(
                ['email' => "citizen{$i}@test.com"],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'role' => 'citizen',
                    'phone' => '+961 70 ' . str_pad((string) $faker->numberBetween(100000, 999999), 6, '0', STR_PAD_LEFT),
                    'national_id' => 'LB-' . str_pad((string) (100000000 + $i), 9, '0', STR_PAD_LEFT),
                    'id_document' => $idDocumentPath,
                    'citizen_verification_status' => 'approved',
                    'citizen_verification_notes' => null,
                    'citizen_verified_at' => now(),
                    'citizen_verified_by' => null,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }

        $citizens = User::where('role', 'citizen')->get();
        $services = Service::with('office')->get();

        $statusPool = [
            'pending', 'pending', 'pending', 'pending',
            'in_review', 'in_review', 'in_review',
            'missing_documents', 'approved', 'approved',
            'rejected', 'completed', 'completed',
        ];

        // 120 sample requests with related docs, logs, messages, appointments, feedback
        for ($i = 0; $i < 120; $i++) {
            $citizen = $citizens->random();
            $service = $services->random();
            $office = $service->office;
            $managerId = $officeManagersByOfficeId[$office->id] ?? User::where('role', 'office_user')->value('id');
            $status = $faker->randomElement($statusPool);

            $paidStatuses = ['approved', 'completed', 'in_review'];
            $isPaid = in_array($status, $paidStatuses, true) ? $faker->boolean(70) : false;
            if ($status === 'completed') {
                $isPaid = true;
            }

            $paymentStatus = $isPaid ? 'paid' : 'unpaid';
            $paymentMethod = $isPaid ? $faker->randomElement(['card', 'crypto']) : null;
            $transactionId = $isPaid ? 'TXN-' . strtoupper(Str::random(10)) : null;

            $createdAt = now()->subDays($faker->numberBetween(1, 240));
            $updatedAt = (clone $createdAt)->addDays($faker->numberBetween(0, 20));
            $completedAt = $status === 'completed' ? (clone $updatedAt) : null;

            $request = ServiceRequest::create([
                'reference_number' => sprintf(
                    'SRQ-%s-%05d-%s',
                    now()->year,
                    $i + 1,
                    strtoupper(Str::random(4))
                ),
                'citizen_id' => $citizen->id,
                'service_id' => $service->id,
                'office_id' => $office->id,
                'status' => $status,
                'notes' => $faker->sentence(10),
                'office_notes' => $status === 'pending' ? null : $faker->sentence(8),
                'qr_code' => 'qrcodes/' . strtolower($service->id . '-' . Str::random(8)) . '.svg',
                'amount_paid' => $isPaid ? $service->price : null,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'transaction_id' => $transactionId,
                'completed_at' => $completedAt,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ]);

            RequestStatusLog::create([
                'service_request_id' => $request->id,
                'changed_by' => $managerId,
                'from_status' => 'pending',
                'to_status' => $status,
                'comment' => 'Seeded status update.',
                'created_at' => $updatedAt,
                'updated_at' => $updatedAt,
            ]);

            $requiredDocs = $service->required_documents ?? ['National ID copy'];
            $docsToCreate = min(count($requiredDocs), $faker->numberBetween(1, 2));
            for ($docIndex = 0; $docIndex < $docsToCreate; $docIndex++) {
                RequestDocument::create([
                    'service_request_id' => $request->id,
                    'file_path' => "seed/request_documents/{$request->id}/doc_" . ($docIndex + 1) . '.pdf',
                    'original_name' => 'document_' . ($docIndex + 1) . '.pdf',
                    'document_type' => $requiredDocs[$docIndex] ?? 'supporting_document',
                    'uploaded_by' => 'citizen',
                ]);
            }

            if ($faker->boolean(50)) {
                Message::create([
                    'service_request_id' => $request->id,
                    'sender_id' => $citizen->id,
                    'body' => 'Kindly check my application status.',
                    'created_at' => (clone $updatedAt)->subHours(6),
                    'updated_at' => (clone $updatedAt)->subHours(6),
                ]);

                Message::create([
                    'service_request_id' => $request->id,
                    'sender_id' => $managerId,
                    'body' => $status === 'missing_documents'
                        ? 'Please upload missing documents.'
                        : 'Your request is under processing.',
                    'read_at' => $updatedAt,
                    'created_at' => (clone $updatedAt)->subHours(2),
                    'updated_at' => (clone $updatedAt)->subHours(2),
                ]);
            }

            if (in_array($status, ['approved', 'completed', 'in_review'], true) && $faker->boolean(45)) {
                Appointment::create([
                    'citizen_id' => $citizen->id,
                    'office_id' => $office->id,
                    'service_request_id' => $request->id,
                    'appointment_date' => now()->addDays($faker->numberBetween(1, 25))->toDateString(),
                    'appointment_time' => sprintf('%02d:00:00', $faker->numberBetween(9, 14)),
                    'status' => $faker->randomElement(['scheduled', 'confirmed']),
                    'notes' => 'Bring original supporting documents.',
                ]);
            }

            if ($status === 'completed' && $faker->boolean(55)) {
                Feedback::create([
                    'citizen_id' => $citizen->id,
                    'office_id' => $office->id,
                    'service_request_id' => $request->id,
                    'rating' => $faker->numberBetween(3, 5),
                    'comment' => $faker->randomElement([
                        'Fast and transparent processing.',
                        'Good service and clear communication.',
                        'Request was completed on time.',
                    ]),
                    'reply_is_public' => true,
                    'created_at' => now()->subDays($faker->numberBetween(0, 40)),
                    'updated_at' => now()->subDays($faker->numberBetween(0, 40)),
                ]);
            }
        }

        $this->command->info('Database seeded with realistic Lebanese sample data.');
        $this->command->table(
            ['Entity', 'Count'],
            [
                ['Municipalities', Municipality::count()],
                ['Offices', Office::count()],
                ['Services', Service::count()],
                ['Users (total)', User::count()],
                ['Citizens', User::where('role', 'citizen')->count()],
                ['Office Users', User::where('role', 'office_user')->count()],
                ['Service Requests', ServiceRequest::count()],
                ['Appointments', Appointment::count()],
                ['Feedbacks', Feedback::count()],
            ]
        );

        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Admin', 'admin@eservices.gov', 'password'],
                ['Office Manager', 'manager@beirut.gov.lb', 'password'],
                ['Citizen', 'citizen1@test.com', 'password'],
            ]
        );
    }
}

