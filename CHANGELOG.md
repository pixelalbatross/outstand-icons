# Changelog

All notable changes to this project will be documented in this file, per [the Keep a Changelog standard](http://keepachangelog.com/).

## [Unreleased]

## [1.0.1] - 2026-07-10

### Changed

- Renamed plugin constants from `OS_ICONS_*` to `OUTSTAND_ICONS_*` to match the Outstand naming convention.
- Renamed the rich-text format from `os-icons/inline-icon` to `outstand/inline-icon` and the script handles from `os-icons-*` to `outstand-*`. The stored `os-icons-inline` marker class is unchanged, so already-saved content is unaffected.
- Guarded the update checker bootstrap with a `class_exists()` check.
- Documented the WordPress 7.0 requirement (`WP_Icons_Registry`) in the README.

## [1.0.0] - 2026-05-20

- Initial release.
