# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 3.7.0 - 2022-12-10


-----

### Release Notes for [3.7.0](https://github.com/Roave/psr-container-doctrine/milestone/24)

Feature release (minor)

### 3.7.0

- Total issues resolved: **0**
- Total pull requests resolved: **3**
- Total contributors: **2**

#### enhancement

 - [89: Support PHP 8.2, drop support for PHP 8.0, upgrade to vimeo/psalm v5](https://github.com/Roave/psr-container-doctrine/pull/89) thanks to @internalsystemerror

#### duplicate,enhancement,renovate

 - [88: Update dependency php to ~8.0.0 || ~8.1.0 || ~8.2.0](https://github.com/Roave/psr-container-doctrine/pull/88) thanks to @renovate[bot]
 - [87: Update dependency vimeo/psalm to v5](https://github.com/Roave/psr-container-doctrine/pull/87) thanks to @renovate[bot]

## 3.6.0 - 2022-10-31


-----

### Release Notes for [3.6.0](https://github.com/Roave/psr-container-doctrine/milestone/22)

Feature release (minor)

### 3.6.0

- Total issues resolved: **0**
- Total pull requests resolved: **6**
- Total contributors: **2**

#### enhancement,renovate

 - [84: Update dependency psr/cache to allow installation of v1, v2 and v3](https://github.com/Roave/psr-container-doctrine/pull/84) thanks to @renovate[bot]

#### renovate

 - [79: Update actions/checkout action to v3](https://github.com/Roave/psr-container-doctrine/pull/79) thanks to @renovate[bot]
 - [78: Update actions/cache action to v3](https://github.com/Roave/psr-container-doctrine/pull/78) thanks to @renovate[bot]
 - [77: Update all non-major dependencies](https://github.com/Roave/psr-container-doctrine/pull/77) thanks to @renovate[bot]

#### enhancement

 - [76: Configure Renovate, drop PHP 7 support](https://github.com/Roave/psr-container-doctrine/pull/76) thanks to @renovate[bot]

#### bug,documentation

 - [73: Fix typo](https://github.com/Roave/psr-container-doctrine/pull/73) thanks to @garygitton

## 3.5.0 - 2022-03-08


-----

### Release Notes for [3.5.0](https://github.com/Roave/psr-container-doctrine/milestone/19)

Feature release (minor)

### 3.5.0

- Total issues resolved: **1**
- Total pull requests resolved: **3**
- Total contributors: **4**

#### enhancement

 - [68: &#91;Feature&#93; Add support for middlewares](https://github.com/Roave/psr-container-doctrine/pull/68) thanks to @samuelnogueira

#### bug,enhancement

 - [67: Add missing dependencies, configure `maglnet/composer-require-checker`](https://github.com/Roave/psr-container-doctrine/pull/67) thanks to @Gared

#### bug

 - [66: Merge release 3.4.1 into 3.5.x](https://github.com/Roave/psr-container-doctrine/pull/66) thanks to @github-actions[bot]

#### bug,help wanted

 - [51: DriverFactory.php incompatible with doctrine/common: &quot;^3.0&quot;](https://github.com/Roave/psr-container-doctrine/issues/51) thanks to @FBurner

## 3.4.0 - 2022-02-15


-----

### Release Notes for [3.4.0](https://github.com/Roave/psr-container-doctrine/milestone/17)

Feature release (minor)

### 3.4.0

- Total issues resolved: **1**
- Total pull requests resolved: **5**
- Total contributors: **3**

#### enhancement

 - [64: Tighten up php compatibility](https://github.com/Roave/psr-container-doctrine/pull/64) thanks to @boesing and @Ocramius
 - [63: PSR-6 cache support](https://github.com/Roave/psr-container-doctrine/pull/63) thanks to @boesing
 - [62: Convert `phpunit/phpunit` assertion method calls to static calls](https://github.com/Roave/psr-container-doctrine/pull/62) thanks to @boesing
 - [61: Bump `vimeo/psalm` to 4.20](https://github.com/Roave/psr-container-doctrine/pull/61) thanks to @boesing
 - [57: Use a stricter range for PHP version in composer.json](https://github.com/Roave/psr-container-doctrine/pull/57) thanks to @edigu and @Ocramius

## 3.3.0 - 2022-01-21


-----

### Release Notes for [3.3.0](https://github.com/Roave/psr-container-doctrine/milestone/15)

Feature release (minor)

### 3.3.0

- Total issues resolved: **0**
- Total pull requests resolved: **1**
- Total contributors: **1**

#### enhancement

 - [55: Allow `psr/container:^2`](https://github.com/Roave/psr-container-doctrine/pull/55) thanks to @snapshotpl

## 3.2.0 - 2022-01-20


-----

### Release Notes for [3.2.0](https://github.com/Roave/psr-container-doctrine/milestone/12)

Feature release (minor)

### 3.2.0

- Total issues resolved: **0**
- Total pull requests resolved: **2**
- Total contributors: **2**

#### enhancement

 - [54: Test against PHP 8.1](https://github.com/Roave/psr-container-doctrine/pull/54) thanks to @snapshotpl

#### bug

 - [53: Merge release 3.1.1 into 3.2.x](https://github.com/Roave/psr-container-doctrine/pull/53) thanks to @github-actions[bot]

## 3.1.0 - 2021-09-09


-----

### Release Notes for [3.1.0](https://github.com/Roave/psr-container-doctrine/milestone/9)

Feature release (minor)

### 3.1.0

- Total issues resolved: **0**
- Total pull requests resolved: **3**
- Total contributors: **3**

#### enhancement

 - [50: Declare hard dependency on doctrine/cache](https://github.com/Roave/psr-container-doctrine/pull/50) thanks to @PowerKiKi
 - [48: Fix namespace of MappingDriverChain in example](https://github.com/Roave/psr-container-doctrine/pull/48) thanks to @rieschl

#### bug

 - [43: README.md: fix class reference](https://github.com/Roave/psr-container-doctrine/pull/43) thanks to @Slamdunk

## 2.1.0 - TBD

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.0.0 - 2020-02-10

### Added

- [#1](https://github.com/roave/psr-container-doctrine/pull/1) Namespace changed from `ContainerInteropDoctrine\*` to `Roave\PsrContainerDoctrine\*` (thanks @asgrim)
  - Note, a shim exists in `bc-namespace-shim.php` so previous namespace will still work. This will be removed in `3.0.0`.
- [DASPRiD#44](https://github.com/DASPRiD/container-interop-doctrine/pull/44) Added support for PhpFileCache (thanks @byan)
- [DASPRiD#43](https://github.com/DASPRiD/container-interop-doctrine/pull/43) Added support for Event Listeners configuration (thanks @daniel-braga)
- [DASPRiD#41](https://github.com/DASPRiD/container-interop-doctrine/pull/41) Added support for setting default driver when using MappingDriverChain (thanks @tobias-trozowski)

### Changed

- [#1](https://github.com/roave/psr-container-doctrine/pull/1) PHP 7.3+ now required. Strict types were added throughout. (thanks @asgrim)
- [#1](https://github.com/roave/psr-container-doctrine/pull/1) Namespace for caches from `CacheFactory` changed to `psr-container-doctrine` (thanks @asgrim)
- [#6](https://github.com/roave/psr-container-doctrine/pull/6) Made AbstractFactory `@internal` and inheritors are now `final` (thanks @asgrim)
- [#11](https://github.com/roave/psr-container-doctrine/pull/11) Simplified driver class check (thanks @edigu)
- [#12](https://github.com/roave/psr-container-doctrine/pull/12) Replaced Prophecy with PHPUnit (thanks @edigu)
- [#14](https://github.com/roave/psr-container-doctrine/pull/14) Improved test coverage, made exceptions `final` (thanks @edigu)
- [DASPRiD#46](https://github.com/DASPRiD/container-interop-doctrine/pull/46) Changed Zend to Laminas (thanks @edigu)

### Deprecated

- Nothing.

### Removed

- [#9](https://github.com/roave/psr-container-doctrine/pull/9) Removed support for `XcacheCache`, `MemcacheCache`, Doctrine namespace updates (thanks @edigu / @asgrim)

### Fixed

- [DASPRiD#42](https://github.com/DASPRiD/container-interop-doctrine/pull/42) Fixed false positive in test (thanks @tobias-trozowski)
