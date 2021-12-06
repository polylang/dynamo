# [DynaMo](https://wordpress.org/plugins/dynamo/)

Welcome to the DynaMo repository on GitHub. Here you can browse the source, discuss open issues and keep track of the development.

If you are not a developer, we recommend to [download DynaMo](https://wordpress.org/plugins/dynamo/) from WordPress directory.

## [Pre-requisites](#pre-requisites)

Before starting, make sure that you have [Composer](https://getcomposer.org/doc/00-intro.md) installed and working on your computer because DynaMo uses its autoloader to work and it is required to install development tools such as PHP CodeSniffer that ensures your code follows coding standards.

## [How to set up DynaMo](#how-to-setup-dynamo)

The simplest way is to clone locally this repository and build it directly in your local WordPress instance by following the steps below:

1. Go to your local WordPress instance wp-content/plugins/ folder:<br/>
`cd your/local/wordpress/path/wp-content/plugins`
2. Clone there the DynaMo repository (or your fork) from GitHub:<br/>
`git clone https://github.com/polylang/dynamo.git`
3. Go to your local DynaMo clone folder from there:
`cd dynamo`
4. Run the composer command:
`composer install`
5. Activate Dynamo as if you had installed it from WordPress.org.
