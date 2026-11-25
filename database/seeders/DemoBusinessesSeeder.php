<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessSource;
use App\Models\ExtractedFact;
use App\Models\FactConfirmation;
use App\Models\Publication;
use App\Models\UploadedFile;
use App\Models\User;
use App\Models\Website;
use App\Models\WebsiteSection;
use App\Models\WebsiteVersion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoBusinessesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Generates rich, authentic demonstration data for two businesses:
     * 1. Patil Traditional Spices & Foods (Kolhapur)
     * 2. Dr. Kulkarni Ayurvedic Wellness Clinic (Pune)
     */
    public function run(): void
    {
        // 0. Primary Demo User
        $demoUser = User::firstOrCreate(
            ['email' => 'demo@websites.co.in'],
            [
                'name' => 'Websites.co.in Reviewer',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // 1. BUSINESS 1: Patil Traditional Spices & Foods
        $this->seedPatilSpices($demoUser);

        // 2. BUSINESS 2: Dr. Kulkarni Ayurvedic Wellness Clinic
        $this->seedKulkarniAyurveda($demoUser);
    }

    protected function seedPatilSpices(User $owner): void
    {
        // Create or update business
        $business = Business::updateOrCreate(
            ['name' => 'Patil Traditional Spices & Foods', 'user_id' => $owner->id],
            [
                'category' => 'retail',
                'city' => 'Kolhapur',
                'state' => 'Maharashtra',
                'country' => 'India',
                'address' => '45 Shahupuri 2nd Lane, Kolhapur, Maharashtra 416001',
                'phone' => '+91 94220 54321',
                'email' => 'orders@patilspices.com',
                'description' => 'Authentic Kolhapuri spice blends, kanda lasun masala, and traditional Maharashtrian culinary staples crafted with handpicked ingredients since 1985.',
                'operating_hours' => ['raw' => 'Monday - Saturday: 9:30 AM - 8:30 PM. Closed on Sundays.'],
                'products' => [
                    'Kolhapuri Kanda Lasun Masala (500g)',
                    'Special Mutton & Chicken Masala (250g)',
                    'Traditional Goda Masala (200g)',
                    'Handmade Jowar Bhakri Flour (1kg)',
                    'Pure Byadgi Chilli Powder (500g)',
                    'Hand-pounded Turmeric Powder (250g)',
                ],
                'services' => [
                    'Custom Bulk Spice Grinding for Weddings & Events',
                    'Pan-India Courier Delivery with Vacuum Packaging',
                    'B2B Wholesale Supply to Restaurants & Caterers',
                ],
                'status' => 'verified',
            ]
        );

        // Ingest Document
        $catalogText = <<<TXT
Patil Traditional Spices & Foods
Official Product Catalog & Price List (2026 Edition)
Location: 45 Shahupuri 2nd Lane, Kolhapur, Maharashtra 416001
Direct Phone: +91 94220 54321
Support Email: orders@patilspices.com

Founded in 1985 by Shri Anandrao Patil, our enterprise manufactures authentic Kolhapuri spice blends with zero artificial colors or preservatives.

Key Products:
- Kolhapuri Kanda Lasun Masala (500g) - ₹180: The signature blend of fried onions, garlic, and roasted red chillies.
- Special Mutton & Chicken Masala (250g) - ₹140: A coarse-ground aromatic masala for traditional rassa gravies.
- Traditional Goda Masala (200g) - ₹110: Sweet-aromatic Brahmin-style blend with dagad phool and coconut.
- Premium Jowar Bhakri Flour (1kg) - ₹75: Stone-ground local jowar flour for soft, puffed bhakris.

Store Hours: Monday to Saturday 9:30 AM to 8:30 PM.
FSSAI License: 11518034000421
TXT;

        UploadedFile::updateOrCreate(
            ['business_id' => $business->id, 'original_name' => 'Patil_Spices_Catalog_2026.txt'],
            [
                'file_path' => 'demo/Patil_Spices_Catalog_2026.txt',
                'file_type' => 'txt',
                'file_size' => strlen($catalogText),
                'mime_type' => 'text/plain',
                'extracted_text' => $catalogText,
                'processing_status' => 'completed',
            ]
        );

        // Ingest Web Source
        BusinessSource::updateOrCreate(
            ['business_id' => $business->id, 'url' => 'https://patilspices.example.com'],
            [
                'source_type' => 'website',
                'title' => 'Patil Traditional Spices Official Website Profile',
                'status' => 'crawled',
                'raw_content' => $catalogText,
            ]
        );

        // Extracted Facts
        $facts = [
            ['phone', 'Phone Number', '+91 94220 54321', 'Direct match: +91 94220 54321', 0.98],
            ['email', 'Email Address', 'orders@patilspices.com', 'Official orders email: orders@patilspices.com', 0.99],
            ['address', 'Physical Address', '45 Shahupuri 2nd Lane, Kolhapur, Maharashtra 416001', 'Store address printed on catalog header', 0.95],
            ['operating_hours', 'Operating Hours', 'Monday - Saturday: 9:30 AM - 8:30 PM. Closed on Sundays.', 'Store timings in catalog footer', 0.95],
            ['founded_year', 'Established Year', '1985', 'Founded in 1985 by Shri Anandrao Patil', 0.99],
            ['certifications', 'FSSAI License', 'FSSAI 11518034000421', 'FSSAI License: 11518034000421', 0.99],
        ];

        foreach ($facts as [$key, $name, $val, $evidence, $conf]) {
            ExtractedFact::updateOrCreate(
                ['business_id' => $business->id, 'fact_key' => $key],
                [
                    'source_type' => 'document',
                    'fact_name' => $name,
                    'fact_value' => $val,
                    'evidence' => $evidence,
                    'verification_status' => 'user-confirmed',
                    'confidence_score' => $conf,
                    'is_confirmed' => true,
                    'is_owner_provided' => true,
                    'user_confirmed_value' => $val,
                    'extraction_time' => now(),
                ]
            );

            FactConfirmation::updateOrCreate(
                ['business_id' => $business->id, 'fact_key' => $key],
                [
                    'confirmed_value' => $val,
                    'resolution_type' => 'accepted',
                    'confirmed_by' => $owner->id,
                    'confirmed_at' => now(),
                ]
            );
        }

        // Generate Website
        $website = Website::updateOrCreate(
            ['business_id' => $business->id],
            [
                'user_id' => $owner->id,
                'title' => 'Patil Traditional Spices & Foods — Authentic Kolhapuri Masalas',
                'slug' => 'patil-traditional-spices',
                'design_preset' => 'premium_elegant',
                'primary_color' => '#b45309',
                'secondary_color' => '#1c1917',
                'font_family' => "'Playfair Display', Georgia, serif",
                'meta_description' => 'Authentic handcrafted Kolhapuri masalas, kanda lasun blend, and Maharashtrian culinary staples from Kolhapur since 1985.',
                'is_published' => true,
                'published_at' => now(),
                'current_version' => 1,
                'settings' => [
                    'website_type' => 'retail',
                    'tagline' => 'Handcrafted Authentic Kolhapuri Spices Since 1985',
                    'ai_generated' => true,
                    'generation_mode' => 'gemini_ai',
                    'primary_color' => '#b45309',
                    'secondary_color' => '#1c1917',
                ],
            ]
        );

        // Sections
        $sections = [
            [
                'section_type' => 'hero',
                'order' => 0,
                'content' => [
                    'heading' => 'Authentic Handcrafted Kolhapuri Spices',
                    'subheading' => 'Experience the rich culinary heritage of Kolhapur with time-tested spice formulations crafted with zero preservatives.',
                    'primary_cta_text' => 'Explore Product Catalog',
                    'primary_cta_link' => '#products',
                    'secondary_cta_text' => 'Contact Store',
                    'secondary_cta_link' => '#contact',
                    'badge' => 'Established 1985 • Kolhapur Heritage',
                    'hero_image' => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=1200&q=80',
                ],
            ],
            [
                'section_type' => 'about',
                'order' => 1,
                'content' => [
                    'heading' => 'Our Heritage of Authentic Flavor',
                    'paragraph_1' => 'Founded in 1985 by Shri Anandrao Patil in Shahupuri, Kolhapur, Patil Traditional Spices & Foods has preserved original culinary recipes passed down through generations.',
                    'paragraph_2' => 'Every batch of our renowned Kanda Lasun Masala is slow-roasted in traditional iron vessels, ensuring rich aroma, deep natural color, and unmatched authenticity.',
                    'stats' => [
                        ['number' => '40+', 'label' => 'Years of Heritage'],
                        ['number' => '100%', 'label' => 'Pure & Preservative-Free'],
                        ['number' => '50,000+', 'label' => 'Families Served'],
                    ],
                ],
            ],
            [
                'section_type' => 'products',
                'order' => 2,
                'content' => [
                    'heading' => 'Featured Spice Blends & Staples',
                    'subheading' => 'Made from hand-picked Byadgi chillies, local garlic, and stone-ground spices.',
                    'items' => [
                        [
                            'title' => 'Kolhapuri Kanda Lasun Masala',
                            'name' => 'Kolhapuri Kanda Lasun Masala',
                            'description' => 'The crown jewel of Kolhapuri cuisine: slow-roasted onions, golden garlic, and sun-dried chillies.',
                            'price' => '₹180 / 500g',
                            'image' => 'https://images.unsplash.com/photo-1599940824399-b87987ceb72a?auto=format&fit=crop&w=600&q=80',
                        ],
                        [
                            'title' => 'Special Mutton & Chicken Masala',
                            'name' => 'Special Mutton & Chicken Masala',
                            'description' => 'Coarsely ground whole spices formulated for authentic Pandhra & Tambda Rassa gravies.',
                            'price' => '₹140 / 250g',
                            'image' => 'https://images.unsplash.com/photo-1509358271058-acd22cc93898?auto=format&fit=crop&w=600&q=80',
                        ],
                        [
                            'title' => 'Traditional Goda Masala',
                            'name' => 'Traditional Goda Masala',
                            'description' => 'Aromatic Brahmin-style blend featuring dagad phool (stone flower), desiccated coconut, and cinnamon.',
                            'price' => '₹110 / 200g',
                            'image' => 'https://images.unsplash.com/photo-1615485290382-441e4d049cb5?auto=format&fit=crop&w=600&q=80',
                        ],
                        [
                            'title' => 'Handmade Jowar Bhakri Flour',
                            'name' => 'Handmade Jowar Bhakri Flour',
                            'description' => 'Stone-ground local jowar flour milled weekly for extra-soft, layered traditional bhakris.',
                            'price' => '₹75 / 1kg',
                            'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=600&q=80',
                        ],
                    ],
                ],
            ],
            [
                'section_type' => 'features',
                'order' => 3,
                'content' => [
                    'heading' => 'Why Discerning Cooks Choose Patil Spices',
                    'features' => [
                        [
                            'title' => 'Zero Artificial Colors or Preservatives',
                            'description' => 'Our vibrant red hues come solely from naturally sun-dried Byadgi chillies, with zero synthetic colorants.',
                        ],
                        [
                            'title' => 'Slow Roasted in Traditional Iron Kadhais',
                            'description' => 'Low-temperature slow roasting locks in essential oils and prevents bitter burnt notes.',
                        ],
                        [
                            'title' => 'FSSAI Certified Quality Standards',
                            'description' => 'State-certified food testing protocols ensuring strict microbiological purity in every pack.',
                        ],
                    ],
                ],
            ],
            [
                'section_type' => 'testimonials',
                'order' => 4,
                'content' => [
                    'heading' => 'Loved by Generations of Cooks',
                    'reviews' => [
                        [
                            'name' => 'Mrs. Sunita Deshmukh',
                            'city' => 'Pune',
                            'comment' => 'Their Kanda Lasun Masala gives that exact authentic Kolhapuri taste my grandmother used to make. We order bulk packets every year!',
                        ],
                        [
                            'name' => 'Chef Rajesh Shinde',
                            'city' => 'Mumbai',
                            'comment' => 'The aroma and color of their Special Mutton Masala are remarkable. Unmatched consistency for restaurant service.',
                        ],
                    ],
                ],
            ],
            [
                'section_type' => 'location',
                'order' => 5,
                'content' => [
                    'heading' => 'Visit Our Kolhapur Store',
                    'address' => '45 Shahupuri 2nd Lane, Kolhapur, Maharashtra 416001',
                    'phone' => '+91 94220 54321',
                    'email' => 'orders@patilspices.com',
                    'hours' => 'Monday to Saturday: 9:30 AM - 8:30 PM (Sunday Closed)',
                ],
            ],
            [
                'section_type' => 'contact',
                'order' => 6,
                'content' => [
                    'heading' => 'Place an Order or Inquire for Bulk Delivery',
                    'description' => 'We deliver across Maharashtra and all major Indian cities via express courier.',
                    'phone' => '+91 94220 54321',
                    'email' => 'orders@patilspices.com',
                    'address' => '45 Shahupuri 2nd Lane, Kolhapur, Maharashtra 416001',
                    'button_text' => 'Call +91 94220 54321',
                ],
            ],
            [
                'section_type' => 'footer',
                'order' => 7,
                'content' => [
                    'business_name' => 'Patil Traditional Spices & Foods',
                    'copyright' => '© ' . date('Y') . ' Patil Traditional Spices & Foods. All rights reserved. FSSAI No. 11518034000421',
                    'note' => 'Authentic Maharashtrian Culinary Traditions Preserved with Pride.',
                ],
            ],
        ];

        WebsiteSection::where('website_id', $website->id)->delete();
        foreach ($sections as $s) {
            WebsiteSection::create([
                'website_id' => $website->id,
                'section_type' => $s['section_type'],
                'order' => $s['order'],
                'is_visible' => true,
                'content' => $s['content'],
            ]);
        }

        // Publication
        Publication::updateOrCreate(
            ['website_id' => $website->id],
            [
                'published_url' => url('/sites/' . $website->slug),
                'published_by' => $owner->id,
                'published_at' => now(),
                'is_active' => true,
            ]
        );

        // Version Snapshot
        WebsiteVersion::updateOrCreate(
            ['website_id' => $website->id, 'version_number' => 1],
            [
                'created_by' => $owner->id,
                'changelog' => 'Initial Published Release (Production Demonstration)',
                'snapshot' => [
                    'title' => $website->title,
                    'design_preset' => $website->design_preset,
                    'primary_color' => $website->primary_color,
                    'secondary_color' => $website->secondary_color,
                    'sections' => $sections,
                ],
                'created_at' => now(),
            ]
        );
    }

    protected function seedKulkarniAyurveda(User $owner): void
    {
        $business = Business::updateOrCreate(
            ['name' => 'Dr. Kulkarni Ayurvedic Wellness Clinic', 'user_id' => $owner->id],
            [
                'category' => 'professional_services',
                'city' => 'Pune',
                'state' => 'Maharashtra',
                'country' => 'India',
                'address' => 'Plot 18, Mayur Colony, Kothrud, Pune, Maharashtra 411038',
                'phone' => '+91 98230 12345',
                'email' => 'contact@ayurvedapune.in',
                'description' => 'Classical Ayurvedic consultations, authentic Panchakarma detoxification treatments, pulse diagnosis (Nadi Pariksha), and herbal wellness therapies guided by 20+ years of clinical expertise.',
                'operating_hours' => ['raw' => 'Monday - Saturday: 8:30 AM - 1:00 PM & 5:00 PM - 8:30 PM. By Appointment.'],
                'products' => [
                    'Personalized Ayurvedic Herbal Formulations',
                    'Medicated Classical Tailas & Ghritams (Oils & Ghee)',
                    'Immunity & Digestive Kadha Concentrates',
                ],
                'services' => [
                    'Nadi Pariksha (Classical Ayurvedic Pulse Diagnosis)',
                    'Panchakarma Detoxification & Rejuvenation Treatments',
                    'Lifestyle & Dietary Guidance for Chronic Metabolic Conditions',
                    'Spine & Joint Care Ayurvedic Therapy (Janu Basti & Kati Basti)',
                ],
                'status' => 'verified',
            ]
        );

        $clinicText = <<<TXT
Dr. Kulkarni Ayurvedic Wellness & Panchakarma Clinic
Lead Physician: Dr. Ananya Kulkarni, BAMS, MD (Ayurveda), MCIM No. 45892
Location: Plot 18, Mayur Colony, Kothrud, Pune, Maharashtra 411038
Phone: +91 98230 12345
Email: contact@ayurvedapune.in

Clinical Services:
1. Nadi Pariksha (Pulse Diagnosis) - Comprehensive dosha imbalance evaluation.
2. Panchakarma Therapy - Complete 7-day, 14-day, and 21-day detox regimens (Vamana, Virechana, Basti, Nasya).
3. Kati Basti & Janu Basti - Targeted herbal oil pooling for chronic lumbar spine and knee osteoarthritis relief.
4. Personalized Diet Counseling - Individualized Ahara (nutrition) plans mapped to Prakriti.

Clinic Consultation Hours:
Monday to Saturday: 8:30 AM - 1:00 PM and 5:00 PM - 8:30 PM.
Prior appointment is strictly required for clinical consultations and therapy bookings.
TXT;

        UploadedFile::updateOrCreate(
            ['business_id' => $business->id, 'original_name' => 'Clinic_Services_Guide.txt'],
            [
                'file_path' => 'demo/Clinic_Services_Guide.txt',
                'file_type' => 'txt',
                'file_size' => strlen($clinicText),
                'mime_type' => 'text/plain',
                'extracted_text' => $clinicText,
                'processing_status' => 'completed',
            ]
        );

        BusinessSource::updateOrCreate(
            ['business_id' => $business->id, 'url' => 'https://kulkarni-ayurveda.example.com'],
            [
                'source_type' => 'website',
                'title' => 'Dr. Kulkarni Ayurvedic Clinic Web Portal',
                'status' => 'crawled',
                'raw_content' => $clinicText,
            ]
        );

        // Extracted Facts
        $facts = [
            ['phone', 'Clinic Phone Number', '+91 98230 12345', 'Clinic appointment desk: +91 98230 12345', 0.99],
            ['email', 'Clinic Email Address', 'contact@ayurvedapune.in', 'Direct clinic email: contact@ayurvedapune.in', 0.99],
            ['address', 'Physical Location', 'Plot 18, Mayur Colony, Kothrud, Pune, Maharashtra 411038', 'Clinical address at Kothrud, Pune', 0.98],
            ['operating_hours', 'Consultation Hours', 'Monday - Saturday: 8:30 AM - 1:00 PM & 5:00 PM - 8:30 PM. By Appointment.', 'Operating hours from clinic schedule', 0.95],
            ['doctor_qualification', 'Physician Qualifications', 'BAMS, MD (Ayurveda)', 'Lead Physician: Dr. Ananya Kulkarni, BAMS, MD (Ayurveda)', 0.99],
            ['registration_number', 'Medical Registration', 'MCIM No. 45892', 'Maharashtra Council of Indian Medicine No. 45892', 0.99],
        ];

        foreach ($facts as [$key, $name, $val, $evidence, $conf]) {
            ExtractedFact::updateOrCreate(
                ['business_id' => $business->id, 'fact_key' => $key],
                [
                    'source_type' => 'document',
                    'fact_name' => $name,
                    'fact_value' => $val,
                    'evidence' => $evidence,
                    'verification_status' => 'user-confirmed',
                    'confidence_score' => $conf,
                    'is_confirmed' => true,
                    'is_owner_provided' => true,
                    'user_confirmed_value' => $val,
                    'extraction_time' => now(),
                ]
            );

            FactConfirmation::updateOrCreate(
                ['business_id' => $business->id, 'fact_key' => $key],
                [
                    'confirmed_value' => $val,
                    'resolution_type' => 'accepted',
                    'confirmed_by' => $owner->id,
                    'confirmed_at' => now(),
                ]
            );
        }

        // Generate Website
        $website = Website::updateOrCreate(
            ['business_id' => $business->id],
            [
                'user_id' => $owner->id,
                'title' => 'Dr. Kulkarni Ayurvedic Wellness & Panchakarma Clinic',
                'slug' => 'dr-kulkarni-ayurveda',
                'design_preset' => 'modern_minimal',
                'primary_color' => '#059669',
                'secondary_color' => '#0f172a',
                'font_family' => 'Inter, system-ui, sans-serif',
                'meta_description' => 'Classical Ayurvedic consultation, authentic Panchakarma detox, and Nadi Pariksha by Dr. Ananya Kulkarni (BAMS, MD) in Kothrud, Pune.',
                'is_published' => true,
                'published_at' => now(),
                'current_version' => 1,
                'settings' => [
                    'website_type' => 'professional_services',
                    'tagline' => 'Classical Ayurveda, Holistic Healing & Panchakarma in Pune',
                    'ai_generated' => true,
                    'generation_mode' => 'gemini_ai',
                    'primary_color' => '#059669',
                    'secondary_color' => '#0f172a',
                ],
            ]
        );

        $sections = [
            [
                'section_type' => 'hero',
                'order' => 0,
                'content' => [
                    'heading' => 'Classical Ayurveda & Authentic Panchakarma',
                    'subheading' => 'Rebalance your mind and body through evidence-grounded Ayurvedic diagnosis, personalized Panchakarma treatments, and herbal therapies.',
                    'primary_cta_text' => 'Book Consultation',
                    'primary_cta_link' => '#contact',
                    'secondary_cta_text' => 'Our Treatments',
                    'secondary_cta_link' => '#services',
                    'badge' => '20+ Years Clinical Experience • Kothrud, Pune',
                    'hero_image' => 'https://images.unsplash.com/photo-1600334089648-b0d9d3028eb2?auto=format&fit=crop&w=1200&q=80',
                ],
            ],
            [
                'section_type' => 'about',
                'order' => 1,
                'content' => [
                    'heading' => 'Dr. Ananya Kulkarni, BAMS, MD (Ayurveda)',
                    'paragraph_1' => 'With over two decades of dedicated clinical practice in Pune, Dr. Ananya Kulkarni combines authentic Ayurvedic texts (Charaka & Sushruta Samhitas) with individualized diagnostic care.',
                    'paragraph_2' => 'Our clinic provides a tranquil, hygienic, and authentic therapeutic environment where every treatment is prepared with medicated classical herbal formulations.',
                    'stats' => [
                        ['number' => '20+', 'label' => 'Years of Practice'],
                        ['number' => '15,000+', 'label' => 'Patients Healed'],
                        ['number' => '100%', 'label' => 'Authentic Classical Oils'],
                    ],
                ],
            ],
            [
                'section_type' => 'services',
                'order' => 2,
                'content' => [
                    'heading' => 'Core Ayurvedic Therapies & Clinical Services',
                    'subheading' => 'Targeted treatments for metabolic disorders, joint health, and holistic rejuvenation.',
                    'items' => [
                        [
                            'title' => 'Nadi Pariksha (Pulse Diagnosis)',
                            'name' => 'Nadi Pariksha (Pulse Diagnosis)',
                            'description' => 'Comprehensive radial pulse examination to uncover root causes of Vata, Pitta, and Kapha imbalances.',
                            'image' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=600&q=80',
                        ],
                        [
                            'title' => 'Panchakarma Detoxification',
                            'name' => 'Panchakarma Detoxification',
                            'description' => 'Supervised seasonal cleansing therapies including Vamana, Virechana, and medicated herbal Basti.',
                            'image' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=600&q=80',
                        ],
                        [
                            'title' => 'Kati Basti (Spine Care)',
                            'name' => 'Kati Basti (Spine Care)',
                            'description' => 'Warm medicated herbal oil pooling therapy for lower back pain, disc stiffness, and sciatica.',
                            'image' => 'https://images.unsplash.com/photo-1519823551278-64ac92734fb1?auto=format&fit=crop&w=600&q=80',
                        ],
                        [
                            'title' => 'Janu Basti (Knee Rejuvenation)',
                            'name' => 'Janu Basti (Knee Rejuvenation)',
                            'description' => 'Localized treatment for knee osteoarthritis, lubricating joints and alleviating inflammation.',
                            'image' => 'https://images.unsplash.com/photo-1512290900672-1f4140ad8238?auto=format&fit=crop&w=600&q=80',
                        ],
                    ],
                ],
            ],
            [
                'section_type' => 'features',
                'order' => 3,
                'content' => [
                    'heading' => 'Clinical Standards & Patient Commitments',
                    'features' => [
                        [
                            'title' => 'Doctor-Supervised Therapies',
                            'description' => 'All Panchakarma treatments are planned and overseen directly by certified Ayurvedic MD physicians.',
                        ],
                        [
                            'title' => 'Pure Classical Medicated Oils',
                            'description' => 'We prepare and source classical tailas and ghritams formulated strictly in accordance with classical Granthas.',
                        ],
                        [
                            'title' => 'Individualized Ahara (Diet) Plans',
                            'description' => 'Tailored dietary guidelines matching each patient unique metabolic constitution and seasonal weather.',
                        ],
                    ],
                ],
            ],
            [
                'section_type' => 'testimonials',
                'order' => 4,
                'content' => [
                    'heading' => 'Patient Healing Stories',
                    'reviews' => [
                        [
                            'name' => 'Mr. Hemant Joshi',
                            'city' => 'Kothrud, Pune',
                            'comment' => 'After suffering from chronic lumbar spondylitis for years, Dr. Kulkarni Kati Basti therapy gave me long-term pain relief without surgery.',
                        ],
                        [
                            'name' => 'Dr. Meera Ranade',
                            'city' => 'Pune',
                            'comment' => 'Her pulse diagnosis is exceptionally accurate. The seasonal Panchakarma detox made a visible difference in my energy and digestion.',
                        ],
                    ],
                ],
            ],
            [
                'section_type' => 'faq',
                'order' => 5,
                'content' => [
                    'heading' => 'Frequently Asked Questions',
                    'items' => [
                        [
                            'question' => 'How do I prepare for a Nadi Pariksha consultation?',
                            'answer' => 'Please arrive on an empty stomach (or at least 2.5 hours after a light meal) and avoid caffeinated beverages prior to the pulse examination.',
                        ],
                        [
                            'question' => 'How many days are required for a complete Panchakarma detox?',
                            'answer' => 'Panchakarma courses typically span 7, 14, or 21 days depending upon the chronic nature of the condition and physician assessment.',
                        ],
                    ],
                ],
            ],
            [
                'section_type' => 'location',
                'order' => 6,
                'content' => [
                    'heading' => 'Clinic Address & Contact Information',
                    'address' => 'Plot 18, Mayur Colony, Kothrud, Pune, Maharashtra 411038',
                    'phone' => '+91 98230 12345',
                    'email' => 'contact@ayurvedapune.in',
                    'hours' => 'Monday to Saturday: 8:30 AM - 1:00 PM & 5:00 PM - 8:30 PM. (By Appointment)',
                ],
            ],
            [
                'section_type' => 'contact',
                'order' => 7,
                'content' => [
                    'heading' => 'Schedule Your Consultation Today',
                    'description' => 'Consultations and treatments require prior appointment. Call our desk to book your convenient slot.',
                    'phone' => '+91 98230 12345',
                    'email' => 'contact@ayurvedapune.in',
                    'address' => 'Plot 18, Mayur Colony, Kothrud, Pune, Maharashtra 411038',
                    'button_text' => 'Call +91 98230 12345',
                ],
            ],
            [
                'section_type' => 'footer',
                'order' => 8,
                'content' => [
                    'business_name' => 'Dr. Kulkarni Ayurvedic Wellness & Panchakarma Clinic',
                    'copyright' => '© ' . date('Y') . ' Dr. Kulkarni Ayurvedic Clinic. All rights reserved. MCIM Reg: 45892.',
                    'note' => 'Dedicated to Authentic Ayurvedic Medicine & Holistic Wellness.',
                ],
            ],
        ];

        WebsiteSection::where('website_id', $website->id)->delete();
        foreach ($sections as $s) {
            WebsiteSection::create([
                'website_id' => $website->id,
                'section_type' => $s['section_type'],
                'order' => $s['order'],
                'is_visible' => true,
                'content' => $s['content'],
            ]);
        }

        Publication::updateOrCreate(
            ['website_id' => $website->id],
            [
                'published_url' => url('/sites/' . $website->slug),
                'published_by' => $owner->id,
                'published_at' => now(),
                'is_active' => true,
            ]
        );

        WebsiteVersion::updateOrCreate(
            ['website_id' => $website->id, 'version_number' => 1],
            [
                'created_by' => $owner->id,
                'changelog' => 'Initial Published Clinical Portal',
                'snapshot' => [
                    'title' => $website->title,
                    'design_preset' => $website->design_preset,
                    'primary_color' => $website->primary_color,
                    'secondary_color' => $website->secondary_color,
                    'sections' => $sections,
                ],
                'created_at' => now(),
            ]
        );
    }
}
