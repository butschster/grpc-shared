job("PHPUnit") {
   container(displayName = "Run Test", image = "spiralscout/php-grpc:8.2") {
      shellScript {
         interpreter = "/bin/bash"
         content = """
            curl -s https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer
            composer install
            composer test
         """
      }
   }
}

job("Code style") {
   container(displayName = "Run Test", image = "spiralscout/php-grpc:8.2") {
      shellScript {
         interpreter = "/bin/bash"
         content = """
            curl -s https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer
            composer install
            composer cs-check
         """
      }
   }
}

