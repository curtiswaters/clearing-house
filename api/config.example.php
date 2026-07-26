<?php
/**
 * Copy this file to config.php (same folder) and fill in the credentials
 * for the MySQL database created in cPanel → MySQL® Databases.
 * config.php is gitignored — never commit real credentials.
 */

return [
  'host' => 'localhost',
  'db'   => 'cpaneluser_clearinghouse',
  'user' => 'cpaneluser_dbuser',
  'pass' => 'change-me',

  // Contact form (api/contact.php). contact_from_email should be a mailbox
  // on your own domain — most cPanel mail setups will flag or reject mail
  // sent "from" an address on another domain. contact_to_email is where
  // messages actually get delivered.
  'contact_to_email'   => 'info@dominatewithbrand.com',
  'contact_from_email' => 'noreply@clearinghousecharlotte.com',

  // Password for /admin/ (editing listings). Generate a hash with:
  //   php -r "echo password_hash('your-password-here', PASSWORD_DEFAULT), \"\n\";"
  // Never put the plain-text password here — only the hash.
  'admin_password_hash' => '$2y$10$replace.this.with.a.real.hash.generated.above',
];
