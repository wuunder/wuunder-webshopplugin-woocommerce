# Change Log
All notable changes to this project will be documented in this file.
Please file changes under `Added`, `Changed`, `Deprecated`, `Removed`, `Fixed` or `Security`.

The format is based on [Keep a Changelog](http://keepachangelog.com/).

## Un-released

## Released

## [2.7.26](https://github.com/wuunder/wuunder-webshopplugin-woocommerce/tag/2.7.26) - 2025-03-25

### Fixed 
- Fix an error case when cart items were not WC_Product

## [2.7.25](https://github.com/wuunder/wuunder-webshopplugin-woocommerce/tag/2.7.25) - 2022-11-24

### Fixed
- Weight calculations ensure number during calculations

## [2.7.24](https://github.com/wuunder/wuunder-webshopplugin-woocommerce/tag/2.7.24) - 2022-04-19

### Fixed
- Replaced deprecated WC functions

## [2.7.23](https://github.com/wuunder/wuunder-webshopplugin-woocommerce/tag/2.7.23) - 2022-04-14

### Fixed
- Weight string type warning when calculating total weight

## [2.7.22](https://github.com/wuunder/wuunder-webshopplugin-woocommerce/tag/2.7.22) - 2022-02-04

### Fixed

- Fix weight calculation by explicit casting vars

## [2.7.20](https://github.com/wuunder/wuunder-webshopplugin-woocommerce/tag/2.7.20) - 2020-12-11

### Fixed

- Fix hook used for webhook processing

## [2.7.19](https://github.com/wuunder/wuunder-webshopplugin-woocommerce/tag/2.7.19) - 2020-07-02

### Added
- Support multi-site wordpress

### Fixed

- Parcelshop shipping method error when disabled


## [2.7.18](https://github.com/wuunder/wuunder-webshopplugin-woocommerce/tag/2.7.18) - 2020-06-02

### Fixed
- Fixed remembering parcelshop id 

## [2.7.17](https://github.com/wuunder/wuunder-webshopplugin-woocommerce/tag/2.7.17) - 2020-05-01

### Fixed
- Fixed customer booking email
- Removed housenumber field placeholder '-'

## [2.7.16](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tag/2.7.16) - 2020-03-23

### Fixed
- Fixed some PHP warnings in hook method code

## [2.7.15](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tag/2.7.15) - 2020-03-17

### Added
- Save selected parcelshop over page refresh

## [2.7.14](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tag/2.7.14) - 2020-03-06

### Added
- Support checkout updating from WC 3.9.x and >

## [2.7.13](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tag/2.7.13) - 2020-02-19

### Added
- Support for orders without selected shipping method


## [2.7.12](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tag/2.7.12) - 2020-02-17

### Added
- Support for WC switch destination address billing/shipping

## [2.7.11](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tag/2.7.11) - 2020-01-21

### Added
- New tag for Wordpress marketplace
### Removed
- Asset screenshots/icons

## [2.7.10](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tag/2.7.10) - 2019-12-11

### Added
-   Wuunder shipping methods are taxable
-   2nd address line is send to wuunder with booking

### Fixed
-   Parcelshop error messages in checkout 
-   Fixed sending company name in delivery address

### Removed
-   Legacy setting google api key


## [2.7.9](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tag/2.7.9) - 2019-11-12

### Added
-   Marketplace assets and readme

## [2.7.8](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tag/2.7.8) - 2019-10-30

### Added
-   Support sanitize functions

### Fixed
-   Removed curl functions and use WP functions for getting filesize of images 

## [2.7.7](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tag/2.7.7) - 2019-10-11

### Added
-   Support for value and weight via mywuunder import (REST API)


## [2.7.6](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tag/2.7.6) - 2019-10-07

### Added
- Booking token support for imported orders via REST API


## [2.7.5](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tag/2.7.5) - 2019-09-30

### Fixed
- Parcelshop locator wuunder production settings
- Order value
- Line ending description


## [2.7.4](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tag/2.7.4) - 2019-09-27

### Added
- Preferred_service_level to Woocommerce REST API Orders resourcelist

## [2.7.3](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tag/2.7.3) - 2019-09-25

### Fix

- Fixed WC version constant usage

## [2.7.2](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tag/2.7.2) - 2019-09-19

### Fix

- Code styling and typo fixes
- Checkout validation parcelshop locator bugfixed [@timoj](https://github.com/timoj)  [WWE-97](https://wuunder.atlassian.net/secure/RapidBoard.jspa?rapidView=6&projectKey=WWE&modal=detail&selectedIssue=WWE-97)
- Code styling [@timoj](https://github.com/timoj)  [WWE-97](https://wuunder.atlassian.net/secure/RapidBoard.jspa?rapidView=6&projectKey=WWE&modal=detail&selectedIssue=WWE-97)

### Added

- Translations checkout text parcelshop locator text [WWE-97](https://wuunder.atlassian.net/secure/RapidBoard.jspa?rapidView=6&projectKey=WWE&modal=detail&selectedIssue=WWE-97)


## [2.7.0](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tag/2.7.0) - 2019-04-01

### Fix

- Parcelshop selection fix & endline char fix ([#12](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/pull/12))
- Changed text when the customer has selected a parcelshop in the checkout page.([#WWE-8](https://wuunder.atlassian.net/projects/WWE/issues/WWE-8))
- Added default option to the parcelshop shipping method ([#WWE-4](https://wuunder.atlassian.net/projects/WWE/issues/WWE-4))

## [2.6.3](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/releases/tag/2.6.3) Pre-release - 2018-1-21

### Fix

- Changed header docs [@timoj](https://github.com/timoj)


## [2.6.2](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/releases/tag/2.6.2) Pre-release - 2018-1-21

### Fix

- Merged guidelines [@timoj](https://github.com/timoj)


## [2.6.1](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/releases/tag/2.6.1) Pre-release - 2018-10-19

### Fix

- Fixed handling dimensions product, changed by woocommerce [@timoj](https://github.com/timoj)


## [2.6.0](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/releases/tag/2.6.0) Pre-release - 2018-10-19

### Added

- Added logging respecting WP_DEBUG [@timoj](https://github.com/timoj)


## [2.5.1](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/releases/tag/2.5.1) Pre-release - 2018-10-10

### Fix

- Bugfix parcelshop locator settings [@timoj](https://github.com/timoj)


## [2.5.0](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/releases/tag/2.5.0) Pre-release - 2018-9-17

### Fix

- Bugfix dimensions [@timoj](https://github.com/timoj)

### Added

- Added default image setting (base64) [@timoj](https://github.com/timoj)


## [2.4.7](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/releases/tag/2.4.7) Pre-release - 2018-9-7

### Fix

- Bugfix escaping quotes js func parameter [@timoj](https://github.com/timoj)


## [2.4.6](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/releases/tag/2.4.6) Pre-release - 2018-9-7

### Added

- Bugfix company name [@timoj](https://github.com/timoj)


## [2.4.5](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/releases/tag/2.4.5) Pre-release - 2018-9-5

### Fix

- Bugfix parcelshoppicker js [@timoj](https://github.com/timoj)


## [2.4.4](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/releases/tag/2.4.4) Pre-release - 2018-8-15

### Added

- Logging [@timoj](https://github.com/timoj)
- Filesize check for images [@timoj](https://github.com/timoj)


## [2.4.3](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/releases/tag/2.4.3) Pre-release - 2018-8-15

### Added

- Added temp logging [@timoj](https://github.com/timoj)
- Added different handler for checking image filesize [@timoj](https://github.com/timoj)

## [2.4.2](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/releases/tag/2.4.2) Pre-release - 2018-8-02

### Added

- Added free from for parcelshop shipping method [@timoj](https://github.com/timoj)


## [2.4.1](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/releases/tag/2.4.1) Pre-release - 2018-7-30

### Fix

- Added bugfixes parcelshop picker [@timoj](https://github.com/timoj)


## [2.4.0](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/releases/tag/2.4.0) Pre-release - 2018-06-08

### Added

This is a test release. Not ready for production.

- Added parcelshop functionality [@timoj](https://github.com/timoj)


## [2.3.1](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/releases/tag/2.3.1) Pre-release - 2018-03-27


### Fix

- Fixed warning with building api data [@timoj](https://github.com/timoj)


## [2.3.0](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/releases/tag/2.3.0) Pre-release - 2018-03-27

### Added

- Added support for 2nd webhook [@timoj](https://github.com/timoj)

### Fix

-Fixed api domain [@timoj](https://github.com/timoj)

## Released


## Old Change Log Format

## [2.2.2](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tree/2.2.2) (2017-12-28)
[Full Changelog](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/compare/2.2.1...2.2.2)

## [2.2.1](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tree/2.2.1) (2017-12-28)
[Full Changelog](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/compare/2.2.0...2.2.1)

## [2.2.0](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tree/2.2.0) (2017-12-27)
[Full Changelog](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/compare/2.1.2...2.2.0)

**Merged pull requests:**

- cleanup [\#3](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/pull/3) ([timoj](https://github.com/timoj))

## [2.1.2](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tree/2.1.2) (2017-11-20)
[Full Changelog](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/compare/2.1.1...2.1.2)

## [2.1.1](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tree/2.1.1) (2017-10-27)
[Full Changelog](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/compare/2.1.0...2.1.1)

## [2.1.0](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tree/2.1.0) (2017-10-03)
[Full Changelog](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/compare/2.0.7...2.1.0)

**Merged pull requests:**

- Preferred service level & post booking order status [\#2](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/pull/2) ([timoj](https://github.com/timoj))

## [2.0.7](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tree/2.0.7) (2017-09-21)
[Full Changelog](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/compare/2.0.6...2.0.7)

## [2.0.6](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tree/2.0.6) (2017-09-20)
[Full Changelog](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/compare/2.0.5...2.0.6)

## [2.0.5](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tree/2.0.5) (2017-09-19)
[Full Changelog](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/compare/2.0.4...2.0.5)

## [2.0.4](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tree/2.0.4) (2017-08-28)
[Full Changelog](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/compare/2.0.3...2.0.4)

## [2.0.3](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tree/2.0.3) (2017-07-07)
[Full Changelog](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/compare/2.0.2...2.0.3)

## [2.0.2](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tree/2.0.2) (2017-07-04)
[Full Changelog](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/compare/2.0.1...2.0.2)

## [2.0.1](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tree/2.0.1) (2017-06-19)
[Full Changelog](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/compare/2.0.0...2.0.1)

## [2.0.0](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tree/2.0.0) (2017-06-15)
[Full Changelog](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/compare/1.0.1...2.0.0)

## [1.0.1](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tree/1.0.1) (2017-06-07)
[Full Changelog](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/compare/1.0...1.0.1)

## [1.0](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tree/1.0) (2017-06-07)
[Full Changelog](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/compare/0.9...1.0)

## [0.9](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tree/0.9) (2017-06-01)
[Full Changelog](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/compare/0.8...0.9)

## [0.8](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tree/0.8) (2017-05-11)
[Full Changelog](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/compare/0.7...0.8)

**Merged pull requests:**

- Dimension/weight fix [\#1](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/pull/1) ([riklempens](https://github.com/riklempens))

## [0.7](https://github.com/kabisa/wuunder-webshopplugin-woocommerce/tree/0.7) (2017-05-09)


\* *This Change Log was automatically generated by [github_changelog_generator](https://github.com/skywinder/Github-Changelog-Generator)*
