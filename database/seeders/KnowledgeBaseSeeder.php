<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KnowledgeBaseCategory;
use App\Models\KnowledgeBaseArticle;

class KnowledgeBaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Categories
        $gettingStarted = KnowledgeBaseCategory::create([
            'name' => 'Getting Started',
            'slug' => 'getting-started',
            'description' => 'Essential guides to help you get started with the school management system',
            'icon' => 'fa-rocket',
            'order_number' => 1,
            'is_active' => true,
        ]);

        $studentManagement = KnowledgeBaseCategory::create([
            'name' => 'Student Management',
            'slug' => 'student-management', 
            'description' => 'Learn how to manage student records, enrollment, and academic information',
            'icon' => 'fa-user-graduate',
            'order_number' => 2,
            'is_active' => true,
        ]);

        $academicManagement = KnowledgeBaseCategory::create([
            'name' => 'Academic Management',
            'slug' => 'academic-management',
            'description' => 'Manage academic years, terms, classes, subjects, and examinations',
            'icon' => 'fa-calendar-alt',
            'order_number' => 3,
            'is_active' => true,
        ]);

        $financialManagement = KnowledgeBaseCategory::create([
            'name' => 'Financial Management',
            'slug' => 'financial-management',
            'description' => 'Handle school fees, payments, financial records, and reporting',
            'icon' => 'fa-calculator',
            'order_number' => 4,
            'is_active' => true,
        ]);

        $reportingAnalytics = KnowledgeBaseCategory::create([
            'name' => 'Reporting & Analytics',
            'slug' => 'reporting-analytics',
            'description' => 'Generate reports, analyze data, and track school performance',
            'icon' => 'fa-chart-bar',
            'order_number' => 5,
            'is_active' => true,
        ]);

        $systemSettings = KnowledgeBaseCategory::create([
            'name' => 'System Settings',
            'slug' => 'system-settings',
            'description' => 'Configure system settings, user permissions, and preferences',
            'icon' => 'fa-cog',
            'order_number' => 6,
            'is_active' => true,
        ]);

        // Create Getting Started Articles
        KnowledgeBaseArticle::create([
            'category_id' => $gettingStarted->id,
            'title' => 'Welcome to School Management System',
            'slug' => 'welcome-to-school-management-system',
            'content' => '<h2>Welcome to Your School Management System</h2>
                         <p>Congratulations on choosing our comprehensive school management platform! This guide will help you get started and make the most of our powerful features.</p>
                         
                         <h3>What You Can Do</h3>
                         <ul>
                         <li><strong>Student Management:</strong> Maintain comprehensive student records, track enrollment, and manage academic progress</li>
                         <li><strong>Academic Planning:</strong> Set up academic years, terms, classes, and subjects</li>
                         <li><strong>Fee Management:</strong> Handle school fees, track payments, and generate financial reports</li>
                         <li><strong>Report Generation:</strong> Create detailed reports for students, parents, and administrators</li>
                         <li><strong>Communication:</strong> Send messages and notifications to students, parents, and staff</li>
                         </ul>
                         
                         <h3>Getting Started Steps</h3>
                         <ol>
                         <li>Complete your school profile setup</li>
                         <li>Configure academic years and terms</li>
                         <li>Set up classes and subjects</li>
                         <li>Import or add student data</li>
                         <li>Configure fee structures</li>
                         <li>Start using the system!</li>
                         </ol>
                         
                         <p>If you need help at any point, our knowledge base has detailed guides for every feature.</p>',
            'excerpt' => 'Get started with your school management system. Learn about key features and follow our step-by-step setup guide.',
            'order_number' => 1,
            'has_youtube_video' => false,
            'is_published' => true,
            'meta_title' => 'Welcome Guide - School Management System',
            'meta_description' => 'Complete guide to getting started with your school management system. Learn key features and setup steps.',
        ]);

        KnowledgeBaseArticle::create([
            'category_id' => $gettingStarted->id,
            'title' => 'First-Time Login and Navigation',
            'slug' => 'first-time-login-and-navigation',
            'content' => '<h2>Logging In for the First Time</h2>
                         <p>This guide will walk you through your first login and help you understand the system navigation.</p>
                         
                         <h3>Accessing the System</h3>
                         <ol>
                         <li>Open your web browser and navigate to your school\'s system URL</li>
                         <li>Click on "Access the System" from the homepage</li>
                         <li>Enter your username and password provided by your administrator</li>
                         <li>Click "Login" to access the dashboard</li>
                         </ol>
                         
                         <h3>Understanding the Dashboard</h3>
                         <p>Once logged in, you\'ll see the main dashboard with:</p>
                         <ul>
                         <li><strong>Navigation Menu:</strong> Access all system features from the left sidebar</li>
                         <li><strong>Statistics Cards:</strong> Quick overview of key numbers (students, classes, etc.)</li>
                         <li><strong>Recent Activity:</strong> Latest actions and updates</li>
                         <li><strong>Quick Actions:</strong> Shortcuts to commonly used features</li>
                         </ul>
                         
                         <h3>Main Navigation Areas</h3>
                         <ul>
                         <li><strong>Dashboard:</strong> Overview and statistics</li>
                         <li><strong>Students:</strong> Student records and management</li>
                         <li><strong>Academic:</strong> Years, terms, classes, and subjects</li>
                         <li><strong>Examinations:</strong> Exam setup and results</li>
                         <li><strong>Finance:</strong> Fees and financial management</li>
                         <li><strong>Reports:</strong> Generate various reports</li>
                         <li><strong>Settings:</strong> System configuration</li>
                         </ul>
                         
                         <p>Take some time to explore each section to familiarize yourself with the available features.</p>',
            'excerpt' => 'Learn how to log in for the first time and navigate the school management system dashboard and main features.',
            'order_number' => 2,
            'has_youtube_video' => true,
            'youtube_video_link' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'is_published' => true,
            'meta_title' => 'First-Time Login Guide - School Management System',
            'meta_description' => 'Step-by-step guide to logging in and navigating the school management system for the first time.',
        ]);

        // Create Student Management Articles
        KnowledgeBaseArticle::create([
            'category_id' => $studentManagement->id,
            'title' => 'Adding and Managing Student Records',
            'slug' => 'adding-and-managing-student-records',
            'content' => '<h2>Student Record Management</h2>
                         <p>Learn how to effectively add, edit, and manage student records in the system.</p>
                         
                         <h3>Adding a New Student</h3>
                         <ol>
                         <li>Navigate to <strong>Students > Students</strong> from the main menu</li>
                         <li>Click the <strong>"Add New Student"</strong> button</li>
                         <li>Fill in the required information:
                            <ul>
                            <li>Personal details (name, date of birth, gender)</li>
                            <li>Contact information</li>
                            <li>Parent/guardian details</li>
                            <li>Academic information (class, enrollment date)</li>
                            </ul>
                         </li>
                         <li>Upload a student photo if available</li>
                         <li>Click <strong>"Save"</strong> to create the record</li>
                         </ol>
                         
                         <h3>Editing Student Information</h3>
                         <p>To update existing student records:</p>
                         <ol>
                         <li>Find the student in the students list</li>
                         <li>Click the "Edit" button next to their name</li>
                         <li>Make the necessary changes</li>
                         <li>Save your updates</li>
                         </ol>
                         
                         <h3>Bulk Student Import</h3>
                         <p>For adding multiple students at once:</p>
                         <ol>
                         <li>Go to <strong>Students > Batch Importers</strong></li>
                         <li>Download the sample CSV template</li>
                         <li>Fill in your student data following the template format</li>
                         <li>Upload the completed CSV file</li>
                         <li>Review and confirm the import</li>
                         </ol>
                         
                         <h3>Student Status Management</h3>
                         <p>Students can have different statuses:</p>
                         <ul>
                         <li><strong>Active:</strong> Currently enrolled and attending</li>
                         <li><strong>Inactive:</strong> Temporarily not attending</li>
                         <li><strong>Graduated:</strong> Completed their studies</li>
                         <li><strong>Transferred:</strong> Moved to another school</li>
                         </ul>
                         
                         <p>Remember to keep student records up-to-date for accurate reporting and communication.</p>',
            'excerpt' => 'Complete guide to adding, editing, and managing student records including bulk import and status management.',
            'order_number' => 1,
            'has_youtube_video' => false,
            'is_published' => true,
        ]);

        // Create Academic Management Articles
        KnowledgeBaseArticle::create([
            'category_id' => $academicManagement->id,
            'title' => 'Setting Up Academic Years and Terms',
            'slug' => 'setting-up-academic-years-and-terms',
            'content' => '<h2>Academic Year and Term Configuration</h2>
                         <p>Properly setting up academic years and terms is crucial for organizing your school\'s academic calendar.</p>
                         
                         <h3>Creating an Academic Year</h3>
                         <ol>
                         <li>Navigate to <strong>Academic > Academic Years</strong></li>
                         <li>Click <strong>"Add New Academic Year"</strong></li>
                         <li>Enter the academic year details:
                            <ul>
                            <li>Year name (e.g., "2023-2024")</li>
                            <li>Start date and end date</li>
                            <li>Description</li>
                            </ul>
                         </li>
                         <li>Set the year as "Current" if it\'s the active academic year</li>
                         <li>Save the academic year</li>
                         </ol>
                         
                         <h3>Adding Terms to Academic Year</h3>
                         <p>Terms divide the academic year into manageable periods:</p>
                         <ol>
                         <li>Go to <strong>Academic > Terms</strong></li>
                         <li>Click <strong>"Add New Term"</strong></li>
                         <li>Select the academic year</li>
                         <li>Enter term details:
                            <ul>
                            <li>Term name (e.g., "Term 1", "First Semester")</li>
                            <li>Start and end dates</li>
                            <li>Term number/order</li>
                            </ul>
                         </li>
                         <li>Save the term</li>
                         </ol>
                         
                         <h3>Best Practices</h3>
                         <ul>
                         <li>Plan your academic calendar before creating terms</li>
                         <li>Ensure term dates don\'t overlap</li>
                         <li>Include holidays and break periods in your planning</li>
                         <li>Set only one academic year as "current" at a time</li>
                         <li>Archive old academic years to keep the system organized</li>
                         </ul>
                         
                         <h3>Managing Academic Calendar</h3>
                         <p>Your academic calendar forms the foundation for:</p>
                         <ul>
                         <li>Student enrollment periods</li>
                         <li>Examination scheduling</li>
                         <li>Fee collection periods</li>
                         <li>Report card generation</li>
                         <li>Academic reporting</li>
                         </ul>
                         
                         <p>Take time to plan your academic structure carefully as it affects many other system functions.</p>',
            'excerpt' => 'Learn how to set up academic years and terms to organize your school\'s academic calendar effectively.',
            'order_number' => 1,
            'has_youtube_video' => true,
            'youtube_video_link' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'is_published' => true,
        ]);

        // Create Financial Management Articles
        KnowledgeBaseArticle::create([
            'category_id' => $financialManagement->id,
            'title' => 'School Fees Setup and Management',
            'slug' => 'school-fees-setup-and-management',
            'content' => '<h2>School Fees Configuration</h2>
                         <p>Set up and manage school fees efficiently to streamline your financial operations.</p>
                         
                         <h3>Setting Up Fee Structures</h3>
                         <ol>
                         <li>Navigate to <strong>Finance > Academic Class Fees</strong></li>
                         <li>Select the academic class</li>
                         <li>Click <strong>"Add New Fee"</strong></li>
                         <li>Configure fee details:
                            <ul>
                            <li>Fee type (tuition, transport, meals, etc.)</li>
                            <li>Amount</li>
                            <li>Payment frequency (termly, annually, monthly)</li>
                            <li>Due date</li>
                            </ul>
                         </li>
                         <li>Save the fee structure</li>
                         </ol>
                         
                         <h3>Recording Payments</h3>
                         <p>To record student fee payments:</p>
                         <ol>
                         <li>Go to <strong>Finance > Transactions</strong></li>
                         <li>Click <strong>"Add New Transaction"</strong></li>
                         <li>Enter payment details:
                            <ul>
                            <li>Student name</li>
                            <li>Amount paid</li>
                            <li>Payment method</li>
                            <li>Payment date</li>
                            <li>Receipt number</li>
                            </ul>
                         </li>
                         <li>Save the transaction</li>
                         </ol>
                         
                         <h3>Managing Fee Balances</h3>
                         <p>Track outstanding balances:</p>
                         <ul>
                         <li>View individual student balance statements</li>
                         <li>Generate class-wise balance reports</li>
                         <li>Send balance notifications to parents</li>
                         <li>Track payment history</li>
                         </ul>
                         
                         <h3>Financial Reporting</h3>
                         <p>Generate various financial reports:</p>
                         <ul>
                         <li><strong>Daily Collection Reports:</strong> Track daily income</li>
                         <li><strong>Outstanding Balances:</strong> See who owes fees</li>
                         <li><strong>Payment History:</strong> Track payment patterns</li>
                         <li><strong>Financial Statements:</strong> Overall financial overview</li>
                         </ul>
                         
                         <h3>Best Practices</h3>
                         <ul>
                         <li>Set clear payment deadlines</li>
                         <li>Send regular balance reminders</li>
                         <li>Keep accurate payment records</li>
                         <li>Reconcile accounts regularly</li>
                         <li>Backup financial data frequently</li>
                         </ul>',
            'excerpt' => 'Complete guide to setting up school fees, recording payments, and managing financial operations.',
            'order_number' => 1,
            'has_youtube_video' => false,
            'is_published' => true,
        ]);

        // Create System Settings Articles
        KnowledgeBaseArticle::create([
            'category_id' => $systemSettings->id,
            'title' => 'User Management and Permissions',
            'slug' => 'user-management-and-permissions',
            'content' => '<h2>Managing Users and Permissions</h2>
                         <p>Learn how to create user accounts and manage access permissions for different roles in your school.</p>
                         
                         <h3>User Roles Overview</h3>
                         <p>The system supports various user roles:</p>
                         <ul>
                         <li><strong>Super Administrator:</strong> Full system access</li>
                         <li><strong>Administrator:</strong> School-level management</li>
                         <li><strong>Teacher:</strong> Class and student management</li>
                         <li><strong>Accountant:</strong> Financial operations</li>
                         <li><strong>Librarian:</strong> Library management</li>
                         <li><strong>Parent:</strong> View child\'s information</li>
                         <li><strong>Student:</strong> Limited access to own records</li>
                         </ul>
                         
                         <h3>Creating New Users</h3>
                         <ol>
                         <li>Go to <strong>System > Users</strong></li>
                         <li>Click <strong>"Add New User"</strong></li>
                         <li>Fill in user details:
                            <ul>
                            <li>Full name</li>
                            <li>Email address</li>
                            <li>Phone number</li>
                            <li>Username</li>
                            <li>Password</li>
                            </ul>
                         </li>
                         <li>Select appropriate user role</li>
                         <li>Set permissions if needed</li>
                         <li>Save the user account</li>
                         </ol>
                         
                         <h3>Managing Permissions</h3>
                         <p>Control what users can access:</p>
                         <ul>
                         <li><strong>Module Access:</strong> Which features users can see</li>
                         <li><strong>Data Access:</strong> What information users can view</li>
                         <li><strong>Action Permissions:</strong> What operations users can perform</li>
                         <li><strong>Report Access:</strong> Which reports users can generate</li>
                         </ul>
                         
                         <h3>Password Management</h3>
                         <p>Ensure account security:</p>
                         <ul>
                         <li>Set strong password requirements</li>
                         <li>Enable password reset functionality</li>
                         <li>Regular password updates</li>
                         <li>Account lockout policies</li>
                         </ul>
                         
                         <h3>Best Practices</h3>
                         <ul>
                         <li>Follow principle of least privilege</li>
                         <li>Regular access reviews</li>
                         <li>Deactivate unused accounts</li>
                         <li>Monitor user activity</li>
                         <li>Train users on security practices</li>
                         </ul>',
            'excerpt' => 'Learn how to create user accounts, assign roles, and manage permissions for secure system access.',
            'order_number' => 1,
            'has_youtube_video' => false,
            'is_published' => true,
        ]);

        echo "Knowledge Base seeded successfully!\n";
        echo "Created " . KnowledgeBaseCategory::count() . " categories\n";
        echo "Created " . KnowledgeBaseArticle::count() . " articles\n";
    }
}
