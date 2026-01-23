import json

data = {
    "name": "owncloud/guests",
    "description": "Share with externals easily via email address",
    "config": {
        "platform": {
            "php": "8.4"
        },
        "allow-plugins": {
            "bamarni/composer-bin-plugin": True
        }
    },
    "require": {
        "php": ">=8.4"
    },
    "require-dev": {
        "bamarni/composer-bin-plugin": "^1.8",
        "phpunit/phpunit": "^10.5"
    },
    "autoload": {
        "psr-4": {
            "OCA" + chr(92) + "Guests" + chr(92): "lib/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "OCA" + chr(92) + "Guests" + chr(92) + "Tests" + chr(92): "tests/"
        }
    },
    "extra": {
        "bamarni-bin": {
            "bin-links": False
        }
    }
}

with open('composer.json', 'w') as f:
    json.dump(data, f, indent=2)
    f.write('\n')

print("composer.json written successfully")