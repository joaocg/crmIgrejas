# Database Map

## Source Files

- `propel/main.schema.xml`
- `src/mysql/install/Install.sql`
- `src/EcclesiaCRM/model/*`

## What Was Confirmed

- The legacy system is a wide EcclesiaCRM schema with person, family, group, event, finance, notes, pastoral care, privacy, plugin, kiosk, and calendar-sync domains.
- The schema contains many legacy tables that are not part of the normalized Laravel model target.
- The Propel schema is the primary source of truth, and the SQL install dump is treated as a consistency check.

## Domain Classification

- Identity: `user_usr`, `userrole_usrrol`, `userconfig_ucfg`, `config_cfg`
- People and families: `person_per`, `family_fam`, `note_nte`, `pastoral_care`, `gdpr_infos`
- Groups and volunteers: `group_grp`, `group_type`, `groupmembers`, `groupprop_master`, `person2group2role_p2g2r`, `volunteeropportunity_vol`, `person2volunteeropp_p2vo`
- Events and attendance: `events_event`, `event_attend`, `event_types`, `eventcountnames_evctnm`, `eventcounts_evtcnt`
- Finance: `pledge_plg`, `deposit_dep`, `donationfund_fun`, `autopayment_aut`, `canvassdata_can`, `egive_egv`, `fundraiser_fr`, `donateditem_di`, `multibuy_mb`, `paddlenum_pn`
- Reference and custom fields: `list_lst`, `list_icon`, `property_pro`, `propertytype_prt`, `record2property_r2p`
- Calendar and sync: `addressbooks`, `addressbookshare`, `addressbookchanges`, `calendars`, `calendarinstances`, `calendarsubscriptions`, `calendarchanges`, `cards`, `collections`, `collectionsinstances`, `principals`, `propertystorage`, `schedulingobjects`, `tokens`, `tokens_password`
- Communications and UI helpers: `email_message_pending_emp`, `email_recipient_pending_erp`, `menu_links`, `ckeditor_templates`, `send_news_letter_user_update`
- Operations and plugins: `plugin`, `plugin_dependencies`, `plugin_menu_bar`, `plugin_user_role`, `query_qry`, `queryparameters_qrp`, `queryparameteroptions_qpo`, `result_res`
- Kiosk: `kioskdevice_kdev`, `kioskassginment_kasm`

## Normalization Targets

The new Laravel project will keep all table, module, class, and migration names in English. Legacy names only remain in this inventory and in import mapping code.

### Core tables to create first

- `tenants`
- `users`
- `roles`
- `persons`
- `families`
- `addresses`
- `groups`
- `group_memberships`
- `events`
- `event_attendances`
- `donation_funds`
- `pledges`
- `deposits`
- `notes`
- `pastoral_care_records`
- `module_definitions`
- `module_settings`

### Immediate cleanup notes

- `user_usr` currently mixes auth, permissions, calendar preferences, webdav secrets, jwt fields, and feature flags.
- `person_per` and `family_fam` carry both entity data and audit/contact/address details.
- `pledge_plg`, `deposit_dep`, and `autopayment_aut` are tightly coupled and should be split into normalized finance entities.
- The legacy calendar/address book tables should only be carried forward if the new product still needs CalDAV/CardDAV compatibility.

### Baseline already in place

- `tenants`, `users`, `roles`
- `addresses`, `families`, `persons`
- `module_definitions`, `module_settings`
- `groups`, `group_memberships`, `events`, `event_attendances`
- `notes`, `pastoral_care_records`
- `personal_access_tokens` for Sanctum bearer auth
