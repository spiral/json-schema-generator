# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](https://semver.org/).

---

## [2.1.2](https://github.com/spiral/json-schema-generator/compare/v2.1.1...v2.1.2) (2026-11-16)


### Bug Fixes

* apply Pattern, Range and Length only to compatible types ([#56](https://github.com/spiral/json-schema-generator/issues/56)) ([acaf86f](https://github.com/spiral/json-schema-generator/commit/acaf86fbe2027aeebf9a0ad6553b919b7e3cefc8))
* ignore tuple array shapes instead of throwing TypeError ([#44](https://github.com/spiral/json-schema-generator/issues/44)) ([dbd3e67](https://github.com/spiral/json-schema-generator/commit/dbd3e67cefa87a2e7258d0574764a88d6bbc6908))
* keep native nullability when the PHPDoc type omits null ([#52](https://github.com/spiral/json-schema-generator/issues/52)) ([5ebec21](https://github.com/spiral/json-schema-generator/commit/5ebec21efad15f5869a8d000360a5a386c4a1a96))
* render unconstrained properties as {} instead of [] ([#53](https://github.com/spiral/json-schema-generator/issues/53)) ([4d2035f](https://github.com/spiral/json-schema-generator/commit/4d2035fa81bfe3632d3aeacf1415879c5dc8c11d))
* serialize an empty default of an object schema as {} ([#54](https://github.com/spiral/json-schema-generator/issues/54)) ([45bb74d](https://github.com/spiral/json-schema-generator/commit/45bb74d72b9f35c243a8a7dfe3da62979048f620))
* stop non-positive-int and non-negative-int from matching their shorter counterparts ([#41](https://github.com/spiral/json-schema-generator/issues/41)) ([8f03625](https://github.com/spiral/json-schema-generator/commit/8f03625ac89407db4fab251a49895b022b138113))


### Documentation

* fix README schema examples that drifted from the generator output ([dc5bbe5](https://github.com/spiral/json-schema-generator/commit/dc5bbe582cbce379888e70955ab28583c64d540a))
* restructure README header and badges ([#47](https://github.com/spiral/json-schema-generator/issues/47)) ([ff9373f](https://github.com/spiral/json-schema-generator/commit/ff9373fd327ec9a8612294c3606b851abc2f7357))


### Styles

* **php-cs-fixer:** fix coding standards ([87c055f](https://github.com/spiral/json-schema-generator/commit/87c055f3f6f5652e0357b751f26a6b107866f63a))


### Dependencies

* require symfony/type-info explicitly ([#45](https://github.com/spiral/json-schema-generator/issues/45)) ([2cf1692](https://github.com/spiral/json-schema-generator/commit/2cf1692e9bad3d1d291885fe8ffec0b9dc5c2d92))


### Code Refactoring

* make Psalm level 1 pass without a baseline ([#43](https://github.com/spiral/json-schema-generator/issues/43)) ([f473a19](https://github.com/spiral/json-schema-generator/commit/f473a19d0d1bc2f9034c4cd444b0b2c1a7aa9e5f))


### Tests

* check README examples in an Acceptance suite ([#51](https://github.com/spiral/json-schema-generator/issues/51)) ([dc5bbe5](https://github.com/spiral/json-schema-generator/commit/dc5bbe582cbce379888e70955ab28583c64d540a))
* cover PHPDoc constraints, transitive definitions and property filtering ([#39](https://github.com/spiral/json-schema-generator/issues/39)) ([77cc74a](https://github.com/spiral/json-schema-generator/commit/77cc74a66a21d9e2671e02e368aa4fa3a52d22ae))
* migrate from PHPUnit to Testo ([#29](https://github.com/spiral/json-schema-generator/issues/29)) ([2405eec](https://github.com/spiral/json-schema-generator/commit/2405eeccfcf32e81cf34b27c0947f809bc3ba8ea))


### Continuous Integration

* add composer audit workflow ([#33](https://github.com/spiral/json-schema-generator/issues/33)) ([134f5d0](https://github.com/spiral/json-schema-generator/commit/134f5d043fa8a4550454e26d07e159853075cc79))
* install spiral/code-style as a bamarni-bin tool ([#34](https://github.com/spiral/json-schema-generator/issues/34)) ([86523b1](https://github.com/spiral/json-schema-generator/commit/86523b13fd27553fd1af165fd090116bb8167a9c))
* run cs-fix on the minimum supported PHP ([#27](https://github.com/spiral/json-schema-generator/issues/27)) ([9fffe9f](https://github.com/spiral/json-schema-generator/commit/9fffe9f82ad1ca6b053f322fb00ebfad3662634f))
* run mutation tests with Infection ([#40](https://github.com/spiral/json-schema-generator/issues/40)) ([58b979d](https://github.com/spiral/json-schema-generator/commit/58b979df09462dc8e6b8e357acccf7a6da5788ed))
* tighten workflow triggers and permissions ([#35](https://github.com/spiral/json-schema-generator/issues/35)) ([decd918](https://github.com/spiral/json-schema-generator/commit/decd9184ad58cf7b861bf12b270f0d82e2088f81))
* upload coverage to Codecov ([77cc74a](https://github.com/spiral/json-schema-generator/commit/77cc74a66a21d9e2671e02e368aa4fa3a52d22ae))

## [2.1.1](https://github.com/spiral/json-schema-generator/compare/v2.1.0...v2.1.1) (2026-08-23)


### Dependencies

* allow phpdocumentor/reflection-docblock 6.0 ([#23](https://github.com/spiral/json-schema-generator/issues/23)) ([1ef3885](https://github.com/spiral/json-schema-generator/commit/1ef3885956e4c5fe7840b8da978343ee7766df3f))


### Continuous Integration

* setup release-please ([9dc3643](https://github.com/spiral/json-schema-generator/commit/9dc3643259d96da006a8814cac3165a6909579a7))

## [1.3.0] – 2025-07-21

### Added
- Support for nullable enums.
- Better property definition accuracy.

### Fixed
- Psalm warnings for Symfony 7.3.

---

## [1.2.0] – 2025-06-02

### Added
- `format` parameter added to the `Field` attribute.

---

## [1.1.1] – 2025-04-29

### Changed
- Upgraded `phpdoc-parser` dependency for improved PHPDoc handling.

---

## [1.1.0] – 2023-12-06

### Added
- Compatibility with `symfony/property-info` v7.0.

### Fixed
- `.gitattributes` configuration.

---

## [1.0.0] – 2023-11-26

### Added
- Initial release.
- JSON Schema generator for PHP.
- Primary use case: structured output generation for LLM-based systems.

### Features
- PHP native type support.
- Nested objects and list (array) support.
- Psalm type annotations support.
- Custom metadata via PHP attributes.
- Enum support.

## [2.0.0] – Unreleased

### Added
- Compatibility with Symfony 7.2 and newer.

> **Note**
> This version takes advantage of updated type system features and is intended for use with modern Symfony applications.
