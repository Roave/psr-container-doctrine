# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.0.0 - TBD

### Added

- [#1](https://github.com/roave/psr-container-doctrine/pull/1) Namespace changed from `ContainerInteropDoctrine\*` to `Roave\PsrContainerDoctrine\*`
  - Note, a shim exists in `bc-namespace-shim.php` so previous namespace will still work. This will be removed in `3.0.0`.

### Changed

- [#1](https://github.com/roave/psr-container-doctrine/pull/1) PHP 7.3+ now required. Strict types were added throughout.
- [#1](https://github.com/roave/psr-container-doctrine/pull/1) Namespace for caches from `CacheFactory` changed to `psr-container-doctrine`
- [#6](https://github.com/roave/psr-container-doctrine/pull/6) Made AbstractFactory `@internal` and inheritors are now `final`

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
