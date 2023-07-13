.PHONY: messages-pot messages-en messages-fr messages-ru \
	xtext xtext-test-pot xtext-test-print

messages-pot:
	docker run --rm -v "$$PWD/..":'/app' -w '/app/tests' php ../bin/xtext \
		TextTest.php \
		>locale/messages.pot

messages-en:
	docker run --rm -v "$$PWD/..":'/app' -w '/app' php bin/po2php \
		-n Vertilia\\Text\\Tests\\Locale \
		-c MessagesEn \
		tests/locale/en/messages.po \
		>locale/MessagesEn.php

messages-fr:
	docker run --rm -v "$$PWD/..":'/app' -w '/app' php bin/po2php \
		-n Vertilia\\Text\\Tests\\Locale \
		-c MessagesFr \
		tests/locale/fr/messages.po \
		>locale/MessagesFr.php

messages-ru:
	docker run --rm -v "$$PWD/..":'/app' -w '/app' php bin/po2php \
		-n Vertilia\\Text\\Tests\\Locale \
		-c MessagesRu \
		tests/locale/ru/messages.po \
		>locale/MessagesRu.php

xtext:
	docker run --rm -v "$$PWD/..":'/app' -w '/app' php bin/xtext \
		-x */locale \
		-i '*.php' \
		-c TRANSLATORS: \
		tests

xtext-test-pot:
	docker run --rm -v "$$PWD/..":'/app' -w '/app' php bin/xtext -c TRANSLATORS: tests/xtext-test.php >xtext-test.pot

xtext-test-print:
	docker run --rm -v "$$PWD/..":'/app' -w '/app' php bin/xtext -c TRANSLATORS: tests/xtext-test.php
