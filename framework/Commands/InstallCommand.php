<?php

namespace AVB\DevelopmentFramework\Commands;

class InstallCommand extends Command
{
  protected $command = 'install';

  protected $description = 'Installs the specified Expression Engine project';

  public function execute($args): void
  {
    $this->installExpressionEngine($args);
  }

  private function installExpressionEngine($args)
  {
    if ($this->isDatabaseEmpty()) {
      $webRoot = DEFAULT_PROJECT_PATH . '/website';
      $systemPath = $webRoot . '/system';
      
      // Check if ExpressionEngine is already downloaded
      if (!file_exists($systemPath . '/ee/installer/config/config.php')) {
        // ExpressionEngine is not downloaded, execute the download command
        $downloadCommand = new DownloadCommand();
        $downloadCommand->execute([]);
      }

      $url = $_ENV['DDEV_PRIMARY_URL'] . '/cms.php?C=wizard&M=do_install&language=english';

      $userdata = array(
          'database' => 'mysql',
          'db_hostname' => $_ENV['PGHOST'],  // DDEV environment variable for DB hostname (defaults to 'db')
          'db_name' => $_ENV['PGDATABASE'],  // DDEV environment variable for DB name (defaults to 'db')
          'db_username' => $_ENV['PGUSER'],  // DDEV environment variable for DB username (defaults to 'db')
          'db_password' => $_ENV['PGPASSWORD'],  // DDEV environment variable for DB password (defaults to 'db')
          'db_prefix' => 'exp',
          'site_label' => $_ENV['DDEV_PROJECT'] ?? '',  // DDEV environment variable for project name
          'site_name' => 'default_site',
          'site_url' => $_ENV['DDEV_PRIMARY_URL'],  // DDEV environment variable for app URL (HTTPS)
          'username' => $_ENV['EE_ADMIN_USERNAME'] ?? '',  // Placeholder for admin username (if configured)
          'password' => $_ENV['EE_ADMIN_PASSWORD'] ?? '',  // Placeholder for admin password (if configured)
          'password_confirm' => $_ENV['EE_ADMIN_PASSWORD'] ?? '',  // Confirm password same as admin pass
          'screen_name' => $_ENV['EE_ADMIN_SCREEN_NAME'] ?? $_ENV['EE_ADMIN_USERNAME'] ?? '',  // Placeholder for screen name (admin username)
          'email_address' => $_ENV['EE_ADMIN_EMAIL'] ?? '',  // Placeholder for admin email (if configured)
          'webmaster_email' => $_ENV['EE_ADMIN_WEBMASTER'] ?? $_ENV['EE_ADMIN_EMAIL'] ?? '',  // Default to admin email if not set
          'default_site_timezone' => date_default_timezone_get(),  // Default timezone
          'license_agreement' => 'y',  // License agreement consent
      );
      // Prompt for missing values
      foreach ($userdata as $key => $value) {
        if (empty($value)) {
            // Only prompt if the value is missing
            echo "Please enter the value for '$key': ";
            $userdata[$key] = trim(fgets(STDIN));  // Read input from CLI
        }
      }

      $postData = http_build_query($userdata);

      $options = array(
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_RETURNTRANSFER => true,
      );
      $this->logger->log("Starting Expression engine installation", 'info');

      $ch = curl_init($url);
      curl_setopt_array($ch, $options);
      $response = curl_exec($ch);

      // Check for cURL errors
      if ($response === false) {
        echo "Error executing cURL request: " . curl_error($ch) . "\n";
        curl_close($ch);
        exit;
      }
      // Get HTTP response code
      $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);
      
      // Handle HTTP response codes
      if ($httpCode === 404) {
        $this->logger->log("Error: ExpressionEngine installation endpoint not found (404). value: ".$url, 'error');
        exit;
      } 

      // Handle successful installation
      $this->logger->log('ExpressionEngine installation completed successfully.', 'success');
      // $themeCommand = new BoilerplateCommand();
      // $themeCommand->execute($arguments);
    } else {

      $this->logger->log('Database not empty.', 'error');
    }
  }
  private function isDatabaseEmpty()
  {
    // Connect to the database
    $dbHost = $_ENV['PGHOST'];
    $dbName = $_ENV['PGDATABASE'];
    $dbUsername = $_ENV['PGUSER'];
    $dbPassword = $_ENV['PGPASSWORD'];

    // Construct the command
    $command = "-e 'SET FOREIGN_KEY_CHECKS = 0; SHOW TABLES' {$dbName} 2>/dev/null";

    // Execute the command
    $response = $this->runMysql($command);

    // Check for errors
    if ($response['code'] !== 0 && isset($response['error'])) {
      $this->logger->log($response['error'], 'error');
      exit;
    }
    // Check if tables are present
    return empty($response['message']);
  }
}
