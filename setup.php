   <?php
   require __DIR__ . '/bootstrap.php';
   
   // PASTE YOUR CREATE TABLE SQL HERE
   $sql = "
   CREATE TABLE IF NOT EXISTS users (
       id INT AUTO_INCREMENT PRIMARY KEY,
       name VARCHAR(50) NOT NULL,
       password _hash VARCHAR(255) NOT NULL
       role VARCHAR(20) NOT NULL email VARCHAR(20) NOT NULL,phone INT NOT NULL,must_change VARCHAR(255) NOT NULL,created_at TIMESTAMP) 
   ";

   try {
       db()->exec($sql);
       echo "✅ Tables created successfully! Now delete this file.";
   } catch (PDOException $e) {
       echo "❌ Error: " . $e->getMessage();
   }
