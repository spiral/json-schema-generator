# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](https://semver.org/).

---

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

