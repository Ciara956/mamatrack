   <?php
   require __DIR__ . '/bootstrap.php';
   
   // PASTE YOUR CREATE TABLE SQL HERE
   $sql = "
   CREATE TABLE IF NOT EXISTS users (
       id INT AUTO_INCREMENT PRIMARY KEY,
       username VARCHAR(50) NOT NULL,
       password VARCHAR(255) NOT NULL
       -- Add your other columns here exactly as they were in XAMPP
   );
   ";

   try {
       db()->exec($sql);
       echo "✅ Tables created successfully! Now delete this file.";
   } catch (PDOException $e) {
       echo "❌ Error: " . $e->getMessage();
   }
