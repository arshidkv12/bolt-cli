## phpBolt cli

https://phpbolt.com encryption by cli.
First install bolt.so file from https://phpbolt.com

### How to install encryption tool? ###

First, download the encryption tool using Composer:

`composer global require  "phpbolt/encrypt @dev"`

Make sure to place composer's system-wide vendor bin directory in your $PATH so the bolt encryption tool executable can be located by your system. This directory exists in different locations based on your operating system; however, some common locations include:

#### macOS and GNU / Linux Distributions: $HOME/.composer/vendor/bin ####
`export PATH=$HOME/.composer/vendor/bin`

#### Windows: %USERPROFILE%\AppData\Roaming\Composer\vendor\bin ####


`bolt -encrypt [source-folder] [key] [output-folder]`

### Example 
`bolt -encrypt test/hello abcdef encrypted`
