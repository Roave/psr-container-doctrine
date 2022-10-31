.PHONY: *

default: unit cs static-analysis ## all the things

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

unit: ## run unit tests
	vendor/bin/phpunit

cs: ## verify code style rules
	vendor/bin/phpcs

static-analysis: ## verify that no static analysis issues were introduced
	vendor/bin/psalm

bc-check: ## check for backwards compatibility breaks
	mkdir -p /tmp/bc-check
	composer require --no-plugins -d/tmp/bc-check roave/backward-compatibility-check 
	/tmp/bc-check/vendor/bin/roave-backward-compatibility-check
	rm -Rf /tmp/bc-check

coverage: ## generate code coverage reports
	vendor/bin/phpunit --testsuite unit --coverage-html build/coverage-html --coverage-text
