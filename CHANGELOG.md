# Changelog

## [2.7.1]
- improvement: added info text to 'base directory' input
- Fix: fixed tree picker ('Choose folder')
- Fix: prevent fatal on reinstall
- Fix: prevent 400 error on creating folders

## [2.7.0]
- Feature: Share API (for 'edit in collaboration app') compatability with basic auth

## [2.6.4]
- Fix Table Lock Fatal Error

## [2.6.3]
- Fix 401 error when opening document in collaboration app

## [2.6.2]
- Fix "Not Authenticated - Offline" bug (race condition on token refresh)

## [2.6.1]
- Fix PHP 7.0

## [2.6.0]
- Create folder
- Fix 3 party library conflict multiple autoload

## [2.5.0]
- ILIAS 6 support
- Fix Docker-ILIAS log

## [2.4.1]
* User HTTP/1.1 instead of HTTP/1.0

## [2.4.0]
* feature: choose base directory on creation (default configurable)

## [2.3.2]
* improvement/feature: secondary email available for user mapping

## [2.3.1]
* bugfix: grant write permissions when opening in Collaboration App (not only read)
* bugfix: update permissions if share already exists
* bugfix/change: "Edit in OwnCloud" now checks "Upload" Permission
* improvement/feature: collaboration app - supported formats configurable

## [2.3.0]
* feature: open & edit documents in OwnCloud Collaboration App (e.g. OnlyOffice)

## [2.2.2]
* bugfix: fixed tree picker ('Choose folder')

## [2.2.1]
* bugfix: fixed tree picker ('Choose folder')
* mini feature: tree picker shows loading icon

## [2.2.0]
* supported ilias versions: 5.4.x
* updated library league/oauth2-client to v2.4.1
* removed library sabreDAV (now integrated in ILIAS core)

## [2.1.4]
* supported ilias versions: 5.2.x - 5.3.x
