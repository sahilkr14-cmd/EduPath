<?php
/**
 * LinguaConnect Setup Script
 * Initializes the database and imports sample data
 */

require_once 'config/database.php';

// Set content type to HTML for better display
header('Content-Type: text/html; charset=UTF-8');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LinguaConnect Setup</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 700;
        }
        .header p {
            margin: 16px 0 0;
            opacity: 0.9;
            font-size: 1.125rem;
        }
        .content {
            padding: 40px;
        }
        .step {
            margin-bottom: 30px;
            padding: 20px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        .step.success {
            border-color: #10b981;
            background: #f0fdf4;
        }
        .step.error {
            border-color: #ef4444;
            background: #fef2f2;
        }
        .step h3 {
            margin: 0 0 12px;
            font-size: 1.25rem;
            font-weight: 600;
        }
        .step.success h3 {
            color: #10b981;
        }
        .step.error h3 {
            color: #ef4444;
        }
        .step p {
            margin: 0;
            color: #6b7280;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
        }
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        .actions {
            text-align: center;
            margin-top: 40px;
        }
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 LinguaConnect Setup</h1>
            <p>Welcome! Let's get your language learning platform up and running.</p>
        </div>
        <div class="content">
            <?php
            $steps = [];
            $overall_success = true;

            // Step 1: Create Database
            echo '<div class="step" id="step1">';
            echo '<h3>Step 1: Creating Database</h3>';
            echo '<p>Setting up the LinguaConnect database...</p>';
            echo '</div>';

            try {
                $database = new Database();
                $result = $database->createDatabase();
                
                if ($result) {
                    echo '<script>document.getElementById("step1").className = "step success";</script>';
                    echo '<script>document.getElementById("step1").innerHTML = \'<h3>✅ Database Created Successfully</h3><p>The LinguaConnect database has been created.</p>\';</script>';
                    $steps[] = ['name' => 'Database Creation', 'status' => 'success'];
                } else {
                    throw new Exception("Failed to create database");
                }
            } catch (Exception $e) {
                echo '<script>document.getElementById("step1").className = "step error";</script>';
                echo '<script>document.getElementById("step1").innerHTML = \'<h3>❌ Database Creation Failed</h3><p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>\';</script>';
                $steps[] = ['name' => 'Database Creation', 'status' => 'error'];
                $overall_success = false;
            }

            // Step 2: Create Tables
            echo '<div class="step" id="step2">';
            echo '<h3>Step 2: Creating Tables</h3>';
            echo '<p>Setting up database tables...</p>';
            echo '</div>';

            try {
                $db = $database->getConnection();
                if (!$db) {
                    throw new Exception("Database connection failed");
                }

                $schema = file_get_contents('database/schema.sql');
                if (!$schema) {
                    throw new Exception("Schema file not found");
                }

                // Split SQL into individual statements
                $statements = array_filter(array_map('trim', explode(';', $schema)));
                
                foreach ($statements as $statement) {
                    if (!empty($statement) && !preg_match('/^(CREATE DATABASE|USE)/i', $statement)) {
                        $db->exec($statement);
                    }
                }

                echo '<script>document.getElementById("step2").className = "step success";</script>';
                echo '<script>document.getElementById("step2").innerHTML = \'<h3>✅ Tables Created Successfully</h3><p>All database tables have been created.</p>\';</script>';
                $steps[] = ['name' => 'Table Creation', 'status' => 'success'];
            } catch (Exception $e) {
                echo '<script>document.getElementById("step2").className = "step error";</script>';
                echo '<script>document.getElementById("step2").innerHTML = \'<h3>❌ Table Creation Failed</h3><p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>\';</script>';
                $steps[] = ['name' => 'Table Creation', 'status' => 'error'];
                $overall_success = false;
            }

            // Step 3: Import Sample Data
            echo '<div class="step" id="step3">';
            echo '<h3>Step 3: Importing Sample Data</h3>';
            echo '<p>Loading sample data for languages, courses, and lessons...</p>';
            echo '</div>';

            try {
                require_once 'import/import_data.php';
                $importer = new DataImporter();
                
                $sample_files = [
                    'languages' => 'sample_data/languages.json',
                    'courses' => 'sample_data/courses.json',
                    'lessons' => 'sample_data/lessons.json',
                    'testimonials' => 'sample_data/testimonials.json',
                    'achievements' => 'sample_data/achievements.json'
                ];

                $imported_tables = 0;
                foreach ($sample_files as $table => $file) {
                    if (file_exists($file)) {
                        $result = $importer->importFromJSON($file, $table);
                        if ($result) {
                            $imported_tables++;
                        }
                    }
                }

                if ($imported_tables > 0) {
                    echo '<script>document.getElementById("step3").className = "step success";</script>';
                    echo '<script>document.getElementById("step3").innerHTML = \'<h3>✅ Sample Data Imported</h3><p>Successfully imported data for ' . $imported_tables . ' tables.</p>\';</script>';
                    $steps[] = ['name' => 'Sample Data Import', 'status' => 'success'];
                } else {
                    throw new Exception("No data was imported");
                }
            } catch (Exception $e) {
                echo '<script>document.getElementById("step3").className = "step error";</script>';
                echo '<script>document.getElementById("step3").innerHTML = \'<h3>❌ Sample Data Import Failed</h3><p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>\';</script>';
                $steps[] = ['name' => 'Sample Data Import', 'status' => 'error'];
                $overall_success = false;
            }

            // Step 4: Setup Complete
            echo '<div class="step" id="step4">';
            if ($overall_success) {
                echo '<h3>🎉 Setup Complete!</h3>';
                echo '<p>Your LinguaConnect platform is ready to use. You can now access the admin panel and start managing your language learning platform.</p>';
            } else {
                echo '<h3>⚠️ Setup Completed with Errors</h3>';
                echo '<p>Some steps encountered errors. Please check the error messages above and try again.</p>';
            }
            echo '</div>';
            ?>

            <div class="actions">
                <?php if ($overall_success): ?>
                    <a href="index.html" class="btn">View Landing Page</a>
                    <a href="admin/index.php" class="btn" style="margin-left: 12px;">Open Admin Panel</a>
                <?php else: ?>
                    <button onclick="location.reload()" class="btn">Try Again</button>
                <?php endif; ?>
            </div>

            <div style="margin-top: 40px; padding: 20px; background: #f8fafc; border-radius: 8px;">
                <h4>Next Steps:</h4>
                <ul>
                    <li>Configure your database settings in <code>config/database.php</code></li>
                    <li>Set up email configuration for contact forms and newsletters</li>
                    <li>Customize the landing page content and styling</li>
                    <li>Add your own language courses and content</li>
                    <li>Configure your web server for production use</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>