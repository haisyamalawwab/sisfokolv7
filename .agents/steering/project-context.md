---
inclusion: always
---

# SISFOKOL Project Context — Agent Steering

> **Baca ini PERTAMA sebelum mulai bekerja di proyek ini.**
> Berlaku untuk semua agent: Antigravity, Kiro, Opencode, Zcode.

## Identitas Proyek

```
Nama       : SISFOKOL Laravel 11
Tipe       : Domain-Modular Monolith (bukan microservices)
Goal       : Konversi SISFOKOL v7 (PHP native) → Laravel 11
Root       : D:\laragon\www\sisfokolv7\
Laravel app: D:\laragon\www\sisfokolv7\sisfokol-laravel\
DB target  : sisfokol_laravel (MySQL 8, InnoDB)
DB legacy  : sisfokol_v7 (MySQL, MyISAM, READ-ONLY — ETL source)
```

## PHP Execution Rule (KRITIS)

```powershell
# SELALU gunakan php83, BUKAN php
php83 artisan <command>
php83 D:\composer\composer.phar <command>

# php default = 8.2.30 → GAGAL (requirement ^8.2 tapi security advisory)
# php83 = 8.3.31 → OK
```

## Memory System (DEV_DOCS)

Semua keputusan, status, dan handoff tersimpan di `DEV_DOCS/`:

| File | Isi |
|------|-----|
| `DEV_DOCS/012_implementation.md` | **STATUS TERKINI** — baca ini dulu |
| `DEV_DOCS/011_handover_final_design_selesai_*.md` | Design phase handover |
| `DEV_DOCS/009_bagian5_core_modules_etl_*.md` | ETL + module detail |
| `DEV_DOCS/010_bagian6_folder_structure_*.md` | Folder structure + tech stack |

## Implementation Plans

Tersimpan di `DOCS/superpowers/plans/` (12 epic files):
- `epic-1` → Setup + Fondasi ← **SEDANG DIKERJAKAN**
- `epic-2` → Auth Module
- `epic-3` → RBAC Builder + Field ACL
- `epic-4` → Plugin Infrastructure
- `epic-5` → Academic (11 tables)
- `epic-6` → Evaluation (7 tables)
- `epic-7` → Finance (5 tables, kritis)
- `epic-8` → Presence (3 tables)
- `epic-9` → Plugin Kurikulum (referensi penuh)
- `epic-10` → 8 Plugin Scaffold
- `epic-11` → ETL Pipeline (20 steps)
- `epic-12` → Testing + Deployment

## Aturan Wajib (dari Karpathy Guidelines)

1. **Backup sebelum edit** → `backups/<tipe>/<nama>.bak_YYYYMMDD`
2. **Verify sebelum execute** perubahan > 50 baris
3. **Jangan refactor** kode yang tidak rusak
4. **Simplicity first** — minimum code
5. **BelongsToTenant trait wajib** di semua model domain
6. **DB::transaction + lockForUpdate()** di PembayaranService
7. **Tulis DEV_DOCS baru** setiap sesi panjang / keputusan penting

## Agent Folders

| Agent | Folder |
|-------|--------|
| Kiro (kiro.dev) | `.kiro/` (skills, steering, workflows) |
| Antigravity (Google DeepMind) | `.agents/` (steering, skills, workflows) |
| Opencode | `.agents/` (shared) |
| Zcode | `.zcode/` |
