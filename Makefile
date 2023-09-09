# Install app
install: build composer.install

# Start Docker containers
up:
	docker compose up -d

# Stop Docker containers
down:
	docker compose down --remove-orphans

# Build Docker containers
build:
	docker compose build

# Install Composer dependencies
composer.install:
	docker compose run --rm composer install

# Update Composer dependencies
composer.update:
	docker compose run --rm composer update

# Downgrade Composer dependencies to lowest versions
composer.lowest:
	docker compose run --rm composer update --prefer-lowest --prefer-stable

# Uninstall Composer dependencies
composer.uninstall:
	sudo rm -rf vendor
	sudo rm composer.lock

# Clear cache
cache.clear:
	sudo rm -rf .cache

# Run PHPUnit
phpunit:
	docker compose run --rm phpunit

# Alias to run PHPUnit
test: phpunit

# Run PHPUnit with a coverage analysis using an HTML output
phpunit.coverage.html:
	docker compose run --rm phpunit --coverage-html tests/.report

# Run PHPUnit with a coverage analysis using a plain text output
phpunit.coverage.text:
	docker compose run --rm phpunit --coverage-text

# Run PHPUnit with a coverage analysis using a Clover's XML output
phpunit.coverage.clover:
	docker compose run --rm phpunit --coverage-clover tests/.report/clover.xml

# Run PHPUnit with a coverage analysis
coverage: phpunit.coverage.text

# Fix the code style
php-cs-fixer:
	docker compose run --rm php-cs-fixer fix

# Check the code style
php-cs-fixer.dry:
	docker compose run --rm php-cs-fixer fix --dry-run

# Alias to fix the code style
style: php-cs-fixer

# Remove installation files
uninstall: down composer.uninstall cache.clear
