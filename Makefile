download-protogen:
	if [ ! -f "protoc-gen-php-grpc" ]; then ./vendor/bin/rr download-protoc-binary; fi

install-composer:
	if [ ! -d "vendor" ]; then composer install; fi

create-dir:
	if [ ! -d "generated" ]; then mkdir generated; fi

clean-dir:
	rm -rf generated/Services; \
	rm -rf src/Bootloader/ServiceBootloader.php; \
	rm -rf src/Command; \
	rm -rf src/Mapper; \
	rm -rf src/Service/Client; \
	rm -rf src/ServiceProvider; \
	rm -rf src/Handler;

compile-files:
	 chmod +x ./generator/bin/console; \
	./generator/bin/console generate

compile: install-composer download-protogen create-dir clean-dir compile-files
